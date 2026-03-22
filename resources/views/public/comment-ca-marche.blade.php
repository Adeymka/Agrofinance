@extends('layouts.app-public')
@section('title', 'Comment ça marche — AgroFinance+')
@section('meta-description', 'Découvrez comment AgroFinance+ calcule vos indicateurs et génère vos rapports pour la microfinance.')

@section('content')
<section style="background:#0D1F0D; min-height:70vh; padding:120px 48px 80px;">
  <div style="max-width:720px; margin:0 auto; text-align:center;">
    <h1 style="font-family:'Space Grotesk',sans-serif; font-size:40px; font-weight:700; color:white; margin:0 0 16px;">
      Comment ça marche
    </h1>
    <p style="font-family:'Inter',sans-serif; font-size:16px; color:rgba(255,255,255,0.45); line-height:1.7; margin:0 0 32px;">
      Page détaillée à compléter : parcours inscription, campagnes, saisies, dashboard et partage PDF.
      En attendant, consultez les <strong style="color:rgba(255,255,255,0.75);">4 étapes</strong> sur la
      <a href="{{ route('accueil') }}" style="color:#4ade80;">page d'accueil</a>.
    </p>
    <a href="{{ route('accueil') }}"
       style="display:inline-flex; align-items:center; gap:8px; font-family:'Inter',sans-serif; font-size:14px; font-weight:600;
              color:white; text-decoration:none; background:#16a34a; padding:12px 24px; border-radius:12px;
              border:1px solid rgba(74,222,128,0.30);">
      Retour à l'accueil
    </a>
  </div>
</section>
@endsection
