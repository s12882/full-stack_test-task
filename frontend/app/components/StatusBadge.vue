<script setup lang="ts">
import type { InvoiceStatus } from '~/types/invoice'

const props = defineProps<{
  status: InvoiceStatus
}>()

const styles: Record<InvoiceStatus, string> = {
  pending: 'bg-amber-100 text-amber-800',
  approved: 'bg-green-100 text-green-800',
  rejected: 'bg-red-100 text-red-800',
}

const label = computed(
  () => props.status.charAt(0).toUpperCase() + props.status.slice(1)
)
</script>

<template>
  <span
    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
    :class="styles[status]"
  >
    {{ label }}
  </span>
</template>
