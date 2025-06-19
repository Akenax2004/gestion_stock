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
use Illuminate\Support\Facades\Session; // NOUVEAU : Importez la façade Session
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use Illuminate\Support\Facades\Log; // Assurez-vous que Log est importé

class PurchaseController extends Controller
{
    /**
     * Affiche une liste de tous les achats de l'utilisateur connecté.
     */
    public function index()
    {
        // Le middleware 'auth' garantit que l'utilisateur est connecté.
        // Le middleware 'company.selected' garantit qu'une entreprise active est en session.

        $userId = Auth::id();
        $activeCompanyId = Session::get('active_company_id');

        // Récupère uniquement les achats créés par l'utilisateur connecté et pour l'entreprise active
        $purchases = Purchase::where('created_by', $userId)
                             ->where('company_id', $activeCompanyId) // AJOUT : Filtrage par entreprise
                             ->latest()->get();

        return view('purchases.index', [
            'purchases' => $purchases,
        ]);
    }

    /**
     * Affiche une liste des achats approuvés par l'utilisateur connecté.
     */
    public function approvedPurchases()
    {
        // Le middleware 'auth' garantit que l'utilisateur est connecté.
        // Le middleware 'company.selected' garantit qu'une entreprise active est en session.

        $userId = Auth::id();
        $activeCompanyId = Session::get('active_company_id');

        // Récupère les achats approuvés par l'utilisateur connecté et pour l'entreprise active
        $purchases = Purchase::with(['supplier'])
            ->where('created_by', $userId) // Filtre par l'utilisateur créateur
            ->where('company_id', $activeCompanyId) // AJOUT : Filtrage par entreprise
            ->where('status', PurchaseStatus::APPROVED)
            ->get();

        return view('purchases.approved-purchases', [
            'purchases' => $purchases,
        ]);
    }

    /**
     * Affiche les détails d'un achat spécifique.
     * S'assure que seul le propriétaire et l'entreprise active peuvent le voir.
     */
    public function show(Purchase $purchase)
    {
        // Vérifie si l'utilisateur connecté est le créateur de l'achat ET s'il appartient à l'entreprise active.
        if (!Auth::check() || $purchase->created_by !== Auth::id() || $purchase->company_id !== Session::get('active_company_id')) {
            abort(403, 'Accès non autorisé à cet achat.');
        }

        // Charge les relations nécessaires
        $purchase->loadMissing(['supplier', 'details', 'createdBy', 'updatedBy']);

        // Récupère les détails des produits pour cet achat (qui sont déjà liés à l'achat parent filtré)
        $products = PurchaseDetails::where('purchase_id', $purchase->id)->get();

        return view('purchases.details-purchase', [
            'purchase' => $purchase,
            'products' => $products
        ]);
    }

    /**
     * Affiche le formulaire de modification d'un achat.
     * S'assure que seul le propriétaire et l'entreprise active peuvent le modifier.
     */
    public function edit(Purchase $purchase)
    {
        // Vérifie si l'utilisateur connecté est le créateur de l'achat ET s'il appartient à l'entreprise active.
        if (!Auth::check() || $purchase->created_by !== Auth::id() || $purchase->company_id !== Session::get('active_company_id')) {
            abort(403, 'Accès non autorisé à modifier cet achat.');
        }

        // Charge les relations nécessaires
        $purchase->loadMissing(['supplier', 'details']);

        return view('purchases.edit', [
            'purchase' => $purchase,
        ]);
    }

