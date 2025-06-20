@extends('layouts.tabler')

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl mb-3">
            <div class="row g-2 align-items-center mb-3">
                <div class="col">
                    <h2 class="page-title">
                        {{ $user->name }}
                    </h2>
                </div>
            </div>

            @include('partials._breadcrumbs', ['model' => $user])
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">

                <div class="row">
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-body">
                                <h3 class="card-title">
                                    {{ __('Image de profil') }}
                                </h3>

                                <img id="image-preview"
                                     class="img-account-profile mb-2"
                                     src="{{ $user->photo ? asset('storage/profile/'.$user->photo) : asset('assets/img/demo/user-placeholder.svg') }}"
                                     alt=""
                                >
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    {{ __('Détails de l\'utilisateur') }}
                                </h3>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered card-table table-vcenter text-nowrap datatable">
                                    <tbody>
                                        <tr>
                                            <td>{{ __('Nom') }}</td>
                                            <td>{{ $user->name }}</td>
                                        </tr>
                                        <tr>
                                            <td>{{ __('Adresse e-mail') }}</td>
                                            <td>{{ $user->email }}</td>
                                        </tr>
                                        <tr>
                                            <td>{{ __('Nom d\'utilisateur') }}</td>
                                            <td>{{ $user->username }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="card-footer text-end">
                                <x-button.edit route="{{ route('users.edit', $user) }}">
                                    {{ __('Modifier') }}
                                </x-button.edit>

                                <x-button.back route="{{ route('users.index') }}">
                                    {{ __('Retour') }}
                                </x-button.back>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection