<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Company; // Importez le modèle Company
use App\Models\LicencePlan; // Importez le modèle LicencePlan
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role; // Importez le modèle Role de Spatie

class UserController extends Controller
{
    /**
     * Affiche une liste des utilisateurs secondaires gérés par l'admin principal pour l'entreprise sélectionnée.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Seul l'admin principal peut gérer les utilisateurs secondaires
        if (!$user->isAdminPrincipal()) {
            return redirect()->route('dashboard')->with('error', 'Vous n\'êtes pas autorisé à gérer les utilisateurs.');
        }

        // Récupère l'entreprise active attachée par le middleware
        $activeCompany = $request->attributes->get('activeCompany');
        if (!$activeCompany) {
            return redirect()->route('manage.companies.index')->with('error', 'Veuillez sélectionner une entreprise.');
        }

        // Récupère les utilisateurs secondaires liés à cet admin principal ET à l'entreprise active
        $users = User::where('admin_principal_id', $user->id)
                     ->where('company_id', $activeCompany->id)
                     ->get();

        // Chemins des vues modifiés de 'users.index' à 'user.index', etc.
        return view('user.index', compact('users', 'activeCompany'));
    }

    /**
     * Affiche le formulaire de création d'un nouvel utilisateur secondaire.
     */
    public function create(Request $request)
    {
        $user = Auth::user();

        if (!$user->isAdminPrincipal()) {
            return redirect()->route('dashboard')->with('error', 'Vous n\'êtes pas autorisé à créer des utilisateurs.');
        }

        $activeCompany = $request->attributes->get('activeCompany');
        if (!$activeCompany) {
            return redirect()->route('manage.companies.index')->with('error', 'Veuillez sélectionner une entreprise avant de créer un utilisateur.');
        }

        $currentUserLicence = $user->licence;

        // Vérifier la limite d'utilisateurs par entreprise selon la licence
        $maxUsersPerCompany = $currentUserLicence->licencePlan->max_users_per_company;
        $currentUsersCount = User::where('admin_principal_id', $user->id)
                                  ->where('company_id', $activeCompany->id)
                                  ->count();

        // -1 pour illimité
        if ($maxUsersPerCompany !== -1 && $currentUsersCount >= $maxUsersPerCompany) {
            return redirect()->route('user.index')->with('error', // Chemin modifié ici
                'Vous avez atteint le nombre maximal d\'utilisateurs (' . $maxUsersPerCompany . ') pour l\'entreprise "' . $activeCompany->name . '" autorisé par votre plan de licence. Veuillez mettre à niveau votre licence pour ajouter plus d\'utilisateurs.'
            );
        }

        // Récupère les rôles disponibles pour les utilisateurs secondaires
        $roles = Role::whereIn('name', ['gestion', 'vente'])->get();

        return view('user.create', compact('roles', 'activeCompany')); // Chemin modifié ici
    }

    /**
     * Stocke un nouvel utilisateur secondaire.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user->isAdminPrincipal()) {
            return redirect()->route('dashboard')->with('error', 'Vous n\'êtes pas autorisé à créer des utilisateurs.');
        }

        $activeCompany = $request->attributes->get('activeCompany');
        if (!$activeCompany) {
            return redirect()->route('manage.companies.index')->with('error', 'Veuillez sélectionner une entreprise avant de créer un utilisateur.');
        }

        $currentUserLicence = $user->licence;
        $maxUsersPerCompany = $currentUserLicence->licencePlan->max_users_per_company;
        $currentUsersCount = User::where('admin_principal_id', $user->id)
                                  ->where('company_id', $activeCompany->id)
                                  ->count();

        if ($maxUsersPerCompany !== -1 && $currentUsersCount >= $maxUsersPerCompany) {
            return redirect()->back()->withErrors('Vous avez atteint le nombre maximal d\'utilisateurs pour cette entreprise autorisé par votre plan de licence.');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users', 'alpha_dash:ascii'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', 'min:8'],
            'role' => ['required', Rule::in(['gestion', 'vente'])], // Valide que le rôle est 'gestion' ou 'vente'
        ]);

        $newUser = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'admin_principal_id' => $user->id, // Lie le nouvel utilisateur à l'admin principal
            'company_id' => $activeCompany->id, // Lie le nouvel utilisateur à l'entreprise sélectionnée
        ]);

        $newUser->assignRole($request->role); // Assigne le rôle choisi

        return redirect()->route('users.index')->with('success', 'Utilisateur créé et rôle attribué avec succès pour l\'entreprise ' . $activeCompany->name . ' !');
    }

    /**
     * Affiche les détails d'un utilisateur secondaire.
     */
    public function show(User $userToShow, Request $request)
    {
        $user = Auth::user();

        // L'admin principal ne peut voir que ses propres utilisateurs secondaires
        if ($user->isAdminPrincipal() && ($userToShow->admin_principal_id !== $user->id || $userToShow->company_id !== $request->attributes->get('activeCompany')->id)) {
            abort(403, 'Accès non autorisé à cet utilisateur.');
        }
        // Un utilisateur secondaire ne peut pas voir d'autres utilisateurs
        if ($user->isSecondaryUser()) {
             abort(403, 'Accès non autorisé.');
        }

        return view('user.show', compact('userToShow')); // Chemin modifié ici
    }