    /**
     * Affiche le formulaire de création d'un nouvel achat.
     * Les catégories et fournisseurs listés sont également filtrés par l'utilisateur et l'entreprise active.
     */
    public function create()
    {
        // Le middleware 'auth' garantit que l'utilisateur est connecté.
        // Le middleware 'company.selected' garantit qu'une entreprise active est en session.

        $userId = Auth::id();
        $activeCompanyId = Session::get('active_company_id');

        return view('purchases.create', [
            // Filtre les catégories et fournisseurs pour n'afficher que ceux de l'utilisateur connecté
            // ET de l'entreprise active.
            'categories' => Category::where('user_id', $userId)
                                    ->where('company_id', $activeCompanyId) // AJOUT : Filtrage par entreprise
                                    ->select(['id', 'name'])->get(),
            'suppliers' => Supplier::where('user_id', $userId)
                                   ->where('company_id', $activeCompanyId) // AJOUT : Filtrage par entreprise
                                   ->select(['id', 'name'])->get(),
            'products' => Product::where('user_id', $userId) // Assurez-vous que les produits sont filtrés ici aussi
                                  ->where('company_id', $activeCompanyId)
                                  ->select(['id', 'name', 'code', 'buying_price', 'selling_price', 'quantity'])
                                  ->get(),
        ]);
    }

    /**
     * Stocke un nouvel achat dans la base de données, l'associant à l'utilisateur connecté et à l'entreprise active.
     */
    public function store(StorePurchaseRequest $request)
    {
        Log::info('Début de la méthode store dans PurchaseController.');

        $userId = Auth::id();
        $activeCompanyId = Session::get('active_company_id');

        Log::info("Utilisateur ID: {$userId} - Entreprise active ID: {$activeCompanyId}.");
        Log::info('Données de la requête validées: ' . json_encode($request->validated()));
        Log::info('InvoiceProducts reçus de la requête: ' . json_encode($request->input('invoiceProducts')));


        // Vérification de sécurité : Assurer que le fournisseur soumis appartient bien à l'utilisateur
        // ET à l'entreprise active.
        $supplier = Supplier::where('id', $request->supplier_id)
                            ->where('user_id', $userId)
                            ->where('company_id', $activeCompanyId)
                            ->first();
        if (!$supplier) {
            Log::warning("Fournisseur non valide ou non propriétaire. ID fournisseur: {$request->supplier_id}, userId: {$userId}, companyId: {$activeCompanyId}.");
            return back()->withErrors(['supplier_id' => 'Le fournisseur sélectionné n\'existe pas ou ne vous appartient pas ou n\'appartient pas à l\'entreprise active.']);
        }
        Log::info("Fournisseur vérifié (ID: {$supplier->id}).");


        DB::beginTransaction(); // Démarre la transaction
        Log::info('Transaction de base de données démarrée.');

        try {
            // Crée l'achat en ajoutant l'ID de l'utilisateur créateur et l'ID de l'entreprise active.
            $purchase = Purchase::create(array_merge($request->validated(), [
                'created_by' => $userId,
                'company_id' => $activeCompanyId, // AJOUT : Associe l'achat à l'entreprise active
                'supplier_id' => $supplier->id,
            ]));
            Log::info("Achat principal créé avec l'ID: {$purchase->id}.");


            if (!empty($request->invoiceProducts)) {
                $pDetails = [];

                foreach ($request->invoiceProducts as $productData) {
                    // Nettoyage et conversion de unitcost et total
                    $productData['unitcost'] = str_replace(',', '.', $productData['unitcost']); // Remplace la virgule par un point
                    $productData['unitcost'] = floatval($productData['unitcost']); // Convertit en float

                    Log::info("Traitement du produit ID: {$productData['product_id']} pour l'achat. Qty: {$productData['quantity']}, UnitCost: {$productData['unitcost']}, Total: {$productData['total']}.");

                    // Vérification de sécurité : Assurer que le produit dans la liste appartient bien à l'utilisateur
                    // ET à l'entreprise active.
                    $product = Product::where('id', $productData['product_id'])
                                      ->where('user_id', $userId)
                                      ->where('company_id', $activeCompanyId)
                                      ->first();
                    if (!$product) {
                        DB::rollBack(); // Annule la création de l'achat si un produit n'est pas valide
                        Log::error("Produit non trouvé ou non propriétaire pour le produit ID: {$productData['product_id']}, userId: {$userId}, companyId: {$activeCompanyId}.");
                        return back()->withErrors('Un produit dans la liste n\'existe pas ou ne vous appartient pas / n\'appartient pas à l\'entreprise active.');
                    }
                    Log::info("Produit vérifié (ID: {$product->id}).");

                    $pDetails[] = [
                        'purchase_id' => $purchase->id,
                        'product_id' => $product->id,
                        'quantity' => $productData['quantity'],
                        'unitcost' => $productData['unitcost'],
                        'total' => $productData['total'],
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ];
                }
                $purchase->details()->insert($pDetails);
                Log::info('Détails des achats insérés en base de données.');
            } else {
                Log::warning('Aucun produit dans invoiceProducts. Les détails de l\'achat ne seront pas insérés.');
            }

            DB::commit(); // Confirme la transaction
            Log::info('Transaction de base de données confirmée avec succès.');

            return redirect()
                ->route('purchases.index')
                ->with('success', 'L\'achat a été créé avec succès!');

        } catch (Exception $e) {
            DB::rollBack(); // Annule la transaction en cas d'erreur
            Log::error("Erreur critique lors de la création de l'achat : " . $e->getMessage() . " - Trace: " . $e->getTraceAsString());
            return back()->withErrors(['error' => 'Une erreur est survenue lors de la création de l\'achat. Veuillez réessayer. (Détails: ' . $e->getMessage() . ')']);
        }
    }

