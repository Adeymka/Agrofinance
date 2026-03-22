<?php

namespace Database\Seeders\Support;

/**
 * Contenus HTML des articles du centre d'aide (résumé + corps).
 * Ajouter ici les articles au fil de la rédaction (slug => [resume, contenu]).
 */
final class HelpArticleBody
{
    /** Slugs de la rubrique « Premiers pas » (ordre logique). */
    public const PREMIERS_PAS_SLUGS = [
        'premiers-pas-creer-mon-compte',
        'premiers-pas-comprendre-otp',
        'premiers-pas-gerer-pin',
        'premiers-pas-creer-exploitation',
        'premiers-pas-choisir-type-exploitation',
    ];

    /**
     * @return array{resume: string|null, contenu: string}
     */
    public static function forSlug(string $slug, string $placeholderHtml): array
    {
        return match ($slug) {
            'premiers-pas-creer-mon-compte' => [
                'resume' => 'Créez votre compte AgroFinance+ en 3 minutes avec votre numéro de téléphone.',
                'contenu' => self::premiersPasCreerMonCompte(),
            ],
            'premiers-pas-comprendre-otp' => [
                'resume' => 'Le code OTP est un code à 6 chiffres envoyé par SMS pour vérifier votre numéro de téléphone.',
                'contenu' => self::premiersPasComprendreOtp(),
            ],
            'premiers-pas-gerer-pin' => [
                'resume' => 'Votre PIN à 4 chiffres remplace le mot de passe. Apprenez à le créer et à le gérer.',
                'contenu' => self::premiersPasGererPin(),
            ],
            'premiers-pas-creer-exploitation' => [
                'resume' => 'Votre exploitation est votre ferme ou unité de production. Créez-la en 2 minutes.',
                'contenu' => self::premiersPasCreerExploitation(),
            ],
            'premiers-pas-choisir-type-exploitation' => [
                'resume' => 'Le type d’exploitation détermine les catégories de transactions proposées pour vos dépenses et recettes.',
                'contenu' => self::premiersPasChoisirTypeExploitation(),
            ],
            default => [
                'resume' => null,
                'contenu' => $placeholderHtml,
            ],
        };
    }

    private static function premiersPasCreerMonCompte(): string
    {
        return <<<'HTML'
<p>Créer un compte AgroFinance+ est simple et rapide. Vous n'avez besoin que de votre <strong>numéro de téléphone béninois</strong>. Pas d'email, pas de carte bancaire.</p>

<h2>Ce dont vous avez besoin</h2>
<ul>
  <li>Votre numéro de téléphone (+229 XX XX XX XX)</li>
  <li>Environ 3 minutes de votre temps</li>
  <li>Votre téléphone ou ordinateur avec connexion internet</li>
</ul>

<h2>Étape 1 — Aller sur la page d'inscription</h2>
<p>Ouvrez votre navigateur et allez sur <strong>agrofinanceplus.bj/inscription</strong> ou cliquez sur le bouton <strong>"Créer un compte"</strong> depuis la page d'accueil.</p>

<h2>Étape 2 — Remplir le formulaire</h2>
<p>Vous devez renseigner :</p>
<ul>
  <li><strong>Votre prénom et nom</strong> — utilisés sur vos rapports PDF</li>
  <li><strong>Votre numéro de téléphone</strong> — c'est votre identifiant de connexion. Format : +229 suivi de 8 chiffres. Exemple : +22967000001</li>
  <li><strong>Type d'exploitation</strong> — choisissez parmi : Cultures vivrières, Élevage, Maraîchage, Transformation ou Mixte. Ce choix adapte les catégories de transactions à votre activité.</li>
</ul>
<p>Cliquez ensuite sur <strong>"Continuer →"</strong>.</p>

<h2>Étape 3 — Vérifier votre numéro (OTP)</h2>
<p>Un code à 6 chiffres est envoyé par SMS sur votre numéro. Saisissez ce code pour confirmer que le numéro vous appartient. Voir l'article <a href="/aide/premiers-pas/premiers-pas-comprendre-otp">Comprendre le code OTP</a> pour plus d'informations.</p>

<h2>Étape 4 — Créer votre PIN</h2>
<p>Choisissez un PIN à 4 chiffres. Ce code vous servira à vous connecter à chaque fois. Choisissez un code que vous pouvez retenir facilement mais difficile à deviner pour quelqu'un d'autre. Évitez 1234, 0000 ou votre date de naissance.</p>

<h2>Étape 5 — Vous êtes prêt !</h2>
<p>Votre compte est créé. Vous bénéficiez automatiquement de <strong>75 jours d'essai gratuit</strong>. Connectez-vous avec votre numéro de téléphone et votre PIN pour accéder à votre tableau de bord.</p>

<blockquote>💡 Vous avez oublié votre PIN ? Contactez-nous via WhatsApp pour le réinitialiser.</blockquote>
HTML;
    }

