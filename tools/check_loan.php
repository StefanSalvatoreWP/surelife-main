<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== FIND CLIENT BY CONTRACT NUMBER ===\n";
$client = App\Models\Client::where('ContractNumber', 'TEST-1772450364')->first();
if ($client) {
    echo "Client ID: " . $client->Id . "\n";
    echo "Name: " . $client->LastName . ", " . $client->FirstName . "\n";
    echo "ContractNumber: " . $client->ContractNumber . "\n";
    
    echo "\n=== LOAN REQUESTS FOR THIS CLIENT ===\n";
    $loans = App\Models\LoanRequest::where('ClientId', $client->Id)->get();
    foreach ($loans as $loan) {
        echo "Loan ID: " . $loan->Id . "\n";
        echo "  Status: '" . $loan->Status . "'\n";
        echo "  Amount: " . $loan->Amount . "\n";
        echo "  remarks: '" . $loan->remarks . "'\n";
    }
    
    echo "\n=== QUERY CHECK (what ClientController uses) ===\n";
    $hasLoanRequest = App\Models\LoanRequest::query()
        ->where('ClientId', $client->Id)
        ->where('Status', 'Approved')
        ->where('remarks', '<>', 'Completed')
        ->first();
        
    if ($hasLoanRequest) {
        echo "✅ Found APPROVED loan!\n";
        echo "   Loan ID: " . $hasLoanRequest->Id . "\n";
        echo "   Amount: " . $hasLoanRequest->Amount . "\n";
        
        $totalPayments = App\Models\LoanPayment::where('loanrequestid', $hasLoanRequest->Id)->sum('amount');
        $balance = $hasLoanRequest->Amount - $totalPayments;
        echo "   Balance: " . $balance . "\n";
        echo "   Balance > 0: " . ($balance > 0 ? 'YES' : 'NO') . "\n";
    } else {
        echo "❌ No approved loan found\n";
    }
} else {
    echo "Client not found!\n";
}
