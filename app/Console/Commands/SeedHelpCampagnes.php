<?php

namespace App\Console\Commands;

use App\Models\HelpArticle;
use Illuminate\Console\Command;

class SeedHelpCampagnes extends Command
{
    protected $signature = 'help:seed-campagnes';

    protected $description = 'Rédiger le contenu des articles Campagnes agricoles';

    public function handle(): int
    {
        $articles = [
            'campagnes-comprendre-campagne' => $this->article1(),
            'campagnes-creer-campagne' => $this->article2(),
            'campagnes-budget-previsionnel' => $this->article3(),
            'campagnes-cloturer-campagne' => $this->article4(),
            'campagnes-statuts-campagne' => $this->article5(),
        ];

        $ok = 0;

        foreach ($articles as $slug => $data) {
            $article = HelpArticle::query()->where('slug', $slug)->first();
            if (! $article) {
                $this->error("✗ Introuvable : {$slug}");

                continue;
            }

            try {
                $article->update([
                    'resume' => $data['resume'],
                    'contenu' => $data['contenu'],
                ]);
                $this->info("✓ {$slug}");
                $ok++;
            } catch (\Throwable $e) {
                $this->error('Erreur : '.$e->getMessage());
                $this->line($e->getTraceAsString());

                return Command::FAILURE;
            }
        }

        $this->newLine();

        if ($ok !== count($articles)) {
            $this->warn('Mise à jour partielle.');

            return Command::FAILURE;
        }

        $this->info('5 articles Campagnes agricoles mis à jour.');

        return Command::SUCCESS;
    }

    private function article1(): array
    {
        return [
            'resume' => 'Une campagne agricole est une période de production avec ses propres dépenses et recettes.',
            'contenu' => <<<'HTML'
<p>Dans AgroFinance+, une <strong>campagne agricole</strong> représente
une période de production bien définie. C'est à ce niveau que vous
enregistrez toutes vos dépenses et recettes, et que vos indicateurs
financiers sont calculés.</p>

<h2>Concrètement, qu'est-ce qu'une campagne ?</h2>
<p>Une campagne correspond à un cycle de production. Par exemple :</p>
<ul>
  <li><strong>Maïs grande saison 2025</strong> — de mars à juillet</li>
  <li><strong>Poulets de chair — Lot avril 2025</strong> — de avril à juin</li>
  <li><strong>Tomates saison sèche</strong> — de novembre à février</li>
  <li><strong>Huile de palme — production mars</strong> — sur 4 semaines</li>
</ul>
<p>Chaque campagne est indépendante. Ses dépenses et recettes ne se
mélangent pas avec celles des autres campagnes.</p>

<h2>Pourquoi créer des campagnes séparées ?</h2>
<p>La séparation par campagne vous permet de savoir précisément
<strong>quelle activité est rentable et laquelle ne l'est pas</strong>.
Si vous mélangez tout, vous ne pouvez pas savoir si c'est votre élevage
ou vos cultures qui vous fait perdre de l'argent.</p>
<p>Exemple concret : vous faites de l'élevage de poulets et du maïs
en même temps. En créant une campagne pour chaque activité, AgroFinance+
calcule séparément la rentabilité de chacune. Vous savez exactement
où vous gagnez et où vous perdez.</p>

<h2>Différence entre Exploitation et Campagne</h2>
<ul>
  <li><strong>Exploitation</strong> = votre ferme dans son ensemble
  (Ferme Akobi, Élevage Adjonou...)</li>
  <li><strong>Campagne</strong> = une période de production précise
  au sein de cette ferme</li>
</ul>
<p>Une exploitation peut avoir plusieurs campagnes actives en même temps.
Par exemple, votre ferme mixte peut avoir simultanément une campagne
maïs et une campagne poulets.</p>

<h2>Combien de campagnes puis-je créer ?</h2>
<p>Il n'y a pas de limite au nombre de campagnes. Vous pouvez créer
autant de campagnes que vous le souhaitez, quel que soit votre plan
d'abonnement.</p>

<h2>Tableau de bord et campagnes</h2>
<p>Votre tableau de bord consolide les indicateurs de toutes vos
campagnes <strong>en cours</strong>. Les campagnes terminées ou
abandonnées n'apparaissent pas dans le tableau de bord principal
mais restent accessibles dans l'historique.</p>

<blockquote>💡 Conseil : Donnez des noms clairs à vos campagnes.
Incluez l'activité et la période. Exemple : "Maïs grande saison 2025"
est plus clair que "Campagne 1".</blockquote>
HTML,
        ];
    }

