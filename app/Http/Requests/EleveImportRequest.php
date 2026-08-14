<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EleveImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasPermission('eleves.create');
    }

    public function rules(): array
    {
        return [
            'classe_id' => 'required|exists:classes,id',
            'fichier' => 'required|file|mimes:csv,txt,xlsx|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'classe_id.required' => 'Veuillez sélectionner une classe.',
            'classe_id.exists' => 'La classe sélectionnée est invalide.',
            'fichier.required' => 'Veuillez sélectionner un fichier Excel ou CSV.',
            'fichier.mimes' => 'Le fichier doit être au format .xlsx ou .csv.',
            'fichier.max' => 'Le fichier ne doit pas dépasser 5 Mo.',
        ];
    }
}
