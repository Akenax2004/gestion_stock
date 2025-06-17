<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompanySelected
{
    /**
     * Gère une requête entrante.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Si l'utilisateur n'est pas connecté, laissez Laravel gérer l'authentification (via le middleware 'auth')
        if (!Auth::check()) {
            return $next($request);
        }

        // Si l'utilisateur tente d'accéder à la page de gestion des entreprises ou à la sélection
        // Laissez-le passer pour qu'il puisse sélectionner ou créer une entreprise.
        if ($request->routeIs('companies.*') || $request->routeIs('logout')) { // Ajoutez 'logout' pour éviter les boucles
            return $next($request);
        }

        // Si aucune entreprise n'est active en session, redirigez l'utilisateur
        if (!Session::has('active_company_id')) {
            // Vous pouvez aussi ajouter un message flash ici
            return redirect()->route('companies.index')
                             ->withErrors('Veuillez sélectionner une entreprise pour continuer.');
        }

        // Si une entreprise est sélectionnée, continuez la requête
        return $next($request);
    }
}
