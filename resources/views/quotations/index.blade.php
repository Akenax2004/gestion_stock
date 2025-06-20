@extends('layouts.tabler')

@section('content')
    <div class="page-body">
        @if($quotations->isEmpty())
            <x-empty
                title="Aucune offre trouvée"
                message="Essayez d'ajuster votre recherche ou votre filtre pour trouver ce que vous cherchez."
                button_label="{{ __('Ajouter votre première Offre') }}"
                button_route="{{ route('quotations.create') }}"
            />
        @else
            <div class="container-xl">
                <x-alert/>

                @livewire('tables.quotation-table')
            </div>
        @endif
    </div>
@endsection