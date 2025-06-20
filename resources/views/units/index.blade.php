@extends('layouts.tabler')

@section('content')
<div class="page-body">
    @if($units->isEmpty())
        <x-empty
            title="{{ __('Aucune unité trouvée') }}"
            message="{{ __('Essayez d\'ajuster votre recherche ou votre filtre pour trouver ce que vous cherchez.') }}"
            button_label="{{ __('Ajouter votre première unité') }}"
            button_route="{{ route('units.create') }}"
        />
    @else
        <div class="container-xl">
            <x-alert/>

            @livewire('tables.unit-table')
        </div>
    @endif
</div>
@endsection