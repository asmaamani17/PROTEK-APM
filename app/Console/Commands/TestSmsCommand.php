<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\TestSmsNotification;
use Illuminate\Console\Command;

class TestSmsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sms:test {phone? : The phone number to send the test SMS to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test SMS notification';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $phone = $this->argument('phone') ?? config('app.admin_phone');
        
        if (!$phone) {
            $this->error('No phone number provided and no default admin phone number is set.');
            $this->info('Please provide a phone number or set a default admin phone number in your .env file.');
            return 1;
        }
        
        try {
            $this->info("Starting test SMS to {$phone}...");
            
            // Output environment information
            $this->info("Environment: " . app()->environment());
            $this->info("Log channel: " . config('logging.default'));
            $this->info("Storage path: " . storage_path());
            
            // Check if storage/logs is writable
            $logPath = storage_path('logs');
            $isWritable = is_writable($logPath) ? 'writable' : 'NOT writable';
            $this->info("Log directory ({$logPath}): {$isWritable}");
            
            // Create a test user
            $user = new \App\Models\User([
                'no_telefon' => $phone,
                'name' => 'Test User',
            ]);
            
            $this->info("Created test user with phone: " . $user->no_telefon);
            
            // Directly test the SimpleSmsService
            $this->info("\nTesting SimpleSmsService directly...");
            $smsService = new \App\Services\SimpleSmsService();
            $testMessage = "Direct test SMS at " . now()->toDateTimeString();
            
            try {
                $result = $smsService->send($phone, $testMessage);
                $this->info("SimpleSmsService result: " . ($result ? 'SUCCESS' : 'FAILED'));
            } catch (\Exception $e) {
                $this->error("SimpleSmsService error: " . $e->getMessage());
                $this->error("Trace: " . $e->getTraceAsString());
            }
            
            // Test via notification
            $this->info("\nTesting via Notification...");
            try {
                $user->notify(new \App\Notifications\TestSmsNotification());
                $this->info("Notification sent successfully");
            } catch (\Exception $e) {
                $this->error("Notification error: " . $e->getMessage());
                $this->error("Trace: " . $e->getTraceAsString());
            }
            
            // Check if log file was created
            $logFile = storage_path('logs/sms.log');
            if (file_exists($logFile)) {
                $this->info("\nSMS log file exists at: " . $logFile);
                $this->info("Last 5 lines of SMS log:");
                $logContent = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                $lastLines = array_slice($logContent, -5);
                foreach ($lastLines as $line) {
                    $this->line($line);
                }
            } else {
                $this->warn("\nSMS log file not found at: " . $logFile);
                $this->info("Checking Laravel log for any messages...");
                
                $laravelLog = storage_path('logs/laravel.log');
                if (file_exists($laravelLog)) {
                    $lastLines = `tail -n 20 "{$laravelLog}"`;
                    $this->info("Last 20 lines of Laravel log:");
                    $this->line($lastLines);
                } else {
                    $this->warn("Laravel log file not found at: " . $laravelLog);
                }
            }
            
            $this->line("\nNote: SMS is currently in log mode. Check the logs above for details.");
            $this->line("To send real SMS, configure an SMS provider in your .env file.");
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("Test failed: " . $e->getMessage());
            $this->error("File: " . $e->getFile() . ":" . $e->getLine());
            $this->error("Trace: " . $e->getTraceAsString());
            return 1;
        }
    }
}
