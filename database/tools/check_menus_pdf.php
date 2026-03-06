<?php

// Require Composer's autoloader to use Dompdf which is already installed in the project
require __DIR__ . '/../../vendor/autoload.php';

use Dompdf\Dompdf;

try {
    // Connect to the database
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=slc_db', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch the menu data
    $stmt = $pdo->query("SELECT id, menuitem, rolelevel FROM tblmenu ORDER BY rolelevel ASC");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Build the HTML for the PDF
    $html = '<h2 style="font-family: sans-serif;">System Menu Privileges</h2>';
    $html .= '<table border="1" cellpadding="8" cellspacing="0" width="100%" style="font-family: sans-serif; border-collapse: collapse;">';
    $html .= '<tr style="background-color: #f2f2f2;">';
    $html .= '<th align="left" width="20%">Role Level</th>';
    $html .= '<th align="left">Menu Item</th>';
    $html .= '<th align="left" width="15%">ID</th>';
    $html .= '</tr>';

    foreach ($rows as $r) {
        $html .= '<tr>';
        $html .= '<td><b>' . htmlspecialchars($r['rolelevel']) . '</b></td>';
        $html .= '<td>' . htmlspecialchars($r['menuitem']) . '</td>';
        $html .= '<td>' . htmlspecialchars($r['id']) . '</td>';
        $html .= '</tr>';
    }

    $html .= '</table>';

    // Initialize Dompdf
    $dompdf = new Dompdf();
    $dompdf->loadHtml($html);

    // (Optional) Setup the paper size and orientation
    $dompdf->setPaper('A4', 'portrait');

    // Render the HTML as PDF
    $dompdf->render();

    // Output the generated PDF to a file
    $outputPath = __DIR__ . '/menu_privileges.pdf';
    file_put_contents($outputPath, $dompdf->output());

    echo "✅ PDF successfully generated at: " . $outputPath . "\n";

} catch (Exception $e) {
    echo "❌ Error generating PDF: " . $e->getMessage() . "\n";
}
