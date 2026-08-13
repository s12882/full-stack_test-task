<script setup lang="ts">
const route = useRoute()
const { getInvoice } = useInvoicesApi()

const id = computed(() => route.params.id as string)

const { data, status, error, refresh } = await useAsyncData(
  () => `invoice-${id.value}`,
  () => getInvoice(id.value),
  { watch: [id] }
)

const invoice = computed(() => data.value?.data)
const isNotFound = computed(
  () => (error.value as { statusCode?: number } | null)?.statusCode === 404
)
</script>

<template>
  <div class="mx-auto max-w-3xl px-4 py-8">
    <NuxtLink to="/invoices" class="text-sm text-gray-500 hover:underline">
      <- Back to invoices
    </NuxtLink>
    <div v-if="status === 'pending' && !invoice" class="mt-6 text-sm text-gray-500">
      Loading invoice…
    </div>
    <div v-else-if="isNotFound" class="mt-6 rounded-md bg-gray-100 p-4 text-sm text-gray-700">
      Invoice not found
    </div>
    <div v-else-if="error && !invoice" class="mt-6 rounded-md bg-red-50 p-4 text-sm text-red-700">
      <p>Failed to load invoice. {{ error.message }}</p>
      <button class="mt-2 font-medium underline" @click="refresh()">
        Retry
      </button>
    </div>

    <div v-else-if="invoice" class="mt-6">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-gray-900">{{ invoice.number }}</h1>
        <StatusBadge :status="invoice.status" />
      </div>

      <dl class="mt-6 grid grid-cols-1 gap-x-6 gap-y-4 rounded-lg border border-gray-200 bg-white p-6 sm:grid-cols-2">
        <div>
          <dt class="text-xs font-medium uppercase text-gray-500">Supplier</dt>
          <dd class="mt-1 text-gray-900">{{ invoice.supplier_name }}</dd>
        </div>
        <div>
          <dt class="text-xs font-medium uppercase text-gray-500">Supplier tax ID</dt>
          <dd class="mt-1 text-gray-900">{{ invoice.supplier_tax_id }}</dd>
        </div>
        <div>
          <dt class="text-xs font-medium uppercase text-gray-500">Net amount</dt>
          <dd class="mt-1 text-gray-900">{{ formatAmount(invoice.net_amount, invoice.currency) }}</dd>
        </div>
        <div>
          <dt class="text-xs font-medium uppercase text-gray-500">VAT amount</dt>
          <dd class="mt-1 text-gray-900">{{ formatAmount(invoice.vat_amount, invoice.currency) }}</dd>
        </div>
        <div>
          <dt class="text-xs font-medium uppercase text-gray-500">Gross amount</dt>
          <dd class="mt-1 font-medium text-gray-900">{{ formatAmount(invoice.gross_amount, invoice.currency) }}</dd>
        </div>
        <div>
          <dt class="text-xs font-medium uppercase text-gray-500">Currency</dt>
          <dd class="mt-1 text-gray-900">{{ invoice.currency }}</dd>
        </div>
        <div>
          <dt class="text-xs font-medium uppercase text-gray-500">Issue date</dt>
          <dd class="mt-1 text-gray-900">{{ formatDate(invoice.issue_date) }}</dd>
        </div>
        <div>
          <dt class="text-xs font-medium uppercase text-gray-500">Due date</dt>
          <dd class="mt-1 text-gray-900">{{ formatDate(invoice.due_date) }}</dd>
        </div>
        <div class="sm:col-span-2">
          <dt class="text-xs font-medium uppercase text-gray-500">Last updated</dt>
          <dd class="mt-1 text-gray-900">{{ formatDateTime(invoice.updated_at) }}</dd>
        </div>
      </dl>

      <h2 class="mt-8 text-lg font-semibold text-gray-900">Edit invoice</h2>
      <div class="mt-4 rounded-lg border border-gray-200 bg-white p-6">
        <InvoiceEditForm
          :key="invoice.id"
          :invoice="invoice"
          @updated="refresh()"
          @conflict="refresh()"
        />
      </div>
    </div>
  </div>
</template>
