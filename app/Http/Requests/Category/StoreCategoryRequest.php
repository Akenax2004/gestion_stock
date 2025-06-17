<?php

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;    // NOUVEAU : Importez la façade Auth
use Illuminate\Validation\Rule;         // NOUVEAU : Importez Rule pour des règles uniques plus complexes
use Illuminate\Support\Facades\Session; // NOUVEAU : Importez la façade Session

class StoreCategoryRequest extends FormRequest
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
                // La règle unique est maintenant composée : 'name' doit être unique pour cet 'user_id' ET ce 'company_id'
                Rule::unique('categories')->where(function ($query) use ($userId, $activeCompanyId) {
                    return $query->where('user_id', $userId)
                                 ->where('company_id', $activeCompanyId);
                }),
            ],
            'slug' => [
                'required',
                'string',
                'max:255',
                'alpha_dash', // Permet lettres, chiffres, tirets et underscores
                // La règle unique est également composée pour 'slug'
                Rule::unique('categories')->where(function ($query) use ($userId, $activeCompanyId) {
                    return $query->where('user_id', $userId)
                                 ->where('company_id', $activeCompanyId);
                }),
            ],
            'short_code' => [ // Assurez-vous que ce champ est traité si vous l'utilisez
                'nullable',
                'string',
                'max:50',
                Rule::unique('categories')->where(function ($query) use ($userId, $activeCompanyId) {
                    return $query->where('user_id', $userId)
                                 ->where('company_id', $activeCompanyId);
                }),
            ],
        ];
    }

    /**
     * Prépare les données pour la validation.
     * Permet de générer le slug automatiquement avant la validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => \Illuminate\Support\Str::slug($this->name),
        ]);
    }

    /**
     * Personnalise les messages d'erreur de validation (facultatif).
     */
    public function messages(): array
    {
        return [
            'name.unique' => 'Ce nom de catégorie existe déjà pour votre entreprise.',
            'slug.unique' => 'Ce slug de catégorie existe déjà pour votre entreprise.',
            'slug.alpha_dash' => 'Le slug ne doit contenir que des lettres, des chiffres, des tirets et des underscores.',
            'short_code.unique' => 'Ce code court de catégorie existe déjà pour votre entreprise.',
        ];
    }
}
