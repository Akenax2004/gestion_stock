@extends('layouts.tabler')

@section('content')
<div class="page-body">
    @if($products->isEmpty())
        <x-empty
            title="Aucun produit trouvé"
            message="Essayez d'ajuster votre recherche ou votre filtre pour trouver ce que vous cherchez."
            button_label="{{ __('Ajouter votre premier Produit') }}"
            button_route="{{ route('products.create') }}"
        />
    @else
        <div class="container container-xl">
            <x-alert/>

            @livewire('tables.product-table')
        </div>
    @endif
</div>
@endsection