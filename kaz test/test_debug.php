<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Verifying TermPrice Fix ===\n\n";

// Replicate the exact ClientController viewClientInfo query
$client = \App\Models\Client::select(
    'tblclient.*',
    'tblclient.Id as cid',
    'tblregion.RegionName',
    'tblbranch.BranchName',
    'tblpackage.Package',
    'tblpaymentterm.Id',
    'tblpaymentterm.PackageId',
    'tblpaymentterm.Term',
    \DB::raw('tblpaymentterm.Price as TermPrice'),
    'tblstaff.LastName as FSALastName',
    'tblstaff.FirstName as FSAFirstName',
    'tblstaff.MiddleName as FSAMiddleName'
)->leftJoin('tblregion', 'tblclient.RegionId', '=', 'tblregion.id')
    ->leftJoin('tblbranch', 'tblclient.BranchId', '=', 'tblbranch.id')
    ->leftJoin('tblpackage', 'tblclient.PackageId', '=', 'tblpackage.id')
    ->leftJoin('tblpaymentterm', 'tblclient.PaymentTermId', '=', 'tblpaymentterm.id')
    ->leftJoin('tblstaff', 'tblclient.RecruitedBy', '=', 'tblstaff.id')
    ->where('tblclient.id', 1)
    ->first();

echo "Client ID: {$client->cid}\n";
echo "Term: {$client->Term}\n";
echo "TermPrice: '{$client->TermPrice}'\n";

// Calculate total_price using the new TermPrice field (same logic as blade)
$base_price = $client->TermPrice;
switch ($client->Term) {
    case "Spotcash":
        $total_price = $base_price;
        break;
    case "Annual":
        $total_price = $base_price * 5;
        break;
    case "Semi-Annual":
        $total_price = ($base_price * 2) * 5;
        break;
    case "Quarterly":
        $total_price = ($base_price * 4) * 5;
        break;
    case "Monthly":
        $total_price = $base_price * 60;
        break;
    default:
        $total_price = $base_price;
}

echo "Computed total_price: " . number_format($total_price, 2) . "\n";
echo "\n=== Checking multiple clients ===\n";

$clients = \App\Models\Client::select(
    'tblclient.Id as cid',
    'tblpaymentterm.Term',
    \DB::raw('tblpaymentterm.Price as TermPrice')
)->leftJoin('tblpaymentterm', 'tblclient.PaymentTermId', '=', 'tblpaymentterm.id')
    ->take(5)->get();

foreach ($clients as $c) {
    $status = ($c->TermPrice > 0) ? '✓ OK' : '✗ ZERO/NULL';
    echo "Client #{$c->cid} - Term: {$c->Term} - TermPrice: '{$c->TermPrice}' $status\n";
}
