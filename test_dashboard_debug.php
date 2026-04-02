<?php
require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Http\Kernel');
$request = \Illuminate\Http\Request::capture();
$response = $kernel->handle($request);

use App\Models\User;
use App\Models\Exploitation;
use App\Services\FinancialIndicatorsService;
use App\Services\DashboardService;

// Fetch test user
$user = User::where('telephone', '+22956392567')->first();
if (!$user) {
    echo "Utilisateur non trouvé\n";
    exit;
}

$uid = $user->id;
echo "✓ Utilisateur: $user->prenom $user->nom (ID: $uid)\n";

// Get exploitations
$exploitations = Exploitation::where('user_id', $uid)
    ->with(['activitesActives' => fn ($q) => $q->with('transactions')])
    ->get();

echo "✓ Exploitations détectées: {$exploitations->count()}\n";

$service = new FinancialIndicatorsService();
$dashboardService = new DashboardService();

foreach ($exploitations as $exp) {
    echo "\n--- Exploitation: {$exp->nom} (ID: {$exp->id}) ---\n";
    
    $calcul = $service->calculerExploitation($exp->id, null, null, now()->toDateString());
    $consolide = $calcul['consolide'] ?? [];
    
    echo "  RNE: " . number_format($consolide['RNE'] ?? 0, 0, ',', ' ') . " FCFA\n";
    echo "  RF: " . number_format($consolide['RF'] ?? 0, 1, ',', ' ') . "%\n";
    echo "  MB: " . number_format($consolide['MB'] ?? 0, 0, ',', ' ') . " FCFA\n";
    echo "  Campagnes actives: " . count($calcul['par_activite'] ?? []) . "\n";
    
    $parActivite = $calcul['par_activite'] ?? [];
    $activiteIds = $exp->activites()->where('statut', 'en_cours')->pluck('id');
    
    echo "  activeId Qty: " . $activiteIds->count() . "\n";
    
    // Debug heroGraph resolution
    $heroGraph = $dashboardService->resoudreHeroEtGraphique(null, $activiteIds, $parActivite, $exp);
    echo "  heroActiviteId: " . ($heroGraph['heroActiviteId'] ?? 'null') . "\n";
    echo "  heroInd exists: " . (isset($heroGraph['heroInd']) ? 'yes' : 'no') . "\n";
    if ($heroGraph['heroInd']) {
        echo "    - RNE: " . $heroGraph['heroInd']['RNE'] . "\n";
    }
}
