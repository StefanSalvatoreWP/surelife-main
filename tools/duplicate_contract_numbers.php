<?php
/**
 * Duplicate Contract Number Finder
 * 
 * Finds all clients that share the same ContractNumber and generates
 * a TXT report and a styled PDF report.
 * 
 * Run: php tools/duplicate_contract_numbers.php
 */

ini_set('memory_limit', '1024M');

$host = '127.0.0.1';
$dbname = 'slc_db';
$user = 'root';
$pass = '';

$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$today = date('Y-m-d');

// ── Step 1: Find contract numbers that appear more than once ──
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
        c.Status AS RawStatus,
        c.DateCreated AS EnrollDate,
        r.RegionName,
        b.BranchName,
        ps.total_paid,
        ps.last_payment_date AS LastPay
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
    WHERE c.ContractNumber IN (
        SELECT ContractNumber
        FROM tblclient
        WHERE ContractNumber IS NOT NULL
        AND ContractNumber != ''
        GROUP BY ContractNumber
        HAVING COUNT(*) > 1
    )
    ORDER BY c.ContractNumber, c.LastName, c.FirstName
";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ── Step 2: Group by ContractNumber ──
$groups = [];
foreach ($rows as $r) {
    $cn = $r['ContractNumber'];
    if (!isset($groups[$cn])) {
        $groups[$cn] = [];
    }
    $groups[$cn][] = $r;
}

$totalDuplicateContracts = count($groups);
$totalAffectedClients = count($rows);

// ── Step 3: Status label helper ──
function getStatusLabel($status)
{
    $map = [
        '1' => 'Pending',
        '2' => 'For Approval',
        '3' => 'Approved',
        '4' => 'Disapproved',
        '5' => 'Lapsed',
        '6' => 'Cancelled',
        '7' => 'Deceased',
        '8' => 'Matured',
    ];
    return $map[$status] ?? "Unknown ($status)";
}

// ── Step 4: TXT Report ──
$eq = str_repeat('=', 120);
$dash = str_repeat('-', 120);
$out = [
    $eq,
    "  SURE LIFE - DUPLICATE CONTRACT NUMBER REPORT",
    $eq,
    "  Generated: $today",
    "  Duplicate Contract Numbers Found: $totalDuplicateContracts",
    "  Total Affected Clients: $totalAffectedClients",
    $dash,
];

foreach ($groups as $contractNum => $clients) {
    $out[] = "";
    $out[] = "  CONTRACT NUMBER: $contractNum  (" . count($clients) . " clients)";
    $out[] = $dash;
    $out[] = str_pad("  ID", 8) . " | " . str_pad("NAME", 30) . " | " . str_pad("STATUS", 12) . " | " . str_pad("TERM", 12) . " | " . str_pad("PACKAGE", 20) . " | " . str_pad("BRANCH", 15) . " | ENROLLED";
    $out[] = $dash;

    foreach ($clients as $c) {
        $name = substr(trim($c['LastName'] . ', ' . $c['FirstName'] . ' ' . $c['MiddleName']), 0, 30);
        $status = getStatusLabel($c['RawStatus']);
        $out[] = str_pad("  " . $c['ClientId'], 8)
            . " | " . str_pad($name, 30)
            . " | " . str_pad($status, 12)
            . " | " . str_pad(($c['PaymentTerm'] ?? '-'), 12)
            . " | " . str_pad(($c['PackageName'] ?? '-'), 20)
            . " | " . str_pad(($c['BranchName'] ?? '-'), 15)
            . " | " . ($c['EnrollDate'] ?? '-');
    }
}

$out[] = "";
$out[] = $eq;
$out[] = "  END OF REPORT";
$out[] = $eq;

file_put_contents(__DIR__ . '/duplicate_contracts_report.txt', implode("\n", $out));

// ── Step 5: PDF Report ──
use Dompdf\Dompdf;
use Dompdf\Options;
require_once __DIR__ . '/../vendor/autoload.php';
$dompdf = new Dompdf((new Options())->set('isHtml5ParserEnabled', true));

