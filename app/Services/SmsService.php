<?php

namespace App\Services;

use App\Models\Sms;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * SMS Service - Handles SMS notifications via Semaphore/Twilio gateways
 * 
 * Usage:
 *   SmsService::queue('09171234567', 'Your payment was received', 'client');
 *   SmsService::sendPending(); // Process queue via cron
 */
class SmsService
{
    // Status constants
    const STATUS_PENDING = 0;
    const STATUS_SENT = 1;
    const STATUS_FAILED = 2;

    // Gateway constants
    const GATEWAY_SEMAPHORE = 'semaphore';
    const GATEWAY_TWILIO = 'twilio';

    /**
     * Queue an SMS message for sending
     *
     * @param string $contactno Phone number (09XXXXXXXXX format)
     * @param string $message Message content (max 160 chars for single SMS)
     * @param string $sendto Recipient type ('client', 'agent', 'admin')
     * @param string|null $referenceType Optional reference type ('payment', 'loan', 'spotcash')
     * @param int|null $referenceId Optional reference ID
     * @return Sms
     */
    public static function queue(
        ?string $contactno,
        string $message,
        string $sendto = 'client',
        ?string $referenceType = null,
        ?int $referenceId = null
    ): ?Sms {
        // Skip if no contact number
        if (!$contactno) {
            return null;
        }

        // Format phone number (ensure 09 prefix)
        $contactno = self::formatPhone($contactno);

        return Sms::create([
            'ContactNo' => $contactno,
            'Message' => $message,
            'SendTo' => $sendto,
            'Status' => self::STATUS_PENDING,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
        ]);
    }

    /**
     * Send all pending SMS messages
     *
     * @param int $limit Max messages to send per batch
     * @return array ['sent' => int, 'failed' => int]
     */
    public static function sendPending(int $limit = 30): array
    {
        $result = ['sent' => 0, 'failed' => 0];

        $messages = Sms::where('Status', self::STATUS_PENDING)
            ->limit($limit)
            ->get();

        foreach ($messages as $sms) {
            if (self::send($sms)) {
                $result['sent']++;
            } else {
                $result['failed']++;
            }
        }

        return $result;
    }

