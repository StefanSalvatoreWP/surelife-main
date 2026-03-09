<?php
/**
 * Semaphore API Test Script
 * Tests if the Semaphore API key is working by sending a test SMS
 */

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\Http;

// Load Laravel environment
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Get API key from config
$apiKey = config('sms.semaphore.api_key');
$senderName = 'Semaphore'; // Use default Semaphore sender name for testing

echo "=== Semaphore API Test ===\n";
echo "API Key: " . ($apiKey ? substr($apiKey, 0, 8) . '...' : 'NOT SET') . "\n";
echo "Sender Name: {$senderName}\n";
echo "Test Phone: 09636466496\n\n";

if (!$apiKey) {
    echo "ERROR: SEMAPHORE_API_KEY not found in config. Please check .env file.\n";
    exit(1);
}

// Prepare request
$endpoint = 'https://api.semaphore.co/api/v4/account'; // Check account first
$message = "Test message from SureLife Loan System - API Key Test " . date('Y-m-d H:i:s');

$data = [
    'apikey' => $apiKey
];

echo "Checking Semaphore account...\n";
echo "Endpoint: {$endpoint}\n\n";

try {
    $response = Http::withoutVerifying()->get($endpoint . '?apikey=' . $apiKey);
    
    echo "Response Status: " . $response->status() . "\n";
    echo "Response Body:\n";
    echo $response->body() . "\n\n";
    
    $responseData = $response->json();
    
    if ($response->successful()) {
        if (isset($responseData[0]['status']) && $responseData[0]['status'] === 'success') {
            echo "✅ SUCCESS: SMS sent successfully!\n";
            echo "Message ID: " . ($responseData[0]['message_id'] ?? 'N/A') . "\n";
        } else {
            echo "❌ ERROR: API returned error response\n";
            echo "Error: " . ($responseData[0]['message'] ?? 'Unknown error') . "\n";
        }
    } else {
        echo "❌ ERROR: HTTP request failed\n";
        echo "HTTP Status: " . $response->status() . "\n";
        if (isset($responseData['error'])) {
            echo "API Error: " . $responseData['error'] . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ EXCEPTION: " . $e->getMessage() . "\n";
    echo "Check your internet connection and API endpoint.\n";
}

echo "\n=== Test Complete ===\n";
