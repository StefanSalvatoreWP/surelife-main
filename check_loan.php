<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Check table columns
echo "=== TABLE COLUMNS ===\n";
$columns = DB::select("SHOW COLUMNS FROM tblloanrequest");
foreach ($columns as $col) {
    echo $col->Field . " (" . $col->Type . ")\n";
}

echo "\n=== LATEST LOAN REQUEST RAW ===\n";
$raw = DB::table('tblloanrequest')->orderBy('Id', 'desc')->first();
print_r($raw);
