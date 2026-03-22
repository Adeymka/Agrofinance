{{-- Modal de confirmation « abandonner la campagne » — style glass AgroFinance+ --}}
@props([
    'formId' => 'agro-form-abandon',
    'modalId' => 'agro-modal-abandon',
])

<form id="{{ $formId }}" method="POST" action="" class="hidden" aria-hidden="true">
    @csrf
</form>

<div
    id="{{ $modalId }}"
    class="hidden fixed inset-0 z-[200] flex items-center justify-center p-4 sm:p-6"
    aria-hidden="true"
    role="dialog"
    aria-modal="true"
    aria-labelledby="{{ $modalId }}-title"
>
    {{-- Fond assombri + flou --}}
    <div
        class="absolute inset-0 z-0 bg-black/60 backdrop-blur-sm transition-opacity"
        data-abandon-backdrop
        aria-hidden="true"
    ></div>

    <div
        class="relative z-10 w-full max-w-md rounded-[20px] border border-white/15 bg-[rgba(12,28,12,0.92)] backdrop-blur-xl shadow-[0_24px_80px_rgba(0,0,0,0.45)] p-6 sm:p-8 text-left"
        style="box-shadow: 0 0 0 1px rgba(255,255,255,0.06) inset;"
    >
        <div class="flex gap-4">
            <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl border border-amber-400/25 bg-amber-500/15">
                <svg class="h-6 w-6 text-amber-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.897 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <h2 id="{{ $modalId }}-title" class="font-display text-lg font-semibold tracking-tight text-white">
                    Abandonner cette campagne ?
                </h2>
                <p class="mt-2 text-sm leading-relaxed text-white/65">
                    Elle disparaîtra du tableau de bord actif et passera dans l’onglet
                    <span class="text-white/90 font-medium">Abandonnées</span>.
                    Vous ne pourrez plus ajouter ni modifier de transactions sur cette campagne.
                    <span class="text-amber-200/90">Cette action est définitive.</span>
                </p>
            </div>
        </div>

        <div class="mt-8 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <button
                type="button"
                class="btn-outline w-full justify-center sm:w-auto"
                data-abandon-cancel
            >
                Annuler
            </button>
            <button
                type="button"
                class="btn-danger w-full justify-center sm:w-auto inline-flex items-center gap-2"
                data-abandon-confirm
            >
                Oui, abandonner
            </button>
        </div>
    </div>
</div>

@once
    @push('scripts')
        <script>
            (function () {
                var form = document.getElementById(@json($formId));
                var modal = document.getElementById(@json($modalId));
                if (!form || !modal) return;

                function open(url) {
                    if (url) form.setAttribute('action', url);
                    modal.classList.remove('hidden');
                    modal.setAttribute('aria-hidden', 'false');
                    document.body.classList.add('overflow-hidden');
                }

                function close() {
                    modal.classList.add('hidden');
                    modal.setAttribute('aria-hidden', 'true');
                    document.body.classList.remove('overflow-hidden');
                }

                document.querySelectorAll('[data-open-abandon-modal]').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        var url = btn.getAttribute('data-abandon-url');
                        open(url);
                    });
                });

                modal.querySelectorAll('[data-abandon-cancel], [data-abandon-backdrop]').forEach(function (el) {
                    el.addEventListener('click', function () {
                        close();
                    });
                });

                var confirmBtn = modal.querySelector('[data-abandon-confirm]');
                if (confirmBtn) {
                    confirmBtn.addEventListener('click', function () {
                        if (form.getAttribute('action')) {
                            form.submit();
                        }
                    });
                }

                document.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                        close();
                    }
                });
            })();
        </script>
    @endpush
@endonce
