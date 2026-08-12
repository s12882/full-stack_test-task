<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules.
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'number' => ['required', 'string', 'max:255', 'unique:invoices,number'],
            'supplier_name' => ['required', 'string', 'max:255'],
            'supplier_tax_id' => ['required', 'string', 'max:255'],
            'net_amount' => ['required', 'numeric', 'gt:0'],
            'vat_amount' => ['required', 'numeric', 'gte:0'],
            'gross_amount' => ['required', 'numeric'],
            'currency' => ['required', 'string', 'size:3'],
            'issue_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:issue_date'],
        ];
    }

    /**
     * Cross-field validation for gross_amount
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $net = $this->input('net_amount');
            $vat = $this->input('vat_amount');
            $gross = $this->input('gross_amount');

            if (! is_numeric($net) || ! is_numeric($vat) || ! is_numeric($gross)) {
                return;
            }

            if (bccomp((string) $gross, bcadd((string) $net, (string) $vat, 2), 2) !== 0) {
                $validator->errors()->add(
                    'gross_amount',
                    'The gross amount must equal net amount + vat amount.'
                );
            }
        });
    }
}
