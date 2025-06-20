@extends('layouts.tabler')

@section('content')
<div class="page-body">
    @if($suppliers->isEmpty())
        <x-empty
            title="Aucun fournisseur trouvé"
            message="Essayez d'ajuster votre recherche ou votre filtre pour trouver ce que vous cherchez."
            button_label="{{ __('Ajouter votre premier Fournisseur') }}"
            button_route="{{ route('suppliers.create') }}"
        />
    @else
        <div class="container-xl">
            <x-alert/>

            @livewire('tables.supplier-table')
        </div>
    @endif
</div>
@endsection