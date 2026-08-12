<?php

namespace App\Http\Requests;

use App\Models\Invoice;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

class UpdateInvoiceRequest extends FormRequest
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
            'net_amount' => ['required', 'numeric', 'gt:0'],
            'vat_amount' => ['required', 'numeric', 'gte:0'],
            'due_date' => [
                'required',
                'date',
                function (string $attribute, mixed $value, \Closure $fail) {
                    /** @var Invoice|null $invoice */
                    $invoice = $this->route('invoice');

                    if ($invoice && Carbon::parse($value)->lt($invoice->issue_date)) {
                        $fail('The due date must be on or after the issue date.');
                    }
                },
            ],
        ];
    }
}
