<script setup lang="ts">
const { listInvoices } = useInvoicesApi()

const page = ref(1)

const {
  data,
  status,
  error,
  refresh,
} = await useAsyncData(
  'invoices-list',
  () => listInvoices({ page: page.value }),
  { watch: [page] }
)

const invoices = computed(() => data.value?.data ?? [])
const meta = computed(() => data.value?.meta)

function goToInvoice(id: string) {
  navigateTo(`/invoices/${id}`)
}
</script>

<template>
  <div class="mx-auto max-w-5xl px-4 py-8">
    <h1 class="text-2xl font-semibold text-gray-900">Invoices</h1>
    <div
      v-if="status === 'pending'" class="mt-6 text-sm text-gray-500">
      Loading invoices…
    </div>

    <div v-else-if="error" class="mt-6 rounded-md bg-red-50 p-4 text-sm text-red-700">
      <p>Failed to load invoices. {{ error.message }}</p>
      <button class="mt-2 font-medium underline" @click="refresh()">
        Retry
      </button>
    </div>

    <div v-else-if="invoices.length === 0" class="mt-6 text-sm text-gray-500">
      No invoices found.
    </div>

    <div v-else class="mt-6 overflow-x-auto rounded-lg border border-gray-200">
      <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-3 text-left font-medium text-gray-500">Number</th>
            <th class="px-4 py-3 text-left font-medium text-gray-500">Supplier</th>
            <th class="px-4 py-3 text-left font-medium text-gray-500">Gross amount</th>
            <th class="px-4 py-3 text-left font-medium text-gray-500">Status</th>
            <th class="px-4 py-3 text-left font-medium text-gray-500">Due date</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 bg-white">
          <tr
            v-for="invoice in invoices"
            :key="invoice.id"
            class="cursor-pointer hover:bg-gray-50"
            @click="goToInvoice(invoice.id)"
          >
            <td class="px-4 py-3 font-medium text-gray-900">{{ invoice.number }}</td>
            <td class="px-4 py-3 text-gray-700">{{ invoice.supplier_name }}</td>
            <td class="px-4 py-3 text-gray-700">
              {{ formatAmount(invoice.gross_amount, invoice.currency) }}
            </td>
            <td class="px-4 py-3">
              <StatusBadge :status="invoice.status" />
            </td>
            <td class="px-4 py-3 text-gray-700">{{ formatDate(invoice.due_date) }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div
      v-if="meta && meta.last_page > 1"
      class="mt-4 flex items-center justify-between text-sm text-gray-600"
    >
      <button
        class="rounded border border-gray-300 px-3 py-1 disabled:opacity-40"
        :disabled="meta.current_page <= 1"
        @click="page = meta.current_page - 1"
      >
        Previous
      </button>
      <span>Page {{ meta.current_page }} of {{ meta.last_page }}</span>
      <button
        class="rounded border border-gray-300 px-3 py-1 disabled:opacity-40"
        :disabled="meta.current_page >= meta.last_page"
        @click="page = meta.current_page + 1"
      >
        Next
      </button>
    </div>
  </div>
</template>
