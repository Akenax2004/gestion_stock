<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use Illuminate\Support\Facades\Auth; // Importez la façade Auth

class CategoryController extends Controller
{
    /**
     * Affiche une liste de toutes les catégories de l'utilisateur connecté.
     */
    public function index()
    {
        // Vérifie si un utilisateur est authentifié
        if (!Auth::check()) {
            return redirect('/login')->withErrors('Veuillez vous connecter.');
        }

        $userId = Auth::id();

        // Récupère uniquement les catégories appartenant à l'utilisateur connecté
        $categories = Category::where('user_id', $userId)->get();

        return view('categories.index', [
            'categories' => $categories,
        ]);
    }

    /**
     * Affiche le formulaire de création d'une nouvelle catégorie.
     */
    public function create()
    {
        return view('categories.create');
    }

    /**
     * Stocke une nouvelle catégorie dans la base de données, l'associant à l'utilisateur connecté.
     */
    public function store(StoreCategoryRequest $request)
    {
        if (!Auth::check()) {
            return redirect('/login')->withErrors('Veuillez vous connecter.');
        }

        $userId = Auth::id();

        // Crée la catégorie en ajoutant l'ID de l'utilisateur.
        Category::create(array_merge($request->validated(), [
            'user_id' => $userId, // Associe la catégorie à l'utilisateur connecté
        ]));

        return redirect()
            ->route('categories.index')
            ->with('success', 'La catégorie a été créée avec succès!');
    }

    /**
     * Affiche les détails d'une catégorie spécifique.
     * S'assure que seul le propriétaire peut la voir.
     */
    public function show(Category $category)
    {
        // Vérifie si l'utilisateur connecté est le propriétaire de la catégorie.
        if (!Auth::check() || $category->user_id !== Auth::id()) {
            abort(403, 'Accès non autorisé à cette catégorie.');
        }

        return view('categories.show', [
            'category' => $category
        ]);
    }

    /**
     * Affiche le formulaire de modification d'une catégorie.
     * S'assure que seul le propriétaire peut la modifier.
     */
    public function edit(Category $category)
    {
        // Vérifie si l'utilisateur connecté est le propriétaire de la catégorie.
        if (!Auth::check() || $category->user_id !== Auth::id()) {
            abort(403, 'Accès non autorisé à modifier cette catégorie.');
        }

        return view('categories.edit', [
            'category' => $category
        ]);
    }

    /**
     * Met à jour une catégorie existante.
     * S'assure que seul le propriétaire peut la modifier.
     */
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        // Vérifie si l'utilisateur connecté est le propriétaire de la catégorie.
        if (!Auth::check() || $category->user_id !== Auth::id()) {
            abort(403, 'Accès non autorisé à mettre à jour cette catégorie.');
        }

        // Utilise $request->validated() si vous utilisez un FormRequest
        $category->update($request->validated());

        return redirect()
            ->route('categories.index')
            ->with('success', 'La catégorie a été mise à jour avec succès!');
    }

    /**
     * Supprime une catégorie.
     * S'assure que seul le propriétaire peut la supprimer.
     */
    public function destroy(Category $category)
    {
        // Vérifie si l'utilisateur connecté est le propriétaire de la catégorie.
        if (!Auth::check() || $category->user_id !== Auth::id()) {
            abort(403, 'Accès non autorisé à supprimer cette catégorie.');
        }

        $category->delete();

        return redirect()
            ->route('categories.index')
            ->with('success', 'La catégorie a été supprimée avec succès!');
    }
}
