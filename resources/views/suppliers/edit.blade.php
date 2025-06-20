@extends('layouts.tabler')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center mb-3">
            <div class="col">
                <h2 class="page-title">
                    {{ __('Modifier le Fournisseur') }}
                </h2>
            </div>
        </div>

        @include('partials._breadcrumbs', ['model' => $supplier])
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row row-cards">

            <form action="{{ route('suppliers.update', $supplier) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('put')
                <div class="row">
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-body">
                                <h3 class="card-title">
                                    {{ __('Image de Profil') }}
                                </h3>

                                <img
                                    class="img-account-profile mb-2"
                                    src="{{ $supplier->photo ? asset('storage/suppliers/' . $supplier->photo) : asset('assets/img/demo/user-placeholder.svg') }}"
                                    id="image-preview"
                                />

                                <div class="small font-italic text-muted mb-2">JPG ou PNG, taille max 1 Mo</div>
                                <input class="form-control form-control-solid mb-2 @error('photo') is-invalid @enderror" type="file"  id="image" name="photo" accept="image/*" onchange="previewImage();">
                                @error('photo')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <div>
                                    <h3 class="card-title">
                                        {{ __('Détails du Fournisseur') }}
                                    </h3>
                                </div>

                                <div class="card-actions">
                                    <x-action.close route="{{ route('suppliers.index') }}" />
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row row-cards">
                                    <div class="col-md-12">
                                        <x-input name="name" label="Nom" :value="old('name', $supplier->name)" :required="true"/>
                                        <x-input name="email" label="Adresse e-mail" :value="old('email', $supplier->email)" :required="true"/>
                                        <x-input name="shopname" label="Nom du magasin" :value="old('shopname', $supplier->shopname)" :required="true"/>
                                        <x-input name="phone" label="Numéro de téléphone" :value="old('phone', $supplier->phone)" :required="true"/>
                                    </div>

                                    <div class="col-sm-6 col-md-6">
                                        <label for="type" class="form-label required">
                                            Type de fournisseur
                                        </label>

                                        <select class="form-select @error('type') is-invalid @enderror" id="type" name="type">
                                            @foreach(\App\Enums\SupplierType::cases() as $supplierType)
                                            <option value="{{ $supplierType->value }}" @selected(old('type', $supplier->type) == $supplierType->value)>
                                                {{ $supplierType->label() }}
                                            </option>
                                            @endforeach
                                        </select>

                                        @error('type')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                        @enderror
                                    </div>

                                    <div class="col-sm-6 col-md-6">
                                        <label for="bank_name" class="form-label required">
                                            Nom de la Banque
                                        </label>

                                        <select class="form-select @error('bank_name') is-invalid @enderror" id="bank_name" name="bank_name">
                                            <option selected="" disabled="">Sélectionner une banque:</option>
                                            <option value="BRI" @if(old('bank_name', $supplier->bank_name) == 'BRI')selected="selected"@endif>BRI</option>
                                            <option value="BNI" @if(old('bank_name', $supplier->bank_name) == 'BNI')selected="selected"@endif>BNI</option>
                                            <option value="BCA" @if(old('bank_name', $supplier->bank_name) == 'BCA')selected="selected"@endif>BCA</option>
                                            <option value="BSI" @if(old('bank_name', $supplier->bank_name) == 'BSI')selected="selected"@endif>BSI</option>
                                            <option value="Mandiri" @if(old('bank_name', $supplier->bank_name) == 'Mandiri')selected="selected"@endif>Mandiri</option>
                                        </select>

                                        @error('bank_name')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                        @enderror
                                    </div>

                                    <div class="col-sm-6 col-md-6">
                                        <x-input name="account_holder"
                                                 label="Titulaire du compte"
                                                 :value="old('account_holder', $supplier->account_holder)"
                                        />
                                    </div>

                                    <div class="col-sm-6 col-md-6">
                                        <x-input name="account_number"
                                                 label="Numéro de compte"
                                                 :value="old('account_number', $supplier->account_number)"
                                        />
                                    </div>

                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="address" class="form-label required">
                                                {{ __('Adresse ') }}
                                            </label>

                                            <textarea id="address"
                                                      name="address"
                                                      rows="3"
                                                      class="form-control @error('address') is-invalid @enderror"
                                            >{{ old('address', $supplier->address) }}</textarea>

                                            @error('address')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-end">
                                <x-button.save type="submit">
                                    {{ __('Mettre à jour') }}
                                </x-button.save>

                                <x-button.back route="{{ route('suppliers.index') }}">
                                    {{ __('Annuler') }}
                                </x-button.back>
                            </div>
                        </div>

                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@pushonce('page-scripts')
<script src="{{ asset('assets/js/img-preview.js') }}"></script>
@endpushonce