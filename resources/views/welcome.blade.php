@extends('layouts.app-desktop')

@section('content')
    <main class="max-w-md mx-auto px-4 pt-12 text-center">
        <h1 class="text-agro-vert text-3xl font-bold inline-flex items-center justify-center gap-2">
            <x-icon name="leaf" class="w-9 h-9 text-agro-vert" /> AgroFinance+
        </h1>
        <p class="mt-2 text-gray-700 font-medium">Gestion financière agricole</p>

        <div class="mt-8 flex justify-center">
            <a href="#" class="btn-primary">
                Commencer
            </a>
        </div>
    </main>
@endsection