    /**
     * Met à jour un achat (généralement pour l'approuver).
     * S'assure que seul le propriétaire et l'entreprise active peuvent le modifier.
     */
    public function update(Purchase $purchase, Request $request)
    {
        // Vérifie si l'utilisateur connecté est le créateur de l'achat ET s'il appartient à l'entreprise active.
        if (!Auth::check() || $purchase->created_by !== Auth::id() || $purchase->company_id !== Session::get('active_company_id')) {
            abort(403, 'Accès non autorisé à approuver cet achat.');
        }

        DB::beginTransaction();
        try {
            $userId = Auth::id();
            $activeCompanyId = Session::get('active_company_id');

            $productsInPurchase = PurchaseDetails::where('purchase_id', $purchase->id)->get();

            foreach ($productsInPurchase as $productDetail) {
                // Vérifier que le produit appartient à l'utilisateur ET à l'entreprise active avant de modifier la quantité
                $product = Product::where('id', $productDetail->product_id)
                                  ->where('user_id', $userId)
                                  ->where('company_id', $activeCompanyId) // AJOUT : Filtrage par entreprise
                                  ->first();

                if ($product) {
                    // Augmente la quantité du produit en stock
                    $product->update(['quantity' => DB::raw('quantity+' . $productDetail->quantity)]);
                } else {
                    DB::rollBack();
                    Log::warning("Produit non trouvé ou non propriétaire pour la mise à jour de stock dans l'achat ID: {$purchase->id}, produit ID: {$productDetail->product_id}, userId: {$userId}, companyId: {$activeCompanyId}.");
                    return redirect()->back()->withErrors('Un produit lié à cet achat n\'existe pas ou ne vous appartient pas ou n\'appartient pas à l\'entreprise active, impossible de mettre à jour le stock.');
                }
            }

            // Met à jour le statut de l'achat et l'utilisateur qui l'a mis à jour
            $purchase->update([
                'status' => PurchaseStatus::APPROVED, // 1 = approved
                'updated_by' => $userId, // Utilise Auth::id()
            ]);

            DB::commit();

            return redirect()
                ->route('purchases.index')
                ->with('success', 'L\'achat a été approuvé avec succès!');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Erreur lors de l'approbation de l'achat : " . $e->getMessage() . " - Trace: " . $e->getTraceAsString());
            return redirect()->back()->withErrors('Une erreur est survenue lors de l\'approbation de l\'achat. Veuillez réessayer.');
        }
    }

