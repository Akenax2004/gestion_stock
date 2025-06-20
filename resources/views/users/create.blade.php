@extends('layouts.tabler')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center mb-3">
            <div class="col">
                <h2 class="page-title">
                    {{ __('Créer un utilisateur') }}
                </h2>
            </div>
        </div>

        @include('partials._breadcrumbs')
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row row-cards">

            <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-body">
                                <h3 class="card-title">
                                    {{ __('Image de l\'utilisateur') }}
                                </h3>

                                <img class="img-account-profile mb-2"
                                     src="{{ asset('assets/img/demo/user-placeholder.svg') }}"
                                     alt=""
                                     id="image-preview"
                                >

                                <div class="small font-italic text-muted mb-2">
                                    JPG ou PNG pas plus de 1 Mo
                                </div>

                                <input type="file"
                                       id="image"
                                       name="photo"
                                       accept="image/*"
                                       onchange="previewImage();"
                                       class="form-control @error('photo') is-invalid @enderror"
                                >

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
                            <div class="card-body">
                                <h3 class="card-title">
                                    {{ __('Détails de l\'utilisateur') }}
                                </h3>

                                <div class="row row-cards">
                                    <div class="col-md-12">

                                        <x-input name="name" :value="old('name')" required="true" label="{{ __('Nom') }}"/>

                                        <x-input name="email" :value="old('email')" required="true" label="{{ __('Adresse e-mail') }}"/>

                                        <x-input name="username" :value="old('username')" required="true" label="{{ __('Nom d\'utilisateur') }}"/>

                                    </div>

                                    <div class="col-sm-6 col-md-6">
                                        <x-input type="password" name="password" label="{{ __('Mot de passe') }}"/>
                                    </div>

                                    <div class="sm-6 col-md-6">
                                        <x-input type="password" name="password_confirmation" label="{{ __('Confirmation du mot de passe') }}"/>
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer text-end">
                                <x-button.save type="submit">
                                    {{ __('Enregistrer') }}
                                </x-button.save>

                                <x-button.back route="{{ route('users.index') }}">
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