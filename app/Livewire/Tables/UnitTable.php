<?php

    namespace App\Livewire\Tables; // <-- MODIFIÉ ICI

    use App\Models\Unit; // Assurez-vous d'importer le modèle Unit
    use Illuminate\Database\Eloquent\Builder; // Pour le typage
    use Illuminate\Support\Facades\Auth; // Pour l'utilisateur connecté
    use Illuminate\Support\Facades\Session; // Pour l'entreprise active
    use Livewire\Component;
    use Livewire\WithPagination;

    class UnitTable extends Component
    {
        use WithPagination;

        public string $search = '';
        public string $perPage = '10'; // Nombre d'éléments par page

        public string $sortField = 'name'; // Ajouté le tri
        public bool $sortAsc = true; // Ajouté le tri

        protected $queryString = [
            'search' => ['except' => ''],
            'perPage' => ['except' => '10'],
            'sortField' => ['except' => 'name'], // Ajouté au queryString
            'sortAsc' => ['except' => true],     // Ajouté au queryString
        ];

        public function sortBy($field): void
        {
            if ($this->sortField === $field) {
                $this->sortAsc = ! $this->sortAsc;

            } else {
                $this->sortAsc = true;
            }

            $this->sortField = $field;
        }

        public function updatingSearch(): void
        {
            $this->resetPage();
        }

        public function render()
        {
            $companyId = Session::get('active_company_id');

            // Si aucune entreprise n'est sélectionnée, renvoyer une collection vide pour éviter l'erreur.
            // La redirection vers la page de sélection d'entreprise devrait être gérée par le middleware.
            if (!$companyId) {
                return view('livewire.tables.unit-table', [
                    'units' => Unit::where('id', null)->paginate((int) $this->perPage), // Retourne une pagination vide
                ]);
            }

            $units = Unit::query()
                ->where('company_id', $companyId) // Filtrer les unités par l'ID de l'entreprise active
                ->with('products') // Charge la relation 'products' pour éviter N+1 si elle est utilisée
                ->when($this->search, function (Builder $query) {
                    $query->where('name', 'like', "%{$this->search}%")
                        ->orWhere('short_code', 'like', "%{$this->search}%");
                })
                ->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc')
                ->paginate((int) $this->perPage);

            return view('livewire.tables.unit-table', [
                'units' => $units,
            ]);
        }

        public function deleteUnit(int $unitId)
        {
            $companyId = Session::get('active_company_id');
            if (!$companyId) {
                session()->flash('error', 'Aucune entreprise sélectionnée.');
                return;
            }

            $unit = Unit::where('company_id', $companyId)->find($unitId);
            if ($unit) {
                $unit->delete();
                session()->flash('message', 'Unité supprimée avec succès.');
            } else {
                session()->flash('error', 'Unité non trouvée ou accès non autorisé.');
            }
        }
    }
    