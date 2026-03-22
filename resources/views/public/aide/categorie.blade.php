@extends('layouts.app-public')
@section('title', $categorie->nom . ' — Centre d\'aide AgroFinance+')
@section('meta-description', $categorie->description ?? 'Articles : '.$categorie->nom)

@section('content')

<section class="aide-page-inner" style="background:#0D1F0D; padding:100px 48px 80px;">
  <div style="max-width:1100px; margin:0 auto;">

    <div style="display:flex; align-items:center; gap:8px; margin-bottom:32px;">
      <a href="{{ route('aide.index') }}"
         style="font-family:'Inter',sans-serif; font-size:13px;
                color:rgba(74,222,128,0.70); text-decoration:none;
                transition:color 0.2s;"
         onmouseover="this.style.color='#4ade80'"
         onmouseout="this.style.color='rgba(74,222,128,0.70)'">
        Centre d'aide
      </a>
      <svg xmlns="http://www.w3.org/2000/svg" style="width:14px;height:14px;
           color:rgba(255,255,255,0.20);" fill="none" viewBox="0 0 24 24"
           stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
      </svg>
      <span style="font-family:'Inter',sans-serif; font-size:13px;
                   color:rgba(255,255,255,0.50);">
        {{ $categorie->nom }}
      </span>
    </div>

    <div class="aide-cat-layout" style="display:grid; gap:48px; align-items:start;">

      <div>

        <div style="display:flex; align-items:center; gap:16px; margin-bottom:32px;">
          <div style="width:56px; height:56px; border-radius:16px; font-size:26px;
                      background:rgba(74,222,128,0.10);
                      border:1px solid rgba(74,222,128,0.20);
                      display:flex; align-items:center; justify-content:center;
                      flex-shrink:0;">
            {{ $categorie->icone }}
          </div>
          <div>
            <h1 style="font-family:'Space Grotesk',sans-serif; font-size:30px;
                       font-weight:700; color:white; letter-spacing:-0.025em;
                       margin:0 0 6px;">
              {{ $categorie->nom }}
            </h1>
            <p style="font-family:'Inter',sans-serif; font-size:14px;
                      color:rgba(255,255,255,0.40); margin:0;">
              {{ $categorie->description }}
              · {{ $articles->count() }} article{{ $articles->count() > 1 ? 's' : '' }}
            </p>
          </div>
        </div>

        <div style="display:flex; flex-direction:column; gap:8px;">
          @forelse($articles as $art)
          <a href="{{ route('aide.article', [$categorie->slug, $art->slug]) }}"
             style="display:flex; align-items:center; gap:16px; text-decoration:none;
                    padding:18px 22px; border-radius:14px;
                    background:rgba(255,255,255,0.04);
                    border:1px solid rgba(255,255,255,0.07);
                    transition:all 0.2s;"
             onmouseover="this.style.background='rgba(255,255,255,0.08)';
                          this.style.borderColor='rgba(74,222,128,0.20)';
                          this.style.transform='translateX(4px)'"
             onmouseout="this.style.background='rgba(255,255,255,0.04)';
                         this.style.borderColor='rgba(255,255,255,0.07)';
                         this.style.transform='translateX(0)'">

            <div style="width:28px; height:28px; border-radius:8px; flex-shrink:0;
                        background:rgba(255,255,255,0.06);
                        border:1px solid rgba(255,255,255,0.10);
                        display:flex; align-items:center; justify-content:center;
                        font-family:'Space Grotesk',sans-serif; font-size:12px;
                        font-weight:600; color:rgba(255,255,255,0.35);">
              {{ str_pad((string) $art->ordre, 2, '0', STR_PAD_LEFT) }}
            </div>

            <div style="flex:1;">
              <div style="font-family:'Inter',sans-serif; font-size:15px; font-weight:500;
                          color:rgba(255,255,255,0.85); margin-bottom:3px;">
                {{ $art->titre }}
              </div>
              @if($art->resume)
              <div style="font-family:'Inter',sans-serif; font-size:12px;
                          color:rgba(255,255,255,0.35);">
                {{ $art->resume }}
              </div>
              @endif
            </div>

            <svg xmlns="http://www.w3.org/2000/svg"
                 style="width:16px;height:16px;flex-shrink:0;
                        color:rgba(74,222,128,0.40);"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
            </svg>

          </a>
          @empty
          <div style="padding:40px; text-align:center;
                      font-family:'Inter',sans-serif; font-size:14px;
                      color:rgba(255,255,255,0.30);">
            Aucun article dans cette catégorie pour le moment.
          </div>
          @endforelse
        </div>

      </div>

      <div class="aide-cat-sidebar" style="position:sticky; top:90px;">
        <p style="font-family:'Inter',sans-serif; font-size:11px; font-weight:600;
                  color:rgba(255,255,255,0.28); text-transform:uppercase;
                  letter-spacing:0.12em; margin:0 0 14px;">
          Autres rubriques
        </p>
        <div style="display:flex; flex-direction:column; gap:6px;">
          @foreach($toutesCategories as $cat)
          <a href="{{ route('aide.categorie', $cat->slug) }}"
             style="display:flex; align-items:center; gap:10px; text-decoration:none;
                    padding:10px 14px; border-radius:10px;
                    background:{{ $cat->id === $categorie->id ? 'rgba(74,222,128,0.10)' : 'rgba(255,255,255,0.03)' }};
                    border:1px solid {{ $cat->id === $categorie->id ? 'rgba(74,222,128,0.25)' : 'rgba(255,255,255,0.06)' }};
                    transition:all 0.2s;"
             onmouseover="this.style.background='rgba(255,255,255,0.07)'"
             onmouseout="this.style.background='{{ $cat->id === $categorie->id ? 'rgba(74,222,128,0.10)' : 'rgba(255,255,255,0.03)' }}'">
            <span style="font-size:16px;">{{ $cat->icone }}</span>
            <span style="font-family:'Inter',sans-serif; font-size:13px;
                         font-weight:{{ $cat->id === $categorie->id ? '600' : '400' }};
                         color:{{ $cat->id === $categorie->id ? 'rgba(255,255,255,0.90)' : 'rgba(255,255,255,0.55)' }};">
              {{ $cat->nom }}
            </span>
          </a>
          @endforeach
        </div>

        <a href="{{ route('aide.index') }}"
           style="display:flex; align-items:center; gap:8px; text-decoration:none;
                  margin-top:20px; padding:10px 14px; border-radius:10px;
                  border:1px solid rgba(255,255,255,0.06);
                  transition:all 0.2s;"
           onmouseover="this.style.borderColor='rgba(255,255,255,0.15)'"
           onmouseout="this.style.borderColor='rgba(255,255,255,0.06)'">
          <svg xmlns="http://www.w3.org/2000/svg" style="width:14px;height:14px;
               color:rgba(255,255,255,0.30);" fill="none" viewBox="0 0 24 24"
               stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
          </svg>
          <span style="font-family:'Inter',sans-serif; font-size:13px;
                       color:rgba(255,255,255,0.40);">
            Toutes les rubriques
          </span>
        </a>
      </div>

    </div>
  </div>
</section>

@endsection
