<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\CompanyLicence; // Assurez-vous d'importer votre modèle CompanyLicence (ou UserLicence si renommé)
use Carbon\Carbon; // Pour la manipulation des dates
use Illuminate\Support\Facades\Log; // Pour les messages de log

class CheckCompanyLicence
{
    /**
     * Gère une requête entrante.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next)
    {
        // 1. Assurez-vous que l'utilisateur est authentifié
        if (!Auth::check()) {
            return redirect('/login'); // Redirige vers la page de connexion si non authentifié
        }

        $user = Auth::user();
        $userLicence = $user->licence; // Récupère la licence de l'utilisateur

        // 2. Vérifiez si l'utilisateur a une licence du tout
        if (!$userLicence) {
            // L'utilisateur n'a pas de licence (c'est peut-être la première connexion ou l'essai n'a pas été généré)
            // Redirigez-le vers la page de sélection/création d'entreprise,
            // où la licence d'essai sera générée lors de la création de la première entreprise.
            // Si l'utilisateur n'a aucune entreprise, on le renvoie à l'index des entreprises où il pourra en créer une.
            Session::flash('info', 'Bienvenue ! Veuillez créer ou sélectionner une entreprise pour commencer. Une licence d\'essai sera activée pour votre première entreprise.');
            return redirect()->route('companies.index'); // Redirection vers la liste/choix des entreprises
        }

        // 3. Vérifiez le statut et la date d'expiration de la licence de l'utilisateur
        if (
            ($userLicence->status !== CompanyLicence::STATUS_ACTIVE && $userLicence->status !== CompanyLicence::STATUS_TRIAL) ||
            ($userLicence->end_date < Carbon::today())
        ) {
            // Si la licence était active/essai mais est maintenant expirée, mettez à jour son statut
            if ($userLicence->status === CompanyLicence::STATUS_ACTIVE || $userLicence->status === CompanyLicence::STATUS_TRIAL) {
                $userLicence->update(['status' => CompanyLicence::STATUS_EXPIRED]);
                Log::info('Licence de l\'utilisateur ' . $user->id . ' mise à jour à EXPIRED car date passée.');
            }
            Session::flash('error', 'Votre licence a expiré ou est inactive. Veuillez la renouveler pour accéder aux fonctionnalités.');
            return redirect()->route('licences.show'); // Redirige vers la page d'achat de licence
        }

        // 4. Vérifiez le nombre d'entreprises de l'utilisateur par rapport à sa licence
        $maxCompaniesAllowed = $userLicence->licencePlan->max_companies;
        $currentCompanyCount = $user->companies->count();

        // Si max_companies est -1, c'est illimité
        if ($maxCompaniesAllowed !== -1 && $currentCompanyCount > $maxCompaniesAllowed) {
            Session::flash('error', 'Vous avez dépassé le nombre maximal d\'entreprises autorisées (' . $maxCompaniesAllowed . '). Veuillez désactiver ou supprimer des entreprises, ou mettre à niveau votre licence.');
            return redirect()->route('licences.show'); // Ou vers une page de gestion des entreprises
        }

        // Si la licence est valide (active ou en essai non expiré) et que les limites sont respectées, continuez la requête.
        return $next($request);
    }
}