    /**
     * Send a single SMS message via configured gateway
     *
     * @param Sms $sms
     * @return bool
     */
    public static function send(Sms $sms): bool
    {
        $gateway = config('sms.gateway', self::GATEWAY_SEMAPHORE);

        try {
            if ($gateway === self::GATEWAY_SEMAPHORE) {
                $response = self::sendViaSemaphore($sms);
            } else {
                $response = self::sendViaTwilio($sms);
            }

            if ($response['success']) {
                $sms->update([
                    'Status' => self::STATUS_SENT,
                    'gateway_response' => json_encode($response['data']),
                    'sent_at' => now(),
                ]);
                return true;
            } else {
                $sms->update([
                    'Status' => self::STATUS_FAILED,
                    'gateway_response' => json_encode($response['error']),
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('SMS send error', [
                'sms_id' => $sms->id,
                'error' => $e->getMessage(),
            ]);

            $sms->update([
                'Status' => self::STATUS_FAILED,
                'gateway_response' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Send via Semaphore API
     *
     * @param Sms $sms
     * @return array ['success' => bool, 'data' => array|null, 'error' => string|null]
     */
    protected static function sendViaSemaphore(Sms $sms): array
    {
        $apiKey = config('sms.semaphore.api_key');
        $senderName = config('sms.semaphore.sender_name', 'SureLife');
        $usePriority = config('sms.semaphore.priority', true); // Use priority by default (no rate limit)

        if (!$apiKey) {
            return [
                'success' => false,
                'error' => 'Semaphore API key not configured',
            ];
        }

        // Priority endpoint has NO rate limit (2 credits/SMS)
        // Regular endpoint has 120 req/min limit (1 credit/SMS)
        $endpoint = $usePriority 
            ? 'https://api.semaphore.co/api/v4/priority'
            : 'https://api.semaphore.co/api/v4/messages';

        $response = Http::asForm()->post($endpoint, [
            'apikey' => $apiKey,
            'number' => $sms->ContactNo,
            'message' => $sms->Message,
            'sendername' => $senderName,
        ]);

        if ($response->successful()) {
            return [
                'success' => true,
                'data' => $response->json(),
            ];
        }

        return [
            'success' => false,
            'error' => $response->body(),
        ];
    }

    /**
     * Send via Twilio API
     *
     * @param Sms $sms
     * @return array ['success' => bool, 'data' => array|null, 'error' => string|null]
     */
    protected static function sendViaTwilio(Sms $sms): array
    {
        $sid = config('sms.twilio.account_sid');
        $token = config('sms.twilio.auth_token');
        $from = config('sms.twilio.from_number');

        if (!$sid || !$token || !$from) {
            return [
                'success' => false,
                'error' => 'Twilio credentials not configured',
            ];
        }

        // Format to E.164 for Twilio (+63XXXXXXXXXX)
        $to = '+63' . substr($sms->ContactNo, 1);

        $response = Http::withBasicAuth($sid, $token)
            ->asForm()
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                'From' => $from,
                'To' => $to,
                'Body' => $sms->Message,
            ]);

        if ($response->successful()) {
            return [
                'success' => true,
                'data' => $response->json(),
            ];
        }

        return [
            'success' => false,
            'error' => $response->body(),
        ];
    }

    /**
     * Format phone number to 09XXXXXXXXX format
     *
     * @param string $phone
     * @return string
     */
    protected static function formatPhone(string $phone): string
    {
        // Remove non-digits
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // If starts with +63, convert to 0
        if (str_starts_with($phone, '63')) {
            $phone = '0' . substr($phone, 2);
        }

        // Ensure starts with 0
        if (!str_starts_with($phone, '0')) {
            $phone = '0' . $phone;
        }

        return $phone;
    }

    /**
     * Send payment confirmation SMS
     *
     * @param object $client Client object with phoneno
     * @param object $payment Payment object with amount, date
     * @param string|null $nextDue Next due date (optional)
     * @return Sms|null
     */
    public static function sendPaymentConfirmation($client, $payment, ?string $nextDue = null): ?Sms
    {
        if (!config('sms.enabled', false)) {
            return null;
        }

        $template = config('sms.templates.payment_received', 
            'Thank you for your payment of P{amount} on {date}. - Surelife Care');

        $message = str_replace(
            ['{amount}', '{date}', '{next_due}'],
            [
                number_format($payment->amount, 2),
                $payment->date ?? now()->format('M d, Y'),
                $nextDue ?? 'N/A',
            ],
            $template
        );

        return self::queue(
            $client->phoneno,
            $message,
            'client',
            'payment',
            $payment->id ?? null
        );
    }

    /**
     * Send loan payment confirmation SMS
     *
     * @param object $client Client object with phoneno
     * @param object $payment Loan payment object
     * @param float $balance Remaining balance
     * @return Sms|null
     */
    public static function sendLoanPaymentConfirmation($client, $payment, float $balance): ?Sms
    {
        if (!config('sms.enabled', false)) {
            return null;
        }

        $template = config('sms.templates.loan_payment',
            'Loan payment of P{amount} received. Remaining balance: P{balance}. - Surelife Care');

        $message = str_replace(
            ['{amount}', '{balance}'],
            [
                number_format($payment->amount, 2),
                number_format($balance, 2),
            ],
            $template
        );

        $sms = self::queue(
            $client->phoneno ?? $client->MobileNumber ?? null,
            $message,
            'client',
            'loan',
            $payment->id ?? null
        );

        if ($sms) {
            self::send($sms);
        }

        return $sms;
    }

    /**
     * Send spot cash approval SMS
     *
     * @param object $client Client object with phoneno
     * @param object $spotcash Spot cash object
     * @return Sms|null
     */
    public static function sendSpotCashApproval($client, $spotcash): ?Sms
    {
        if (!config('sms.enabled', false)) {
            return null;
        }

        $template = config('sms.templates.spotcash_approved',
            'Spot cash payment of P{amount} approved. Thank you! - Surelife Care');

        $message = str_replace(
            ['{amount}'],
            [number_format($spotcash->amount ?? 0, 2)],
            $template
        );

        return self::queue(
            $client->phoneno,
            $message,
            'client',
            'spotcash',
            $spotcash->id ?? null
        );
    }

    /**
     * Send full payment completion SMS
     *
     * @param object $client Client object with phoneno
     * @return Sms|null
     */
    public static function sendFullPaymentCongratulations($client): ?Sms
    {
        if (!config('sms.enabled', false)) {
            return null;
        }

        $template = config('sms.templates.full_payment',
            'Congratulations! Your plan is now fully paid. Thank you for choosing Surelife Care!');

        return self::queue(
            $client->phoneno,
            $template,
            'client',
            'full_payment',
            null
        );
    }
}
