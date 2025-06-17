<?php

namespace App\Http\Controllers\Purchase;

use App\Enums\PurchaseStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Purchase\StorePurchaseRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseDetails;
use App\Models\Supplier;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; // Importez la façade Auth
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;

class PurchaseController extends Controller
{
    /**
     * Affiche une liste de tous les achats de l'utilisateur connecté.
     */
    public function index()
    {
        // Vérifie si un utilisateur est authentifié
        if (!Auth::check()) {
            return redirect('/login')->withErrors('Veuillez vous connecter.');
        }

        $userId = Auth::id();

        // Récupère uniquement les achats créés par l'utilisateur connecté
        $purchases = Purchase::where('created_by', $userId)->latest()->get();

        return view('purchases.index', [
            'purchases' => $purchases,
        ]);
    }

    /**
     * Affiche une liste des achats approuvés par l'utilisateur connecté.
     */
    public function approvedPurchases()
    {
        if (!Auth::check()) {
            return redirect('/login')->withErrors('Veuillez vous connecter.');
        }

        $userId = Auth::id();

        // Récupère les achats approuvés par l'utilisateur connecté
        $purchases = Purchase::with(['supplier'])
            ->where('created_by', $userId) // Filtre par l'utilisateur créateur
            ->where('status', PurchaseStatus::APPROVED)
            ->get();

        return view('purchases.approved-purchases', [
            'purchases' => $purchases,
        ]);
    }

    /**
     * Affiche les détails d'un achat spécifique.
     * S'assure que seul le propriétaire peut le voir.
     */
    public function show(Purchase $purchase)
    {
        // Vérifie si l'utilisateur connecté est le créateur de l'achat.
        if (!Auth::check() || $purchase->created_by !== Auth::id()) {
            abort(403, 'Accès non autorisé à cet achat.');
        }

        // Charge les relations nécessaires
        $purchase->loadMissing(['supplier', 'details', 'createdBy', 'updatedBy']);

        // Récupère les détails des produits pour cet achat (implicitement liés à l'utilisateur via l'achat parent)
        $products = PurchaseDetails::where('purchase_id', $purchase->id)->get();

        return view('purchases.details-purchase', [
            'purchase' => $purchase,
            'products' => $products
        ]);
    }

    /**
     * Affiche le formulaire de modification d'un achat.
     * S'assure que seul le propriétaire peut le modifier.
     */
    public function edit(Purchase $purchase)
    {
        // Vérifie si l'utilisateur connecté est le créateur de l'achat.
        if (!Auth::check() || $purchase->created_by !== Auth::id()) {
            abort(403, 'Accès non autorisé à modifier cet achat.');
        }

        // Charge les relations nécessaires
        $purchase->loadMissing(['supplier', 'details']); // Correction: Utilisation de loadMissing au lieu de with()->get()

        return view('purchases.edit', [
            'purchase' => $purchase,
        ]);
    }

    /**
     * Affiche le formulaire de création d'un nouvel achat.
     * Les catégories et fournisseurs listés sont également filtrés par l'utilisateur.
     */
    public function create()
    {
        if (!Auth::check()) {
            return redirect('/login')->withErrors('Veuillez vous connecter.');
        }

        $userId = Auth::id();

        return view('purchases.create', [
            // Filtre les catégories et fournisseurs pour n'afficher que ceux de l'utilisateur connecté
            'categories' => Category::where('user_id', $userId)->select(['id', 'name'])->get(),
            'suppliers' => Supplier::where('user_id', $userId)->select(['id', 'name'])->get(),
        ]);
    }

