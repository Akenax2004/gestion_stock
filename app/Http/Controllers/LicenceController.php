<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LicencePlan;
use App\Models\UserLicence; // Utilise le modèle UserLicence, c'est le bon nom maintenant
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Providers\RouteServiceProvider; // Pour la redirection vers la page de gestion des entreprises

class LicenceController extends Controller
{
    /**
     * Affiche la page des plans de licence
     */
    public function chooseLicense() // C'est cette méthode qui doit exister
    {
        // Seuls les admin_principal devraient voir cette page
        if (!Auth::user()->isAdminPrincipal()) {
            // Rediriger ou afficher une erreur si un non-admin principal tente d'accéder
            return redirect()->route('dashboard')->with('error', 'Vous n\'êtes pas autorisé à gérer les licences.');
        }

        $licencePlans = LicencePlan::all(); // Récupère tous les plans disponibles

        // Récupère la licence active de l'utilisateur connecté (admin_principal)
        $currentUserLicence = Auth::user()->licence;

        return view('licences.choose', compact('licencePlans', 'currentUserLicence')); // Utilise 'licences.choose' pour la vue
    }

    /**
     * Gère la soumission du formulaire d'achat (y compris l'activation de l'essai gratuit)
     */
    public function processPurchase(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:licence_plans,id',
        ]);

        $plan = LicencePlan::findOrFail($request->plan_id);
        $user = Auth::user();
        $userId = $user->id;

        // Seuls les admin_principal peuvent souscrire des licences
        if (!$user->isAdminPrincipal()) {
            return redirect()->back()->withErrors('Seuls les administrateurs principaux peuvent souscrire à une licence.');
        }

        // Récupère la licence existante de l'utilisateur ou en crée une nouvelle instance
        $licence = UserLicence::firstOrNew(['user_id' => $userId]);

        // Vérifie si le plan choisi est un essai gratuit
        if ($plan->plan_type === LicencePlan::PLAN_TRIAL) {
            // Logique pour l'essai gratuit
            if ($licence->exists && ($licence->status === UserLicence::STATUS_TRIAL || $licence->status === UserLicence::STATUS_ACTIVE)) {
                // Si l'utilisateur a déjà un essai ou une licence active, ne pas permettre un nouvel essai gratuit
                Log::warning('Utilisateur ' . $userId . ' a tenté de souscrire à un nouvel essai gratuit alors qu\'il a déjà une licence valide.');
                return redirect()->back()->withErrors('Vous avez déjà une licence active ou vous avez déjà utilisé votre essai gratuit.');
            }

            $licence->licence_plan_id = $plan->id;
            $licence->start_date = Carbon::today();
            $licence->end_date = Carbon::today()->addDays($plan->duration_days);
            $licence->status = UserLicence::STATUS_TRIAL;
            $licence->transaction_id = null; // Pas de transaction ID pour l'essai gratuit
            $licence->save();

            Log::info('Licence d\'essai gratuite activée pour l\'utilisateur: ' . $userId);
            // Redirection après succès : vers la page de gestion des entreprises de l'admin principal
            return redirect()->route('manage.companies.index')->with('success', 'Votre essai gratuit de ' . $plan->duration_days . ' jours a été activé !');

        } else {
            // Logique pour les plans payants
            // Normalement, ici vous initieriez une transaction avec une passerelle de paiement.

            // Pseudo-code pour l'intégration de paiement:
            // $paymentService = new YourPaymentGatewayService();
            // try {
            //     $transactionDetails = $paymentService->createPayment($plan->price_xof, $userId, $plan->id);
            //     // Enregistrez la licence avec un statut 'PENDING'
            //     $licence->licence_plan_id = $plan->id;
            //     $licence->start_date = Carbon::today(); // Date de début temporaire ou à définir après paiement
            //     $licence->end_date = Carbon::today()->addDays($plan->duration_days); // Date de fin temporaire
            //     $licence->status = UserLicence::STATUS_PENDING;
            //     $licence->transaction_id = $transactionDetails['transaction_id']; // Utilisez le vrai ID de transaction
            //     $licence->save();
            //
            //     return redirect()->away($transactionDetails['payment_url']); // Redirection vers la passerelle
            // } catch (\Exception $e) {
            //     Log::error('Erreur lors de l\'initialisation du paiement pour l\'utilisateur ' . $userId . ': ' . $e->getMessage());
            //     return redirect()->back()->withErrors('Une erreur est survenue lors de l\'initialisation du paiement. Veuillez réessayer.');
            // }

            // Pour le moment, sans intégration de paiement réelle, nous allons l'activer directement
            // CECI EST À REMPLACER PAR VOTRE VRAIE LOGIQUE DE PAIEMENT
            $licence->licence_plan_id = $plan->id;
            $licence->start_date = Carbon::today();
            $licence->end_date = Carbon::today()->addDays($plan->duration_days);
            $licence->status = UserLicence::STATUS_ACTIVE; // Activez directement pour les tests
            $licence->transaction_id = 'FAKE_TXN_' . uniqid(); // Placeholder
            $licence->save();

            Log::info('Licence payante activée (mode test) pour l\'utilisateur: ' . $userId . ' - Plan: ' . $plan->name);
            // Redirection après succès : vers la page de gestion des entreprises de l'admin principal
            return redirect()->route('manage.companies.index')->with('success', 'Votre licence "' . $plan->name . '" a été activée avec succès !');
        }
    }

    /**
     * Gère les webhooks de la passerelle de paiement (pour les plans payants)
     */
    public function handleWebhook(Request $request, string $provider)
    {
        // ... (Logique de webhook existante, en s'assurant qu'elle met à jour UserLicence::class)

        // Exemple simplifié:
        $payload = $request->all();
        $transactionId = $payload['transaction_id'] ?? null; // À adapter selon le prestataire

        if ($transactionId) {
            $licence = UserLicence::where('transaction_id', $transactionId)->first();
            if ($licence) {
                // Vérifications de sécurité: montant, statut, etc.
                $licence->start_date = Carbon::today();
                $licence->end_date = Carbon::today()->addDays($licence->licencePlan->duration_days);
                $licence->status = UserLicence::STATUS_ACTIVE;
                $licence->save();
                Log::info('Licence activée via webhook pour user_id: ' . $licence->user_id . ' (Transaction: ' . $transactionId . ')');
                return response()->json(['message' => 'Webhook received and processed'], 200);
            }
        }
        Log::warning('Webhook reçu mais transaction_id non trouvé ou payload invalide pour le fournisseur: ' . $provider);
        return response()->json(['message' => 'Invalid webhook or transaction not found'], 400);
    }
}
