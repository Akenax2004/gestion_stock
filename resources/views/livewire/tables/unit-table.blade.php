<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title">
                {{ __('Unités') }}
            </h3>
        </div>

        <div class="card-actions">
            <x-action.create route="{{ route('units.create') }}" />
        </div>
    </div>

    <div class="card-body border-bottom py-3">
        <div class="d-flex">
            <div class="text-secondary">
                Afficher
                <div class="mx-2 d-inline-block">
                    <select wire:model.live="perPage" class="form-select form-select-sm" aria-label="résultats par page">
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="15">15</option>
                        <option value="25">25</option>
                    </select>
                </div>
                entrées
            </div>
            <div class="ms-auto text-secondary">
                Rechercher :
                <div class="ms-2 d-inline-block">
                    <input type="text" wire:model.live="search" class="form-control form-control-sm" aria-label="Rechercher unité">
                </div>
            </div>
        </div>
    </div>

    <x-spinner.loading-spinner/>

    <div class="table-responsive">
        <table wire:loading.remove class="table table-bordered card-table table-vcenter text-nowrap datatable">
            <thead class="thead-light">
            <tr>
                <th class="align-middle text-center w-1">
                    {{ __('N°') }}
                </th>
                <th scope="col" class="align-middle text-center">
                    <a wire:click.prevent="sortBy('name')" href="#" role="button">
                        {{ __('Nom') }}
                        @include('inclues._sort-icon', ['field' => 'name'])
                    </a>
                </th>
                <th scope="col" class="align-middle text-center d-none d-sm-table-cell">
                    <a wire:click.prevent="sortBy('slug')" href="#" role="button">
                        {{ __('Identifiant') }}
                        @include('inclues._sort-icon', ['field' => 'slug'])
                    </a>
                </th>
                <th scope="col" class="align-middle text-center">
                    <a wire:click.prevent="sortBy('short_code')" href="#" role="button">
                        {{ __('Code court') }}
                        @include('inclues._sort-icon', ['field' => 'short_code'])
                    </a>
                </th>
                {{-- AJOUTEZ CETTE NOUVELLE COLONNE POUR LES PRODUITS --}}
                <th scope="col" class="align-middle text-center">
                    {{ __('Produits associés') }}
                </th>
                {{-- FIN DE LA NOUVELLE COLONNE --}}
                <th scope="col" class="align-middle text-center">
                    {{ __('Action') }}
                </th>
            </tr>
            </thead>
            <tbody>
            @forelse ($units as $unit)
                <tr>
                    <td class="align-middle text-center">
                        {{ ($units->currentPage() - 1) * $units->perPage() + $loop->iteration }}
                    </td>
                    <td class="align-middle">
                        {{ $unit->name }}
                    </td>
                    <td class="align-middle">
                        {{ $unit->slug }}
                    </td>
                    <td class="align-middle text-center">
                        {{ $unit->short_code }}
                    </td>
                    {{-- AFFICHAGE DES PRODUITS ASSOCIÉS --}}
                    <td class="align-middle">
                        @forelse ($unit->products as $product)
                            {{ $product->name }} ({{ $product->code }})@unless($loop->last), @endunless
                        @empty
                            <span class="text-secondary">Aucun produit</span>
                        @endforelse
                    </td>
                    {{-- FIN DE L'AFFICHAGE DES PRODUITS --}}
                    <td class="align-middle text-center" style="width: 10%">
                        <x-button.show class="btn-icon" route="{{ route('units.show', $unit) }}"/>
                        <x-button.edit class="btn-icon" route="{{ route('units.edit', $unit) }}"/>
                        <x-button.delete class="btn-icon" route="{{ route('units.destroy', $unit) }}"/>
                    </td>
                </tr>
            @empty
                <tr>
                    <td class="align-middle text-center" colspan="9"> {{-- Notez le colspan augmenté à 9 pour la nouvelle colonne --}}
                        Aucun résultat trouvé
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-footer d-flex align-items-center">
        <p class="m-0 text-secondary d-none d-sm-block">
            Affichage de <span>{{ $units->firstItem() }}</span> à <span>{{ $units->lastItem() }}</span> sur <span>{{ $units->total() }}</span> entrées
        </p>

        <ul class="pagination m-0 ms-auto">
            {{ $units->links() }}
        </ul>
    </div>
</div>