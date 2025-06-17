<?php

namespace App\Http\Requests\Unit;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth; // Importez la façade Auth

class StoreUnitRequest extends FormRequest
{
    /**
     * Détermine si l'utilisateur est autorisé à faire cette requête.
     */
    public function authorize(): bool
    {
        // L'autorisation est généralement gérée au niveau du contrôleur ou du middleware,
        // donc nous pouvons laisser ceci à 'true' si le contrôleur est déjà protégé.
        return Auth::check(); // S'assurer que l'utilisateur est connecté pour autoriser la requête
    }

    /**
     * Récupère les règles de validation qui s'appliquent à la requête.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        // Récupère l'ID de l'utilisateur connecté pour les règles d'unicité.
        // Cela permet à chaque utilisateur d'avoir des noms et des slugs d'unité uniques
        // par rapport à ses PROPRES unités, et non globalement.
        $userId = Auth::id();

        return [
            // Le nom doit être unique PAR UTILISATEUR
            'name' => 'required|string|max:255|unique:units,name,NULL,id,user_id,' . $userId,
            // Le slug doit être unique PAR UTILISATEUR et au format 'alpha_dash'
            'slug' => 'required|string|max:255|unique:units,slug,NULL,id,user_id,' . $userId . '|alpha_dash',
            // 'short_code' était marqué 'required' dans votre version précédente.
            // Cependant, votre migration 'create_units_table' le définit comme 'nullable()'.
            // Je l'ai rendu 'nullable' ici pour correspondre au schéma de la base de données.
            // Si vous souhaitez qu'il soit obligatoire, retirez 'nullable'.
            'short_code' => 'nullable|string|max:255',
        ];
    }

    /**
     * Personnalise les messages d'erreur de validation (facultatif).
     */
    public function messages(): array
    {
        return [
            'name.unique' => 'Ce nom d\'unité existe déjà pour cet utilisateur.',
            'slug.unique' => 'Ce slug d\'unité existe déjà pour cet utilisateur.',
            'slug.alpha_dash' => 'Le slug ne peut contenir que des lettres, des chiffres, des tirets et des underscores.',
            'short_code.required' => 'Le champ code court est obligatoire.', // Si vous le rendez obligatoire
        ];
    }
}
