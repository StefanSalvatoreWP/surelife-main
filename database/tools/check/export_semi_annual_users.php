<?php
/**
 * Export All Clients with Term-Aware Lapse Analysis
 * 
 * Lapse Thresholds (Term + Grace):
 * - Monthly:     3 months
 * - Quarterly:   6 months
 * - Semi-Annual: 12 months
 * - Annual:      24 months
 * 
 * Payment filters match the application logic exactly:
 * - VoidStatus != '1'
 * - Remarks IN (NULL, 'Standard', 'Partial', 'Custom')
 * - Only Approved (Status = 3) clients
 */

ini_set('memory_limit', '1024M');

$host = '127.0.0.1';
$dbname = 'slc_db';
$user = 'root';
$pass = '';

$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$today = date('Y-m-d');

// ── Query using tblpaymentterm.Term directly (no hardcoded IDs) ──
$sql = "
    SELECT
        c.Id AS ClientId,
        c.ContractNumber,
        c.LastName,
        c.FirstName,
        IFNULL(c.MiddleName, '') AS MiddleName,
        pt.Term AS PaymentTerm,
        pk.Package AS PackageName,
        pt.Price AS TermPrice,
        c.Status AS DBStatus,
        c.DateCreated AS EnrollDate,
        r.RegionName,
        b.BranchName,
        ps.total_paid,
        ps.last_payment_date AS LastPay,
        COALESCE(ps.last_payment_date, c.DateCreated) AS RefDate,
        TIMESTAMPDIFF(MONTH, COALESCE(ps.last_payment_date, c.DateCreated), ?) AS MonthsElapsed,
        CASE pt.Term
            WHEN 'Monthly'     THEN 3
            WHEN 'Quarterly'   THEN 6
            WHEN 'Semi-Annual' THEN 12
            WHEN 'Annual'      THEN 24
            ELSE 3
        END AS Threshold,
        CASE pt.Term
            WHEN 'Spotcash'    THEN pt.Price
            WHEN 'Annual'      THEN pt.Price * 5
            WHEN 'Semi-Annual' THEN pt.Price * 10
            WHEN 'Quarterly'   THEN pt.Price * 20
            WHEN 'Monthly'     THEN pt.Price * 60
            ELSE pt.Price * 60
        END AS TotalPrice
    FROM tblclient c
    LEFT JOIN tblpaymentterm pt ON pt.Id = c.PaymentTermId
    LEFT JOIN tblpackage pk ON pk.Id = c.PackageID
    LEFT JOIN tblregion r ON r.Id = c.RegionId
    LEFT JOIN tblbranch b ON b.Id = c.BranchId
    LEFT JOIN (
        SELECT
            clientid,
            SUM(AmountPaid) AS total_paid,
            MAX(Date) AS last_payment_date
        FROM tblpayment
        WHERE VoidStatus != '1'
        AND (Remarks IS NULL OR Remarks IN ('Standard', 'Partial', 'Custom'))
        GROUP BY clientid
    ) ps ON c.Id = ps.clientid
    WHERE c.Status = '3'
    AND pt.Term IN ('Monthly', 'Quarterly', 'Semi-Annual', 'Annual')
    ORDER BY pt.Term, c.LastName, c.FirstName
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$today]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ── Classify each client ──
$results = [];
$summary = ['Monthly' => ['active' => 0, 'lapse' => 0], 'Quarterly' => ['active' => 0, 'lapse' => 0], 'Semi-Annual' => ['active' => 0, 'lapse' => 0], 'Annual' => ['active' => 0, 'lapse' => 0]];

foreach ($rows as $r) {
    $months = (int) $r['MonthsElapsed'];
    $threshold = (int) $r['Threshold'];
    $totalPaid = (float) ($r['total_paid'] ?? 0);
    $totalPrice = (float) $r['TotalPrice'];
    $isFullyPaid = $totalPaid >= $totalPrice;

    if ($isFullyPaid) {
        $r['Verdict'] = 'FULLY PAID';
        $r['StatusChange'] = '';
    } elseif ($months >= $threshold) {
        $r['Verdict'] = 'LAPSE';
        $r['StatusChange'] = '[APPROVED -> SHOULD LAPSE]';
        $summary[$r['PaymentTerm']]['lapse']++;
    } else {
        $r['Verdict'] = 'ACTIVE';
        $r['StatusChange'] = '';
        $summary[$r['PaymentTerm']]['active']++;
    }

    $r['Balance'] = $totalPrice - $totalPaid;
    $results[] = $r;
}

