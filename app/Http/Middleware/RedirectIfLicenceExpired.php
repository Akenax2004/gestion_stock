<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\CompanyLicence; // Utilisez CompanyLicence (qui pointe vers user_licences)
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class RedirectIfLicenceExpired
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
        if (!Auth::check()) {
            return redirect('/login'); // Redirige vers la page de connexion si non authentifié
        }

        $user = Auth::user();
        $userLicence = $user->licence; // Récupère la licence de l'utilisateur

        // 1. Vérifiez si l'utilisateur a une licence du tout ou si elle est active/valide
        // Si pas de licence OU si la licence n'est PAS ACTIVE/TRIAL OU si la date est passée
        if (
            !$userLicence ||
            ($userLicence->status !== CompanyLicence::STATUS_ACTIVE && $userLicence->status !== CompanyLicence::STATUS_TRIAL) ||
            ($userLicence->end_date < Carbon::today())
        ) {
            // Si la licence était active/essai mais est maintenant expirée, mettez à jour son statut
            if ($userLicence && ($userLicence->status === CompanyLicence::STATUS_ACTIVE || $userLicence->status === CompanyLicence::STATUS_TRIAL) && $userLicence->end_date < Carbon::today()) {
                $userLicence->update(['status' => CompanyLicence::STATUS_EXPIRED]);
                Log::info('Licence de l\'utilisateur ' . $user->id . ' mise à jour à EXPIRED car date passée.');
            }
            Session::flash('error', 'Votre licence a expiré ou est inactive. Veuillez la renouveler ou choisir un plan.');
            return redirect()->route('licences.show'); // Redirige vers la page d'achat de licence
        }

        // 2. Si la licence est valide, vérifiez le nombre d'entreprises de l'utilisateur
        $maxCompaniesAllowed = $userLicence->licencePlan->max_companies;
        $currentCompanyCount = $user->companies->count();

        if ($maxCompaniesAllowed !== -1 && $currentCompanyCount > $maxCompaniesAllowed) {
            Session::flash('error', 'Vous avez dépassé le nombre maximal d\'entreprises autorisées (' . $maxCompaniesAllowed . '). Veuillez désactiver ou supprimer des entreprises, ou mettre à niveau votre licence.');
            return redirect()->route('licences.show'); // Redirige vers la page d'achat/mise à niveau
        }

        // Si tout est bon, continuez la requête
        return $next($request);
    }
}
