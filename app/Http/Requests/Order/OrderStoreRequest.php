<?php

namespace App\Http\Requests\Order;

use App\Enums\OrderStatus;
use Gloudemans\Shoppingcart\Facades\Cart;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth; // Assurez-vous que Auth est importé ici

class OrderStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        // L'autorisation est gérée ici. S'assurer que l'utilisateur est connecté pour autoriser la requête.
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'customer_id' => 'required|integer|exists:customers,id', // Le client doit exister
            'payment_type' => 'required|string|max:255',
            'pay' => 'required|numeric|min:0', // Le montant payé doit être un nombre non négatif
            // Les autres champs sont fusionnés dans prepareForValidation(), mais leurs règles peuvent être ici si besoin
            'order_date' => 'required|date_format:Y-m-d', // Ajouté comme 'required' car généré
            'order_status' => 'required|integer', // ou 'in:0,1', 'in:App\Enums\OrderStatus::values()'
            'total_products' => 'required|integer|min:0',
            'sub_total' => 'required|numeric|min:0',
            'vat' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'invoice_no' => 'required|string|max:255', // Doit être requis car généré
            'due' => 'required|numeric',
        ];
    }

    public function prepareForValidation(): void
    {
        // Vérifier si le panier est vide avant de tenter d'accéder à son contenu
        if (Cart::instance('order')->count() == 0) {
            // Si le panier est vide, il faut ajouter une erreur de validation.
            // Cette méthode est appelée avant la validation, donc nous utilisons addValidationErrors.
            // Note: C'est un pattern pour les FormRequests. Le contrôleur peut aussi gérer cette vérification.
            $this->validator->errors()->add('cart_empty', 'Le panier de commande est vide. Veuillez ajouter des produits.');
            // Retourner tôt ou lancer une exception pourrait être une option, mais Laravel continue la validation
            // et l'erreur sera capturée par le système de validation.
        }

        $totalCartAmount = Cart::instance('order')->total();
        $paidAmount = $this->pay; // Utilisez la valeur soumise par l'utilisateur pour 'pay'

        $this->merge([
            'order_date' => Carbon::now()->format('Y-m-d'),
            // 'order_status' provient de l'énumération, assurez-vous de le gérer côté front ou dans le contrôleur.
            // Si le statut est toujours 'PENDING' à la création, c'est bon.
            'order_status' => OrderStatus::PENDING->value,
            'total_products' => Cart::instance('order')->count(),
            'sub_total' => Cart::instance('order')->subtotal(),
            'vat' => Cart::instance('order')->tax(),
            'total' => $totalCartAmount,
            'invoice_no' => IdGenerator::generate([
                'table' => 'orders',
                'field' => 'invoice_no',
                'length' => 10,
                'prefix' => 'INV-',
            ]),
            'due' => ($totalCartAmount - $paidAmount), // Calcule le 'due' basé sur le total du panier et le montant payé
        ]);
    }

    /**
     * Personnalise les messages d'erreur de validation.
     */
    public function messages(): array
    {
        return [
            'customer_id.required' => 'Le client est obligatoire.',
            'customer_id.exists' => 'Le client sélectionné est invalide.',
            'payment_type.required' => 'Le type de paiement est obligatoire.',
            'pay.required' => 'Le montant payé est obligatoire.',
            'pay.numeric' => 'Le montant payé doit être un nombre.',
            'pay.min' => 'Le montant payé ne peut pas être négatif.',
            'total_amount.required' => 'Le montant total est obligatoire.',
            'total_amount.numeric' => 'Le montant total doit être un nombre.',
            'total_amount.min' => 'Le montant total ne peut pas être négatif.',
            // Messages pour les champs générés (si vous les rendez 'required' dans rules())
            'order_date.required' => 'La date de commande est obligatoire.',
            'order_status.required' => 'Le statut de la commande est obligatoire.',
            'total_products.required' => 'Le nombre total de produits est obligatoire.',
            'sub_total.required' => 'Le sous-total est obligatoire.',
            'vat.required' => 'La TVA est obligatoire.',
            'total.required' => 'Le total est obligatoire.',
            'invoice_no.required' => 'Le numéro de facture est obligatoire.',
            'due.required' => 'Le montant dû est obligatoire.',
        ];
    }
}