    private function article2(): array
    {
        return [
            'resume' => 'Créez une nouvelle campagne agricole en quelques secondes pour commencer à enregistrer vos dépenses et recettes.',
            'contenu' => <<<'HTML'
<p>Pour commencer à enregistrer des transactions et voir vos indicateurs
financiers, vous devez d'abord créer une <strong>campagne agricole</strong>.
Voici comment faire étape par étape.</p>

<h2>Accéder à la création de campagne</h2>
<p>Depuis votre tableau de bord ou le menu <strong>Campagnes</strong>,
cliquez sur le bouton <strong>"+ Nouvelle campagne"</strong>. Vous arrivez
sur le formulaire de création.</p>

<h2>Remplir le formulaire</h2>

<h3>Le nom de la campagne</h3>
<p>Donnez un nom clair et précis qui décrit l'activité et la période.
Exemples :</p>
<ul>
  <li>Maïs grande saison 2025</li>
  <li>Poulets de chair — Lot mars 2025</li>
  <li>Tomates saison sèche 2025</li>
  <li>Huile de palme — Avril 2025</li>
</ul>

<h3>Le type de campagne</h3>
<p>Choisissez parmi trois types :</p>
<ul>
  <li>🌽 <strong>Culture</strong> — pour toutes les cultures végétales
  (maïs, manioc, riz, tomates, piment...)</li>
  <li>🐔 <strong>Élevage</strong> — pour l'élevage d'animaux
  (poulets, bovins, porcins, ovins...)</li>
  <li>🪴 <strong>Transformation</strong> — pour la transformation
  de produits agricoles (huile, farine, jus...)</li>
</ul>

<h3>La date de début</h3>
<p>Indiquez la date à laquelle vous avez commencé cette campagne.
Cette date est utilisée pour calculer la durée de la campagne
et filtrer vos transactions.</p>

<h3>La date de fin (optionnel)</h3>
<p>Si vous connaissez déjà la date de fin prévue, vous pouvez
l'indiquer. Vous pouvez aussi la laisser vide et la renseigner
plus tard lors de la clôture.</p>

<h3>Le budget prévisionnel (optionnel)</h3>
<p>Entrez le montant total que vous prévoyez de dépenser pour
cette campagne. Ce champ est <strong>optionnel</strong> mais
très utile : il active les <strong>alertes budget</strong> qui
vous préviennent quand vous avez consommé 70%, 90% ou 100% de
votre budget prévu.</p>
<p>Exemple : si vous prévoyez de dépenser 150 000 FCFA pour votre
campagne maïs, entrez 150000 dans ce champ.</p>

<h2>Après la création</h2>
<p>Une fois la campagne créée, vous pouvez commencer immédiatement
à enregistrer des transactions. La campagne apparaît dans votre
tableau de bord avec des indicateurs à zéro — normal, vous n'avez
pas encore saisi de données.</p>

<blockquote>⚠️ Important : La campagne doit être au statut
<strong>"En cours"</strong> pour que ses données apparaissent
dans votre tableau de bord. Une campagne terminée ou abandonnée
n'est plus prise en compte dans les consolidations.</blockquote>
HTML,
        ];
    }