    /**
     * Stocke un nouvel achat dans la base de données, l'associant à l'utilisateur connecté.
     */
    public function store(StorePurchaseRequest $request)
    {
        if (!Auth::check()) {
            return redirect('/login')->withErrors('Veuillez vous connecter.');
        }

        $userId = Auth::id();

        // Vérification de sécurité : Assurer que le fournisseur soumis appartient bien à l'utilisateur
        $supplier = Supplier::where('id', $request->supplier_id)->where('user_id', $userId)->first();
        if (!$supplier) {
            return back()->withErrors(['supplier_id' => 'Le fournisseur sélectionné n\'existe pas ou ne vous appartient pas.']);
        }

        try {
            // Crée l'achat en ajoutant l'ID de l'utilisateur créateur
            $purchase = Purchase::create(array_merge($request->validated(), [
                'created_by' => $userId, // Associe l'achat à l'utilisateur connecté
                'supplier_id' => $supplier->id, // Utilise l'ID du fournisseur vérifié
            ]));

            if (!empty($request->invoiceProducts)) { // Utilisation de !empty au lieu de ! $request->invoiceProducts == null
                $pDetails = [];

                foreach ($request->invoiceProducts as $productData) {
                    // Vérification de sécurité : Assurer que le produit dans la liste appartient bien à l'utilisateur
                    $product = Product::where('id', $productData['product_id'])
                                      ->where('user_id', $userId)
                                      ->first();
                    if (!$product) {
                        DB::rollBack(); // Annule la création de l'achat si un produit n'est pas valide
                        return back()->withErrors('Un produit dans la liste n\'existe pas ou ne vous appartient pas.');
                    }

                    $pDetails[] = [ // Ajouter à un tableau pour insertion multiple
                        'purchase_id' => $purchase->id, // Utilisez $purchase->id
                        'product_id' => $product->id,    // Utilisez $product->id pour le produit vérifié
                        'quantity' => $productData['quantity'],
                        'unitcost' => $productData['unitcost'],
                        'total' => $productData['total'],
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(), // Ajoutez updated_at pour les timestamps
                    ];
                }
                $purchase->details()->insert($pDetails); // Insertion en une seule fois
            }

            return redirect()
                ->route('purchases.index')
                ->with('success', 'L\'achat a été créé avec succès!');

        } catch (Exception $e) {
            // Gérer les erreurs inattendues, il est recommandé de les logger
            \Log::error("Erreur lors de la création de l'achat : " . $e->getMessage());
            return back()->withErrors(['error' => 'Une erreur est survenue lors de la création de l\'achat.']);
        }
    }

    /**
     * Met à jour un achat (généralement pour l'approuver).
     * S'assure que seul le propriétaire peut le modifier.
     */
    public function update(Purchase $purchase, Request $request)
    {
        // Vérifie si l'utilisateur connecté est le créateur de l'achat.
        if (!Auth::check() || $purchase->created_by !== Auth::id()) {
            abort(403, 'Accès non autorisé à approuver cet achat.');
        }

        // Récupère les détails de l'achat
        $productsInPurchase = PurchaseDetails::where('purchase_id', $purchase->id)->get();

        foreach ($productsInPurchase as $productDetail) {
            // Vérifier que le produit appartient à l'utilisateur avant de modifier la quantité
            $product = Product::where('id', $productDetail->product_id)
                              ->where('user_id', Auth::id())
                              ->first();

            if ($product) {
                // Augmente la quantité du produit en stock
                $product->update(['quantity' => DB::raw('quantity+' . $productDetail->quantity)]);
            } else {
                // Gérer le cas où un produit lié à l'achat n'appartient pas à l'utilisateur (erreur ou données incohérentes)
                \Log::warning("Produit non trouvé ou non propriétaire pour la mise à jour de stock dans l'achat ID: {$purchase->id}, produit ID: {$productDetail->product_id}");
                return redirect()->back()->withErrors('Un produit lié à cet achat n\'existe pas ou ne vous appartient pas, impossible de mettre à jour le stock.');
            }
        }

        // Met à jour le statut de l'achat et l'utilisateur qui l'a mis à jour
        $purchase->update([
            'status' => PurchaseStatus::APPROVED, // 1 = approved
            'updated_by' => Auth::id(), // Utilise Auth::id()
        ]);

        return redirect()
            ->route('purchases.index')
            ->with('success', 'L\'achat a été approuvé avec succès!');
    }

    /**
     * Supprime un achat.
     * S'assure que seul le propriétaire peut le supprimer.
     */
    public function destroy(Purchase $purchase)
    {
        // Vérifie si l'utilisateur connecté est le créateur de l'achat.
        if (!Auth::check() || $purchase->created_by !== Auth::id()) {
            abort(403, 'Accès non autorisé à supprimer cet achat.');
        }

        $purchase->delete();

        return redirect()
            ->back() // Correction: revenir à la page précédente au lieu de route('purchases.index')
            ->with('success', 'L\'achat a été supprimé avec succès!');
    }