    private static function premiersPasComprendreOtp(): string
    {
        return <<<'HTML'
<p>Quand vous créez un compte ou demandez un nouveau code, AgroFinance+ vous envoie un <strong>code OTP</strong> (One Time Password) par SMS. Ce code à 6 chiffres confirme que le numéro de téléphone vous appartient vraiment.</p>

<h2>Pourquoi ce code ?</h2>
<p>AgroFinance+ utilise votre numéro de téléphone comme identifiant principal. Le code OTP garantit que personne ne peut créer un compte avec un numéro qui ne lui appartient pas. C'est une mesure de sécurité pour protéger vos données financières.</p>

<h2>Comment recevoir le code ?</h2>
<ul>
  <li>Le code est envoyé par <strong>SMS</strong> sur le numéro que vous avez saisi lors de l'inscription</li>
  <li>Le SMS arrive généralement en <strong>moins de 30 secondes</strong></li>
  <li>L'expéditeur s'appelle <strong>AgroFinance</strong></li>
</ul>

<h2>Règles importantes</h2>
<ul>
  <li>Le code est valable <strong>10 minutes</strong> seulement. Passé ce délai, demandez un nouveau code.</li>
  <li>Vous avez droit à <strong>5 tentatives</strong> maximum. Après 5 erreurs, le code est bloqué pendant 15 minutes.</li>
  <li>Ne partagez jamais ce code avec quelqu'un d'autre.</li>
</ul>

<h2>Je n'ai pas reçu le code — Que faire ?</h2>
<ul>
  <li>Vérifiez que votre numéro est correct (format +229 XX XX XX XX)</li>
  <li>Attendez au moins 60 secondes avant de cliquer sur "Renvoyer le code"</li>
  <li>Vérifiez que votre téléphone capte bien le réseau</li>
  <li>Vérifiez que votre boîte SMS n'est pas pleine</li>
</ul>

<h2>Renvoyer un nouveau code</h2>
<p>Si vous n'avez pas reçu le code ou s'il a expiré, cliquez sur le lien <strong>"Renvoyer le code"</strong> sur la page de vérification. Un nouveau code à 6 chiffres vous sera envoyé immédiatement.</p>
HTML;
    }

