@extends('layouts.tabler')

@section('content')
<div class="container-xl px-4 mt-4">
    <x-alert/>

    <nav class="nav nav-borders">
        <a class="nav-link ms-0" href="{{ route('profile.edit') }}">Profil</a>
        <a class="nav-link active" href="{{ route('profile.settings') }}">Paramètres</a>
    </nav>

    <hr class="mt-0 mb-4" />

    @include('partials.session')

    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">
                            {{ __('Changer le mot de passe') }}
                        </h3>
                    </div>
                </div>

                <x-form action="{{ route('password.update') }}" method="PUT">
                    <div class="card-body">
                        <x-input type="password" name="current_password" label="Mot de passe actuel" required />
                        <x-input type="password" name="password" label="Nouveau mot de passe" required />
                        <x-input type="password" name="password_confirmation" label="Confirmer le mot de passe" required />
                    </div>

                    <div class="card-footer text-end">
                        <x-button type="submit">{{ __('Enregistrer') }}</x-button>
                    </div>
                </x-form>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    Authentification à deux facteurs
                </div>
                <div class="card-body">
                    <p>
                        Ajoutez un niveau de sécurité supplémentaire à votre compte en activant l'authentification à deux facteurs.
                        Nous vous enverrons un message texte pour vérifier vos tentatives de connexion sur les appareils et navigateurs non reconnus.
                    </p>
                    <form>
                        <div class="form-check">
                            <input class="form-check-input" id="twoFactorOn" type="radio" name="twoFactor" checked="" />
                            <label class="form-check-label" for="twoFactorOn">Activé</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" id="twoFactorOff" type="radio" name="twoFactor" />
                            <label class="form-check-label" for="twoFactorOff">Désactivé</label>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    Supprimer le compte
                </div>
                <div class="card-body">
                    <p>
                        La suppression de votre compte est une action permanente et irréversible. Si vous êtes sûr de vouloir supprimer votre compte, sélectionnez le bouton ci-dessous.
                    </p>
                    <button type="button" class="btn btn-danger-soft text-danger">
                        Je comprends, supprimer mon compte
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection