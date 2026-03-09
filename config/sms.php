<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SMS Gateway Configuration
    |--------------------------------------------------------------------------
    |
    | Configure your SMS gateway provider and credentials.
    | Supported gateways: 'semaphore', 'twilio'
    |
    */

    // Enable/disable SMS notifications globally
    'enabled' => env('SMS_ENABLED', false),

    // Default gateway: 'semaphore' (Philippines) or 'twilio' (International)
    'gateway' => env('SMS_GATEWAY', 'semaphore'),

    /*
    |--------------------------------------------------------------------------
    | Semaphore Configuration (Philippines)
    |--------------------------------------------------------------------------
    | Get your API key from: https://semaphore.co/dashboard
    | Cost: ~0.50 PHP per SMS
    |
    */
    'semaphore' => [
        'api_key' => env('SEMAPHORE_API_KEY'),
        'sender_name' => env('SEMAPHORE_SENDER_NAME', 'SureLife'),
        'priority' => env('SEMAPHORE_USE_PRIORITY', true), // Use priority endpoint (NO rate limit, 2 credits/SMS)
        'endpoint' => 'https://api.semaphore.co/api/v4/priority', // No rate limit
        'endpoint_regular' => 'https://api.semaphore.co/api/v4/messages', // 120 req/min limit
    ],

    /*
    |--------------------------------------------------------------------------
    | Twilio Configuration (International)
    |--------------------------------------------------------------------------
    | Get credentials from: https://www.twilio.com/console
    | Cost: ~0.05 USD per SMS
    |
    */
    'twilio' => [
        'account_sid' => env('TWILIO_ACCOUNT_SID'),
        'auth_token' => env('TWILIO_AUTH_TOKEN'),
        'from_number' => env('TWILIO_FROM_NUMBER'),
    ],

    /*
    |--------------------------------------------------------------------------
    | SMS Templates
    |--------------------------------------------------------------------------
    | Available placeholders: {amount}, {date}, {next_due}, {balance}
    |
    */
    'templates' => [
        // Standard payment received
        'payment_received' => 'Thank you for your payment of P{amount} on {date}. Next due: {next_due}. - Surelife Care',

        // Loan payment received
        'loan_payment' => 'Loan payment of P{amount} received. Remaining balance: P{balance}. - Surelife Care',

        // Spot cash approved
        'spotcash_approved' => 'Spot cash payment of P{amount} approved. Thank you! - Surelife Care',

        // Full payment completed
        'full_payment' => 'Congratulations! Your plan is now fully paid. Thank you for choosing Surelife Care!',

        // Payment reminder (for scheduled reminders)
        'payment_reminder' => 'Your payment of P{amount} is due on {date}. Please pay to avoid late fees. - Surelife Care',

        // Overdue notice
        'payment_overdue' => 'Your payment of P{amount} is overdue. Please settle immediately. - Surelife Care',
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Settings
    |--------------------------------------------------------------------------
    |
    */
    'queue' => [
        // Max messages to send per batch (for sms:send command)
        'batch_size' => 30,

        // Retry failed messages after X minutes
        'retry_after_minutes' => 5,

        // Max retry attempts
        'max_retries' => 3,
    ],
];
