<?php

namespace App\Http\Requests\Order;

use App\Enums\OrderStatus;
use Gloudemans\Shoppingcart\Facades\Cart;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;         // NOUVEAU : Importez Rule pour des règles de validation plus complexes
use Illuminate\Support\Facades\Session; // NOUVEAU : Importez la façade Session

class OrderStoreRequest extends FormRequest
{
    /**
     * Détermine si l'utilisateur est autorisé à effectuer cette requête.
     * L'autorisation doit être basée sur l'authentification et la sélection d'une entreprise active.
     */
    public function authorize(): bool
    {
        // L'utilisateur doit être connecté ET avoir une entreprise active sélectionnée.
        return Auth::check() && Session::has('active_company_id');
    }

    /**
     * Obtient les règles de validation qui s'appliquent à la requête.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = Auth::id();
        $activeCompanyId = Session::get('active_company_id');

        return [
            'customer_id' => [
                'required',
                'integer',
                // NOUVEAU : Vérifie que le customer_id existe DANS LA TABLE customers
                // ET qu'il appartient à l'utilisateur connecté ET à l'entreprise active.
                Rule::exists('customers', 'id')->where(function ($query) use ($userId, $activeCompanyId) {
                    return $query->where('user_id', $userId)
                                 ->where('company_id', $activeCompanyId);
                }),
            ],
            'payment_type' => 'required|string|max:255',
            'pay' => 'required|numeric|min:0',
            'order_date' => 'required|date_format:Y-m-d',
            'order_status' => 'required|integer',
            'total_products' => 'required|integer|min:0',
            'sub_total' => 'required|numeric|min:0',
            'vat' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'invoice_no' => [
                'required',
                'string',
                'max:255',
                // OPTIONNEL : Si invoice_no doit être unique par entreprise, ajoutez cette règle
                // Notez que IdGenerator peut déjà gérer l'unicité globale, mais pour l'unicité par entreprise
                // vous devrez peut-être ajuster IdGenerator ou faire une vérification manuelle.
                // Rule::unique('orders', 'invoice_no')->where(function ($query) use ($activeCompanyId) {
                //     return $query->where('company_id', $activeCompanyId);
                // }),
            ],
            'due' => 'required|numeric',
        ];
    }

    /**
     * Prépare les données pour la validation.
     * Définit les champs calculés ou générés avant l'application des règles.
     */
    public function prepareForValidation(): void
    {
        // Si le panier est vide, ajoutez une erreur pour empêcher la création de la commande.
        // Cette vérification est cruciale.
        if (Cart::instance('order')->count() == 0) {
            $this->validator->errors()->add('cart_empty', 'Le panier de commande est vide. Veuillez ajouter des produits.');
            // Il est important de ne pas continuer la fusion si le panier est vide et que la validation échouera.
            return;
        }

        $totalCartAmount = Cart::instance('order')->total();
        $paidAmount = $this->pay;

        // Générer le numéro de facture ici si ce n'est pas déjà géré ailleurs pour l'unicité par entreprise
        $generatedInvoiceNo = IdGenerator::generate([
            'table' => 'orders',
            'field' => 'invoice_no',
            'length' => 10,
            'prefix' => 'INV-',
            // Si vous avez besoin d'une unicité par entreprise via IdGenerator,
            // vous devriez implémenter une logique personnalisée ou s'assurer que
            // votre implémentation de IdGenerator supporte cela, ou ajouter le company_id
            // comme critère de recherche pour l'unicité.
            // Actuellement, IdGenerator ne prend pas de 'where' clause directement pour la génération.
            // Donc, la règle 'unique' dans 'rules()' est plus appropriée pour forcer l'unicité par entreprise si nécessaire.
        ]);

        $this->merge([
            'order_date' => Carbon::now()->format('Y-m-d'),
            'order_status' => OrderStatus::PENDING->value,
            'total_products' => Cart::instance('order')->count(),
            'sub_total' => Cart::instance('order')->subtotal(),
            'vat' => Cart::instance('order')->tax(),
            'total' => $totalCartAmount,
            'invoice_no' => $generatedInvoiceNo,
            'due' => ($totalCartAmount - $paidAmount),
        ]);
    }

    /**
     * Personnalise les messages d'erreur de validation.
     */
    public function messages(): array
    {
        return [
            'customer_id.required' => 'Le client est obligatoire.',
            'customer_id.integer' => 'L\'ID du client doit être un nombre entier.',
            'customer_id.exists' => 'Le client sélectionné est invalide ou n\'appartient pas à votre entreprise.',
            'payment_type.required' => 'Le type de paiement est obligatoire.',
            'pay.required' => 'Le montant payé est obligatoire.',
            'pay.numeric' => 'Le montant payé doit être un nombre.',
            'pay.min' => 'Le montant payé ne peut pas être négatif.',
            'total_products.required' => 'Le nombre total de produits est obligatoire.',
            'sub_total.required' => 'Le sous-total est obligatoire.',
            'vat.required' => 'La TVA est obligatoire.',
            'total.required' => 'Le total est obligatoire.',
            'invoice_no.required' => 'Le numéro de facture est obligatoire.',
            // 'invoice_no.unique' => 'Ce numéro de facture existe déjà pour votre entreprise.', // Décommenter si la règle unique est appliquée
            'due.required' => 'Le montant dû est obligatoire.',
            'order_date.required' => 'La date de commande est obligatoire.',
            'order_status.required' => 'Le statut de la commande est obligatoire.',
        ];
    }
}