$activeToLapse = array_filter($results, fn($c) => $c['Verdict'] === 'LAPSE');
$activeClients = array_filter($results, fn($c) => $c['Verdict'] === 'ACTIVE');
$fullyPaid = array_filter($results, fn($c) => $c['Verdict'] === 'FULLY PAID');

// ── TXT Report ──
$eq = str_repeat('=', 110);
$dash = str_repeat('-', 110);
$out = [
    $eq,
    "  SURE LIFE - TERM-AWARE LAPSE ANALYSIS",
    $eq,
    "  Generated: $today",
    "  Thresholds: Monthly=3mo | Quarterly=6mo | Semi-Annual=12mo | Annual=24mo",
    $dash,
    "  SUMMARY:",
];

foreach ($summary as $term => $counts) {
    $total = $counts['active'] + $counts['lapse'];
    $out[] = "    $term: {$counts['active']} Active, {$counts['lapse']} Lapsed (of $total with balance)";
}

$out[] = "    Fully Paid: " . count($fullyPaid);
$out[] = $eq . "\n";

// Lapsed clients section
$out[] = "  LAPSED CLIENTS (" . count($activeToLapse) . " total)";
$out[] = $dash;
$out[] = str_pad("ID", 8) . " | " . str_pad("NAME", 28) . " | " . str_pad("TERM", 12) . " | " . str_pad("LAST PAY", 10) . " | " . str_pad("MOS", 5) . " | " . str_pad("LIMIT", 5) . " | BALANCE";
$out[] = $dash;

foreach ($activeToLapse as $c) {
    $name = substr(trim($c['LastName'] . ', ' . $c['FirstName']), 0, 28);
    $balance = number_format((float) $c['Balance'], 2);
    $out[] = str_pad($c['ClientId'], 8) . " | " . str_pad($name, 28) . " | " . str_pad($c['PaymentTerm'], 12) . " | " . str_pad(($c['LastPay'] ?: 'NONE'), 10) . " | " . str_pad($c['MonthsElapsed'], 5) . " | " . str_pad($c['Threshold'] . "mo", 5) . " | P $balance";
}

$out[] = "\n" . $eq;

// Active clients section
$out[] = "  ACTIVE CLIENTS (" . count($activeClients) . " total)";
$out[] = $dash;
$out[] = str_pad("ID", 8) . " | " . str_pad("NAME", 28) . " | " . str_pad("TERM", 12) . " | " . str_pad("LAST PAY", 10) . " | " . str_pad("MOS", 5) . " | " . str_pad("LIMIT", 5) . " | BALANCE";
$out[] = $dash;

foreach ($activeClients as $c) {
    $name = substr(trim($c['LastName'] . ', ' . $c['FirstName']), 0, 28);
    $balance = number_format((float) $c['Balance'], 2);
    $out[] = str_pad($c['ClientId'], 8) . " | " . str_pad($name, 28) . " | " . str_pad($c['PaymentTerm'], 12) . " | " . str_pad(($c['LastPay'] ?: 'NONE'), 10) . " | " . str_pad($c['MonthsElapsed'], 5) . " | " . str_pad($c['Threshold'] . "mo", 5) . " | P $balance";
}

$out[] = "\n$eq\n  END OF REPORT\n$eq";
file_put_contents(__DIR__ . '/lapse_report.txt', implode("\n", $out));

// ── PDF Report ──
use Dompdf\Dompdf;
use Dompdf\Options;
require_once __DIR__ . '/../vendor/autoload.php';
$dompdf = new Dompdf((new Options())->set('isHtml5ParserEnabled', true));

