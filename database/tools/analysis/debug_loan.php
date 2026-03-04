<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$lr = App\Models\LoanRequest::orderBy('Id', 'desc')->first();
echo "=== LOAN REQUEST MODEL ACCESS ===\n";
echo "ID: " . $lr->Id . "\n";
echo "Amount: " . var_export($lr->Amount, true) . "\n";
echo "MonthlyAmount: " . var_export($lr->MonthlyAmount, true) . "\n";
echo "ClientId: " . var_export($lr->ClientId, true) . "\n";
echo "term_months: " . var_export($lr->term_months, true) . "\n";
echo "processing_fee: " . var_export($lr->processing_fee, true) . "\n";
echo "total_repayable: " . var_export($lr->total_repayable, true) . "\n";

$client = App\Models\Client::find($lr->ClientId);
if ($client) {
    echo "\n=== CLIENT ===\n";
    echo "PackagePrice: " . ($client->PackagePrice ?? 'NULL') . "\n";
    echo "PaymentTermAmount: " . ($client->PaymentTermAmount ?? 'NULL') . "\n";
}

echo "\n=== EXPECTED VALUES ===\n";
$contractPrice = $client->PackagePrice ?? 20000;
$totalPremiumsPaid = App\Models\Payment::where('clientid', $lr->ClientId)->sum('amountpaid');
echo "Contract Price: $contractPrice\n";
echo "Total Premiums Paid: $totalPremiumsPaid\n";
echo "Premium %: " . round(($totalPremiumsPaid / $contractPrice) * 100) . "%\n";