    private function article3(): array
    {
        return [
            'resume' => 'Le budget prévisionnel active des alertes automatiques à 70%, 90% et 100% pour éviter les dépassements.',
            'contenu' => <<<'HTML'
<p>Le <strong>budget prévisionnel</strong> est le montant total de
dépenses que vous prévoyez pour une campagne. Ce champ est optionnel
mais il active un système d'alertes automatiques très utile pour
piloter votre exploitation.</p>

<h2>Comment définir le budget prévisionnel ?</h2>
<p>Lors de la création d'une campagne, entrez
le montant total estimé de vos dépenses dans le champ
<strong>"Budget prévisionnel"</strong>. Par exemple :</p>
<ul>
  <li>Campagne maïs 1ha → 150 000 FCFA</li>
  <li>Lot de 200 poulets → 250 000 FCFA</li>
  <li>Production huile de palme → 80 000 FCFA</li>
</ul>
<p>Ce montant représente <strong>l'ensemble de vos charges prévues</strong>
(intrants, main-d'œuvre, transport, équipements...) avant d'avoir
vos premières recettes.</p>

<h2>Les 3 niveaux d'alerte</h2>
<p>AgroFinance+ surveille automatiquement la consommation de votre
budget et vous alerte à 3 seuils :</p>
<ul>
  <li>🟡 <strong>70% du budget consommé</strong> — Alerte jaune.
  Vous avez dépensé 70% de votre budget prévu. Moment de faire
  le point et de vérifier si vous êtes dans les prévisions.</li>
  <li>🟠 <strong>90% du budget consommé</strong> — Alerte orange.
  Attention, vous approchez de la limite. Réduisez les dépenses
  non indispensables.</li>
  <li>🔴 <strong>100% du budget dépassé</strong> — Alerte rouge.
  Vous avez dépassé votre budget. Analysez les écarts et prenez
  des mesures correctives.</li>
</ul>

<h2>Où apparaissent les alertes ?</h2>
<p>Les alertes budget apparaissent :</p>
<ul>
  <li>Dans le <strong>tableau de bord</strong> sur la card de
  la campagne concernée (barre de progression colorée)</li>
  <li>Dans la page <strong>détail de la campagne</strong>
  en haut de page avec le pourcentage consommé</li>
</ul>

<h2>Comment est calculé le pourcentage ?</h2>
<p>Le pourcentage est calculé ainsi :</p>
<ul>
  <li>Pourcentage = (Coût Total ÷ Budget prévisionnel) × 100</li>
  <li>Le Coût Total est la somme de <strong>toutes vos dépenses</strong>
  (fixes + variables)</li>
</ul>
<p>Exemples :</p>
<ul>
  <li>Budget prévu = 150 000 FCFA, dépenses = 112 500 FCFA → 75% →
  <strong>alerte jaune</strong> (premier seuil atteint).</li>
  <li>Même budget, dépenses = 142 500 FCFA → 95% →
  <strong>alerte orange</strong> (seuil 90% dépassé).</li>
</ul>

<h2>Puis-je modifier le budget en cours de campagne ?</h2>
<p>Oui, tant que la campagne est <strong>en cours</strong> : si votre budget initial
n'était pas réaliste, vous pouvez ajuster le montant du budget prévisionnel.
Cela ne modifie pas vos transactions déjà enregistrées.</p>

<h2>Campagne sans budget prévisionnel</h2>
<p>Si vous ne définissez pas de budget prévisionnel, la barre de
progression n'apparaît pas et les alertes ne sont pas déclenchées.
Vous pouvez toujours suivre vos dépenses via les indicateurs financiers agricoles.</p>

<blockquote>💡 Conseil : Même une estimation approximative est mieux
que rien. Basez-vous sur vos dépenses des saisons précédentes pour
estimer votre budget. Vous l'affinerez au fil du temps.</blockquote>
HTML,
        ];
    }

    private function article4(): array
    {
        return [
            'resume' => 'Clôturer une campagne termine le cycle de production et exclut la campagne du tableau de bord principal.',
            'contenu' => <<<'HTML'
<p>La <strong>clôture d'une campagne</strong> marque la fin officielle
d'un cycle de production. Une fois clôturée, la campagne passe au
statut <strong>"Terminée"</strong> et n'apparaît plus dans le tableau
de bord principal, mais reste consultable dans l'historique.</p>

<h2>Quand clôturer une campagne ?</h2>
<p>Clôturez une campagne quand :</p>
<ul>
  <li>Vous avez vendu toute votre production</li>
  <li>Vous avez enregistré toutes vos dépenses et recettes</li>
  <li>Le cycle de production est terminé</li>
</ul>
<p>Ne clôturez pas une campagne trop tôt. Attendez d'avoir enregistré
<strong>toutes les transactions</strong> (y compris les paiements tardifs
et les dernières dépenses de récolte) avant de clôturer.</p>

<h2>Comment clôturer une campagne ?</h2>
<ol>
  <li>Allez dans la page <strong>Campagnes</strong> depuis le menu</li>
  <li>Cliquez sur la campagne à clôturer</li>
  <li>En bas de la page de détail, cliquez sur le bouton
  <strong>"Clôturer la campagne"</strong></li>
  <li>Une confirmation vous est demandée — validez pour confirmer</li>
</ol>
<p>AgroFinance+ enregistre automatiquement la date de clôture.</p>

<h2>Marquer une campagne comme abandonnée</h2>
<p>Sur la page de détail d'une campagne <strong>en cours</strong>, sous les actions
en bas de page, vous pouvez utiliser le bouton
<strong>« Marquer comme abandonnée »</strong> si le cycle ne peut pas se terminer
normalement (sinistre, maladie des cultures, manque de financement...).
Une confirmation vous est demandée avant d'appliquer le statut.</p>

<h2>Que se passe-t-il après la clôture ?</h2>
<ul>
  <li>La campagne passe au statut <strong>"Terminée"</strong></li>
  <li>Elle disparaît du tableau de bord principal</li>
  <li>Elle reste accessible dans l'historique des campagnes</li>
  <li>Ses indicateurs finaux (MB, RNE, RF) restent consultables</li>
  <li>Vous ne pouvez plus y ajouter de nouvelles transactions</li>
</ul>

<h2>Puis-je rouvrir une campagne clôturée ?</h2>
<p>Non. Une campagne clôturée ne peut pas être rouverte. Si vous avez
oublié d'enregistrer des transactions, contactez le support AgroFinance+
pour assistance.</p>

<h2>Campagne abandonnée vs campagne terminée</h2>
<ul>
  <li><strong>Terminée</strong> — Le cycle de production s'est achevé
  normalement. Vous avez récolté et vendu votre production.</li>
  <li><strong>Abandonnée</strong> — La campagne n'a pas pu se terminer
  (maladie des cultures, sinistre, manque de financement...).
  Utilisez ce statut pour garder une trace des échecs sans les mélanger
  avec les succès.</li>
</ul>

<blockquote>💡 Conseil : Avant de clôturer, vérifiez que le solde de
vos recettes est complet. Une recette oubliée après clôture ne pourra
plus être ajoutée. Consultez vos notes et carnets avant de valider.</blockquote>
HTML,
        ];
    }

    private function article5(): array
    {
        return [
            'resume' => 'Une campagne peut être En cours, Terminée ou Abandonnée. Chaque statut a un impact sur le tableau de bord.',
            'contenu' => <<<'HTML'
<p>Chaque campagne agricole dans AgroFinance+ a un <strong>statut</strong>
qui indique où elle en est dans son cycle de vie. Ce statut détermine
comment la campagne apparaît dans votre tableau de bord et vos rapports.</p>

<h2>Les 3 statuts possibles</h2>

<h3>🟢 En cours</h3>
<p>C'est le statut initial de toute campagne créée. Une campagne
"En cours" signifie que le cycle de production est actif :</p>
<ul>
  <li>Apparaît dans le <strong>tableau de bord principal</strong></li>
  <li>Ses indicateurs sont inclus dans la <strong>consolidation</strong>
  de l'exploitation</li>
  <li>Vous pouvez y ajouter des transactions</li>
  <li>Les alertes budget sont actives</li>
</ul>

<h3>✅ Terminée</h3>
<p>Une campagne passe en "Terminée" quand vous la clôturez manuellement.
C'est le statut normal à la fin d'un cycle de production réussi :</p>
<ul>
  <li>N'apparaît <strong>plus</strong> dans le tableau de bord principal</li>
  <li>N'est <strong>plus</strong> incluse dans la consolidation active</li>
  <li>Reste consultable dans la section <strong>"Terminées"</strong>
  de la page Campagnes</li>
  <li>Ses indicateurs finaux restent disponibles</li>
  <li>Aucune nouvelle transaction ne peut être ajoutée</li>
</ul>

<h3>❌ Abandonnée</h3>
<p>Une campagne "Abandonnée" est une campagne qui n'a pas pu se terminer
normalement (catastrophe naturelle, maladie des cultures, manque de
financement...) :</p>
<ul>
  <li>N'apparaît <strong>plus</strong> dans le tableau de bord principal</li>
  <li>Reste consultable dans l'onglet <strong>« Abandonnées »</strong> de la page Campagnes</li>
  <li>Permet de garder une trace des pertes sans biaiser les
  statistiques des campagnes réussies</li>
</ul>

<h2>Impact sur le tableau de bord</h2>
<p>Le tableau de bord consolide <strong>uniquement</strong> les campagnes
au statut <strong>"En cours"</strong>. Si vous avez 5 campagnes dont
2 terminées et 1 abandonnée, seules les 2 en cours apparaissent dans
les indicateurs consolidés du tableau de bord.</p>

<h2>Comment changer le statut ?</h2>
<ul>
  <li><strong>En cours → Terminée</strong> : En clôturant la campagne
  via le bouton "Clôturer la campagne"</li>
  <li><strong>En cours → Abandonnée</strong> : En utilisant l'option
  "Marquer comme abandonnée" dans les options de la campagne</li>
  <li><strong>Retour arrière impossible</strong> : Une campagne terminée
  ou abandonnée ne peut pas repasser en "En cours"</li>
</ul>

<h2>Campagnes et rapports PDF</h2>
<p>Vous pouvez générer un rapport PDF pour n'importe quelle campagne,
quel que soit son statut. Les campagnes terminées donnent les meilleurs
rapports car toutes les transactions sont complètes.
Un rapport sur une campagne en cours donne un bilan intermédiaire.</p>

<blockquote>💡 Astuce : Si vous avez une campagne en cours depuis très
longtemps et que vous n'avez plus ajouté de transactions depuis des
semaines, vérifiez si elle doit être clôturée ou abandonnée.
Des campagnes oubliées "En cours" faussent votre tableau de bord.</blockquote>
HTML,
        ];
    }
}
