<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- PAYMENT TERMS ---\n";
$terms = \DB::table('tblpaymentterm')->take(10)->get();
foreach ($terms as $term) {
    echo "Term ID: {$term->Id}, Package ID: {$term->PackageId}, Term: {$term->Term}, Price: {$term->Price}\n";
}

echo "\n--- PACKAGES ---\n";
$packages = \DB::table('tblpackage')->take(5)->get();
foreach ($packages as $pkg) {
    echo "Package ID: {$pkg->Id}, Name: {$pkg->Package}, Price: {$pkg->Price}\n";
}

echo "\n--- CLIENTS ---\n";
$clients = \DB::table('tblclient')->take(5)->get();
foreach ($clients as $c) {
    echo "Client ID: {$c->Id}, Contract: {$c->ContractNumber}, PkgID: {$c->PackageID}, TermId: {$c->PaymentTermId}\n";
}
