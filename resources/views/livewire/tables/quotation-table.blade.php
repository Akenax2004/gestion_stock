<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title">
                {{ __('Devis') }}
            </h3>
        </div>

        <div class="card-actions">
            <x-action.create route="{{ route('quotations.create') }}" />
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
                    <input type="text" wire:model.live="search" class="form-control form-control-sm" aria-label="Rechercher devis">
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
                    <a wire:click.prevent="sortBy('reference')" href="#" role="button">
                        {{ __('N° de devis') }}
                        @include('inclues._sort-icon', ['field' => 'reference'])
                    </a>
                </th>
                <th scope="col" class="align-middle text-center">
                    <a wire:click.prevent="sortBy('date')" href="#" role="button">
                        {{ __('Date') }}
                        @include('inclues._sort-icon', ['field' => 'date'])
                    </a>
                </th>
                <th scope="col" class="align-middle text-center">
                    <a wire:click.prevent="sortBy('customer_name')" href="#" role="button">
                        {{ __('Nom du client') }}
                        @include('inclues._sort-icon', ['field' => 'customer_name'])
                    </a>
                </th>
                <th scope="col" class="align-middle text-center">
                    <a wire:click.prevent="sortBy('total')" href="#" role="button">
                        {{ __('Montant total') }}
                        @include('inclues._sort-icon', ['field' => 'total_amount'])
                    </a>
                </th>
                <th scope="col" class="align-middle text-center">
                    <a wire:click.prevent="sortBy('order_status')" href="#" role="button">
                        {{ __('Statut') }}
                        @include('inclues._sort-icon', ['field' => 'order_status'])
                    </a>
                </th>
                <th scope="col" class="align-middle text-center">
                    {{ __('Action') }}
                </th>
            </tr>
            </thead>
            <tbody>
            @forelse ($quotations as $quotation)
                <tr>
                    <td class="align-middle text-center">
                        {{ $loop->iteration }}
                    </td>
                    <td class="align-middle text-center">
                        {{ $quotation->reference }}
                    </td>
                    <td class="align-middle text-center">
                        {{ $quotation->date->format('d-m-Y') }}
                    </td>
                    <td class="align-middle text-center">
                        {{ $quotation->customer->name }}
                    </td>
                    <td class="align-middle text-center">
                        {{ Number::currency($quotation->total_amount, 'EUR') }}
                    </td>
                    <td class="align-middle text-center">
                        <span class="badge {{ $quotation->status === \App\Enums\QuotationStatus::PENDING ? 'bg-orange' : 'bg-green' }} text-white text-uppercase">
                            {{ $quotation->status->label() }}
                        </span>
                    </td>
                    <td class="align-middle text-center">
                        <x-button.show class="btn-icon" route="{{ route('quotations.show', $quotation) }}"/>
                        <x-button.edit class="btn-icon" route="{{ route('quotations.edit', $quotation) }}"/>
                        <x-button.delete class="btn-icon" route="{{ route('quotations.destroy', $quotation) }}"/>
                    </td>
                </tr>
            @empty
                <tr>
                    <td class="align-middle text-center" colspan="8">
                        Aucun résultat trouvé
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-footer d-flex align-items-center">
        <p class="m-0 text-secondary">
            Affichage de <span>{{ $quotations->firstItem() }}</span> à <span>{{ $quotations->lastItem() }}</span> sur <span>{{ $quotations->total() }}</span> entrées
        </p>

        <ul class="pagination m-0 ms-auto">
            {{ $quotations->links() }}
        </ul>
    </div>
</div>