<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Models\UserLicence; // Assurez-vous d'importer le modèle UserLicence
use Carbon\Carbon;

class EnsureUserHasActiveLicense
{
    /**
     * Gère une requête entrante.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Si l'utilisateur n'est pas authentifié, le laisser passer (pour le middleware 'auth')
        // Ou le rediriger vers la page de connexion s'il n'y est pas déjà
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // Si l'utilisateur n'est PAS un admin_principal, le laisser passer.
        // Les utilisateurs secondaires n'ont pas à gérer les licences et seront redirigés
        // vers leur dashboard d'entreprise via une autre logique (ou des Gates/Policies).
        if (!$user->isAdminPrincipal()) {
            // Optionnel: Vous pourriez ajouter une redirection ici si un secondary_user accède à une route non prévue pour lui
            // Par exemple, si cette route est censée être réservée aux admin_principal
            // Pour l'instant, on suppose que les routes secondaires seront protégées différemment.
            return $next($request);
        }

        // Si l'utilisateur EST un admin_principal
        // Vérifie si l'utilisateur a une licence active
        $userLicence = $user->licence; // Utilise la relation définie dans le modèle User

        // Si l'utilisateur n'a PAS de licence, ou si elle n'est PAS active
        if (!$userLicence || !$userLicence->isActive()) {
            // Redirige vers la page de choix de licence
            // Sauf si la requête est déjà pour la page de choix de licence ou le traitement d'achat
            if ($request->routeIs('choose-license') || $request->routeIs('process-purchase')) {
                return $next($request); // Laisse passer si c'est déjà la page de licence
            }

            return redirect()->route('choose-license')->with('info', 'Veuillez choisir ou activer une licence pour continuer.');
        }

        // Si l'utilisateur est un admin_principal et a une licence active,
        // et qu'il essaie d'accéder à la page de choix de licence, le rediriger
        // vers la page de gestion des entreprises.
        if ($userLicence && $userLicence->isActive() && $request->routeIs('choose-license')) {
             return redirect()->route('manage.companies.index'); // Redirige vers la gestion des entreprises
        }

        return $next($request);
    }
}
