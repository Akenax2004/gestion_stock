<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Company; // Assurez-vous d'importer votre modèle Company

class EnsureCompanySelected
{
    /**
     * Gère une requête entrante.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Si l'utilisateur n'est pas authentifié, le laisser passer (sera géré par le middleware 'auth')
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // Logique pour les admin_principal
        if ($user->isAdminPrincipal()) {
            // Si l'admin_principal n'a pas d'entreprise sélectionnée en session
            if (!Session::has('active_company_id') || !Session::get('active_company_id')) {
                // Sauf si la requête est déjà pour la page de gestion des entreprises (où l'on sélectionne)
                if ($request->routeIs('manage.companies.index') ||
                    $request->routeIs('manage.companies.create') ||
                    $request->routeIs('manage.companies.store') ||
                    $request->routeIs('companies.select'))
                {
                    return $next($request); // Laisser passer pour ces routes
                }

                // Rediriger vers la page de gestion des entreprises pour en sélectionner/créer une
                return redirect()->route('manage.companies.index')->with('info', 'Veuillez sélectionner une entreprise pour accéder à cette fonctionnalité.');
            }

            // Vérifier que l'entreprise sélectionnée en session appartient bien à l'utilisateur actuel
            $activeCompanyId = Session::get('active_company_id');
            $company = Company::find($activeCompanyId);

            if (!$company || $company->user_id !== $user->id) {
                // Si l'entreprise n'existe plus ou n'appartient pas à l'utilisateur, vider la session et rediriger
                Session::forget('active_company_id');
                Session::forget('active_company_name');
                return redirect()->route('manage.companies.index')->with('error', 'L\'entreprise sélectionnée n\'est plus valide. Veuillez en choisir une nouvelle.');
            }

            // Attacher l'entreprise active à la requête pour un accès facile dans les contrôleurs
            $request->attributes->set('activeCompany', $company);

        }
        // Logique pour les utilisateurs secondaires (gestion/vente)
        // Les utilisateurs secondaires sont directement rattachés à une entreprise via leur colonne 'company_id'
        elseif ($user->isSecondaryUser()) {
            if (!$user->company_id) {
                // Un utilisateur secondaire sans company_id est une anomalie ou doit être redirigé vers une page d'erreur
                Auth::logout(); // Déconnexion car l'état est incohérent
                return redirect('/login')->with('error', 'Votre compte n\'est pas associé à une entreprise valide. Veuillez contacter l\'administrateur.');
            }

            $company = Company::find($user->company_id);

            if (!$company) {
                // L'entreprise rattachée n'existe plus
                Auth::logout();
                return redirect('/login')->with('error', 'L\'entreprise associée à votre compte n\'existe plus. Veuillez contacter l\'administrateur.');
            }

            // Attacher l'entreprise directement via la relation pour les utilisateurs secondaires
            $request->attributes->set('activeCompany', $company);

            // Mettre l'entreprise en session pour une cohérence avec le reste de l'application (utile pour l'affichage)
            Session::put('active_company_id', $company->id);
            Session::put('active_company_name', $company->name);

        } else {
            // Si l'utilisateur n'est ni admin_principal ni secondaire, ou si son rôle n'est pas géré ici
            // Rediriger vers un dashboard par défaut ou une page d'erreur
            return redirect()->route('dashboard')->with('error', 'Accès non autorisé.');
        }


        return $next($request);
    }
}
