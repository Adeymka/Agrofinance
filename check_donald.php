<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Exploitation;
use App\Services\FinancialIndicatorsService;

$exploitation = Exploitation::where('nom', 'Donald')->first();

if (!$exploitation) {
    echo "❌ Exploitation 'Donald' non trouvée" . PHP_EOL;
    exit(1);
}

echo "=== EXPLOITATION: " . $exploitation->nom . " ===" . PHP_EOL;
echo "User ID: " . $exploitation->user_id . PHP_EOL;

$service = app(FinancialIndicatorsService::class);
$resultats = $service->calculerExploitation($exploitation->id, null);

echo "\nIndicateurs consolidés :" . PHP_EOL;
echo json_encode($resultats['consolide'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;

echo "\nPar activité :" . PHP_EOL;
foreach ($resultats['par_activite'] as $id => $ind) {
    echo "  Activite {$id}: " . json_encode($ind, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
}
