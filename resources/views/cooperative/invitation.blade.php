@extends($layout)
@section('title', 'Invitation coopérative — AgroFinance+')
@section('page-title', 'Invitation coopérative')
@section('page-subtitle', $invitation->cooperative?->nom ?? 'Coopérative')

@section('content')
<div class="max-w-2xl mx-auto card p-5 space-y-4">
    <p class="text-sm text-gray-600">
        Vous êtes connecté avec <strong>{{ $actor->telephone }}</strong>.
    </p>

    <div class="text-sm space-y-1">
        <p>Rôle proposé : <strong>{{ ucfirst($invitation->role) }}</strong></p>
        <p>Téléphone invité : <strong>{{ $invitation->invited_phone }}</strong></p>
        <p>Expire le : <strong>{{ $invitation->invitation_expires_at?->format('d/m/Y H:i') ?? '—' }}</strong></p>
    </div>

    @if($invitation->statut === 'active')
        <div class="rounded-lg border border-emerald-300/40 bg-emerald-500/15 px-3 py-2 text-sm">
            Cette invitation est déjà acceptée.
        </div>
    @elseif($isExpired)
        <div class="rounded-lg border border-amber-300/40 bg-amber-500/15 px-3 py-2 text-sm">
            Cette invitation a expiré.
        </div>
    @elseif($isPhoneMismatch)
        <div class="rounded-lg border border-red-300/40 bg-red-500/15 px-3 py-2 text-sm">
            Le numéro connecté ne correspond pas au numéro invité.
        </div>
    @else
        <form method="POST" action="{{ route('cooperative.invitation.accept', ['token' => $invitation->invitation_token]) }}">
            @csrf
            <button type="submit" class="btn-primary">Accepter l’invitation</button>
        </form>
    @endif
</div>
@endsection
