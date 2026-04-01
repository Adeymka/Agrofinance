@props(['active' => 'dashboard'])

<aside
    class="w-64 shrink-0 min-h-screen border-r border-white/10 bg-[#0a1810]/90 backdrop-blur-xl text-[#E3EED4] flex flex-col"
>
    <div class="p-6 border-b border-white/10">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2 font-serif text-xl font-bold text-[#F0A83A] tracking-tight">
            <img src="{{ asset('images/logo-agrofinanceplus.png') }}" alt="Logo AgroFinance+" class="w-7 h-7 rounded-md object-contain" />
            AgroFinance+
        </a>
        <p class="text-xs text-[#6B9071] mt-1">Pilotage agricole</p>
    </div>
    <nav class="flex-1 p-3 space-y-0.5 overflow-y-auto text-sm">
        @php
            $links = [
                ['key' => 'dashboard', 'label' => 'Tableau de bord', 'href' => route('dashboard'), 'icon' => 'chart-bar'],
                ['key' => 'exploitations', 'label' => 'Exploitations', 'href' => route('exploitations.index'), 'icon' => 'building-office-2'],
                ['key' => 'transactions-index', 'label' => 'Historique transactions', 'href' => route('transactions.index'), 'icon' => 'document-text'],
                ['key' => 'nouvelle-exploitation', 'label' => 'Nouvelle exploitation', 'href' => route('exploitations.create'), 'icon' => 'plus-circle'],
                ['key' => 'campagnes', 'label' => 'Campagnes', 'href' => route('activites.index'), 'icon' => 'leaf'],
                ['key' => 'nouvelle-campagne', 'label' => 'Nouvelle campagne', 'href' => route('activites.create'), 'icon' => 'plus-circle'],
                ['key' => 'saisie', 'label' => 'Saisie rapide', 'href' => route('transactions.create'), 'icon' => 'bolt'],
                ['key' => 'rapports', 'label' => 'Rapports', 'href' => route('rapports.index'), 'icon' => 'document-text'],
                ['key' => 'abonnement', 'label' => 'Abonnement', 'href' => route('abonnement'), 'icon' => 'credit-card'],
                ['key' => 'profil', 'label' => 'Profil', 'href' => route('profil'), 'icon' => 'user-circle'],
            ];
        @endphp
        @foreach ($links as $link)
            <a
                href="{{ $link['href'] }}"
                class="flex items-center gap-3 rounded-lg px-3 py-2.5 transition
                    {{ $active === $link['key']
                        ? 'bg-white/10 text-[#F0A83A] border border-white/10'
                        : 'text-[#AEC3B0] hover:bg-white/5 hover:text-[#E3EED4]' }}"
            >
                <span class="w-6 flex items-center justify-center shrink-0 opacity-90">
                    <x-icon :name="$link['icon']" class="w-5 h-5" />
                </span>
                <span>{{ $link['label'] }}</span>
            </a>
        @endforeach
    </nav>
    <div class="p-4 border-t border-white/10">
        <form method="POST" action="{{ route('deconnexion') }}">
            @csrf
            <button
                type="submit"
                class="w-full text-left text-xs text-[#6B9071] hover:text-[#E3EED4] py-2"
            >
                Déconnexion
            </button>
        </form>
    </div>
</aside>
