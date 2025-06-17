<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Http\Requests\Unit\StoreUnitRequest;
use App\Http\Requests\Unit\UpdateUnitRequest;
use Illuminate\Support\Facades\Auth; // N'oubliez pas d'importer la façade Auth

class UnitController extends Controller
{
    /**
     * Affiche une liste de toutes les unités de l'utilisateur connecté.
     */
    public function index()
    {
        // Vérifie si un utilisateur est authentifié
        if (!Auth::check()) {
            return redirect('/login')->withErrors('Veuillez vous connecter.');
        }

        $userId = Auth::id();

        // Récupère uniquement les unités appartenant à l'utilisateur connecté
        $units = Unit::query()
            ->where('user_id', $userId) // Filtre par l'ID de l'utilisateur connecté
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
        return view('units.create');
    }

    /**
     * Stocke une nouvelle unité dans la base de données, l'associant à l'utilisateur connecté.
     */
    public function store(StoreUnitRequest $request)
    {
        if (!Auth::check()) {
            return redirect('/login')->withErrors('Veuillez vous connecter.');
        }

        $userId = Auth::id();

        // Crée l'unité en ajoutant l'ID de l'utilisateur.
        Unit::create(array_merge($request->validated(), [
            'user_id' => $userId, // Associe l'unité à l'utilisateur connecté
        ]));

        return redirect()
            ->route('units.index')
            ->with('success', 'L\'unité a été créée avec succès!');
    }

    /**
     * Affiche les détails d'une unité spécifique.
     * S'assure que seul le propriétaire peut la voir.
     */
    public function show(Unit $unit)
    {
        // Vérifie si l'utilisateur connecté est le propriétaire de l'unité.
        if (!Auth::check() || $unit->user_id !== Auth::id()) {
            abort(403, 'Accès non autorisé à cette unité.');
        }

        // Charge les relations nécessaires (sans le get() superflu)
        $unit->loadMissing('products');

        return view('units.show', [
            'unit' => $unit
        ]);
    }

    /**
     * Affiche le formulaire de modification d'une unité.
     * S'assure que seul le propriétaire peut la modifier.
     */
    public function edit(Unit $unit)
    {
        // Vérifie si l'utilisateur connecté est le propriétaire de l'unité.
        if (!Auth::check() || $unit->user_id !== Auth::id()) {
            abort(403, 'Accès non autorisé à modifier cette unité.');
        }

        return view('units.edit', [
            'unit' => $unit
        ]);
    }

    /**
     * Met à jour une unité existante.
     * S'assure que seul le propriétaire peut la modifier.
     */
    public function update(UpdateUnitRequest $request, Unit $unit)
    {
        // Vérifie si l'utilisateur connecté est le propriétaire de l'unité.
        if (!Auth::check() || $unit->user_id !== Auth::id()) {
            abort(403, 'Accès non autorisé à mettre à jour cette unité.');
        }

        // Utilise $request->validated() si vous utilisez un FormRequest
        $unit->update($request->validated());

        return redirect()
            ->route('units.index')
            ->with('success', 'L\'unité a été mise à jour avec succès!');
    }

    /**
     * Supprime une unité.
     * S'assure que seul le propriétaire peut la supprimer.
     */
    public function destroy(Unit $unit)
    {
        // Vérifie si l'utilisateur connecté est le propriétaire de l'unité.
        if (!Auth::check() || $unit->user_id !== Auth::id()) {
            abort(403, 'Accès non autorisé à supprimer cette unité.');
        }

        $unit->delete();

        return redirect()
            ->route('units.index')
            ->with('success', 'L\'unité a été supprimée avec succès!');
    }
}
