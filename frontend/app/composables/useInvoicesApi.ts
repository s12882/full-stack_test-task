import type {
  CreateInvoicePayload,
  InvoiceEnvelope,
  PaginatedInvoices,
  UpdateInvoicePayload,
} from '~/types/invoice'

export function useInvoicesApi() {
  const config = useRuntimeConfig()

  const client = $fetch.create({
    baseURL: config.public.apiBase,
  })

  return {
    listInvoices: (params?: { status?: string; page?: number }) =>
      client<PaginatedInvoices>('/invoices', { params }),

    getInvoice: (id: string) => client<InvoiceEnvelope>(`/invoices/${id}`),

    createInvoice: (payload: CreateInvoicePayload) =>
      client<InvoiceEnvelope>('/invoices', {
        method: 'POST',
        body: payload,
      }),

    updateInvoice: (id: string, payload: UpdateInvoicePayload) =>
      client<InvoiceEnvelope>(`/invoices/${id}`, {
        method: 'PUT',
        body: payload,
      }),
  }
}
