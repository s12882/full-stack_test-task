import type {
  CreateInvoicePayload,
  InvoiceEnvelope,
  PaginatedInvoices,
  UpdateInvoicePayload,
} from '~/types/invoice'

export function useInvoicesApi() {
  const config = useRuntimeConfig()
  const baseURL = import.meta.server ? config.apiBaseInternal : config.public.apiBase
  const client = $fetch.create({ baseURL })

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