    /**
     * Affiche le rapport quotidien des achats de l'utilisateur connecté.
     */
    public function dailyPurchaseReport()
    {
        if (!Auth::check()) {
            return redirect('/login')->withErrors('Veuillez vous connecter.');
        }

        $userId = Auth::id();

        $purchases = Purchase::with(['supplier'])
            ->where('created_by', $userId) // Filtre par l'utilisateur créateur
            ->where('date', today()->format('Y-m-d')) // 'date' est le nom de la colonne
            ->get();

        return view('purchases.daily-report', [
            'purchases' => $purchases,
        ]);
    }

    public function getPurchaseReport()
    {
        return view('purchases.report-purchase');
    }

    /**
     * Exporte le rapport d'achats filtré par l'utilisateur connecté.
     */
    public function exportPurchaseReport(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/login')->withErrors('Veuillez vous connecter.');
        }

        $userId = Auth::id();

        $rules = [
            'start_date' => 'required|string|date_format:Y-m-d',
            'end_date' => 'required|string|date_format:Y-m-d',
        ];

        $validatedData = $request->validate($rules);

        $sDate = $validatedData['start_date'];
        $eDate = $validatedData['end_date'];

        $purchases = DB::table('purchase_details')
            ->join('products', 'purchase_details.product_id', '=', 'products.id')
            ->join('purchases', 'purchase_details.purchase_id', '=', 'purchases.id')
            ->join('users', 'users.id', '=', 'purchases.created_by')
            // AJOUT DU FILTRE PAR L'UTILISATEUR CRÉATEUR
            ->where('purchases.created_by', $userId)
            ->whereBetween('purchases.date', [$sDate, $eDate]) // 'date' est le nom de la colonne
            ->where('purchases.status', PurchaseStatus::APPROVED) // 'status' est le nom de la colonne (valeur 1 pour approuvé)
            ->select(
                'purchases.purchase_no',
                'purchases.date', // Utilisez 'date' au lieu de 'purchase_date'
                'suppliers.name as supplier_name', // Joignez la table suppliers pour le nom
                'products.code',
                'products.name',
                'purchase_details.quantity',
                'purchase_details.unitcost',
                'purchase_details.total',
                'users.name as created_by'
            )
            ->join('suppliers', 'purchases.supplier_id', '=', 'suppliers.id') // Joignez la table des fournisseurs
            ->get();

        // Correction: Supprime le dd($purchases) car il arrête l'exécution
        // dd($purchases);

        $purchase_array[] = [
            'Date',
            'No Purchase',
            'Supplier', // Changé de 'Supplier' à 'Supplier Name' pour correspondre au select
            'Product Code',
            'Product',
            'Quantity',
            'Unitcost',
            'Total',
            'Created By'
        ];

        foreach ($purchases as $purchase) {
            $purchase_array[] = [
                'Date' => $purchase->date, // Utilisez 'date'
                'No Purchase' => $purchase->purchase_no,
                'Supplier' => $purchase->supplier_name, // Utilisez le nom du fournisseur joint
                'Product Code' => $purchase->code, // Utilisez products.code
                'Product' => $purchase->name,     // Utilisez products.name
                'Quantity' => $purchase->quantity,
                'Unitcost' => $purchase->unitcost,
                'Total' => $purchase->total,
                'Created By' => $purchase->created_by
            ];
        }

        $this->exportExcel($purchase_array);
    }

    public function exportExcel($products)
    {
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '4000M');

        try {
            $spreadSheet = new Spreadsheet();
            $spreadSheet->getActiveSheet()->getDefaultColumnDimension()->setWidth(20);
            $spreadSheet->getActiveSheet()->fromArray($products);
            $Excel_writer = new Xls($spreadSheet);
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="purchase-report.xls"');
            header('Cache-Control: max-age=0');
            ob_end_clean();
            $Excel_writer->save('php://output');
            exit();
        } catch (Exception $e) {
            // Il est préférable de logguer l'exception et de retourner une réponse conviviale
            \Log::error("Erreur lors de l'exportation du rapport d'achat : " . $e->getMessage());
            return back()->withErrors(['error' => 'Une erreur est survenue lors de l\'exportation du rapport.']);
        }
    }
}
