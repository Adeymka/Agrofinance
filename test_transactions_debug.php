<?php
require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

use App\Models\User;
use App\Models\Exploitation;
use App\Models\Activite;
use App\Models\Transaction;

$user = User::where('telephone', '+22956392567')->first();
$uid = $user->id;

// Target exploitation Donald (ID: 8)
$exp = Exploitation::find(8);
$activiteId = 23; // Transformation arrachide

echo "=== TRANSACTIONS DEBUG ===\n\n";

$txs = Transaction::where('activite_id', $activiteId)->get();
echo "Transactions pour activité $activiteId: {$txs->count()}\n\n";

foreach ($txs as $tx) {
    echo "ID: {$tx->id}\n";
    echo "  Type: {$tx->type}\n";
    echo "  Montant: " . number_format($tx->montant, 0, ',', ' ') . " FCFA\n";
    echo "  Date: {$tx->date_transaction}\n";
    echo "  Catégorie: {$tx->categorie}\n";
    echo "  Nature: {$tx->nature}\n";
    echo "\n";
}

echo "\n=== ACTIVITÉ INFO ===\n";
$activite = Activite::find($activiteId);
if ($activite) {
    echo "Nom: {$activite->nom}\n";
    echo "Exploitation: {$activite->exploitation_id}\n";
    echo "Statut: {$activite->statut}\n";
    echo "Date début: {$activite->date_debut}\n";
    echo "Date fin: {$activite->date_fin}\n";
    echo "Budget: {$activite->budget_previsionnel}\n";
}

echo "\n=== TEST CALCUL SERVICE ===\n";
$service = new App\Services\FinancialIndicatorsService();

// Test direct calculation on this activity
$result = $service->calculer($activiteId);
echo "Calcul direct pour activité $activiteId:\n";
echo "  PB: " . ($result['PB'] ?? 'N/A') . "\n";
echo "  CT: " . ($result['CT'] ?? 'N/A') . "\n";
echo "  MB: " . ($result['MB'] ?? 'N/A') . "\n";
echo "  RNE: " . ($result['RNE'] ?? 'N/A') . "\n";
echo "  RF: " . ($result['RF'] ?? 'N/A') . "\n";

// Test exploitation calculation
echo "\nCalcul exploitation 8:\n";
$result2 = $service->calculerExploitation(8);
echo "  Consolide PB: " . ($result2['consolide']['PB'] ?? 'N/A') . "\n";
echo "  Consolide CT: " . ($result2['consolide']['CT'] ?? 'N/A') . "\n";
echo "  Consolide RNE: " . ($result2['consolide']['RNE'] ?? 'N/A') . "\n";

if (isset($result2['par_activite'][23])) {
    echo "\nPar activité 23:\n";
    print_r($result2['par_activite'][23]);
} else {
    echo "\nPar activité 23: PAS TROUVÉE !\n";
    echo "Keys dans par_activite: " . implode(', ', array_keys($result2['par_activite'] ?? [])) . "\n";
}
