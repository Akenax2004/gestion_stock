<?php

namespace App\Http\Requests\Unit;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;    // NOUVEAU : Importez la façade Auth
use Illuminate\Validation\Rule;         // NOUVEAU : Importez Rule pour des règles uniques plus complexes
use Illuminate\Support\Facades\Session; // NOUVEAU : Importez la façade Session

class StoreUnitRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $userId = Auth::id();
        $activeCompanyId = Session::get('active_company_id');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                // Le nom doit être unique pour cet 'user_id' ET ce 'company_id'
                Rule::unique('units')->where(function ($query) use ($userId, $activeCompanyId) {
                    return $query->where('user_id', $userId)
                                 ->where('company_id', $activeCompanyId);
                }),
            ],
            'slug' => [
                'required',
                'string',
                'max:255',
                'alpha_dash',
                // Le slug doit être unique pour cet 'user_id' ET ce 'company_id'
                Rule::unique('units')->where(function ($query) use ($userId, $activeCompanyId) {
                    return $query->where('user_id', $userId)
                                 ->where('company_id', $activeCompanyId);
                }),
            ],
            'short_code' => [
                'nullable', // Gardé nullable comme dans votre migration
                'string',
                'max:255',
                // Le short_code doit être unique pour cet 'user_id' ET ce 'company_id'
                Rule::unique('units')->where(function ($query) use ($userId, $activeCompanyId) {
                    return $query->where('user_id', $userId)
                                 ->where('company_id', $activeCompanyId);
                }),
            ],
        ];
    }

    /**
     * Personnalise les messages d'erreur de validation (facultatif).
     */
    public function messages(): array
    {
        return [
            'name.unique' => 'Ce nom d\'unité existe déjà pour votre entreprise.',
            'slug.unique' => 'Ce slug d\'unité existe déjà pour votre entreprise.',
            'slug.alpha_dash' => 'Le slug ne doit contenir que des lettres, des chiffres, des tirets et des underscores.',
            'short_code.unique' => 'Ce code court d\'unité existe déjà pour votre entreprise.',
        ];
    }
}
