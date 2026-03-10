<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$clients = \App\Models\Client::select(
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
)
    ->leftJoin('tblregion', 'tblclient.RegionId', '=', 'tblregion.id')
    ->leftJoin('tblbranch', 'tblclient.BranchId', '=', 'tblbranch.id')
    ->leftJoin('tblpackage', 'tblclient.PackageId', '=', 'tblpackage.id')
    ->leftJoin('tblpaymentterm', 'tblclient.PaymentTermId', '=', 'tblpaymentterm.id')
    ->leftJoin('tblstaff', 'tblclient.RecruitedBy', '=', 'tblstaff.id')
    ->where('tblclient.id', 60391)
    ->first();

echo "Raw Client CID: " . ($clients->cid ?? 'NULL') . "\n";
echo "Raw tblclient.Id via object: " . ($clients->Id ?? 'NULL') . "\n";
echo "Attributes:\n";
print_r($clients->getAttributes());
