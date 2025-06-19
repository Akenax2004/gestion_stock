<?php

namespace App\Livewire\Tables;

use App\Models\Quotation;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;    
use Illuminate\Support\Facades\Session;

class QuotationTable extends Component
{
    use WithPagination;

    public $perPage = 10;

    public $search = '';

    public $sortField = 'reference';

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
        // Récupère l'ID de l'utilisateur connecté.
        $userId = Auth::id();
        // Récupère l'ID de l'entreprise active en session.
        $activeCompanyId = Session::get('active_company_id');

        // Initialise la requête pour les devis.
        $quotationsQuery = Quotation::query();

        // Applique les filtres par l'utilisateur créateur et l'entreprise active.
        // C'est la partie cruciale pour la multi-entreprise.
        // Assurez-vous que votre modèle Quotation a un champ 'user_id' et 'company_id'.
        $quotationsQuery->where('user_id', $userId)
                        ->where('company_id', $activeCompanyId);

        // Charge les relations nécessaires.
        $quotationsQuery->with(['quotationDetails', 'customer']);

        // Applique la recherche si un terme est fourni.
        if ($this->search) {
            $quotationsQuery->search($this->search); // Utilise la méthode scopeSearch de votre modèle Quotation
        }

        // Applique le tri.
        $quotationsQuery->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc');

        // Récupère les devis paginés.
        $quotations = $quotationsQuery->paginate($this->perPage);

        return view('livewire.tables.quotation-table', [
            'quotations' => $quotations,
        ]);
    }
}