    /**
     * Supprime un achat.
     * S'assure que seul le propriétaire et l'entreprise active peuvent le supprimer.
     */
    public function destroy(Purchase $purchase)
    {
        // Vérifie si l'utilisateur connecté est le créateur de l'achat ET s'il appartient à l'entreprise active.
        if (!Auth::check() || $purchase->created_by !== Auth::id() || $purchase->company_id !== Session::get('active_company_id')) {
            abort(403, 'Accès non autorisé à supprimer cet achat.');
        }

        /**
         * Supprime la photo si elle existe.
         */
        if($purchase->photo){ // Assurez-vous que Purchase a une colonne 'photo' si vous utilisez cette logique
            // Storage::disk('public')->delete('purchases/' . $purchase->photo);
            // Log::warning("La suppression de photo n'est pas gérée ici car le modèle Purchase n'a pas de colonne 'photo' dans le fillable. Si Purchase a une photo, la logique devrait être dans un trait ou un observer.");
        }

        $purchase->delete();

        return redirect()
            ->back()
            ->with('success', 'L\'achat a été supprimé avec succès!');
    }

    /**
     * Affiche le rapport quotidien des achats de l'utilisateur connecté et de l'entreprise active.
     */
    public function dailyPurchaseReport()
    {
        // Le middleware 'auth' garantit que l'utilisateur est connecté.
        // Le middleware 'company.selected' garantit qu'une entreprise active est en session.

        $userId = Auth::id();
        $activeCompanyId = Session::get('active_company_id');

        $purchases = Purchase::with(['supplier'])
            ->where('created_by', $userId) // Filtre par l'utilisateur créateur
            ->where('company_id', $activeCompanyId) // AJOUT : Filtrage par entreprise
            ->where('date', today()->format('Y-m-d'))
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
     * Exporte le rapport d'achats filtré par l'utilisateur connecté et l'entreprise active.
     */
    public function exportPurchaseReport(Request $request)
    {
        // Le middleware 'auth' garantit que l'utilisateur est connecté.
        // Le middleware 'company.selected' garantit qu'une entreprise active est en session.

        $userId = Auth::id();
        $activeCompanyId = Session::get('active_company_id');

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
            ->join('suppliers', 'purchases.supplier_id', '=', 'suppliers.id') // Joignez la table des fournisseurs
            // AJOUT DU FILTRE PAR L'UTILISATEUR CRÉATEUR ET L'ENTREPRISE ACTIVE
            ->where('purchases.created_by', $userId)
            ->where('purchases.company_id', $activeCompanyId) // AJOUT : Filtrage par entreprise
            ->whereBetween('purchases.date', [$sDate, $eDate])
            ->where('purchases.status', PurchaseStatus::APPROVED)
            ->select(
                'purchases.purchase_no',
                'purchases.date',
                'suppliers.name as supplier_name',
                'products.code',
                'products.name',
                'purchase_details.quantity',
                'purchase_details.unitcost',
                'purchase_details.total',
                'users.name as created_by'
            )
            ->get();

        $purchase_array[] = [
            'Date',
            'No Purchase',
            'Supplier',
            'Product Code',
            'Product',
            'Quantity',
            'Unitcost',
            'Total',
            'Created By'
        ];

        foreach ($purchases as $purchase) {
            $purchase_array[] = [
                'Date' => $purchase->date,
                'No Purchase' => $purchase->purchase_no,
                'Supplier' => $purchase->supplier_name,
                'Product Code' => $purchase->code,
                'Product' => $purchase->name,
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
            Log::error("Erreur lors de l'exportation du rapport d'achat : " . $e->getMessage());
            return back()->withErrors(['error' => 'Une erreur est survenue lors de l\'exportation du rapport.']);
        }
    }
}
