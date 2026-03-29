@extends('layouts.app-public')
@section('title', 'Conditions d’utilisation — AgroFinance+')
@section('meta-description', 'Conditions d’utilisation du service AgroFinance+ : rôle de l’outil, responsabilités et abonnement.')

@section('content')
<section style="padding-top:120px; padding-bottom:80px; padding-left:clamp(1.25rem,4vw,3rem); padding-right:clamp(1.25rem,4vw,3rem); background:#0D1F0D;">
  <div style="max-width:720px; margin:0 auto;">
    <h1 style="font-family:'Space Grotesk',sans-serif; font-size:clamp(1.75rem,4vw,2.25rem); color:white; margin:0 0 12px;">Conditions d’utilisation</h1>
    <p style="font-family:'Inter',sans-serif; font-size:14px; color:rgba(255,255,255,0.45); margin:0 0 40px;">Dernière mise à jour : mars 2026. Document d’information ; faites valider par un professionnel du droit si besoin.</p>

    <div style="font-family:'Inter',sans-serif; font-size:15px; line-height:1.75; color:rgba(255,255,255,0.78);">
      <h2 style="font-size:17px; color:#a7f3d0; margin:28px 0 12px;">1. Objet du service</h2>
      <p style="margin:0 0 16px;">AgroFinance+ est un outil d’aide à la <strong>gestion et au suivi</strong> de l’activité agricole (saisie des flux, indicateurs financiers agricoles, rapports selon formule). Il ne remplace pas un comptable, un expert-comptable ni un conseiller juridique ou financier.</p>

      <h2 style="font-size:17px; color:#a7f3d0; margin:28px 0 12px;">2. Compte et accès</h2>
      <p style="margin:0 0 16px;">Vous êtes responsable de la confidentialité de votre <strong>numéro de téléphone</strong> et de votre <strong>code PIN</strong>. Toute action effectuée depuis votre compte est réputée faite par vous, sauf fraude dûment signalée. Vous devez fournir des informations exactes lors de l’inscription.</p>

      <h2 style="font-size:17px; color:#a7f3d0; margin:28px 0 12px;">3. Utilisation loyale</h2>
      <p style="margin:0 0 16px;">Il est interdit d’utiliser le service pour des activités illégales, pour tenter d’accéder aux données d’autres utilisateurs, ou pour surcharger volontairement la plateforme. Des limitations techniques (par exemple sur les tentatives de connexion) peuvent s’appliquer pour protéger le service.</p>

      <h2 style="font-size:17px; color:#a7f3d0; margin:28px 0 12px;">4. Abonnement et paiement</h2>
      <p style="margin:0 0 16px;">Certaines fonctions dépendent d’un <strong>abonnement</strong> actif et du <strong>plan</strong> souscrit. Les tarifs et modalités de paiement (mobile money, etc.) sont présentés avant souscription. En cas d’impayé ou de fin d’abonnement, l’accès aux fonctions payantes peut être restreint conformément aux règles affichées dans l’application.</p>

      <h2 style="font-size:17px; color:#a7f3d0; margin:28px 0 12px;">5. Données et indicateurs</h2>
      <p style="margin:0 0 16px;">Les chiffres et indicateurs calculés dans l’application repose sur <strong>vos saisies</strong>. Vous restez responsable de la qualité et de l’exhaustivité des informations. Les rapports et tableaux de bord sont des aides à la décision, pas une garantie de résultat économique ou d’obtention de crédit.</p>

      <h2 style="font-size:17px; color:#a7f3d0; margin:28px 0 12px;">6. Disponibilité</h2>
      <p style="margin:0 0 16px;">Nous visons une bonne disponibilité du service, mais des interruptions (maintenance, réseau, cas de force majeure) peuvent survenir. Le mode hors ligne sur mobile permet de retarder l’envoi des données ; la synchronisation nécessite une connexion.</p>

      <h2 style="font-size:17px; color:#a7f3d0; margin:28px 0 12px;">7. Contact</h2>
      <p style="margin:0 0 16px;">Pour toute question sur ces conditions, utilisez la page <a href="{{ route('contact') }}" style="color:#4ade80;">Contact</a>. La <a href="{{ route('confidentialite') }}" style="color:#4ade80;">politique de confidentialité</a> précise le traitement des données personnelles.</p>
    </div>
  </div>
</section>
@endsection
