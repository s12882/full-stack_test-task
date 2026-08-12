<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Exceptions\InvoiceNotEditableException;
use App\Models\Invoice;
use Illuminate\Pagination\LengthAwarePaginator;

class InvoiceService
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function list(array $filters = []): LengthAwarePaginator
    {
        return Invoice::query()
            ->when(
                $filters['status'] ?? null,
                fn ($query, $status) => $query->where('status', $status)
            )
            ->orderByDesc('created_at')
            ->paginate(15);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Invoice
    {
        return Invoice::create([...$data, 'status' => InvoiceStatus::Pending]);
    }

    /**
     * Update an invoice
     * @param  array<string, mixed>  $data
     * @throws InvoiceNotEditableException
     */
    public function update(Invoice $invoice, array $data): Invoice
    {
        if ($invoice->status !== InvoiceStatus::Pending) {
            throw new InvoiceNotEditableException();
        }

        $invoice->update([
            'net_amount' => $data['net_amount'],
            'vat_amount' => $data['vat_amount'],
            'gross_amount' => bcadd((string) $data['net_amount'], (string) $data['vat_amount'], 2),
            'due_date' => $data['due_date'],
        ]);

        return $invoice;
    }
}
