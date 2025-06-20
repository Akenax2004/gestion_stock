@extends('layouts.tabler')

@section('content')
<div class="page-body">
    <div class="container-xl">
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title">
                        {{ __('Détails de l\'unité') }}
                    </h3>
                </div>

                <div class="card-actions">
                    <x-action.close route="{{ route('units.index') }}" />
                </div>
            </div>

            <form action="{{ route('units.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <livewire:name />

                    <livewire:slug />

                    <x-input
                        label="{{ __('Code court') }}"
                        id="short_code"
                        name="short_code"
                        :value="old('short_code')"
                        required
                    />
                </div>

                <div class="card-footer text-end">
                    <x-button.save type="submit">
                        {{ __('Enregistrer') }}
                    </x-button.save>

                    <x-button.back route="{{ route('units.index') }}">
                        {{ __('Annuler') }}
                    </x-button.back>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@pushonce('page-scripts')
<script>
    // Slug Generator
    const title = document.querySelector("#name");
    const slug = document.querySelector("#slug");
    title.addEventListener("keyup", function() {
        let preslug = title.value;
        preslug = preslug.replace(/ /g,"-");
        slug.value = preslug.toLowerCase();
    });
</script>
@endpushonce