<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class SimpleSmsService
{
    /**
     * Send an SMS message.
     *
     * @param  string  $to
     * @param  string  $message
     * @return bool
     */
    public function send(string $to, string $message): bool
    {
        $logPrefix = "[SMS Service] ";
        
        try {
            $logMessage = $logPrefix . "Attempting to send SMS to: {$to}";
            error_log($logMessage); // Directly log to PHP error log
            
            // Validate phone number
            if (!$this->isValidPhoneNumber($to)) {
                $errorMsg = $logPrefix . "Invalid phone number: {$to}";
                error_log($errorMsg);
                throw new \InvalidArgumentException($errorMsg);
            }
            
            // Validate message
            if (empty(trim($message))) {
                $errorMsg = $logPrefix . "Message cannot be empty";
                error_log($errorMsg);
                throw new \InvalidArgumentException($errorMsg);
            }
            
            // Prepare log message
            $logMessage = $logPrefix . "Sending to: {$to} | Message: {$message}";
            
            // Log to PHP error log
            error_log($logMessage);
            
            // Try Laravel log
            try {
                \Illuminate\Support\Facades\Log::info($logMessage);
            } catch (\Exception $e) {
                error_log($logPrefix . "Failed to write to Laravel log: " . $e->getMessage());
            }
            
            // Try dedicated log file
            try {
                $logPath = storage_path('logs/sms.log');
                $logEntry = now()->toDateTimeString() . ' - ' . $logMessage . "\n";
                
                // Ensure the directory exists and is writable
                $logDir = dirname($logPath);
                if (!is_dir($logDir)) {
                    if (!mkdir($logDir, 0755, true)) {
                        throw new \RuntimeException("Failed to create log directory: {$logDir}");
                    }
                }
                
                if (file_put_contents($logPath, $logEntry, FILE_APPEND) === false) {
                    throw new \RuntimeException("Failed to write to log file: {$logPath}");
                }
                
                error_log($logPrefix . "Successfully wrote to log file: {$logPath}");
                
            } catch (\Exception $e) {
                error_log($logPrefix . "Failed to write to SMS log file: " . $e->getMessage());
                // Continue execution even if file logging fails
            }
            
            // In a real application, you would integrate with an SMS gateway here
            // Example with Twilio:
            // $twilio = new \Twilio\Rest\Client(config('services.twilio.sid'), config('services.twilio.token'));
            // $twilio->messages->create($to, [
            //     'from' => config('services.twilio.from'),
            //     'body' => $message
            // ]);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Failed to send SMS: ' . $e->getMessage(), [
                'to' => $to ?? 'unknown',
                'exception' => $e
            ]);
            
            return false;
        }
    }
    
    /**
     * Format phone number to standard format (remove any non-numeric characters, add country code if needed)
     * 
     * @param  string  $phone
     * @return string
     */
    protected function formatPhoneNumber($phone)
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // If the number starts with 01 and doesn't have country code, add Malaysia country code (60)
        if (preg_match('/^01\d{8,9}$/', $phone)) {
            return '60' . substr($phone, 1);
        }
        
        return $phone;
    }
    
    /**
     * Validate phone number format
     * 
     * @param  string  $phone
     * @return bool
     */
    protected function isValidPhoneNumber($phone)
    {
        // Basic validation for Malaysian phone numbers
        // Accepts formats like: 60123456789, +60123456789, 012-345 6789, etc.
        return (bool) preg_match('/^(\+?6?01)[0-9]{8,9}$/', $phone);
    }
}
