<?php

namespace App\Livewire\Tables;

use App\Models\Supplier;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;    
use Illuminate\Support\Facades\Session;

class SupplierTable extends Component
{
    use WithPagination;

    public $perPage = 10;

    public $search = '';

    public $sortField = 'name';

    public $sortAsc = false;

    public function sortBy($field): void
    {
        if ($this->sortField === $field) {
            $this->sortAsc = ! $this->sortAsc;

        } else {
            $this->sortAsc = true;
        }

        $this->sortField = $field;
    }

    public function render()
    {
        // Récupère l'ID de l'utilisateur connecté et l'ID de l'entreprise active
        $userId = Auth::id();
        $activeCompanyId = Session::get('active_company_id');

        // Initialise la requête pour les fournisseurs
        $suppliersQuery = Supplier::query();

        // Applique les filtres par utilisateur et par entreprise active
        // C'est la partie CRUCIALE qui manquait probablement
        $suppliersQuery->where('user_id', $userId)
                       ->where('company_id', $activeCompanyId);

        // Si vous avez des fonctionnalités de recherche ou de tri dans votre Livewire,
        // ajoutez-les ici. Exemple:
        /*
        if ($this->search) {
            $suppliersQuery->where('name', 'like', '%' . $this->search . '%')
                           ->orWhere('email', 'like', '%' . $this->search . '%');
        }

        $suppliersQuery->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc');
        */

        // Récupère les fournisseurs paginés
        $suppliers = $suppliersQuery->paginate(10); // Adaptez le nombre par page si nécessaire

        return view('livewire.tables.supplier-table', [
            'suppliers' => $suppliers,
        ]);
    }
}
