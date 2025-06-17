<?php

namespace App\Http\Requests\Invoice;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;    // NOUVEAU : Importez la façade Auth
use Illuminate\Validation\Rule;         // NOUVEAU : Importez Rule pour des règles de validation plus complexes
use Illuminate\Support\Facades\Session; // NOUVEAU : Importez la façade Session

class StoreInvoiceRequest extends FormRequest
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
            // Vous pourriez avoir d'autres champs liés à la facture ici, tels que
            // 'items.*.product_id' => 'required|integer|exists:products,id',
            // 'items.*.quantity' => 'required|integer|min:1',
            // ...
        ];
    }

    /**
     * Personnalise les messages d'erreur de validation (facultatif).
     */
    public function messages(): array
    {
        return [
            'customer_id.required' => 'Le client est obligatoire.',
            'customer_id.integer' => 'L\'ID du client doit être un nombre entier.',
            'customer_id.exists' => 'Le client sélectionné est invalide ou n\'appartient pas à votre entreprise.',
        ];
    }
}
