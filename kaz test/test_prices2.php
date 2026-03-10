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
    'tblpaymentterm.Price',
    'tblstaff.LastName as FSALastName',
    'tblstaff.FirstName as FSAFirstName',
    'tblstaff.MiddleName as FSAMiddleName'
)
    ->leftJoin('tblregion', 'tblclient.RegionId', '=', 'tblregion.id')
    ->leftJoin('tblbranch', 'tblclient.BranchId', '=', 'tblbranch.id')
    ->leftJoin('tblpackage', 'tblclient.PackageId', '=', 'tblpackage.id')
    ->leftJoin('tblpaymentterm', 'tblclient.PaymentTermId', '=', 'tblpaymentterm.id')
    ->leftJoin('tblstaff', 'tblclient.RecruitedBy', '=', 'tblstaff.id')
    ->take(5)
    ->get();

foreach ($clients as $c) {
    echo "Client ID: {$c->cid}\n";
    echo "  Package: {$c->Package}\n";
    echo "  Term: {$c->Term}\n";
    echo "  Price: '{$c->Price}'\n";
    echo "  Raw attrs: \n";
    print_r($c->getAttributes());
    echo "------------------\n";
}