    /**
     * Affiche le formulaire de modification d'un utilisateur secondaire.
     */
    public function edit(User $userToEdit, Request $request)
    {
        $user = Auth::user();

        // L'admin principal ne peut modifier que ses propres utilisateurs secondaires
        if ($user->isAdminPrincipal() && ($userToEdit->admin_principal_id !== $user->id || $userToEdit->company_id !== $request->attributes->get('activeCompany')->id)) {
            abort(403, 'Accès non autorisé à modifier cet utilisateur.');
        }
        // Un utilisateur secondaire ne peut pas modifier d'autres utilisateurs
        if ($user->isSecondaryUser()) {
            abort(403, 'Accès non autorisé.');
        }

        $roles = Role::whereIn('name', ['gestion', 'vente'])->get();

        return view('user.edit', compact('userToEdit', 'roles')); // Chemin modifié ici
    }

    /**
     * Met à jour un utilisateur secondaire.
     */
    public function update(Request $request, User $userToUpdate)
    {
        $user = Auth::user();

        if ($user->isAdminPrincipal() && ($userToUpdate->admin_principal_id !== $user->id || $userToUpdate->company_id !== $request->attributes->get('activeCompany')->id)) {
            abort(403, 'Accès non autorisé à mettre à jour cet utilisateur.');
        }
        if ($user->isSecondaryUser()) {
            abort(403, 'Accès non autorisé.');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'alpha_dash:ascii', Rule::unique('users')->ignore($userToUpdate->id)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($userToUpdate->id)],
            'role' => ['required', Rule::in(['gestion', 'vente'])],
        ]);

        $userToUpdate->update($request->only('name', 'username', 'email'));

        // Mettre à jour le rôle
        $userToUpdate->syncRoles([$request->role]);

        return redirect()->route('users.index')->with('success', 'Utilisateur mis à jour avec succès !');
    }

    /**
     * Supprime un utilisateur secondaire.
     */
    public function destroy(User $userToDelete, Request $request)
    {
        $user = Auth::user();

        if ($user->isAdminPrincipal() && ($userToDelete->admin_principal_id !== $user->id || $userToDelete->company_id !== $request->attributes->get('activeCompany')->id)) {
            abort(403, 'Accès non autorisé à supprimer cet utilisateur.');
        }
        if ($user->isSecondaryUser()) {
            abort(403, 'Accès non autorisé.');
        }

        // Empêcher un admin principal de supprimer son propre compte
        if ($userToDelete->id === $user->id) {
            return redirect()->back()->withErrors('Vous ne pouvez pas supprimer votre propre compte administrateur principal.');
        }

        $userToDelete->delete();

        return redirect()->route('users.index')->with('success', 'Utilisateur supprimé avec succès !');
    }

    /**
     * Met à jour le mot de passe d'un utilisateur.
     * (Cette méthode peut être utilisée pour le profil personnel de l'utilisateur aussi, ou pour l'admin principal qui change le mot de passe d'un secondaire)
     */
    public function updatePassword(Request $request, User $userToUpdate)
    {
        $user = Auth::user();

        // L'admin principal peut changer le mot de passe de ses secondaires.
        // Un utilisateur peut changer son propre mot de passe.
        if (!($user->isAdminPrincipal() && $userToUpdate->admin_principal_id === $user->id && $userToUpdate->company_id === $request->attributes->get('activeCompany')->id) &&
            !($user->id === $userToUpdate->id)) {
            abort(403, 'Accès non autorisé à modifier le mot de passe de cet utilisateur.');
        }

        $request->validate([
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $userToUpdate->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->back()->with('success', 'Mot de passe mis à jour avec succès!');
    }
}
