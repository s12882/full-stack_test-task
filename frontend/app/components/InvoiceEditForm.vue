<script setup lang="ts">
import { toTypedSchema } from '@vee-validate/zod'
import { useForm } from 'vee-validate'
import { z } from 'zod'
import type { ApiValidationError, Invoice } from '~/types/invoice'

const props = defineProps<{
  invoice: Invoice
}>()

const emit = defineEmits<{
  updated: []
  conflict: []
}>()

const { updateInvoice } = useInvoicesApi()

const isLocked = computed(() => props.invoice.status !== 'pending')

const schema = computed(() =>
  z.object({
      net_amount: z.coerce
        .number({ message: 'Net amount must be a number' })
        .gt(0, 'Net amount must be greater than 0'),
      vat_amount: z.coerce
        .number({ message: 'VAT amount must be a number' })
        .gte(0, 'VAT amount must be 0 or greater'),
      due_date: z.string().min(1, 'Due date is required')
    })
    .refine((data) => data.due_date >= props.invoice.issue_date, {
      message: 'Due date must be on or after the issue date',
      path: ['due_date']
    })
)

const { handleSubmit, defineField, errors, isSubmitting, setErrors } = useForm({
  validationSchema: computed(() => toTypedSchema(schema.value)),
  initialValues: {
    net_amount: Number(props.invoice.net_amount),
    vat_amount: Number(props.invoice.vat_amount),
    due_date: props.invoice.due_date,
  }
})

const [netAmount, netAmountAttrs] = defineField('net_amount')
const [vatAmount, vatAmountAttrs] = defineField('vat_amount')
const [dueDate, dueDateAttrs] = defineField('due_date')

const grossAmountPreview = computed(() => {
  const net = Number(netAmount.value)
  const vat = Number(vatAmount.value)

  if (Number.isNaN(net) || Number.isNaN(vat))
    return '—'

  return formatAmount((net + vat).toFixed(2), props.invoice.currency)
})

const serverError = ref('')
const successMessage = ref('')

const onSubmit = handleSubmit(async (values) => {
  serverError.value = ''
  successMessage.value = ''

  try {
    await updateInvoice(props.invoice.id, {
      net_amount: values.net_amount,
      vat_amount: values.vat_amount,
      due_date: values.due_date,
    })
    successMessage.value = 'Invoice updated successfully.'
    emit('updated')
  } catch (err) {
    handleSubmitError(err)
  }
})

function handleSubmitError(err: unknown) {
  const fetchError = err as { statusCode?: number; data?: ApiValidationError }

  if (fetchError.statusCode === 409) {
    serverError.value = 'This invoice can no longer be edited'
    emit('conflict')

    return
  }

  if (fetchError.statusCode === 422 && fetchError.data?.errors) {
    setErrors(
      Object.fromEntries(
        Object.entries(fetchError.data.errors).map(([field, messages]) => [
          field,
          messages[0],
        ])
      )
    )

    return
  }

  serverError.value = 'Failed to update the invoice. Please try again.'
}
</script>

<template>
  <form class="space-y-4" @submit="onSubmit">
    <div v-if="isLocked" class="rounded-md bg-gray-100 p-3 text-sm text-gray-600">
      Locked — this invoice is <span class="font-medium">{{ invoice.status }}</span> and can no longer be edited.
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700" for="net_amount">
        Net amount
      </label>
      <input
        id="net_amount"
        v-model="netAmount"
        v-bind="netAmountAttrs"
        type="number"
        step="0.01"
        :disabled="isLocked || isSubmitting"
        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm disabled:bg-gray-50 disabled:text-gray-400"
      >
      <p v-if="errors.net_amount" class="mt-1 text-sm text-red-600">
        {{ errors.net_amount }}
      </p>
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700" for="vat_amount">
        VAT amount
      </label>
      <input
        id="vat_amount"
        v-model="vatAmount"
        v-bind="vatAmountAttrs"
        type="number"
        step="0.01"
        :disabled="isLocked || isSubmitting"
        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm disabled:bg-gray-50 disabled:text-gray-400"
      >
      <p v-if="errors.vat_amount" class="mt-1 text-sm text-red-600">
        {{ errors.vat_amount }}
      </p>
    </div>
    <div>
      <span class="block text-sm font-medium text-gray-700">Gross amount</span>
      <p class="mt-1 text-sm text-gray-900">{{ grossAmountPreview }}</p>
      <p class="mt-1 text-xs text-gray-400">Calculated automatically from net + VAT.</p>
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700" for="due_date">
        Due date
      </label>
      <input
        id="due_date"
        v-model="dueDate"
        v-bind="dueDateAttrs"
        type="date"
        :disabled="isLocked || isSubmitting"
        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm disabled:bg-gray-50 disabled:text-gray-400"
      >
      <p v-if="errors.due_date" class="mt-1 text-sm text-red-600">
        {{ errors.due_date }}
      </p>
    </div>

    <div v-if="serverError" class="rounded-md bg-red-50 p-3 text-sm text-red-700">
      {{ serverError }}
    </div>
    <div v-if="successMessage" class="rounded-md bg-green-50 p-3 text-sm text-green-700">
      {{ successMessage }}
    </div>

    <button
      type="submit"
      :disabled="isLocked || isSubmitting"
      class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white disabled:opacity-40"
    >
      {{ isSubmitting ? 'Saving…' : 'Save changes' }}
    </button>
  </form>
</template>
