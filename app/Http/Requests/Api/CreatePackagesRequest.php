<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreatePackagesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'CLIENT';
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'packages' => 'required|array|min:1|max:100',
            'packages.*.pickup_address_id' => [
                'required',
                'integer',
                'exists:client_pickup_addresses,id',
                function ($attribute, $value, $fail) {
                    $address = \App\Models\ClientPickupAddress::find($value);
                    if ($address && $address->client_id !== auth()->id()) {
                        $fail('Cette adresse de ramassage ne vous appartient pas');
                    }
                }
            ],
            'packages.*.recipient_name' => 'required|string|max:100',
            'packages.*.recipient_phone' => [
                'required',
                'regex:/^[0-9]{8}$/',
                'not_in:00000000,11111111,22222222,33333333,44444444,55555555,66666666,77777777,88888888,99999999'
            ],
            'packages.*.recipient_phone_2' => [
                'nullable',
                'regex:/^[0-9]{8}$/',
                'not_in:00000000,11111111,22222222,33333333,44444444,55555555,66666666,77777777,88888888,99999999'
            ],
            'packages.*.recipient_gouvernorat' => 'required|string|max:50',
            'packages.*.recipient_delegation' => 'required|string|max:50',
            'packages.*.recipient_address' => 'required|string|max:255',
            'packages.*.package_content' => 'required|string|max:100',
            'packages.*.package_price' => 'required|numeric|min:0|max:999999.99',
            'packages.*.delivery_type' => 'required|in:HOME,STOP_DESK',
            'packages.*.payment_type' => 'required|in:COD,PREPAID',
            'packages.*.cod_amount' => 'required_if:packages.*.payment_type,COD|nullable|numeric|min:0|max:999999.99',
            'packages.*.is_fragile' => 'nullable|boolean',
            'packages.*.is_exchange' => 'nullable|boolean',
            'packages.*.comment' => 'nullable|string|max:500',
            'packages.*.external_reference' => 'nullable|string|max:255',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'packages.required' => 'Le tableau de colis est requis',
            'packages.array' => 'Les colis doivent être fournis sous forme de tableau',
            'packages.min' => 'Vous devez créer au moins un colis',
            'packages.max' => 'Vous ne pouvez pas créer plus de 100 colis par requête',
            
            'packages.*.pickup_address_id.required' => 'L\'adresse de ramassage est requise',
            'packages.*.pickup_address_id.exists' => 'L\'adresse de ramassage est invalide',
            
            'packages.*.recipient_name.required' => 'Le nom du destinataire est requis',
            'packages.*.recipient_name.max' => 'Le nom du destinataire ne peut pas dépasser 100 caractères',
            
            'packages.*.recipient_phone.required' => 'Le téléphone du destinataire est requis',
            'packages.*.recipient_phone.regex' => 'Le téléphone doit contenir exactement 8 chiffres',
            'packages.*.recipient_phone.not_in' => 'Numéro de téléphone invalide',
            
            'packages.*.recipient_phone_2.regex' => 'Le téléphone 2 doit contenir exactement 8 chiffres',
            
            'packages.*.recipient_gouvernorat.required' => 'Le gouvernorat est requis',
            'packages.*.recipient_delegation.required' => 'La délégation est requise',
            'packages.*.recipient_address.required' => 'L\'adresse complète est requise',
            'packages.*.recipient_address.max' => 'L\'adresse ne peut pas dépasser 255 caractères',
            
            'packages.*.package_content.required' => 'Le contenu du colis est requis',
            'packages.*.package_content.max' => 'Le contenu ne peut pas dépasser 100 caractères',
            
            'packages.*.package_price.required' => 'Le prix du colis est requis',
            'packages.*.package_price.numeric' => 'Le prix doit être un nombre',
            'packages.*.package_price.min' => 'Le prix ne peut pas être négatif',
            
            'packages.*.delivery_type.required' => 'Le type de livraison est requis',
            'packages.*.delivery_type.in' => 'Le type de livraison doit être HOME ou STOP_DESK',
            
            'packages.*.payment_type.required' => 'Le type de paiement est requis',
            'packages.*.payment_type.in' => 'Le type de paiement doit être COD ou PREPAID',
            
            'packages.*.cod_amount.required_if' => 'Le montant COD est requis pour le paiement à la livraison',
            'packages.*.cod_amount.numeric' => 'Le montant COD doit être un nombre',
            
            'packages.*.comment.max' => 'Le commentaire ne peut pas dépasser 500 caractères',
            'packages.*.external_reference.max' => 'La référence externe ne peut pas dépasser 255 caractères',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        if ($this->has('packages')) {
            $packages = $this->packages;
            
            foreach ($packages as &$package) {
                // Trim strings
                if (isset($package['recipient_name'])) {
                    $package['recipient_name'] = trim($package['recipient_name']);
                }
                if (isset($package['recipient_address'])) {
                    $package['recipient_address'] = trim($package['recipient_address']);
                }
                if (isset($package['package_content'])) {
                    $package['package_content'] = trim($package['package_content']);
                }
                
                // Sanitize comment
                if (isset($package['comment'])) {
                    $package['comment'] = strip_tags(trim($package['comment']));
                }
                
                // Convert booleans
                if (isset($package['is_fragile'])) {
                    $package['is_fragile'] = filter_var($package['is_fragile'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
                }
                if (isset($package['is_exchange'])) {
                    $package['is_exchange'] = filter_var($package['is_exchange'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
                }
            }
            
            $this->merge(['packages' => $packages]);
        }
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Erreur de validation. Veuillez vérifier les données envoyées',
                'errors' => $validator->errors()
            ], 422)
        );
    }
}