    private static function premiersPasGererPin(): string
    {
        return <<<'HTML'
<p>Le PIN (Personal Identification Number) est un code à <strong>4 chiffres</strong> que vous choisissez lors de la création de votre compte. Il remplace le mot de passe traditionnel pour vous connecter rapidement depuis votre téléphone.</p>

<h2>Pourquoi un PIN et pas un mot de passe ?</h2>
<p>AgroFinance+ est conçu pour être utilisé principalement sur téléphone mobile. Un PIN à 4 chiffres est :</p>
<ul>
  <li>Plus rapide à saisir sur un téléphone</li>
  <li>Plus facile à retenir</li>
  <li>Stocké de manière sécurisée (chiffré) dans la base de données — AgroFinance+ ne connaît jamais votre vrai PIN</li>
</ul>

<h2>Choisir un bon PIN</h2>
<p>Un bon PIN est facile à retenir pour vous mais difficile à deviner pour les autres :</p>
<ul>
  <li>✅ Un code lié à un souvenir personnel connu de vous seul</li>
  <li>✅ Les 4 derniers chiffres d'un numéro que vous connaissez par cœur</li>
  <li>❌ Évitez 1234, 4321, 0000, 1111</li>
  <li>❌ Évitez votre date de naissance (facilement devinable)</li>
  <li>❌ Évitez les 4 premiers chiffres de votre numéro de téléphone</li>
</ul>

<h2>Comment créer son PIN</h2>
<p>À la fin de l'inscription, après avoir vérifié votre numéro par OTP, vous arrivez sur la page "Créez votre PIN". Saisissez votre PIN deux fois pour confirmer, puis cliquez sur <strong>"Créer mon PIN"</strong>.</p>

<h2>Sécurité de votre PIN</h2>
<p>Votre PIN est <strong>haché</strong> (chiffré de manière irréversible) avant d'être stocké. Cela signifie que même l'équipe AgroFinance+ ne peut pas voir votre PIN. Si vous l'oubliez, il ne peut pas être récupéré — seulement réinitialisé.</p>

<h2>J'ai oublié mon PIN — Que faire ?</h2>
<p>Actuellement, si vous avez oublié votre PIN, contactez le support AgroFinance+ via WhatsApp. Après vérification de votre identité, votre PIN sera réinitialisé et vous pourrez en créer un nouveau.</p>

<blockquote>🔒 Ne partagez jamais votre PIN avec quelqu'un d'autre, même quelqu'un qui dit être du support AgroFinance+. Nous ne vous demanderons jamais votre PIN.</blockquote>
HTML;
    }

    private static function premiersPasCreerExploitation(): string
    {
        return <<<'HTML'
<p>Une <strong>exploitation</strong> dans AgroFinance+ représente votre ferme ou unité de production agricole. C'est le niveau principal qui regroupe toutes vos campagnes agricoles. Après votre première connexion, AgroFinance+ vous guide automatiquement vers la création de votre exploitation.</p>

<h2>Accéder à la création d'exploitation</h2>
<p>Si vous n'avez pas encore d'exploitation, vous êtes automatiquement redirigé vers la page de création. Vous pouvez aussi y accéder depuis le menu en cliquant sur <strong>"Créer une exploitation"</strong>.</p>

<h2>Les informations à renseigner</h2>

<h3>Le nom de l'exploitation</h3>
<p>Donnez un nom clair à votre exploitation. Ce nom apparaîtra sur votre tableau de bord et dans vos rapports PDF. Exemples : "Ferme Akobi", "Exploitation Adjonou", "Maraîchage Calavi".</p>

<h3>Le type d'exploitation</h3>
<p>Choisissez le type qui correspond le mieux à votre activité principale. Ce choix est important car il détermine les catégories de transactions qui vous seront proposées en priorité lors de la saisie :</p>
<ul>
  <li>🌽 <strong>Cultures vivrières</strong> — maïs, mil, sorgho, manioc, igname...</li>
  <li>🐔 <strong>Élevage</strong> — poulets, bovins, porcins, ovins, caprins...</li>
  <li>🥬 <strong>Maraîchage</strong> — tomates, piment, légumes verts, oignon...</li>
  <li>🪴 <strong>Transformation</strong> — huile de palme, farine, jus de fruits...</li>
  <li>🌿 <strong>Mixte</strong> — si vous combinez plusieurs types d'activités</li>
</ul>

<h3>La localisation (optionnel)</h3>
<p>Indiquez la zone géographique de votre exploitation. Exemple : "Abomey-Calavi, Atlantique" ou "Glazoué, Collines". Cette information apparaît dans vos rapports PDF.</p>

<h2>Combien d'exploitations puis-je créer ?</h2>
<p>Le nombre d'exploitations dépend de votre plan d'abonnement :</p>
<ul>
  <li><strong>Plan Gratuit / Essentielle</strong> — 1 exploitation</li>
  <li><strong>Plan Pro</strong> — jusqu'à 5 exploitations</li>
  <li><strong>Plan Coopérative</strong> — exploitations illimitées</li>
</ul>
<p>Si vous avez plusieurs fermes distinctes, le plan Pro ou Coopérative est recommandé.</p>

<h2>Après la création</h2>
<p>Une fois votre exploitation créée, vous êtes redirigé vers votre <strong>tableau de bord</strong>. Il sera vide pour l'instant — c'est normal ! La prochaine étape est de créer votre première <strong>campagne agricole</strong>.</p>

<blockquote>💡 Conseil : Si vous avez plusieurs types d'activités (ex : cultures et élevage), choisissez le type "Mixte". Vous aurez accès à toutes les catégories de transactions.</blockquote>
HTML;
    }

