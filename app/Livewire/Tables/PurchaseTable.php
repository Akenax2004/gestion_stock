<?php

namespace App\Livewire\Tables;

use Livewire\Component;
use App\Models\Purchase;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class PurchaseTable extends Component
{
    use WithPagination;

    public $perPage = 10;

    public $search = '';

    public $sortField = 'purchase_no';

    public $sortAsc = false;

    /**
     * Définit le champ de tri et l'ordre.
     *
     * @param string $field Le champ par lequel trier.
     * @return void
     */
    public function sortBy($field): void
    {
        if ($this->sortField === $field) {
            $this->sortAsc = ! $this->sortAsc;

        } else {
            $this->sortAsc = true;
        }

        $this->sortField = $field;
    }

    /**
     * Rendu du composant Livewire.
     * Cette méthode récupère les données des achats en appliquant les filtres nécessaires.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function render()
    {
        // Récupère l'ID de l'utilisateur connecté.
        $userId = Auth::id();
        // Récupère l'ID de l'entreprise active en session.
        $activeCompanyId = Session::get('active_company_id');

        // Initialise la requête pour les achats.
        $purchasesQuery = Purchase::query();

        // AJOUT : Eager loading de la relation 'supplier' pour éviter les requêtes N+1
        $purchasesQuery->with('supplier');

        // Applique les filtres par l'utilisateur créateur et l'entreprise active.
        $purchasesQuery->where('created_by', $userId)
                       ->where('company_id', $activeCompanyId);

        // Applique la recherche si un terme est fourni.
        if ($this->search) {
            $purchasesQuery->search($this->search);
        }

        // Applique le tri.
        $purchasesQuery->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc');

        // Récupère les achats paginés.
        $purchases = $purchasesQuery->paginate($this->perPage);

        return view('livewire.tables.purchase-table', [
            'purchases' => $purchases,
        ]);
    }

    /**
     * Réinitialise la pagination lors de la mise à jour du terme de recherche.
     *
     * @return void
     */
    public function updatingSearch(): void
    {
        $this->resetPage();
    }
}
