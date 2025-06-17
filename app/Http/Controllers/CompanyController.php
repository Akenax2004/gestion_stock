<?php

namespace App\Http\Controllers; // Assurez-vous que le namespace est correct

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule; // Pour les règles de validation Rule::unique ou Rule::exists

class CompanyController extends Controller
{
    /**
     * Affiche une liste de toutes les entreprises de l'utilisateur connecté.
     * Cette méthode NE DOIT PAS être filtrée par l'entreprise active en session,
     * car son but est de permettre à l'utilisateur de sélectionner une entreprise.
     */
    public function index()
    {
        // Vérifie si l'utilisateur est authentifié. Si non, il ne devrait pas être ici.
        if (!Auth::check()) {
            return redirect('/login')->withErrors('Veuillez vous connecter.');
        }

        $userId = Auth::id();

        // Récupère TOUTES les entreprises appartenant à l'utilisateur connecté.
        // C'est la clé : pas de filtrage par Session::get('active_company_id') ici.
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
            'name' => [
                'required',
                'string',
                'max:255',
                // S'assurer que le nom de l'entreprise est unique pour cet utilisateur
                Rule::unique('companies')->where(function ($query) use ($userId) {
                    return $query->where('user_id', $userId);
                }),
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
                // L'email doit être unique pour cet utilisateur
                Rule::unique('companies')->where(function ($query) use ($userId) {
                    return $query->where('user_id', $userId);
                }),
            ],
            'phone' => [
                'nullable',
                'string',
                'max:25',
                // Le téléphone doit être unique pour cet utilisateur
                Rule::unique('companies')->where(function ($query) use ($userId) {
                    return $query->where('user_id', $userId);
                }),
            ],
            'address' => 'nullable|string|max:255',
            'vat_number' => 'nullable|string|max:50',
            'logo' => 'nullable|image|max:2048', // Max 2MB
            'is_active' => 'boolean',
        ]);

        $company = Company::create(array_merge($validatedData, [
            'user_id' => $userId, // Associe l'entreprise à l'utilisateur connecté
            'is_active' => $request->boolean('is_active'), // Assure que c'est bien un booléen
        ]));

        // Gérer le téléchargement du logo
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = hexdec(uniqid()) . '.' . $file->getClientOriginalExtension();
            $file->storeAs('companies/logos', $filename, 'public');
            $company->update(['logo' => $filename]);
        }

        // Si c'est la première entreprise de l'utilisateur ou si elle est marquée comme active,
        // la définir comme entreprise active en session.
        if (Auth::user()->companies->count() === 1 || $company->is_active) {
            Session::put('active_company_id', $company->id);
            Session::put('active_company_name', $company->name);
        }

        return redirect()->route('companies.index')->with('success', 'Entreprise créée avec succès!');
    }

    /**
     * Affiche les détails d'une entreprise spécifique.
     * S'assure que seul le propriétaire peut la voir.
     */
    public function show(Company $company)
    {
        // Vérifie si l'entreprise appartient à l'utilisateur connecté
        if ($company->user_id !== Auth::id()) {
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
        // Vérifie si l'entreprise appartient à l'utilisateur connecté
        if ($company->user_id !== Auth::id()) {
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
        // Vérifie si l'entreprise appartient à l'utilisateur connecté
        if ($company->user_id !== Auth::id()) {
            abort(403, 'Accès non autorisé à mettre à jour cette entreprise.');
        }

        $userId = Auth::id();

        $validatedData = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                // S'assurer que le nom est unique pour cet utilisateur, en ignorant l'entreprise actuelle
                Rule::unique('companies')->where(function ($query) use ($userId) {
                    return $query->where('user_id', $userId);
                })->ignore($company->id),
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('companies')->where(function ($query) use ($userId) {
                    return $query->where('user_id', $userId);
                })->ignore($company->id),
            ],
            'phone' => [
                'nullable',
                'string',
                'max:25',
                Rule::unique('companies')->where(function ($query) use ($userId) {
                    return $query->where('user_id', $userId);
                })->ignore($company->id),
            ],
            'address' => 'nullable|string|max:255',
            'vat_number' => 'nullable|string|max:50',
            'logo' => 'nullable|image|max:2048',
            'is_active' => 'boolean',
        ]);

        $company->update(array_merge($validatedData, [
            'is_active' => $request->boolean('is_active'),
        ]));

        // Gérer la mise à jour du logo
        if ($request->hasFile('logo')) {
            if ($company->logo) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete('companies/logos/' . $company->logo);
            }
            $file = $request->file('logo');
            $filename = hexdec(uniqid()) . '.' . $file->getClientOriginalExtension();
            $file->storeAs('companies/logos', $filename, 'public');
            $company->update(['logo' => $filename]);
        }

        // Si l'entreprise mise à jour est celle actuellement active, mettez à jour son nom en session
        if (Session::get('active_company_id') == $company->id) {
            Session::put('active_company_name', $company->name);
        }

        return redirect()->route('companies.index')->with('success', 'Entreprise mise à jour avec succès!');
    }

    /**
     * Supprime une entreprise.
     * S'assure que seul le propriétaire peut la supprimer.
     */
    public function destroy(Company $company)
    {
        // Vérifie si l'entreprise appartient à l'utilisateur connecté
        if ($company->user_id !== Auth::id()) {
            abort(403, 'Accès non autorisé à supprimer cette entreprise.');
        }

        // Empêcher la suppression de l'entreprise active
        if (Session::get('active_company_id') == $company->id) {
            return redirect()->back()->withErrors('Vous ne pouvez pas supprimer l\'entreprise actuellement sélectionnée. Veuillez en sélectionner une autre d\'abord.');
        }

        // Supprimer le logo si il existe
        if ($company->logo) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete('companies/logos/' . $company->logo);
        }

        $company->delete();

        return redirect()->route('companies.index')->with('success', 'Entreprise supprimée avec succès!');
    }

    /**
     * Définit l'entreprise active en session et redirige vers le tableau de bord.
     */
    public function select(Company $company)
    {
        // Vérifier que l'entreprise appartient à l'utilisateur connecté
        if ($company->user_id !== Auth::id()) {
            abort(403, 'Accès non autorisé à cette entreprise.');
        }

        Session::put('active_company_id', $company->id);
        Session::put('active_company_name', $company->name);

        return redirect()->route('dashboard')->with('success', 'Entreprise "' . $company->name . '" sélectionnée.');
    }
}
