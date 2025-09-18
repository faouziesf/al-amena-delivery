<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\TopupRequest;

class StoreTopupRequestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isClient();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'amount' => [
                'required',
                'numeric',
                'min:10',
                'max:10000',
                'regex:/^\d+(\.\d{1,3})?$/' // Maximum 3 décimales
            ],
            'method' => [
                'required',
                'in:BANK_TRANSFER,BANK_DEPOSIT,CASH'
            ],
            'bank_transfer_id' => [
                'required_if:method,BANK_TRANSFER,BANK_DEPOSIT',
                'nullable',
                'string',
                'max:100',
                'min:3',
                function ($attribute, $value, $fail) {
                    if ($value && !TopupRequest::isBankTransferIdUnique($value)) {
                        $fail('Cet identifiant de virement/versement a déjà été utilisé.');
                    }
                },
            ],
            'proof_document' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:5120' // 5MB
            ],
            'notes' => [
                'nullable',
                'string',
                'max:500'
            ],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'amount.required' => 'Le montant est obligatoire.',
            'amount.numeric' => 'Le montant doit être un nombre.',
            'amount.min' => 'Le montant minimum est de 10 DT.',
            'amount.max' => 'Le montant maximum est de 10 000 DT.',
            'amount.regex' => 'Le montant ne peut avoir plus de 3 décimales.',
            
            'method.required' => 'Veuillez sélectionner une méthode de paiement.',
            'method.in' => 'La méthode de paiement sélectionnée n\'est pas valide.',
            
            'bank_transfer_id.required_if' => 'L\'identifiant de virement/versement est requis pour cette méthode.',
            'bank_transfer_id.min' => 'L\'identifiant doit contenir au moins 3 caractères.',
            'bank_transfer_id.max' => 'L\'identifiant ne peut pas dépasser 100 caractères.',
            
            'proof_document.file' => 'Le justificatif doit être un fichier.',
            'proof_document.mimes' => 'Le justificatif doit être une image (JPG, PNG) ou un fichier PDF.',
            'proof_document.max' => 'Le justificatif ne doit pas dépasser 5 MB.',
            
            'notes.max' => 'Les notes ne peuvent pas dépasser 500 caractères.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'amount' => 'montant',
            'method' => 'méthode de paiement',
            'bank_transfer_id' => 'identifiant de virement/versement',
            'proof_document' => 'justificatif',
            'notes' => 'notes',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // Nettoyer l'identifiant bancaire
        if ($this->bank_transfer_id) {
            $this->merge([
                'bank_transfer_id' => trim(strtoupper($this->bank_transfer_id))
            ]);
        }

        // Formater le montant pour s'assurer qu'il respecte notre format décimal
        if ($this->amount) {
            $this->merge([
                'amount' => number_format((float)$this->amount, 3, '.', '')
            ]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Vérification supplémentaire: l'utilisateur ne peut pas avoir plus de 5 demandes en attente
            $pendingCount = auth()->user()->topupRequests()->pending()->count();
            if ($pendingCount >= 5) {
                $validator->errors()->add('general', 'Vous avez déjà 5 demandes en attente. Veuillez attendre qu\'elles soient traitées avant d\'en créer une nouvelle.');
            }

            // Pour les paiements en espèces, vérifier qu'il n'y a pas déjà une demande en cours
            if ($this->method === 'CASH') {
                $existingCashRequest = auth()->user()->topupRequests()
                    ->where('method', 'CASH')
                    ->pending()
                    ->exists();
                
                if ($existingCashRequest) {
                    $validator->errors()->add('method', 'Vous avez déjà une demande de paiement en espèces en cours. Attendez qu\'elle soit traitée avant d\'en créer une nouvelle.');
                }
            }

            // Vérification du montant cumulé en attente (limite de 50 000 DT)
            $pendingAmount = auth()->user()->topupRequests()->pending()->sum('amount');
            if (($pendingAmount + (float)$this->amount) > 50000) {
                $validator->errors()->add('amount', 'Le montant total de vos demandes en attente ne peut pas dépasser 50 000 DT.');
            }
        });
    }

    /**
     * Get the validated data from the request with custom formatting.
     */
    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);
        
        // S'assurer que le montant est bien formaté en décimal
        if (isset($validated['amount'])) {
            $validated['amount'] = number_format((float)$validated['amount'], 3, '.', '');
        }
        
        return is_null($key) ? $validated : data_get($validated, $key, $default);
    }
}