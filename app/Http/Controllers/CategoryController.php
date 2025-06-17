<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session; // NOUVEAU : Importez la façade Session

class CategoryController extends Controller
{
    /**
     * Affiche une liste de toutes les catégories de l'utilisateur connecté et de l'entreprise active.
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
            return redirect()->route('companies.index')->withErrors('Veuillez sélectionner une entreprise pour gérer les catégories.');
        }

        // Récupère uniquement les catégories appartenant à l'utilisateur connecté ET à l'entreprise active
        $categories = Category::where('user_id', $userId)
                                ->where('company_id', $activeCompanyId) // FILTRAGE PAR ENTREPRISE
                                ->get();

        return view('categories.index', [
            'categories' => $categories,
        ]);
    }

    /**
     * Affiche le formulaire de création d'une nouvelle catégorie.
     */
    public function create()
    {
        if (!Auth::check()) {
            return redirect('/login')->withErrors('Veuillez vous connecter.');
        }

        $activeCompanyId = Session::get('active_company_id');

        // Redirige si aucune entreprise n'est sélectionnée
        if (!$activeCompanyId) {
            return redirect()->route('companies.index')->withErrors('Veuillez sélectionner une entreprise avant de créer une catégorie.');
        }

        return view('categories.create');
    }

    /**
     * Stocke une nouvelle catégorie dans la base de données, l'associant à l'utilisateur connecté et à l'entreprise active.
     */
    public function store(StoreCategoryRequest $request)
    {
        if (!Auth::check()) {
            return redirect('/login')->withErrors('Veuillez vous connecter.');
        }

        $userId = Auth::id();
        $activeCompanyId = Session::get('active_company_id');

        // Redirige si aucune entreprise n'est sélectionnée
        if (!$activeCompanyId) {
            return redirect()->route('companies.index')->withErrors('Veuillez sélectionner une entreprise avant de créer une catégorie.');
        }

        // Crée la catégorie en ajoutant l'ID de l'utilisateur et l'ID de l'entreprise active.
        Category::create(array_merge($request->validated(), [
            'user_id' => $userId, // Associe la catégorie à l'utilisateur connecté
            'company_id' => $activeCompanyId, // AJOUT : Associe la catégorie à l'entreprise active
        ]));

        return redirect()
            ->route('categories.index')
            ->with('success', 'La catégorie a été créée avec succès!');
    }

    /**
     * Affiche les détails d'une catégorie spécifique.
     * S'assure que seul le propriétaire et l'entreprise active peuvent la voir.
     */
    public function show(Category $category)
    {
        // Vérifie si l'utilisateur connecté est le propriétaire de la catégorie ET si elle appartient à l'entreprise active.
        if (!Auth::check() || $category->user_id !== Auth::id() || $category->company_id !== Session::get('active_company_id')) {
            abort(403, 'Accès non autorisé à cette catégorie.');
        }

        return view('categories.show', [
            'category' => $category
        ]);
    }

    /**
     * Affiche le formulaire de modification d'une catégorie.
     * S'assure que seul le propriétaire et l'entreprise active peuvent la modifier.
     */
    public function edit(Category $category)
    {
        // Vérifie si l'utilisateur connecté est le propriétaire de la catégorie ET si elle appartient à l'entreprise active.
        if (!Auth::check() || $category->user_id !== Auth::id() || $category->company_id !== Session::get('active_company_id')) {
            abort(403, 'Accès non autorisé à modifier cette catégorie.');
        }

        return view('categories.edit', [
            'category' => $category
        ]);
    }

    /**
     * Met à jour une catégorie existante.
     * S'assure que seul le propriétaire et l'entreprise active peuvent la modifier.
     */
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        // Vérifie si l'utilisateur connecté est le propriétaire de la catégorie ET si elle appartient à l'entreprise active.
        if (!Auth::check() || $category->user_id !== Auth::id() || $category->company_id !== Session::get('active_company_id')) {
            abort(403, 'Accès non autorisé à mettre à jour cette catégorie.');
        }

        // Utilisez $request->validated() si vous utilisez un FormRequest
        $category->update($request->validated());

        return redirect()
            ->route('categories.index')
            ->with('success', 'La catégorie a été mise à jour avec succès!');
    }

    /**
     * Supprime une catégorie.
     * S'assure que seul le propriétaire et l'entreprise active peuvent la supprimer.
     */
    public function destroy(Category $category)
    {
        // Vérifie si l'utilisateur connecté est le propriétaire de la catégorie ET si elle appartient à l'entreprise active.
        if (!Auth::check() || $category->user_id !== Auth::id() || $category->company_id !== Session::get('active_company_id')) {
            abort(403, 'Accès non autorisé à supprimer cette catégorie.');
        }

        $category->delete();

        return redirect()
            ->route('categories.index')
            ->with('success', 'La catégorie a été supprimée avec succès!');
    }
}
