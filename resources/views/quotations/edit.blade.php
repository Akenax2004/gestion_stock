@extends('layouts.tabler')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <x-alert/>
            
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                {{ __('Modifier le devis') }} #{{ $quotation->reference }}
                            </h3>
                            <div class="card-actions">
                                <a href="{{ route('quotations.index') }}" class="btn btn-outline-secondary">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-arrow-left" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="m0 0h24v24H0z" fill="none"></path>
                                        <line x1="5" y1="12" x2="19" y2="12"></line>
                                        <line x1="5" y1="12" x2="11" y2="18"></line>
                                        <line x1="5" y1="12" x2="11" y2="6"></line>
                                    </svg>
                                    {{ __('Retour à la liste') }}
                                </a>
                            </div>
                        </div>

                        @livewire('forms.quotation-edit-form', [
                            'quotation' => $quotation,
                            'customers' => $customers,
                            'products' => $products
                        ])
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection