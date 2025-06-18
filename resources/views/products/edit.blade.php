@extends('layouts.tabler')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center mb-3">
            <div class="col">
                <h2 class="page-title">
                    {{ __('Edit Product') }}
                </h2>
            </div>
        </div>

        @include('partials._breadcrumbs', ['model' => $product])
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row row-cards">

            <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('put')

                <div class="row">
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-body">
                                <h3 class="card-title">
                                    {{ __('Product Image') }}
                                </h3>

                                <img
                                    class="img-account-profile mb-2"
                                    src="{{ $product->image ? asset('storage/products/images/'.$product->image) : asset('assets/img/products/default.webp') }}" {{-- Updated path for image --}}
                                    id="image-preview"
                                >

                                <div class="small font-italic text-muted mb-2">
                                    JPG ou PNG, max 2 Mo
                                </div>

                                <input
                                    type="file"
                                    accept="image/*"
                                    id="image"
                                    name="image" {{-- Changed from product_image to image, matching controller validation --}}
                                    class="form-control @error('image') is-invalid @enderror"
                                    onchange="previewImage();"
                                >

                                @error('image') {{-- Changed from product_image to image --}}
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-8">

                        <div class="card">
                            <div class="card-body">
                                <h3 class="card-title">
                                    {{ __('Product Details') }}
                                </h3>

                                <div class="row row-cards">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">
                                                Nom du Produit
                                                <span class="text-danger">*</span>
                                            </label>

                                            <input type="text"
                                                   id="name"
                                                   name="name"
                                                   class="form-control @error('name') is-invalid @enderror"
                                                   placeholder="Nom du produit"
                                                   value="{{ old('name', $product->name) }}"
                                                   required {{-- Added required attribute --}}
                                            >

                                            @error('name')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-sm-6 col-md-6">
                                        <div class="mb-3">
                                            <label for="category_id" class="form-label">
                                                Catégorie du Produit
                                                <span class="text-danger">*</span>
                                            </label>

                                            @if ($categories->count() === 1 && $categories->first())
                                                {{-- Si une seule catégorie disponible, la sélectionner par défaut et la rendre lecture seule --}}
                                                <select name="category_id" id="category_id"
                                                        class="form-select @error('category_id') is-invalid @enderror"
                                                        readonly
                                                >
                                                    <option value="{{ $categories->first()->id }}" selected>
                                                        {{ $categories->first()->name }}
                                                    </option>
                                                </select>
                                                <input type="hidden" name="category_id" value="{{ $categories->first()->id }}"> {{-- Champ caché pour s'assurer que la valeur est soumise --}}
                                            @else
                                                <select name="category_id" id="category_id"
                                                        class="form-select @error('category_id') is-invalid @enderror"
                                                        required {{-- Added required attribute --}}
                                                >
                                                    <option value="" disabled="">Sélectionner une catégorie:</option>
                                                    @foreach ($categories as $category)
                                                        <option value="{{ $category->id }}" @if(old('category_id', $product->category_id) == $category->id) selected="selected" @endif>{{ $category->name }}</option>
                                                    @endforeach
                                                </select>
                                            @endif

                                            @error('category_id')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>
                                    </div>


                                    <div class="col-sm-6 col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="unit_id">
                                                Unité
                                                <span class="text-danger">*</span>
                                            </label>

                                            @if ($units->count() === 1 && $units->first())
                                                {{-- Si une seule unité disponible, la sélectionner par défaut et la rendre lecture seule --}}
                                                <select name="unit_id" id="unit_id"
                                                        class="form-select @error('unit_id') is-invalid @enderror"
                                                        readonly
                                                >
                                                    <option value="{{ $units->first()->id }}" selected>
                                                        {{ $units->first()->name }}
                                                    </option>
                                                </select>
                                                <input type="hidden" name="unit_id" value="{{ $units->first()->id }}"> {{-- Champ caché pour s'assurer que la valeur est soumise --}}
                                            @else
                                                <select name="unit_id" id="unit_id"
                                                        class="form-select @error('unit_id') is-invalid @enderror"
                                                        required {{-- Added required attribute --}}
                                                >
                                                    <option value="" disabled="">
                                                        Sélectionner une unité:
                                                    </option>

                                                    @foreach ($units as $unit)
                                                        <option value="{{ $unit->id }}" @if(old('unit_id', $product->unit_id) == $unit->id) selected="selected" @endif>{{ $unit->name }} ({{ $unit->short_code }})</option>
                                                    @endforeach
                                                </select>
                                            @endif

                                            @error('unit_id')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-sm-6 col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="buying_price">
                                                Prix d'Achat
                                                <span class="text-danger">*</span>
                                            </label>

                                            <input type="number" {{-- Changed type to number --}}
                                                   id="buying_price"
                                                   name="buying_price"
                                                   class="form-control @error('buying_price') is-invalid @enderror"
                                                   placeholder="0"
                                                   value="{{ old('buying_price', $product->buying_price) }}"
                                                   step="0.01" min="0" {{-- Added step and min for currency --}}
                                                   required {{-- Added required attribute --}}
                                            >

                                            @error('buying_price')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-sm-6 col-md-6">
                                        <div class="mb-3">
                                            <label for="selling_price" class="form-label">
                                                Prix de Vente
                                                <span class="text-danger">*</span>
                                            </label>

                                            <input type="number" {{-- Changed type to number --}}
                                                   id="selling_price"
                                                   name="selling_price"
                                                   class="form-control @error('selling_price') is-invalid @enderror"
                                                   placeholder="0"
                                                   value="{{ old('selling_price', $product->selling_price) }}"
                                                   step="0.01" min="0" {{-- Added step and min for currency --}}
                                                   required {{-- Added required attribute --}}
                                            >

                                            @error('selling_price')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-sm-6 col-md-6">
                                        <div class="mb-3">
                                            <label for="quantity" class="form-label">
                                                Quantité
                                                <span class="text-danger">*</span>
                                            </label>

                                            <input type="number"
                                                   id="quantity"
                                                   name="quantity"
                                                   class="form-control @error('quantity') is-invalid @enderror"
                                                   min="0"
                                                   value="{{ old('quantity', $product->quantity) }}"
                                                   placeholder="0"
                                                   required {{-- Added required attribute --}}
                                            >

                                            @error('quantity')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-sm-6 col-md-6">
                                        <div class="mb-3">
                                            <label for="quantity_alert" class="form-label">
                                                Alerte Quantité
                                                <span class="text-danger">*</span>
                                            </label>

                                            <input type="number"
                                                   id="quantity_alert"
                                                   name="quantity_alert"
                                                   class="form-control @error('quantity_alert') is-invalid @enderror"
                                                   min="0"
                                                   placeholder="0"
                                                   value="{{ old('quantity_alert', $product->quantity_alert) }}"
                                                   required {{-- Added required attribute --}}
                                            >

                                            @error('quantity_alert')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-sm-6 col-md-6">
                                        <div class="mb-3">
                                            <label for="tax" class="form-label">
                                                Taxe
                                            </label>

                                            <input type="number"
                                                   id="tax"
                                                   name="tax"
                                                   class="form-control @error('tax') is-invalid @enderror"
                                                   min="0"
                                                   placeholder="0"
                                                   value="{{ old('tax', $product->tax) }}"
                                                   step="0.01" {{-- Added step for tax --}}
                                            >

                                            @error('tax')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-sm-6 col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="tax_type">
                                                Type de Taxe
                                            </label>

                                            <select name="tax_type" id="tax_type"
                                                    class="form-select @error('tax_type') is-invalid @enderror"
                                            >
                                                <option value="">Sélectionner un type de taxe</option> {{-- Added empty option --}}
                                                @foreach(\App\Enums\TaxType::cases() as $taxType)
                                                <option value="{{ $taxType->value }}" @selected(old('tax_type', $product->tax_type) == $taxType->value)>
                                                    {{ $taxType->label() }}
                                                </option>
                                                @endforeach
                                            </select>

                                            @error('tax_type')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="mb-3"> {{-- Removed mb-0 as it's not needed here and can cause layout issues --}}
                                            <label for="notes" class="form-label">
                                                Notes
                                            </label>

                                            <textarea name="notes"
                                                      id="notes"
                                                      rows="5"
                                                      class="form-control @error('notes') is-invalid @enderror"
                                                      placeholder="Notes sur le produit"
                                            >{{ old('notes', $product->notes) }}</textarea>

                                            @error('notes')
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
                                    Mettre à jour
                                </x-button.save>

                                <x-button.back route="{{ route('products.index') }}">
                                    Annuler
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
