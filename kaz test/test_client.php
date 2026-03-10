<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$clientsWithPayments = \DB::select("
    SELECT clientid, SUM(AmountPaid) as tot
    FROM tblpayment
    WHERE VoidStatus = '0'
    GROUP BY clientid
    HAVING tot = 5205 OR tot = 5205.00
");

$res = [];
foreach ($clientsWithPayments as $row) {
    $c = \App\Models\Client::find($row->clientid);
    if (!$c)
        continue;
    $term = \DB::table('tblpaymentterm')->where('id', $c->PaymentTermId)->first();
    $res[] = [
        'id' => $c->Id,
        'package_price' => $c->PackagePrice,
        'term_price' => $term ? $term->Price : null,
        'term' => $term ? $term->Term : null
    ];
}
echo json_encode($res, JSON_PRETTY_PRINT);
