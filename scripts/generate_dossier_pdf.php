<?php
// Script to generate a test PDF for the latest Dossier
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Dossier;
use Dompdf\Dompdf;

echo "Bootstrapped application\n";

$dossier = Dossier::latest()->first();
if (! $dossier) {
    echo "No dossier found in database.\n";
    exit(1);
}

$pageGardeDataUri = '';
if (! empty($dossier->page_garde_path)) {
    $candidate = storage_path('app/public/' . $dossier->page_garde_path);
    if (file_exists($candidate)) {
        $data = file_get_contents($candidate);
        $ext = pathinfo($candidate, PATHINFO_EXTENSION);
        $pageGardeDataUri = 'data:image/' . $ext . ';base64,' . base64_encode($data);
        echo "Found page_garde file: {$candidate}\n";
    } else {
        echo "page_garde_path set but file not found at: {$candidate}\n";
    }
}

// Render Blade view to HTML
try {
    $html = view('dossiers.pdf', compact('dossier', 'pageGardeDataUri'))->render();
} catch (\Throwable $e) {
    echo "Error rendering view: " . $e->getMessage() . "\n";
    exit(2);
}

// Generate PDF with Dompdf
try {
    $dompdf = new Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $output = $dompdf->output();
    $outPath = storage_path('app/public/test_page_garde_' . $dossier->id . '.pdf');
    file_put_contents($outPath, $output);
    echo "PDF generated: {$outPath}\n";
} catch (\Throwable $e) {
    echo "Error generating PDF: " . $e->getMessage() . "\n";
    exit(3);
}

exit(0);