$html = '<html><head><style>
    body { font-family: DejaVu Sans; font-size: 8px; margin: 10px; }
    h2 { text-align: center; margin-bottom: 2px; color: #1e293b; }
    .subtitle { text-align: center; color: #64748b; font-size: 7px; margin-bottom: 10px; }
    table { width: 100%; border-collapse: collapse; margin-top: 4px; margin-bottom: 12px; }
    th, td { border: 1px solid #cbd5e1; padding: 3px 5px; }
    th { background: #1e40af; color: white; font-size: 7px; text-transform: uppercase; }
    .group-header { background: #f8fafc; padding: 6px 8px; margin-top: 10px; border-left: 4px solid #1e40af; font-weight: bold; font-size: 9px; color: #1e293b; }
    .summary-box { background: #eff6ff; padding: 8px; border: 1px solid #93c5fd; margin-bottom: 10px; border-radius: 4px; font-size: 8px; }
    .status-approved { background: #dcfce7; color: #166534; padding: 1px 4px; border-radius: 3px; font-weight: bold; font-size: 7px; }
    .status-lapsed { background: #fee2e2; color: #991b1b; padding: 1px 4px; border-radius: 3px; font-weight: bold; font-size: 7px; }
    .status-pending { background: #fef9c3; color: #854d0e; padding: 1px 4px; border-radius: 3px; font-weight: bold; font-size: 7px; }
    .status-other { background: #e2e8f0; color: #334155; padding: 1px 4px; border-radius: 3px; font-weight: bold; font-size: 7px; }
    .row-even { background: #f8fafc; }
    .row-odd { background: #ffffff; }
    .footer { text-align: center; color: #94a3b8; font-size: 7px; margin-top: 15px; border-top: 1px solid #e2e8f0; padding-top: 5px; }
</style></head><body>
    <h2>SURE LIFE CORPORATION</h2>
    <p class="subtitle">DUPLICATE CONTRACT NUMBER REPORT &mdash; Generated: ' . $today . '</p>
    <div class="summary-box">
        <strong>Summary:</strong> Found <strong>' . $totalDuplicateContracts . '</strong> contract numbers shared by multiple clients, 
        affecting <strong>' . $totalAffectedClients . '</strong> total client records.
    </div>';

$groupIndex = 0;
foreach ($groups as $contractNum => $clients) {
    $groupIndex++;
    $html .= '<div class="group-header">' . $groupIndex . '. Contract Number: ' . htmlspecialchars($contractNum) . ' &mdash; ' . count($clients) . ' client(s)</div>';
    $html .= '<table>
        <thead><tr>
            <th>ID</th><th>Full Name</th><th>Status</th><th>Payment Term</th><th>Package</th><th>Branch</th><th>Region</th><th>Enrolled</th><th>Last Payment</th><th>Total Paid</th>
        </tr></thead><tbody>';

    $rowIdx = 0;
    foreach ($clients as $c) {
        $rowClass = ($rowIdx % 2 === 0) ? 'row-even' : 'row-odd';
        $name = htmlspecialchars(trim($c['LastName'] . ', ' . $c['FirstName'] . ' ' . $c['MiddleName']));
        $status = getStatusLabel($c['RawStatus']);
        $totalPaid = number_format((float) ($c['total_paid'] ?? 0), 2);

        // Status badge class
        $statusClass = 'status-other';
        if ($c['RawStatus'] == '3')
            $statusClass = 'status-approved';
        elseif ($c['RawStatus'] == '5')
            $statusClass = 'status-lapsed';
        elseif ($c['RawStatus'] == '1' || $c['RawStatus'] == '2')
            $statusClass = 'status-pending';

        $html .= '<tr class="' . $rowClass . '">'
            . '<td>' . $c['ClientId'] . '</td>'
            . '<td>' . $name . '</td>'
            . '<td><span class="' . $statusClass . '">' . $status . '</span></td>'
            . '<td>' . ($c['PaymentTerm'] ?? '-') . '</td>'
            . '<td>' . ($c['PackageName'] ?? '-') . '</td>'
            . '<td>' . ($c['BranchName'] ?? '-') . '</td>'
            . '<td>' . ($c['RegionName'] ?? '-') . '</td>'
            . '<td>' . ($c['EnrollDate'] ?? '-') . '</td>'
            . '<td>' . ($c['LastPay'] ?: 'NONE') . '</td>'
            . '<td>P ' . $totalPaid . '</td>'
            . '</tr>';
        $rowIdx++;
    }

    $html .= '</tbody></table>';
}

$html .= '<div class="footer">Sure Life Corporation &mdash; Duplicate Contract Number Report &mdash; Page generated on ' . $today . '</div>';
$html .= '</body></html>';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
file_put_contents(__DIR__ . '/duplicate_contracts_report.pdf', $dompdf->output());

// ── Console output ──
echo "\n" . str_repeat('=', 60) . "\n";
echo "  DUPLICATE CONTRACT NUMBER REPORT GENERATED\n";
echo str_repeat('=', 60) . "\n";
echo "  Date: $today\n\n";
echo "  Duplicate Contract Numbers: $totalDuplicateContracts\n";
echo "  Total Affected Clients:     $totalAffectedClients\n\n";

if ($totalDuplicateContracts > 0) {
    echo "  Top duplicates:\n";
    $top = array_slice($groups, 0, 10, true);
    foreach ($top as $cn => $clients) {
        echo "    Contract# $cn => " . count($clients) . " clients\n";
    }
    if ($totalDuplicateContracts > 10) {
        echo "    ... and " . ($totalDuplicateContracts - 10) . " more (see full report)\n";
    }
}

echo "\n  Files:\n";
echo "    TXT: tools/duplicate_contracts_report.txt\n";
echo "    PDF: tools/duplicate_contracts_report.pdf\n";
echo str_repeat('=', 60) . "\n";
