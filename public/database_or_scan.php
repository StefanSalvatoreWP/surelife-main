<?php

/*
 * OR Database Diagnostic Script
 * Run: php database_or_scan.php
 * This will show all OR data in the database
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\OrBatch;
use App\Models\OfficialReceipt;

echo "═══════════════════════════════════════════════════════════\n";
echo "           OR DATABASE DEEPSCAN REPORT                     \n";
echo "═══════════════════════════════════════════════════════════\n\n";

// 1. Total counts
echo "📊 OVERALL STATISTICS\n";
echo "───────────────────────────────────────────────────────────\n";
$totalBatches = OrBatch::count();
$totalORs = OfficialReceipt::count();
$availableORs = OfficialReceipt::where('Status', '1')->count();
$usedORs = OfficialReceipt::where('Status', '!=', '1')->orWhereNull('Status')->count();

echo "Total OR Batches:        $totalBatches\n";
echo "Total OR Numbers:        $totalORs\n";
echo "Available ORs (Status=1): $availableORs\n";
echo "Used ORs (Status!=1):     $usedORs\n\n";

// 2. Breakdown by Region and Branch
echo "📍 OR BREAKDOWN BY REGION & BRANCH\n";
echo "───────────────────────────────────────────────────────────\n";

$regionBranchData = OrBatch::select(
    'RegionId',
    'BranchId',
    'SeriesCode',
    \DB::raw('COUNT(DISTINCT tblorbatch.id) as batch_count'),
    \DB::raw('COUNT(tblofficialreceipt.id) as total_ors'),
    \DB::raw('SUM(CASE WHEN tblofficialreceipt.Status = 1 THEN 1 ELSE 0 END) as available_ors')
)
->leftJoin('tblofficialreceipt', 'tblorbatch.id', '=', 'tblofficialreceipt.orbatchid')
->groupBy('RegionId', 'BranchId', 'SeriesCode')
->orderBy('RegionId')
->orderBy('BranchId')
->orderBy('SeriesCode')
->get();

if ($regionBranchData->isEmpty()) {
    echo "❌ NO OR DATA FOUND IN DATABASE\n";
} else {
    printf("%-8s %-8s %-15s %-8s %-10s %-10s\n", "Region", "Branch", "Series", "Batches", "Total ORs", "Available");
    echo str_repeat("-", 65) . "\n";
    
    foreach ($regionBranchData as $row) {
        printf("%-8s %-8s %-15s %-8s %-10s %-10s\n",
            $row->RegionId ?? 'N/A',
            $row->BranchId ?? 'N/A',
            $row->SeriesCode ?? 'N/A',
            $row->batch_count,
            $row->total_ors,
            $row->available_ors
        );
    }
}

echo "\n";

// 3. Show sample OR numbers
echo "🔍 SAMPLE OR NUMBERS (First 20 available)\n";
echo "───────────────────────────────────────────────────────────\n";

$sampleORs = OfficialReceipt::select('tblofficialreceipt.*', 'tblorbatch.RegionId', 'tblorbatch.BranchId', 'tblorbatch.SeriesCode')
    ->join('tblorbatch', 'tblorbatch.id', '=', 'tblofficialreceipt.orbatchid')
    ->where('tblofficialreceipt.Status', '1')
    ->orderBy('tblofficialreceipt.ORNumber')
    ->limit(20)
    ->get();

if ($sampleORs->isEmpty()) {
    echo "❌ NO AVAILABLE OR NUMBERS FOUND\n";
} else {
    printf("%-10s %-8s %-8s %-15s\n", "OR Number", "Region", "Branch", "Series");
    echo str_repeat("-", 50) . "\n";
    
    foreach ($sampleORs as $or) {
        printf("%-10s %-8s %-8s %-15s\n",
            $or->ORNumber,
            $or->RegionId ?? 'N/A',
            $or->BranchId ?? 'N/A',
            $or->SeriesCode ?? 'N/A'
        );
    }
}

echo "\n";

// 4. Check for specific Region 4, Branch 24 (mentioned in previous error)
echo "🔎 SPECIFIC CHECK: Region 4, Branch 24\n";
echo "───────────────────────────────────────────────────────────\n";

$region4Branch24 = OrBatch::select(
    'RegionId',
    'BranchId',
    'SeriesCode',
    \DB::raw('COUNT(tblofficialreceipt.id) as total_ors'),
    \DB::raw('SUM(CASE WHEN tblofficialreceipt.Status = 1 THEN 1 ELSE 0 END) as available_ors')
)
->leftJoin('tblofficialreceipt', 'tblorbatch.id', '=', 'tblofficialreceipt.orbatchid')
->where('RegionId', '4')
->where('BranchId', '24')
->groupBy('RegionId', 'BranchId', 'SeriesCode')
->get();

if ($region4Branch24->isEmpty()) {
    echo "❌ NO OR DATA FOUND FOR Region 4, Branch 24\n";
    echo "This confirms the dropdown correctly shows no data.\n";
} else {
    echo "✅ OR DATA FOUND FOR Region 4, Branch 24:\n";
    printf("%-8s %-8s %-15s %-10s %-10s\n", "Region", "Branch", "Series", "Total ORs", "Available");
    echo str_repeat("-", 55) . "\n";
    
    foreach ($region4Branch24 as $row) {
        printf("%-8s %-8s %-15s %-10s %-10s\n",
            $row->RegionId,
            $row->BranchId,
            $row->SeriesCode ?? 'N/A',
            $row->total_ors,
            $row->available_ors
        );
    }
}

echo "\n═══════════════════════════════════════════════════════════\n";
echo "                    END OF REPORT                          \n";
echo "═══════════════════════════════════════════════════════════\n";
