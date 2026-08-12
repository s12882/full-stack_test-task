<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class InvoiceController extends Controller
{
    public function __construct(private readonly InvoiceService $invoices)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $invoices = $this->invoices->list($request->only('status'));

        return InvoiceResource::collection($invoices);
    }

    public function show(Invoice $invoice): InvoiceResource
    {
        return InvoiceResource::make($invoice);
    }

    public function store(StoreInvoiceRequest $request): JsonResponse
    {
        $invoice = $this->invoices->create($request->validated());

        return InvoiceResource::make($invoice)
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice): InvoiceResource
    {
        $invoice = $this->invoices->update($invoice, $request->validated());

        return InvoiceResource::make($invoice);
    }
}
