<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\LicencePlan;
use App\Models\UserLicence; // Utilise le modèle UserLicence, c'est le bon nom maintenant
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CompanyController extends Controller
{
    /**
     * Affiche une liste de toutes les entreprises de l'utilisateur connecté (admin_principal).
     * Cette méthode NE DOIT PAS être filtrée par l'entreprise active en session,
     * car son but est de permettre à l'utilisateur de sélectionner ou de créer une entreprise.
     */
    public function index()
    {
        // Le middleware 'auth' devrait déjà gérer l'authentification.
        // Si cette méthode est appelée, l'utilisateur est authentifié.
        $user = Auth::user();

        // S'assurer que seul un admin_principal peut voir cette page.
        // Normalement, le middleware 'has.license' aurait déjà vérifié cela pour les routes générales,
        // mais pour une sécurité accrue et une clarté logique, on peut le garder ici.
        if (!$user->isAdminPrincipal()) {
            return redirect()->route('dashboard')->with('error', 'Vous n\'êtes pas autorisé à gérer les entreprises.');
        }

        $companies = Company::where('user_id', $user->id)->get();

        return view('companies.index', compact('companies'));
    }

    /**
     * Affiche le formulaire de création d'une nouvelle entreprise.
     * Vérifie si l'utilisateur peut créer une nouvelle entreprise en fonction de sa licence.
     */
    public function create()
    {
        $user = Auth::user();

        // S'assurer que seul un admin_principal peut créer une entreprise.
        if (!$user->isAdminPrincipal()) {
            return redirect()->route('dashboard')->with('error', 'Vous n\'êtes pas autorisé à créer des entreprises.');
        }

        $currentUserLicence = $user->licence;

        // Vérification de la licence avant d'afficher le formulaire.
        // Si l'utilisateur n'a pas de licence OU si la licence n'est pas active, on ne devrait même pas arriver ici
        // si le middleware 'has.license' est correctement appliqué. Mais c'est une vérification supplémentaire.
        if (!$currentUserLicence || !$currentUserLicence->isActive()) { // Utilisez isActive() pour le modèle UserLicence
             return redirect()->route('choose-license')->with('info', 'Veuillez activer votre licence pour créer des entreprises.');
        }

        // Si l'utilisateur a une licence et qu'il a atteint le nombre max d'entreprises, redirigez-le.
        $maxCompaniesAllowed = $currentUserLicence->licencePlan->max_companies;
        $currentCompanyCount = $user->companies->count();

        // -1 pour illimité
        if ($maxCompaniesAllowed !== -1 && $currentCompanyCount >= $maxCompaniesAllowed) {
            return redirect()->route('manage.companies.index')->with('error',
                'Vous avez atteint le nombre maximal d\'entreprises autorisé par votre plan de licence (' . $maxCompaniesAllowed . ' entreprises). Veuillez mettre à niveau votre licence pour ajouter plus d\'entreprises.'
            );
        }

        // Sinon, affichez le formulaire de création d'entreprise.
        return view('companies.create');
    }

    /**
     * Stocke une nouvelle entreprise dans la base de données, l'associant à l'utilisateur connecté.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // S'assurer que seul un admin_principal peut créer une entreprise.
        if (!$user->isAdminPrincipal()) {
            return redirect()->route('dashboard')->with('error', 'Vous n\'êtes pas autorisé à créer des entreprises.');
        }

        $currentUserLicence = $user->licence;
        $currentCompanyCount = $user->companies->count();

        // --- Logique de vérification de licence AVANT création d'entreprise ---
        // Si l'utilisateur n'a pas de licence OU si la licence n'est pas active
        if (!$currentUserLicence || !$currentUserLicence->isActive()) {
            Log::warning('Tentative de création d\'entreprise par l\'utilisateur ' . $user->id . ' avec une licence expirée ou inactive.');
            return redirect()->route('choose-license')->withErrors('Votre licence est expirée ou inactive. Veuillez la renouveler pour créer de nouvelles entreprises.');
        }

        // Si l'utilisateur a une licence, vérifie si la création de nouvelle entreprise est autorisée
        $maxCompaniesAllowed = $currentUserLicence->licencePlan->max_companies;

        if ($maxCompaniesAllowed !== -1 && $currentCompanyCount >= $maxCompaniesAllowed) {
            Log::warning('Tentative de création d\'entreprise par l\'utilisateur ' . $user->id . ' mais la limite de licence (' . $maxCompaniesAllowed . ') est atteinte ou dépassée.');
            return redirect()->back()->withErrors('Vous avez atteint le nombre maximal d\'entreprises autorisé par votre plan de licence (' . $maxCompaniesAllowed . ' entreprises). Veuillez mettre à niveau votre licence pour ajouter plus d\'entreprises.');
        }
        // --- FIN : Logique de vérification de licence AVANT création d'entreprise ---

        // La validation et la création de l'entreprise se font ici
        $validatedData = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('companies')->where(function ($query) use ($user) { // Utilise $user au lieu de $userId
                    return $query->where('user_id', $user->id);
                }),
            ],
            'email' => [
                'nullable', 'email', 'max:255',
                Rule::unique('companies')->where(function ($query) use ($user) {
                    return $query->where('user_id', $user->id);
                }),
            ],
            'phone' => [
                'nullable', 'string', 'max:25',
                Rule::unique('companies')->where(function ($query) use ($user) {
                    return $query->where('user_id', $user->id);
                }),
            ],
            'address' => 'nullable|string|max:255',
            'vat_number' => 'nullable|string|max:50',
            'logo' => 'nullable|image|max:2048',
            'is_active' => 'boolean', // Assurez-vous que le champ est_active est géré
        ]);

        $company = Company::create(array_merge($validatedData, [
            'user_id' => $user->id, // Attribue l'entreprise à l'admin principal
            'is_active' => $request->boolean('is_active', true), // Par défaut, active la nouvelle entreprise
        ]));

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = hexdec(uniqid()) . '.' . $file->getClientOriginalExtension();
            // Utiliser Storage::putFileAs pour un stockage plus propre
            $file->storeAs('companies/logos', $filename, 'public');
            $company->update(['logo' => $filename]);
        }

        // Définir l'entreprise comme active en session après sa création
        // Cela est important pour le middleware 'company.selected'
        Session::put('active_company_id', $company->id);
        Session::put('active_company_name', $company->name);

        // Redirection après succès : vers la page de gestion des entreprises de l'admin principal
        // Utilisez le nom de route cohérent
        return redirect()->route('manage.companies.index')->with('success', 'Entreprise créée avec succès!');
    }

    /**
     * Affiche les détails d'une entreprise spécifique.
     * Cette route est protégée par 'has.license' et 'company.selected'.
     */
    public function show(Company $company)
    {
        // La validation d'accès via $company->user_id !== Auth::id() est bonne et doit être maintenue.
        if ($company->user_id !== Auth::id()) {
            abort(403, 'Accès non autorisé à cette entreprise.');
        }
        return view('companies.show', compact('company'));
    }

    /**
     * Affiche le formulaire de modification d'une entreprise.
     * Cette route est protégée par 'has.license' et 'company.selected'.
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
     * Cette route est protégée par 'has.license' et 'company.selected'.
     */
    public function update(Request $request, Company $company)
    {
        if ($company->user_id !== Auth::id()) {
            abort(403, 'Accès non autorisé à mettre à jour cette entreprise.');
        }

        $user = Auth::user(); // Utilise $user au lieu de $userId
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('companies')->where(function ($query) use ($user) { return $query->where('user_id', $user->id); })->ignore($company->id),],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('companies')->where(function ($query) use ($user) { return $query->where('user_id', $user->id); })->ignore($company->id),],
            'phone' => ['nullable', 'string', 'max:25', Rule::unique('companies')->where(function ($query) use ($user) { return $query->where('user_id', $user->id); })->ignore($company->id),],
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

        // Si l'entreprise active en session est celle qui vient d'être mise à jour, mettez à jour son nom en session
        if (Session::get('active_company_id') == $company->id) {
            Session::put('active_company_name', $company->name);
        }

        return redirect()->route('manage.companies.index')->with('success', 'Entreprise mise à jour avec succès!');
    }

    /**
     * Supprime une entreprise.
     * Cette route est protégée par 'has.license' et 'company.selected'.
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

        return redirect()->route('manage.companies.index')->with('success', 'Entreprise supprimée avec succès!');
    }

    /**
     * Définit l'entreprise active en session et redirige vers le tableau de bord.
     * Cette route est protégée par 'auth', 'verified', et 'has.license'
     * (car il faut une licence pour sélectionner une entreprise et ensuite aller au dashboard)
     */
    public function select(Company $company)
    {
        if ($company->user_id !== Auth::id()) {
            abort(403, 'Accès non autorisé à cette entreprise.');
        }

        // Vérification supplémentaire de la licence juste avant la sélection,
        // bien que le middleware 'has.license' devrait déjà l'avoir géré.
        $user = Auth::user();
        $currentUserLicence = $user->licence;

        if (!$currentUserLicence || !$currentUserLicence->isActive()) {
            // Si pas de licence ou licence inactive/expirée, redirige vers la page de choix de licence
            // C'est 'choose-license' maintenant, pas 'licences.show'
            return redirect()->route('choose-license')->with('info', 'Veuillez choisir un plan de licence pour accéder aux fonctionnalités de votre entreprise.');
        }

        // Si tout est bon, mettez l'entreprise en session
        Session::put('active_company_id', $company->id);
        Session::put('active_company_name', $company->name);

        // Redirige vers le tableau de bord global, qui lui-même redirigera vers la bonne page
        // (par exemple, vers le dashboard spécifique de l'entreprise ou le dashboard par défaut)
        return redirect()->route('dashboard')->with('success', 'Entreprise "' . $company->name . '" sélectionnée.');
    }
}
