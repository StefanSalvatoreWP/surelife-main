<?php

namespace App\Console\Commands;

use App\Services\SmsService;
use Illuminate\Console\Command;

class SendSms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sms:send {--limit=30 : Maximum messages to send per batch}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send pending SMS messages from queue';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = (int) $this->option('limit');
        
        if (!config('sms.enabled', false)) {
            $this->error('SMS notifications are disabled. Set SMS_ENABLED=true in .env');
            return 1;
        }
        
        $this->info("Processing SMS queue (max {$limit} messages)...");
        
        $result = SmsService::sendPending($limit);
        
        $this->info("Sent: {$result['sent']}");
        $this->info("Failed: {$result['failed']}");
        
        if ($result['sent'] > 0 || $result['failed'] > 0) {
            $this->info('SMS processing complete.');
            return 0;
        }
        
        $this->info('No pending SMS messages found.');
        return 0;
    }
}
