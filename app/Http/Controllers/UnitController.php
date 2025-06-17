<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Http\Requests\Unit\StoreUnitRequest;
use App\Http\Requests\Unit\UpdateUnitRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session; // NOUVEAU : Importez la façade Session

class UnitController extends Controller
{
    /**
     * Affiche une liste de toutes les unités de l'utilisateur connecté et de l'entreprise active.
     */
    public function index()
    {
        if (!Auth::check()) {
            return redirect('/login')->withErrors('Veuillez vous connecter.');
        }

        $userId = Auth::id();
        $activeCompanyId = Session::get('active_company_id');

        // Redirige si aucune entreprise n'est sélectionnée
        if (!$activeCompanyId) {
            return redirect()->route('companies.index')->withErrors('Veuillez sélectionner une entreprise pour gérer les unités.');
        }

        // Récupère uniquement les unités appartenant à l'utilisateur connecté ET à l'entreprise active
        $units = Unit::query()
            ->where('user_id', $userId)
            ->where('company_id', $activeCompanyId) // FILTRAGE PAR ENTREPRISE
            ->select(['id', 'name', 'slug', 'short_code'])
            ->get();

        return view('units.index', [
            'units' => $units,
        ]);
    }

    /**
     * Affiche le formulaire de création d'une nouvelle unité.
     */
    public function create()
    {
        if (!Auth::check()) {
            return redirect('/login')->withErrors('Veuillez vous connecter.');
        }

        $activeCompanyId = Session::get('active_company_id');

        // Redirige si aucune entreprise n'est sélectionnée
        if (!$activeCompanyId) {
            return redirect()->route('companies.index')->withErrors('Veuillez sélectionner une entreprise avant de créer une unité.');
        }

        return view('units.create');
    }

    /**
     * Stocke une nouvelle unité dans la base de données, l'associant à l'utilisateur connecté et à l'entreprise active.
     */
    public function store(StoreUnitRequest $request)
    {
        if (!Auth::check()) {
            return redirect('/login')->withErrors('Veuillez vous connecter.');
        }

        $userId = Auth::id();
        $activeCompanyId = Session::get('active_company_id');

        // Redirige si aucune entreprise n'est sélectionnée
        if (!$activeCompanyId) {
            return redirect()->route('companies.index')->withErrors('Veuillez sélectionner une entreprise avant de créer une unité.');
        }

        // Crée l'unité en ajoutant l'ID de l'utilisateur et l'ID de l'entreprise active.
        Unit::create(array_merge($request->validated(), [
            'user_id' => $userId, // Associe l'unité à l'utilisateur connecté
            'company_id' => $activeCompanyId, // AJOUT : Associe l'unité à l'entreprise active
        ]));

        return redirect()
            ->route('units.index')
            ->with('success', 'L\'unité a été créée avec succès!');
    }

    /**
     * Affiche les détails d'une unité spécifique.
     * S'assure que seul le propriétaire et l'entreprise active peuvent la voir.
     */
    public function show(Unit $unit)
    {
        // Vérifie si l'utilisateur connecté est le propriétaire de l'unité ET si elle appartient à l'entreprise active.
        if (!Auth::check() || $unit->user_id !== Auth::id() || $unit->company_id !== Session::get('active_company_id')) {
            abort(403, 'Accès non autorisé à cette unité.');
        }

        // Charge les relations nécessaires (les produits liés à cette unité, qui devraient aussi être filtrés par company_id si vous utilisez un Global Scope)
        $unit->loadMissing('products');

        return view('units.show', [
            'unit' => $unit
        ]);
    }

    /**
     * Affiche le formulaire de modification d'une unité.
     * S'assure que seul le propriétaire et l'entreprise active peuvent la modifier.
     */
    public function edit(Unit $unit)
    {
        // Vérifie si l'utilisateur connecté est le propriétaire de l'unité ET si elle appartient à l'entreprise active.
        if (!Auth::check() || $unit->user_id !== Auth::id() || $unit->company_id !== Session::get('active_company_id')) {
            abort(403, 'Accès non autorisé à modifier cette unité.');
        }

        return view('units.edit', [
            'unit' => $unit
        ]);
    }

    /**
     * Met à jour une unité existante.
     * S'assure que seul le propriétaire et l'entreprise active peuvent la modifier.
     */
    public function update(UpdateUnitRequest $request, Unit $unit)
    {
        // Vérifie si l'utilisateur connecté est le propriétaire de l'unité ET si elle appartient à l'entreprise active.
        if (!Auth::check() || $unit->user_id !== Auth::id() || $unit->company_id !== Session::get('active_company_id')) {
            abort(403, 'Accès non autorisé à mettre à jour cette unité.');
        }

        // Utilise $request->validated() si vous utilisez un FormRequest.
        // Les règles d'unicité dans UpdateUnitRequest (pour 'name', 'slug', 'short_code')
        // doivent être mises à jour pour inclure le company_id.
        $unit->update($request->validated());

        return redirect()
            ->route('units.index')
            ->with('success', 'L\'unité a été mise à jour avec succès!');
    }

    /**
     * Supprime une unité.
     * S'assure que seul le propriétaire et l'entreprise active peuvent la supprimer.
     */
    public function destroy(Unit $unit)
    {
        // Vérifie si l'utilisateur connecté est le propriétaire de l'unité ET si elle appartient à l'entreprise active.
        if (!Auth::check() || $unit->user_id !== Auth::id() || $unit->company_id !== Session::get('active_company_id')) {
            abort(403, 'Accès non autorisé à supprimer cette unité.');
        }

        $unit->delete();

        return redirect()
            ->route('units.index')
            ->with('success', 'L\'unité a été supprimée avec succès!');
    }
}
