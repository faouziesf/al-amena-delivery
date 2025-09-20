<?php

namespace App\Http\Requests\Deliverer;

use Illuminate\Foundation\Http\FormRequest;

class ScanPackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'DELIVERER';
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:255'
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Le code du colis est requis',
            'code.string' => 'Le code doit être une chaîne de caractères',
            'code.max' => 'Le code est trop long'
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => trim($this->code ?? '')
        ]);
    }
}