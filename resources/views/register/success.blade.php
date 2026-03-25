@extends('layouts.app')

@section('title', 'Inscription confirmee')

@section('content')
<div class="max-w-lg mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-10">
        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 mb-3">Votre inscription est confirmee !</h1>
        <p class="text-gray-600 mb-2">
            Merci pour votre inscription a PointagePro.
        </p>
        <p class="text-gray-600 mb-6">
            Un email contenant vos identifiants de connexion a ete envoye a votre adresse email. Veuillez verifier votre boite de reception.
        </p>

        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6 text-left">
            <p class="text-sm font-semibold text-blue-900 mb-2">Prochaines etapes :</p>
            <ul class="text-sm text-blue-800 space-y-1 list-disc list-inside">
                <li>Consultez votre email pour vos identifiants</li>
                <li>Connectez-vous a votre espace client</li>
                <li>Commencez a gerer vos ouvriers</li>
            </ul>
        </div>

        <a href="{{ route('login') }}" class="inline-flex items-center bg-blue-600 text-white font-semibold px-8 py-3 rounded-lg hover:bg-blue-700 transition">
            Se connecter
            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
        </a>
    </div>
</div>
@endsection
