<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Importez la façade Auth
use Illuminate\Support\Facades\Storage; // Pour la gestion des fichiers (logo)

class CompanyController extends Controller
{
    /**
     * Affiche une liste de toutes les entreprises de l'utilisateur connecté.
     */
    public function index()
    {
        if (!Auth::check()) {
            return redirect('/login')->withErrors('Veuillez vous connecter.');
        }

        $userId = Auth::id();
        $companies = Company::where('user_id', $userId)->get();

        return view('companies.index', compact('companies'));
    }

    /**
     * Affiche le formulaire de création d'une nouvelle entreprise.
     */
    public function create()
    {
        return view('companies.create');
    }

    /**
     * Stocke une nouvelle entreprise dans la base de données, l'associant à l'utilisateur connecté.
     */
    public function store(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/login')->withErrors('Veuillez vous connecter.');
        }

        $userId = Auth::id();

        $validatedData = $request->validate([
            // La validation unique par utilisateur est gérée par le 6e paramètre de la règle unique
            'name' => 'required|string|max:255|unique:companies,name,NULL,id,user_id,' . $userId,
            'email' => 'nullable|email|unique:companies,email,NULL,id,user_id,' . $userId,
            'phone' => 'nullable|string|max:255|unique:companies,phone,NULL,id,user_id,' . $userId,
            'address' => 'nullable|string|max:255',
            'vat_number' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Validation pour le fichier logo
        ]);

        $company = Company::create(array_merge($validatedData, [
            'user_id' => $userId, // Associe l'entreprise à l'utilisateur connecté
        ]));

        // Gère le téléchargement du logo
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = hexdec(uniqid()) . '.' . $file->getClientOriginalExtension();
            $file->storeAs('companies/logos', $filename, 'public'); // Stocke dans storage/app/public/companies/logos
            $company->update(['logo' => $filename]);
        }

        return redirect()->route('companies.index')->with('success', 'Entreprise créée avec succès!');
    }

    /**
     * Affiche les détails d'une entreprise spécifique.
     * S'assure que seul le propriétaire peut la voir.
     */
    public function show(Company $company)
    {
        if (!Auth::check() || $company->user_id !== Auth::id()) {
            abort(403, 'Accès non autorisé à cette entreprise.');
        }

        return view('companies.show', compact('company'));
    }

    /**
     * Affiche le formulaire de modification d'une entreprise.
     * S'assure que seul le propriétaire peut la modifier.
     */
    public function edit(Company $company)
    {
        if (!Auth::check() || $company->user_id !== Auth::id()) {
            abort(403, 'Accès non autorisé à modifier cette entreprise.');
        }

        return view('companies.edit', compact('company'));
    }

    /**
     * Met à jour une entreprise existante.
     * S'assure que seul le propriétaire peut la modifier.
     */
    public function update(Request $request, Company $company)
    {
        if (!Auth::check() || $company->user_id !== Auth::id()) {
            abort(403, 'Accès non autorisé à mettre à jour cette entreprise.');
        }

        $validatedData = $request->validate([
            // La validation unique par utilisateur pour la mise à jour (exclut l'ID actuel de l'entreprise)
            'name' => 'required|string|max:255|unique:companies,name,' . $company->id . ',id,user_id,' . Auth::id(),
            'email' => 'nullable|email|unique:companies,email,' . $company->id . ',id,user_id,' . Auth::id(),
            'phone' => 'nullable|string|max:255|unique:companies,phone,' . $company->id . ',id,user_id,' . Auth::id(),
            'address' => 'nullable|string|max:255',
            'vat_number' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $company->update($request->except('logo')); // Exclut le logo pour le traiter séparément

        if ($request->hasFile('logo')) {
            // Supprime l'ancien logo si elle existe
            if ($company->logo) {
                Storage::disk('public')->delete('companies/logos/' . $company->logo);
            }

            $file = $request->file('logo');
            $filename = hexdec(uniqid()) . '.' . $file->getClientOriginalExtension();
            $file->storeAs('companies/logos', $filename, 'public');
            $company->update(['logo' => $filename]);
        } elseif ($request->input('logo_removed')) { // Gère la suppression explicite du logo (ex: via checkbox dans le formulaire)
             if ($company->logo) {
                Storage::disk('public')->delete('companies/logos/' . $company->logo);
                $company->update(['logo' => null]);
            }
        }

        return redirect()->route('companies.index')->with('success', 'Entreprise mise à jour avec succès!');
    }

    /**
     * Supprime une entreprise.
     * S'assure que seul le propriétaire peut la supprimer.
     */
    public function destroy(Company $company)
    {
        if (!Auth::check() || $company->user_id !== Auth::id()) {
            abort(403, 'Accès non autorisé à supprimer cette entreprise.');
        }

        // Supprime le logo associé si elle existe
        if ($company->logo) {
            Storage::disk('public')->delete('companies/logos/' . $company->logo);
        }

        $company->delete();

        return redirect()->route('companies.index')->with('success', 'Entreprise supprimée avec succès!');
    }
}
