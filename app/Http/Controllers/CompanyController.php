<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\LicencePlan;
use App\Models\CompanyLicence; // Ou UserLicence, selon votre choix de nom de modèle
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CompanyController extends Controller
{
    /**
     * Affiche une liste de toutes les entreprises de l'utilisateur connecté.
     * Cette méthode NE DOIT PAS être filtrée par l'entreprise active en session,
     * car son but est de permettre à l'utilisateur de sélectionner une entreprise.
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
     * Vérifie si l'utilisateur peut créer une nouvelle entreprise en fonction de sa licence.
     */
    public function create()
    {
        if (!Auth::check()) {
            return redirect('/login')->withErrors('Veuillez vous connecter.');
        }

        $user = Auth::user();
        $currentUserLicence = $user->licence;

        // Si l'utilisateur a une licence et qu'il a atteint le nombre max d'entreprises, redirigez-le.
        if ($currentUserLicence) {
            $maxCompaniesAllowed = $currentUserLicence->licencePlan->max_companies;
            $currentCompanyCount = $user->companies->count();

            if ($maxCompaniesAllowed !== -1 && $currentCompanyCount >= $maxCompaniesAllowed) {
                return redirect()->route('companies.index')->with('error',
                    'Vous avez atteint le nombre maximal d\'entreprises autorisé par votre plan de licence (' . $maxCompaniesAllowed . ' entreprises). Veuillez mettre à niveau votre licence pour ajouter plus d\'entreprises.'
                );
            }
        }
        // Sinon, affichez le formulaire de création d'entreprise.
        return view('companies.create');
    }

    /**
     * Stocke une nouvelle entreprise dans la base de données, l'associant à l'utilisateur connecté.
     * La logique de licence d'essai est maintenant gérée par le LicenceController après sélection.
     */
    public function store(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/login')->withErrors('Veuillez vous connecter.');
        }

        $user = Auth::user();
        $userId = $user->id;
        $currentUserLicence = $user->licence;
        $currentCompanyCount = $user->companies->count();

        // --- DÉBUT : Logique de vérification de licence AVANT création d'entreprise ---
        // Si l'utilisateur a une licence, vérifie si la création de nouvelle entreprise est autorisée
        if ($currentUserLicence) {
            $maxCompaniesAllowed = $currentUserLicence->licencePlan->max_companies;

            if ($maxCompaniesAllowed !== -1 && $currentCompanyCount >= $maxCompaniesAllowed) {
                Log::warning('Tentative de création d\'entreprise par l\'utilisateur ' . $userId . ' mais la limite de licence (' . $maxCompaniesAllowed . ') est atteinte ou dépassée.');
                return redirect()->back()->withErrors('Vous avez atteint le nombre maximal d\'entreprises autorisé par votre plan de licence (' . $maxCompaniesAllowed . ' entreprises). Veuillez mettre à niveau votre licence pour ajouter plus d\'entreprises.');
            }
            // Si la licence est expirée, on ne permet pas la création d'entreprise (le middleware devrait déjà rediriger)
            if (!$currentUserLicence->isActive) {
                Log::warning('Tentative de création d\'entreprise par l\'utilisateur ' . $userId . ' avec une licence expirée.');
                return redirect()->route('licences.show')->withErrors('Votre licence est expirée ou inactive. Veuillez la renouveler pour créer de nouvelles entreprises.');
            }
        }
        // --- FIN : Logique de vérification de licence AVANT création d'entreprise ---

        // La validation et la création de l'entreprise se font ici
        $validatedData = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('companies')->where(function ($query) use ($userId) {
                    return $query->where('user_id', $userId);
                }),
            ],
            'email' => [
                'nullable', 'email', 'max:255',
                Rule::unique('companies')->where(function ($query) use ($userId) {
                    return $query->where('user_id', $userId);
                }),
            ],
            'phone' => [
                'nullable', 'string', 'max:25',
                Rule::unique('companies')->where(function ($query) use ($userId) {
                    return $query->where('user_id', $userId);
                }),
            ],
            'address' => 'nullable|string|max:255',
            'vat_number' => 'nullable|string|max:50',
            'logo' => 'nullable|image|max:2048',
            'is_active' => 'boolean',
        ]);

        $company = Company::create(array_merge($validatedData, [
            'user_id' => $userId,
            'is_active' => $request->boolean('is_active'),
        ]));

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = hexdec(uniqid()) . '.' . $file->getClientOriginalExtension();
            $file->storeAs('companies/logos', $filename, 'public');
            $company->update(['logo' => $filename]);
        }

        // Définir l'entreprise comme active en session après sa création
        Session::put('active_company_id', $company->id);
        Session::put('active_company_name', $company->name);

        // --- NOUVEAU FLUX DE LICENCE : Redirection vers la page des offres si l'utilisateur n'a pas de licence active ---
        // Après la création de l'entreprise, si l'utilisateur n'a PAS de licence active (ni payante ni essai),
        // on le redirige vers la page de choix des licences.
        // Le middleware 'RedirectIfLicenceExpired' devrait aussi gérer ce cas, mais c'est une sécurité après création.
        if (!$currentUserLicence || !$currentUserLicence->isActive) {
            return redirect()->route('licences.show')->with('info', 'Votre entreprise a été créée ! Veuillez choisir un plan de licence pour commencer à l\'utiliser.');
        }


        return redirect()->route('companies.index')->with('success', 'Entreprise créée avec succès!');
    }

    /**
     * Affiche les détails d'une entreprise spécifique.
     */
    public function show(Company $company)
    {
        if ($company->user_id !== Auth::id()) {
            abort(403, 'Accès non autorisé à cette entreprise.');
        }
        return view('companies.show', compact('company'));
    }

    /**
     * Affiche le formulaire de modification d'une entreprise.
     */
    public function edit(Company $company)
    {
        if ($company->user_id !== Auth::id()) {
            abort(403, 'Accès non autorisé à modifier cette entreprise.');
        }
        return view('companies.edit', compact('company'));
    }

    /**
     * Met à jour une entreprise existante.
     */
    public function update(Request $request, Company $company)
    {
        if ($company->user_id !== Auth::id()) {
            abort(403, 'Accès non autorisé à mettre à jour cette entreprise.');
        }

        $userId = Auth::id();
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('companies')->where(function ($query) use ($userId) { return $query->where('user_id', $userId); })->ignore($company->id),],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('companies')->where(function ($query) use ($userId) { return $query->where('user_id', $userId); })->ignore($company->id),],
            'phone' => ['nullable', 'string', 'max:25', Rule::unique('companies')->where(function ($query) use ($userId) { return $query->where('user_id', $userId); })->ignore($company->id),],
            'address' => 'nullable|string|max:255',
            'vat_number' => 'nullable|string|max:50',
            'logo' => 'nullable|image|max:2048',
            'is_active' => 'boolean',
        ]);

        $company->update(array_merge($validatedData, ['is_active' => $request->boolean('is_active'),]));

        if ($request->hasFile('logo')) {
            if ($company->logo) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete('companies/logos/' . $company->logo);
            }
            $file = $request->file('logo');
            $filename = hexdec(uniqid()) . '.' . $file->getClientOriginalExtension();
            $file->storeAs('companies/logos', $filename, 'public');
            $company->update(['logo' => $filename]);
        }

        if (Session::get('active_company_id') == $company->id) {
            Session::put('active_company_name', $company->name);
        }

        return redirect()->route('companies.index')->with('success', 'Entreprise mise à jour avec succès!');
    }

    /**
     * Supprime une entreprise.
     */
    public function destroy(Company $company)
    {
        if ($company->user_id !== Auth::id()) {
            abort(403, 'Accès non autorisé à supprimer cette entreprise.');
        }

        if (Session::get('active_company_id') == $company->id) {
            return redirect()->back()->withErrors('Vous ne pouvez pas supprimer l\'entreprise actuellement sélectionnée. Veuillez en sélectionner une autre d\'abord.');
        }

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
        if ($company->user_id !== Auth::id()) {
            abort(403, 'Accès non autorisé à cette entreprise.');
        }

        Session::put('active_company_id', $company->id);
        Session::put('active_company_name', $company->name);

        // Après la sélection d'entreprise, vérifie si l'utilisateur a une licence active
        $user = Auth::user();
        $currentUserLicence = $user->licence;

        if (!$currentUserLicence || !$currentUserLicence->isActive) {
            // Si pas de licence ou licence inactive/expirée, redirige vers la page des offres
            return redirect()->route('licences.show')->with('info', 'Veuillez choisir un plan de licence pour accéder aux fonctionnalités de votre entreprise.');
        }

        return redirect()->route('dashboard')->with('success', 'Entreprise "' . $company->name . '" sélectionnée.');
    }
}
