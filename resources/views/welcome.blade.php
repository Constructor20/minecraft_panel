@extends('layouts.app')

@section('title', 'Accueil')

@section('content')
<div class= text-center py-24>
    <h1 class=text-4xl font-bold mb-4>Minecraft Panel</h1>
    <p class=text-gray-400 mb-8>Gérez vos serveurs Minecraft facilement</p>
    @guest
        <div class=flex justify-center gap-4>
            <a href=/login class=px-6 py-3 bg-green-600 hover:bg-green-500 rounded-lg font-semibold>Connexion</a>
            <a href=/register class=px-6 py-3 bg-gray-700 hover:bg-gray-600 rounded-lg font-semibold>Inscription</a>
        </div>
    @endguest
    @auth
        <a href=/dashboard class=px-6 py-3 bg-green-600 hover:bg-green-500 rounded-lg font-semibold>Tableau de bord</a>
    @endauth
</div>
@endsection
