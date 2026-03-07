<?php

require __DIR__ . '/../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

// Increase limits to handle PDF generation
ini_set('memory_limit', '1G');
set_time_limit(300);

$html = '
<!DOCTYPE html>
<html>
<head>
    <title>Surelife Data Availability Checker</title>
    <style>
        body { font-family: sans-serif; padding: 20px; }
        h1 { color: #2e7d32; text-align: center; border-bottom: 2px solid #ddd; padding-bottom: 10px; }
        h2 { color: #1565c0; margin-top: 30px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f5f5f5; }
        p.empty { color: #d32f2f; font-style: italic; }
        .note { font-size: 12px; color: #666; font-style: italic; }
    </style>
</head>
<body>
    <h1>Surelife Data Availability Checker</h1>
    <p>This document lists recent dates with available data for testing the Client Status Reports.</p>
    <p class="note">Note: Output is limited to the top 10 recent dates per category to prevent PDF memory exhaustion.</p>
';

// 1. Completed
$html .= '<h2>1. Completed Reports (Based on Date Accomplished)</h2>';
$completedData = DB::table('tblclient')
    ->select(DB::raw('DATE(dateaccomplished) as date'), DB::raw('COUNT(*) as total'))
    ->where('status', '3')
    ->whereNotNull('dateaccomplished')
    ->groupBy(DB::raw('DATE(dateaccomplished)'))
    ->orderBy(DB::raw('DATE(dateaccomplished)'), 'desc')
    ->limit(10) // Limit restored to prevent memory issues with DomPDF
    ->get();

if ($completedData->isEmpty()) {
    $html .= '<p class="empty">No completed clients found with a Date Accomplished.</p>';
} else {
    $html .= '<table><tr><th>Date Accomplished</th><th>Total Clients</th></tr>';
    foreach ($completedData as $row) {
        $html .= '<tr><td>' . htmlspecialchars($row->date) . '</td><td>' . htmlspecialchars($row->total) . '</td></tr>';
    }
    $html .= '</table>';
}

// 2. Active & Lapse Reports Filter Logic Test
$html .= '<h2>2. Active & Lapse Reports (Based on Date Accomplished)</h2>';
$html .= '<p class="note">Since Active & Lapse reports now use Date Accomplished for filtering, here are top 10 recent dates with clients (Status=3):</p>';

$activeLapseData = DB::table('tblclient')
    ->select(DB::raw('DATE(dateaccomplished) as date'), DB::raw('COUNT(*) as total'))
    ->where('status', '3')
    ->whereNotNull('dateaccomplished')
    ->groupBy(DB::raw('DATE(dateaccomplished)'))
    ->orderBy(DB::raw('DATE(dateaccomplished)'), 'desc')
    ->limit(10) // Limit restored to prevent memory issues with DomPDF
    ->get();

if ($activeLapseData->isEmpty()) {
    $html .= '<p class="empty">No clients found to be tested for Active/Lapse.</p>';
} else {
    $html .= '<table><tr><th>Date Accomplished</th><th>Total Clients</th></tr>';
    foreach ($activeLapseData as $row) {
        $html .= '<tr><td>' . htmlspecialchars($row->date) . '</td><td>' . htmlspecialchars($row->total) . '</td></tr>';
    }
    $html .= '</table>';
}

// 3. Transfer Reports
$html .= '<h2>3. Transfer Reports (Based on Date Created)</h2>';
$transferData = DB::table('tblclienttransfer')
    ->select(DB::raw('DATE(DateCreated) as date'), DB::raw('COUNT(*) as total'))
    ->whereNotNull('DateCreated')
    ->groupBy(DB::raw('DATE(DateCreated)'))
    ->orderBy(DB::raw('DATE(DateCreated)'), 'desc')
    ->limit(10) // Limit restored to prevent memory issues with DomPDF
    ->get();

if ($transferData->isEmpty()) {
    $html .= '<p class="empty">No transfer records found.</p>';
} else {
    $html .= '<table><tr><th>Date Transferred</th><th>Total Clients</th></tr>';
    foreach ($transferData as $row) {
        $html .= '<tr><td>' . htmlspecialchars($row->date) . '</td><td>' . htmlspecialchars($row->total) . '</td></tr>';
    }
    $html .= '</table>';
}

$html .= '</body></html>';

try {
    $pdf = Pdf::loadHTML($html);
    $outputPath = __DIR__ . '/test_reports_data.pdf';
    $pdf->save($outputPath);
    echo "PDF generated successfully at: " . $outputPath . "\n";
} catch (\Exception $e) {
    echo "Failed to generate PDF: " . $e->getMessage() . "\n";
}
