@extends('layouts.tabler')

@section('content')
<div class="page-body">
    @if($purchases->isEmpty())
    <x-empty
        title="Aucun achat trouvé"
        message="Essayez d'ajuster votre recherche ou votre filtre pour trouver ce que vous cherchez."
        button_label="{{ __('Ajouter votre premier Achat') }}"
        button_route="{{ route('purchases.create') }}"
    />
    @else
    <div class="container-xl">
        <x-alert/>

        @livewire('tables.purchase-table')
    </div>
    @endif
</div>
@endsection