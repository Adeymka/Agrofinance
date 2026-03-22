@extends('layouts.app-public')
@section('title', $article->titre . ' — Centre d\'aide AgroFinance+')
@section('meta-description', $article->resume ?? \Illuminate\Support\Str::limit(strip_tags($article->contenu), 160))

@section('content')

<section class="aide-page-inner" style="background:#0D1F0D; padding:100px 48px 80px;">
  <div style="max-width:1100px; margin:0 auto;">

    <div style="display:flex; align-items:center; gap:8px; margin-bottom:40px;
                flex-wrap:wrap;">
      <a href="{{ route('aide.index') }}"
         style="font-family:'Inter',sans-serif; font-size:13px;
                color:rgba(74,222,128,0.70); text-decoration:none;"
         onmouseover="this.style.color='#4ade80'"
         onmouseout="this.style.color='rgba(74,222,128,0.70)'">
        Centre d'aide
      </a>
      <svg xmlns="http://www.w3.org/2000/svg" style="width:14px;height:14px;
           color:rgba(255,255,255,0.20);" fill="none" viewBox="0 0 24 24"
           stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
      </svg>
      <a href="{{ route('aide.categorie', $categorie->slug) }}"
         style="font-family:'Inter',sans-serif; font-size:13px;
                color:rgba(74,222,128,0.70); text-decoration:none;"
         onmouseover="this.style.color='#4ade80'"
         onmouseout="this.style.color='rgba(74,222,128,0.70)'">
        {{ $categorie->nom }}
      </a>
      <svg xmlns="http://www.w3.org/2000/svg" style="width:14px;height:14px;
           color:rgba(255,255,255,0.20);" fill="none" viewBox="0 0 24 24"
           stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
      </svg>
      <span style="font-family:'Inter',sans-serif; font-size:13px;
                   color:rgba(255,255,255,0.45);">
        {{ $article->titre }}
      </span>
    </div>

    <div class="aide-article-layout" style="display:grid; gap:56px; align-items:start;">

      <div class="aide-article-toc" style="position:sticky; top:90px;">

        <p style="font-family:'Inter',sans-serif; font-size:11px; font-weight:600;
                  color:rgba(255,255,255,0.28); text-transform:uppercase;
                  letter-spacing:0.12em; margin:0 0 12px;">
          {{ $categorie->nom }}
        </p>
        <div style="display:flex; flex-direction:column; gap:4px; margin-bottom:28px;">
          @foreach($articlesCategorie as $art)
          <a href="{{ route('aide.article', [$categorie->slug, $art->slug]) }}"
             style="display:block; text-decoration:none; padding:8px 12px;
                    border-radius:8px; font-family:'Inter',sans-serif; font-size:13px;
                    font-weight:{{ $art->id === $article->id ? '600' : '400' }};
                    color:{{ $art->id === $article->id ? 'rgba(255,255,255,0.90)' : 'rgba(255,255,255,0.45)' }};
                    background:{{ $art->id === $article->id ? 'rgba(74,222,128,0.10)' : 'transparent' }};
                    border-left:2px solid {{ $art->id === $article->id ? '#4ade80' : 'transparent' }};
                    transition:all 0.2s;"
             onmouseover="this.style.color='rgba(255,255,255,0.80)';this.style.background='rgba(255,255,255,0.05)'"
             onmouseout="this.style.color='{{ $art->id === $article->id ? 'rgba(255,255,255,0.90)' : 'rgba(255,255,255,0.45)' }}';
                         this.style.background='{{ $art->id === $article->id ? 'rgba(74,222,128,0.10)' : 'transparent' }}'">
            {{ $art->titre }}
          </a>
          @endforeach
        </div>

        <div style="height:1px; background:rgba(255,255,255,0.07); margin-bottom:20px;"></div>

        <a href="{{ route('aide.categorie', $categorie->slug) }}"
           style="display:flex; align-items:center; gap:8px; text-decoration:none;
                  font-family:'Inter',sans-serif; font-size:12px;
                  color:rgba(255,255,255,0.35); transition:color 0.2s;"
           onmouseover="this.style.color='rgba(74,222,128,0.70)'"
           onmouseout="this.style.color='rgba(255,255,255,0.35)'">
          <svg xmlns="http://www.w3.org/2000/svg" style="width:13px;height:13px;"
               fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
          </svg>
          Retour à {{ $categorie->nom }}
        </a>

      </div>

      <div class="aide-article-main">

        <h1 style="font-family:'Space Grotesk',sans-serif; font-size:34px; font-weight:700;
                   color:white; letter-spacing:-0.03em; line-height:1.15; margin:0 0 20px;">
          {{ $article->titre }}
        </h1>

        <div style="display:flex; align-items:center; gap:16px; margin-bottom:36px;
                    padding-bottom:28px;
                    border-bottom:1px solid rgba(255,255,255,0.08);">
          <span style="display:inline-flex; align-items:center; gap:6px;
                       background:rgba(74,222,128,0.08);
                       border:1px solid rgba(74,222,128,0.18);
                       border-radius:999px; padding:4px 12px;
                       font-family:'Inter',sans-serif; font-size:12px;
                       font-weight:500; color:rgba(74,222,128,0.80);">
            {{ $categorie->icone }} {{ $categorie->nom }}
          </span>
          <span style="font-family:'Inter',sans-serif; font-size:12px;
                       color:rgba(255,255,255,0.28);">
            {{ $article->vues }} lecture{{ $article->vues > 1 ? 's' : '' }}
          </span>
        </div>

        <div class="aide-content">
          {!! $article->contenu !!}
        </div>

        @if($article->images->count() > 0)
        <div style="display:flex; flex-direction:column; gap:20px; margin-top:32px;">
          @foreach($article->images as $img)
          <figure style="margin:0;">
            <img src="{{ asset('storage/' . $img->chemin) }}"
                 alt="{{ $img->alt ?? $article->titre }}"
                 style="width:100%; border-radius:14px;
                         border:1px solid rgba(255,255,255,0.10);
                         box-shadow:0 16px 40px rgba(0,0,0,0.40);">
            @if($img->legende)
            <figcaption style="font-family:'Inter',sans-serif; font-size:12px;
                               color:rgba(255,255,255,0.30); text-align:center;
                               margin-top:10px; font-style:italic;">
              {{ $img->legende }}
            </figcaption>
            @endif
          </figure>
          @endforeach
        </div>
        @endif

        <div style="height:1px; background:rgba(255,255,255,0.08);
                    margin:48px 0 36px;"></div>

        <div id="aideVoteBlock" style="background:rgba(255,255,255,0.04);
                    border:1px solid rgba(255,255,255,0.08);
                    border-radius:14px; padding:20px 24px;
                    display:flex; align-items:center; justify-content:space-between;
                    margin-bottom:36px; flex-wrap:wrap; gap:12px;">
          <span style="font-family:'Inter',sans-serif; font-size:14px;
                       color:rgba(255,255,255,0.55);">
            Cet article vous a aidé ?
          </span>
          <div style="display:flex; gap:10px;">
            <button type="button" onclick="voterArticle(true)"
                    style="display:flex; align-items:center; gap:6px; cursor:pointer;
                           background:rgba(74,222,128,0.10);
                           border:1px solid rgba(74,222,128,0.25);
                           border-radius:8px; padding:7px 16px;
                           font-family:'Inter',sans-serif; font-size:13px;
                           font-weight:500; color:rgba(74,222,128,0.80);
                           transition:all 0.2s;"
                    onmouseover="this.style.background='rgba(74,222,128,0.18)'"
                    onmouseout="this.style.background='rgba(74,222,128,0.10)'">
              👍 Oui
            </button>
            <button type="button" onclick="voterArticle(false)"
                    style="display:flex; align-items:center; gap:6px; cursor:pointer;
                           background:rgba(255,255,255,0.05);
                           border:1px solid rgba(255,255,255,0.12);
                           border-radius:8px; padding:7px 16px;
                           font-family:'Inter',sans-serif; font-size:13px;
                           font-weight:500; color:rgba(255,255,255,0.45);
                           transition:all 0.2s;"
                    onmouseover="this.style.background='rgba(255,255,255,0.10)'"
                    onmouseout="this.style.background='rgba(255,255,255,0.05)'">
              👎 Non
            </button>
          </div>
          <div id="voteFeedback" style="display:none; width:100%;
               font-family:'Inter',sans-serif; font-size:13px;
               color:rgba(74,222,128,0.70);">
            Merci pour votre retour !
          </div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;" class="aide-article-nav">

          @if($precedent)
          <a href="{{ route('aide.article', [$categorie->slug, $precedent->slug]) }}"
             style="display:flex; align-items:center; gap:12px; text-decoration:none;
                    padding:16px 18px; border-radius:12px;
                    background:rgba(255,255,255,0.04);
                    border:1px solid rgba(255,255,255,0.08);
                    transition:all 0.2s;"
             onmouseover="this.style.background='rgba(255,255,255,0.08)';this.style.borderColor='rgba(255,255,255,0.15)'"
             onmouseout="this.style.background='rgba(255,255,255,0.04)';this.style.borderColor='rgba(255,255,255,0.08)'">
            <svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;
                 flex-shrink:0;color:rgba(255,255,255,0.30);" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
            <div>
              <div style="font-family:'Inter',sans-serif; font-size:11px;
                          color:rgba(255,255,255,0.28); margin-bottom:3px;">
                Article précédent
              </div>
              <div style="font-family:'Inter',sans-serif; font-size:13px;
                          font-weight:500; color:rgba(255,255,255,0.70);">
                {{ \Illuminate\Support\Str::limit($precedent->titre, 50) }}
              </div>
            </div>
          </a>
          @else
          <div></div>
          @endif

          @if($suivant)
          <a href="{{ route('aide.article', [$categorie->slug, $suivant->slug]) }}"
             style="display:flex; align-items:center; justify-content:flex-end;
                    gap:12px; text-decoration:none;
                    padding:16px 18px; border-radius:12px;
                    background:rgba(255,255,255,0.04);
                    border:1px solid rgba(255,255,255,0.08);
                    transition:all 0.2s;"
             onmouseover="this.style.background='rgba(255,255,255,0.08)';this.style.borderColor='rgba(255,255,255,0.15)'"
             onmouseout="this.style.background='rgba(255,255,255,0.04)';this.style.borderColor='rgba(255,255,255,0.08)'">
            <div style="text-align:right;">
              <div style="font-family:'Inter',sans-serif; font-size:11px;
                          color:rgba(255,255,255,0.28); margin-bottom:3px;">
                Article suivant
              </div>
              <div style="font-family:'Inter',sans-serif; font-size:13px;
                          font-weight:500; color:rgba(255,255,255,0.70);">
                {{ \Illuminate\Support\Str::limit($suivant->titre, 50) }}
              </div>
            </div>
            <svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;
                 flex-shrink:0;color:rgba(255,255,255,0.30);" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
          </a>
          @endif

        </div>

      </div>
    </div>
  </div>
</section>

@endsection

@push('scripts')
<script>
function voterArticle(positif) {
  document.querySelectorAll('#aideVoteBlock button').forEach(function (b) {
    b.disabled = true;
  });
  document.getElementById('voteFeedback').style.display = 'block';
}
</script>
@endpush
