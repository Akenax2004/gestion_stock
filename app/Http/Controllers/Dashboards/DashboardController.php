<?php

namespace App\Http\Controllers\Dashboards;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Quotation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\Company;
use Illuminate\Http\Request; // <-- AJOUTEZ CETTE LIGNE

class DashboardController extends Controller
{
    /**
     * Affiche le tableau de bord pour l'entreprise sélectionnée.
     * Ce contrôleur est protégé par les middlewares 'has.license' et 'company.selected'.
     */
    public function index(Request $request) // Utilisez l'objet Request ici
    {
        $user = Auth::user();

        // L'entreprise active est attachée à la requête par le middleware EnsureCompanySelected
        // Si le middleware est correctement appliqué, $activeCompany sera toujours une instance de Company ici.
        $activeCompany = $request->attributes->get('activeCompany');

        // Si pour une raison quelconque l'entreprise n'est pas trouvée (ne devrait pas arriver avec le middleware)
        // Rediriger vers la page de sélection d'entreprise.
        if (!$activeCompany) {
            return redirect()->route('manage.companies.index')->with('error', 'Aucune entreprise sélectionnée ou valide.');
        }

        // Vérification de la propriété de l'entreprise pour une sécurité accrue (bien que le middleware 'company.selected' devrait déjà le garantir)
        if ($user->isAdminPrincipal() && $activeCompany->user_id !== $user->id) {
            return redirect()->route('manage.companies.index')->with('error', 'Accès non autorisé à cette entreprise.');
        }
        // Pour les utilisateurs secondaires, on s'assure qu'ils sont liés à cette entreprise
        if ($user->isSecondaryUser() && $user->company_id !== $activeCompany->id) {
             Auth::logout(); // Déconnexion en cas de tentative d'accès non autorisé
             return redirect('/login')->with('error', 'Accès non autorisé à cette entreprise. Votre compte a été déconnecté.');
        }


        // Maintenant, filtrez toutes les données par $activeCompany->id
        $orders = Order::where('company_id', $activeCompany->id)->count();
        $completedOrders = Order::where('company_id', $activeCompany->id)
                                ->where('order_status', OrderStatus::COMPLETE)
                                ->count();

        $products = Product::where('company_id', $activeCompany->id)->count();

        $purchases = Purchase::where('company_id', $activeCompany->id)->count();
        $todayPurchases = Purchase::query()
                                  ->where('company_id', $activeCompany->id)
                                  ->whereDate('date', today())
                                  ->count();

        $categories = Category::where('company_id', $activeCompany->id)->count();

        $quotations = Quotation::where('company_id', $activeCompany->id)->count();
        $todayQuotations = Quotation::query()
                                    ->where('company_id', $activeCompany->id)
                                    ->whereDate('date', today())
                                    ->count();

        // Récupérer le nom de l'entreprise pour l'affichage
        $companyName = $activeCompany->name;

        return view('dashboard', [
            'companyName' => $companyName, // Passe le nom de l'entreprise à la vue
            'products' => $products,
            'orders' => $orders,
            'completedOrders' => $completedOrders,
            'purchases' => $purchases,
            'todayPurchases' => $todayPurchases,
            'categories' => $categories,
            'quotations' => $quotations,
            'todayQuotations' => $todayQuotations,
        ]);
    }
}