    private static function premiersPasChoisirTypeExploitation(): string
    {
        return <<<'HTML'
<p>Le <strong>type d’exploitation</strong> que vous choisissez à l’inscription ou lors de la création de votre exploitation sert surtout à une chose : vous proposer les <strong>bonnes catégories</strong> pour enregistrer vos dépenses et vos recettes selon votre activité (cultures, élevage, maraîchage, transformation, ou plusieurs à la fois).</p>
<p>Les écrans principaux (menus, tableau de bord, indicateurs) fonctionnent de la même façon pour tout le monde : ce qui change, c’est la <strong>liste des catégories</strong> proposées quand vous saisissez une transaction, pour coller à votre filière.</p>

<h2>Les 5 types disponibles</h2>

<h3>🌽 Cultures vivrières</h3>
<p>Pour les producteurs de céréales et tubercules : maïs, mil, sorgho, riz, manioc, igname, patate douce. Les catégories proposées incluent : semences, engrais minéraux, pesticides, herbicides, main-d’œuvre, transport, vente au marché, vente bord champ.</p>

<h3>🐔 Élevage</h3>
<p>Pour les éleveurs de volailles, bovins, porcins, ovins ou caprins. Les catégories proposées incluent : aliments animaux (provende), vaccins, médicaments vétérinaires, eau d’abreuvement, vente d’animaux, vente de lait, vente d’œufs.</p>

<h3>🥬 Maraîchage</h3>
<p>Pour les producteurs de légumes et cultures de saison : tomates, piment, oignon, laitue, carotte, concombre. Les catégories proposées incluent : semences légumes, compost, eau d’irrigation, fongicides, vente au marché, vente aux restaurateurs.</p>

<h3>🪴 Transformation</h3>
<p>Pour les unités de transformation agricole : huile de palme, farine de manioc, jus de fruits, gari, fromage de soja (tofu). Les catégories proposées incluent : matières premières, énergie, emballages, produits de transformation, vente de produits transformés.</p>

<h3>🌿 Mixte</h3>
<p>Pour les exploitations qui combinent plusieurs filières. AgroFinance+ vous propose l’ensemble des catégories de tous les types, regroupées par famille. C’est le choix recommandé si vous faites à la fois de l’élevage et des cultures, par exemple.</p>

<h2>Puis-je changer de type plus tard ?</h2>
<p>Oui. Vous pouvez modifier le type de votre exploitation depuis votre <strong>profil</strong> ou depuis la page de votre exploitation. Le changement prend effet immédiatement sur les catégories proposées lors des prochaines saisies. Les transactions passées ne sont pas affectées.</p>

<h2>Et si mon exploitation évolue ?</h2>
<p>C’est normal que votre exploitation change avec le temps. Si vous commencez avec des cultures vivrières et que vous ajoutez de l’élevage, passez en type <strong>Mixte</strong>. Vous aurez ainsi accès à toutes les catégories sans restriction.</p>

<blockquote>💡 En cas de doute, choisissez « Mixte ». Vous aurez accès à toutes les catégories et pourrez affiner plus tard selon votre activité principale.</blockquote>
HTML;
    }
}
