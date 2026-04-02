<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Activite, App\Models\Transaction;

$activites = Activite::with('exploitation')->get();
echo "=== CAMPAGNES ===" . PHP_EOL;
foreach ($activites as $a) {
    $nbTx = $a->transactions->count();
    $totalRecettes = $a->transactions->where('type', 'recette')->sum('montant');
    $totalDepenses = $a->transactions->where('type', 'depense')->sum('montant');
    echo "ID: {$a->id} | Nom: {$a->nom} | Statut: {$a->statut} | Exploitation: {$a->exploitation->nom} | Transactions: {$nbTx} | Recettes: {$totalRecettes} | Dépenses: {$totalDepenses}" . PHP_EOL;
}
