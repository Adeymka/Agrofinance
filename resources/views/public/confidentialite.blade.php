@extends('layouts.app-public')
@section('title', 'Politique de confidentialité — AgroFinance+')
@section('meta-description', 'Comment AgroFinance+ traite vos données personnelles : finalités, durées, droits et contact.')

@section('content')
<section style="padding-top:120px; padding-bottom:80px; padding-left:clamp(1.25rem,4vw,3rem); padding-right:clamp(1.25rem,4vw,3rem); background:#0D1F0D;">
  <div style="max-width:720px; margin:0 auto;">
    <h1 style="font-family:'Space Grotesk',sans-serif; font-size:clamp(1.75rem,4vw,2.25rem); color:white; margin:0 0 12px;">Politique de confidentialité</h1>
    <p style="font-family:'Inter',sans-serif; font-size:14px; color:rgba(255,255,255,0.45); margin:0 0 40px;">Dernière mise à jour : mars 2026. Texte d’information simple ; pour toute question juridique précise, adaptez avec un conseil compétent.</p>

    <div style="font-family:'Inter',sans-serif; font-size:15px; line-height:1.75; color:rgba(255,255,255,0.78);">
      <h2 style="font-size:17px; color:#a7f3d0; margin:28px 0 12px;">1. Qui sommes-nous ?</h2>
      <p style="margin:0 0 16px;">AgroFinance+ est un service numérique qui permet de suivre l’activité financière de votre exploitation agricole (recettes, dépenses) et d’afficher des <strong>indicateurs financiers agricoles</strong> (par exemple soldes et marges sur une période).</p>

      <h2 style="font-size:17px; color:#a7f3d0; margin:28px 0 12px;">2. Quelles données collectons-nous ?</h2>
      <p style="margin:0 0 12px;">Nous traitons notamment :</p>
      <ul style="margin:0 0 16px; padding-left:1.25rem;">
        <li>identité et contact : nom, prénom, numéro de téléphone ;</li>
        <li>données de compte : code PIN (stocké sous forme sécurisée, jamais en clair) ;</li>
        <li>données d’exploitation : exploitations, campagnes, transactions et éventuels justificatifs que vous choisissez d’ajouter ;</li>
        <li>données techniques : journaux techniques du service, adresse IP lors des connexions (pour la sécurité et le bon fonctionnement).</li>
      </ul>

      <h2 style="font-size:17px; color:#a7f3d0; margin:28px 0 12px;">3. Pourquoi utilisons-nous ces données ?</h2>
      <p style="margin:0 0 12px;">Les finalités principales sont :</p>
      <ul style="margin:0 0 16px; padding-left:1.25rem;">
        <li>créer et gérer votre compte ;</li>
        <li>afficher vos tableaux de bord et indicateurs ;</li>
        <li>générer des rapports (PDF) si votre formule le permet ;</li>
        <li>gérer l’abonnement et les paiements ;</li>
        <li>envoyer des codes par SMS lors de l’inscription ou la vérification du numéro ;</li>
        <li>assurer la sécurité du service (limitation des tentatives de connexion, prévention des abus).</li>
      </ul>
      <p style="margin:0 0 16px;">La base légale habituelle est l’<strong>exécution du contrat</strong> (vous utilisez le service) et, pour certaines opérations, l’<strong>intérêt légitime</strong> de sécuriser la plateforme.</p>

      <h2 style="font-size:17px; color:#a7f3d0; margin:28px 0 12px;">4. Combien de temps conservons-nous les données ?</h2>
      <p style="margin:0 0 16px;">Les données de compte et d’exploitation sont conservées pendant la durée d’utilisation du service et selon les règles de votre formule (par exemple limite d’historique pour certaines offres). Des durées plus courtes peuvent s’appliquer aux codes à usage unique (OTP) et aux journaux techniques.</p>

      <h2 style="font-size:17px; color:#a7f3d0; margin:28px 0 12px;">5. Qui peut avoir accès à vos données ?</h2>
      <p style="margin:0 0 16px;">Outre les personnes habilitées au sein de l’éditeur, des <strong>prestataires techniques</strong> peuvent intervenir : hébergement du site et de la base de données, envoi de SMS, passerelle de paiement. Ces acteurs ne traitent les données que pour les besoins du service et dans le cadre de leurs engagements (sous-traitance conformément à la réglementation applicable).</p>

      <h2 style="font-size:17px; color:#a7f3d0; margin:28px 0 12px;">6. Vos droits</h2>
      <p style="margin:0 0 16px;">Selon le droit applicable, vous pouvez demander l’accès à vos données, leur rectification, leur effacement, la limitation du traitement ou vous opposer à certains traitements. Pour exercer vos droits, contactez-nous via la page <a href="{{ route('contact') }}" style="color:#4ade80;">Contact</a>. Une réponse pourra être préparée manuellement dans un délai raisonnable.</p>

      <h2 style="font-size:17px; color:#a7f3d0; margin:28px 0 12px;">7. Sécurité</h2>
      <p style="margin:0 0 16px;">Nous mettons en œuvre des mesures adaptées (connexion sécurisée en production, stockage des fichiers justificatifs hors accès public direct, limitation des tentatives de connexion). Aucun système n’est toutefois exempt de risque : protégez votre téléphone et ne partagez pas votre PIN.</p>

      <h2 style="font-size:17px; color:#a7f3d0; margin:28px 0 12px;">8. Modifications</h2>
      <p style="margin:0 0 16px;">Cette page peut être mise à jour. La date en tête d’article indique la dernière révision importante.</p>
    </div>
  </div>
</section>
@endsection
