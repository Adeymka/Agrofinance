@props(['statut' => 'rouge'])
@php
    $statut = in_array($statut, ['vert', 'orange', 'rouge'], true) ? $statut : 'rouge';
    $map = [
        'vert' => ['bg' => 'bg-emerald-500', 'ring' => 'ring-emerald-400/40'],
        'orange' => ['bg' => 'bg-amber-500', 'ring' => 'ring-amber-400/40'],
        'rouge' => ['bg' => 'bg-red-500', 'ring' => 'ring-red-400/40'],
    ];
    $c = $map[$statut];
@endphp
<span {{ $attributes->merge(['class' => 'inline-flex h-2.5 w-2.5 shrink-0 rounded-full ' . $c['bg'] . ' ring-2 ' . $c['ring']]) }} title="{{ ucfirst($statut) }}" aria-hidden="true"></span>