$html = '<html><head><style>
    body { font-family: DejaVu Sans; font-size: 8px; margin: 10px; }
    h2 { text-align: center; margin-bottom: 5px; }
    table { width: 100%; border-collapse: collapse; margin-top: 8px; }
    th, td { border: 1px solid #ccc; padding: 3px 5px; }
    th { background: #2d5016; color: white; font-size: 7px; text-transform: uppercase; }
    .lapse { background: #fee2e2; }
    .active { background: #dcfce7; }
    .summary { background: #f0fdf4; padding: 8px; border: 1px solid #86efac; margin-bottom: 8px; border-radius: 4px; }
    .summary-grid { display: flex; gap: 8px; }
    .badge-lapse { background: #dc2626; color: white; padding: 1px 5px; border-radius: 3px; font-weight: bold; }
    .badge-active { background: #16a34a; color: white; padding: 1px 5px; border-radius: 3px; font-weight: bold; }
    .badge-paid { background: #2563eb; color: white; padding: 1px 5px; border-radius: 3px; font-weight: bold; }
    .section-title { background: #f1f5f9; padding: 6px; margin-top: 12px; border-left: 3px solid #2d5016; font-weight: bold; font-size: 9px; }
</style></head><body>
    <h2>SURE LIFE - TERM-AWARE LAPSE REPORT</h2>
    <p style="text-align:center; color:#666; font-size:7px;">Generated: ' . $today . '</p>
    <div class="summary">
        <strong>Lapse Thresholds:</strong> Monthly = 3 mos | Quarterly = 6 mos | Semi-Annual = 12 mos | Annual = 24 mos<br>';

foreach ($summary as $term => $counts) {
    $total = $counts['active'] + $counts['lapse'];
    $html .= "<strong>$term:</strong> {$counts['active']} Active, {$counts['lapse']} Lapsed (of $total) &nbsp;&nbsp;";
}

$html .= '<br><strong>Fully Paid:</strong> ' . count($fullyPaid) . '
    </div>';

// Lapsed clients table
$html .= '<div class="section-title">LAPSED CLIENTS (' . count($activeToLapse) . ')</div>
    <table>
        <thead><tr><th>ID</th><th>Name</th><th>Contract</th><th>Term</th><th>Branch</th><th>Last Pay</th><th>Elapsed</th><th>Limit</th><th>Balance</th><th>Status</th></tr></thead>
        <tbody>';

foreach (array_slice($activeToLapse, 0, 500) as $c) {
    $name = htmlspecialchars(trim($c['LastName'] . ', ' . $c['FirstName']));
    $balance = number_format((float) $c['Balance'], 2);
    $html .= '<tr class="lapse"><td>' . $c['ClientId'] . '</td><td>' . $name . '</td><td>' . $c['ContractNumber'] . '</td><td>' . $c['PaymentTerm'] . '</td><td>' . ($c['BranchName'] ?? '-') . '</td><td>' . ($c['LastPay'] ?: 'NONE') . '</td><td>' . $c['MonthsElapsed'] . ' mos</td><td>' . $c['Threshold'] . ' mos</td><td>P ' . $balance . '</td><td><span class="badge-lapse">LAPSE</span></td></tr>';
}

if (count($activeToLapse) > 500) {
    $html .= '<tr><td colspan="10" style="text-align:center; color:#999;">... and ' . (count($activeToLapse) - 500) . ' more (see TXT report for full list)</td></tr>';
}

$html .= '</tbody></table>';

// Active clients table
$html .= '<div class="section-title">ACTIVE CLIENTS (' . count($activeClients) . ')</div>
    <table>
        <thead><tr><th>ID</th><th>Name</th><th>Contract</th><th>Term</th><th>Branch</th><th>Last Pay</th><th>Elapsed</th><th>Limit</th><th>Balance</th><th>Status</th></tr></thead>
        <tbody>';

foreach (array_slice($activeClients, 0, 500) as $c) {
    $name = htmlspecialchars(trim($c['LastName'] . ', ' . $c['FirstName']));
    $balance = number_format((float) $c['Balance'], 2);
    $html .= '<tr class="active"><td>' . $c['ClientId'] . '</td><td>' . $name . '</td><td>' . $c['ContractNumber'] . '</td><td>' . $c['PaymentTerm'] . '</td><td>' . ($c['BranchName'] ?? '-') . '</td><td>' . ($c['LastPay'] ?: 'NONE') . '</td><td>' . $c['MonthsElapsed'] . ' mos</td><td>' . $c['Threshold'] . ' mos</td><td>P ' . $balance . '</td><td><span class="badge-active">ACTIVE</span></td></tr>';
}

if (count($activeClients) > 500) {
    $html .= '<tr><td colspan="10" style="text-align:center; color:#999;">... and ' . (count($activeClients) - 500) . ' more (see TXT report for full list)</td></tr>';
}

$html .= '</tbody></table></body></html>';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
file_put_contents(__DIR__ . '/lapse_report.pdf', $dompdf->output());

// ── Console output ──
echo "\n" . str_repeat('=', 60) . "\n";
echo "  TERM-AWARE LAPSE REPORT GENERATED\n";
echo str_repeat('=', 60) . "\n";
echo "  Date: $today\n\n";

foreach ($summary as $term => $counts) {
    $total = $counts['active'] + $counts['lapse'];
    echo "  $term: {$counts['active']} Active, {$counts['lapse']} Lapsed (of $total)\n";
}

echo "  Fully Paid: " . count($fullyPaid) . "\n";
echo "  Total Clients: " . count($results) . "\n\n";
echo "  Files:\n";
echo "    TXT: tools/lapse_report.txt\n";
echo "    PDF: tools/lapse_report.pdf\n";
echo str_repeat('=', 60) . "\n";
