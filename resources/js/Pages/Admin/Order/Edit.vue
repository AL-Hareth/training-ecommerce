<script setup lang="ts">
import { defineProps, computed } from 'vue'
import {Link, useForm, usePage} from '@inertiajs/vue3'

type Product = {
  id?: string | number
  name?: string
  image?: string | null
}

type OrderItem = {
  id?: string | number
  quantity: number
  ordering_price: number | string
  product?: Product | null
}

type Order = {
  id: string | number
  status?: string | null
  total_price?: number | string | null
  created_at?: string | null
  payment_method?: string | null
  shipping_address?: string | null
  shipping_phone?: string | null
  items?: OrderItem[] | null
}

const props = defineProps<{
  order: Order
  statusOptions?: string[]
  updateUrl?: string
  backUrl?: string
}>()

const user = usePage().props.auth?.user ?? null;

const form = useForm({
  status: props.order.status ?? ''
})

const availableStatuses = computed(() => {
  const opts = props.statusOptions && props.statusOptions.length
    ? props.statusOptions
    : ['pending', 'processing', 'shipped', 'delivered', 'cancelled']
  // Ensure unique and normalized values while preserving original casing for provided options
  const seen = new Set<string>()
  return opts
    .map(s => String(s).trim())
    .filter(s => {
      const key = s.toLowerCase()
      if (seen.has(key)) return false
      seen.add(key)
      return key.length > 0
    })
})

function toLabel(value: string) {
  return value.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase())
}

function formatMoney(value?: number | string | null, currency = 'USD') {
  const n = value != null ? Number(value) : 0
  try {
    return new Intl.NumberFormat(undefined, { style: 'currency', currency }).format(n)
  } catch {
    return `$${n.toFixed(2)}`
  }
}

function formatDate(dateStr?: string | null) {
  if (!dateStr) return '—'
  try {
    return new Intl.DateTimeFormat(undefined, {
      year: 'numeric', month: 'short', day: '2-digit',
      hour: '2-digit', minute: '2-digit'
    }).format(new Date(dateStr))
  } catch {
    return dateStr
  }
}

function statusBadgeClasses(status?: string | null) {
  const s = (status ?? '').toLowerCase()
  if (s.includes('paid') || s.includes('completed') || s.includes('delivered')) {
    return 'bg-emerald-100 text-emerald-800 ring-1 ring-inset ring-emerald-200'
  }
  if (s.includes('pending') || s.includes('processing')) {
    return 'bg-amber-100 text-amber-800 ring-1 ring-inset ring-amber-200'
  }
  if (s.includes('cancel') || s.includes('failed')) {
    return 'bg-rose-100 text-rose-800 ring-1 ring-inset ring-rose-200'
  }
  return 'bg-gray-100 text-gray-800 ring-1 ring-inset ring-gray-200'
}

function submit() {
  form.put(`/admin/orders/${props.order.id}`, {
    preserveScroll: true
  })
}
</script>

<template>
  <div class="min-h-screen bg-gradient-to-b from-indigo-50 to-white py-10 px-6">
    <div class="max-w-6xl mx-auto">
      <!-- Back Navigation -->
      <div class="mb-6">
        <Link :href="backUrl ?? '/admin/orders'" class="inline-flex items-center gap-2 text-sm font-medium text-indigo-600 hover:text-indigo-800 transition">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Back to Orders
        </Link>
      </div>

      <!-- Header Section -->
      <header class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-3xl font-extrabold tracking-tight text-gray-900">
            Edit Order
          </h1>
          <p class="mt-2 text-sm text-gray-500">
            Order ID: <span class="font-mono text-gray-700">{{ order.id }}</span>
          </p>
        </div>
        <div class="flex items-center gap-3">
          <span class="text-sm text-gray-500">Placed on {{ formatDate(order.created_at) }}</span>
          <span class="inline-flex items-center rounded-full px-3 py-1.5 text-sm font-medium" :class="statusBadgeClasses(order.status)">
            {{ order.status ?? 'Unknown' }}
          </span>
        </div>
      </header>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left: Edit Form -->
        <div class="lg:col-span-2 space-y-6">
          <div class="rounded-2xl border border-gray-100 bg-white shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 bg-gray-50">
              <h2 class="text-lg font-semibold text-gray-800">Update Status</h2>
              <p class="mt-1 text-sm text-gray-500">Only the order status can be edited.</p>
            </div>

            <div class="p-6 space-y-6">
              <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Order status</label>
                <div class="mt-2">
                  <select
                    id="status"
                    v-model="form.status"
                    class="block w-full rounded-lg border-gray-300 bg-white text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 disabled:opacity-50"
                    :disabled="form.processing || user.role !== 'admin'"
                  >
                    <option v-for="opt in availableStatuses" :key="opt" :value="opt">
                      {{ toLabel(opt) }}
                    </option>
                  </select>
                </div>
                <p v-if="form.errors.status" class="mt-2 text-sm text-rose-600">{{ form.errors.status }}</p>
              </div>

              <div class="flex items-center justify-end gap-3 pt-2">
                <Link
                  :href="backUrl ?? `/admin/orders/${order.id}`"
                  class="inline-flex items-center rounded-lg px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-300"
                  :disabled="form.processing"
                  as="button"
                >
                  Cancel
                </Link>
                <button
                  type="button"
                  @click="submit"
                  :disabled="form.processing"
                  class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:opacity-60"
                >
                  <svg v-if="form.processing" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                  </svg>
                  Save changes
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Right: Contextual Info -->
        <div class="space-y-6">
          <!-- Order Summary -->
          <div class="rounded-2xl border border-gray-100 bg-white shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Order Summary</h2>
            <dl class="space-y-3 text-sm text-gray-600">
              <div class="flex justify-between border-t border-gray-100 pt-3 text-base font-bold text-gray-900">
                <dt>Total Amount</dt>
                <dd class="text-indigo-600">{{ formatMoney(order.total_price) }}</dd>
              </div>
            </dl>
          </div>

          <!-- Shipping Info -->
          <div class="rounded-2xl border border-gray-100 bg-white shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Shipping Information</h2>
            <div class="space-y-4">
              <div>
                <span class="block text-xs font-semibold uppercase text-gray-400 tracking-wider mb-1">Address</span>
                <p class="text-sm text-gray-800 leading-relaxed">{{ order.shipping_address || 'No shipping address provided.' }}</p>
              </div>
              <div>
                <span class="block text-xs font-semibold uppercase text-gray-400 tracking-wider mb-1">Contact Phone</span>
                <p class="text-sm text-gray-800">{{ order.shipping_phone || 'N/A' }}</p>
              </div>
            </div>
          </div>

          <!-- Payment Info -->
          <div class="rounded-2xl border border-gray-100 bg-white shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Payment Details</h2>
            <div>
              <span class="block text-xs font-semibold uppercase text-gray-400 tracking-wider mb-1">Method</span>
              <div class="flex items-center gap-2 text-sm text-gray-800 capitalize mt-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500" viewBox="0 0 20 20" fill="currentColor">
                  <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z" />
                  <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd" />
                </svg>
                {{ (order.payment_method || 'Unknown').replace('_', ' ') }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Subtle hover effect for list items if any are added later */
@media (hover: hover) {
  li:hover {
    background: rgb(249 250 251 / 0.5);
  }
}
</style>
