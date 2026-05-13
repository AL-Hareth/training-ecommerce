<script setup lang="ts">
import { defineProps, computed } from 'vue'
import { Link } from '@inertiajs/vue3'


type OrderItem = {
  id?: string | number
  quantity?: number
  product?: {
    id?: string | number
    name?: string
    image?: string | null
    price?: number | string | null
  } | null
}

type Order = {
  id: string | number
  status?: string | null
  total_price?: number | string | null
  created_at?: string | null,
    payment_method?: string | null,
  items?: OrderItem[] | null
}

const props = defineProps<{
  orders?: Order[]
}>()

const orders = computed(() => props.orders ?? [])

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
</script>

<template>
  <div class="min-h-screen bg-gradient-to-b from-indigo-50 to-white py-10 px-6">
    <div class="max-w-6xl mx-auto">
      <header class="mb-8">
        <h1 class="text-3xl font-extrabold tracking-tight text-gray-900">Your Orders</h1>
        <p class="mt-2 text-gray-600">Track your recent purchases and their delivery status.</p>
      </header>

      <div v-if="orders.length === 0" class="rounded-2xl border border-dashed border-gray-200 bg-white p-12 text-center shadow-sm">
        <div class="mx-auto w-14 h-14 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center mb-4">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="currentColor"><path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zm10 0c-1.1 0-1.99.9-1.99 2S15.9 22 17 22s2-.9 2-2-.9-2-2-2zM7.16 14h9.69c.75 0 1.41-.41 1.75-1.03l3.58-6.49a1 1 0 0 0-.87-1.48H6.21L5.27 2.9A1.99 1.99 0 0 0 3.41 2H2v2h1.41l3.6 7.59-1.35 2.44A2 2 0 0 0 7.16 18H20v-2H7.16l1.25-2.25.75-1.35z"/></svg>
        </div>
        <h2 class="text-lg font-semibold text-gray-800">No orders yet</h2>
        <p class="mt-1 text-gray-500">When you place an order it will appear here.</p>
        <div class="mt-6">
          <Link href="/products" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-white shadow hover:bg-indigo-700">
            Browse products
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 opacity-90" viewBox="0 0 24 24" fill="currentColor"><path d="M13 5l7 7-7 7v-4H4v-6h9V5z"/></svg>
          </Link>
        </div>
      </div>

      <div v-else class="space-y-6">
        <!-- Desktop table -->
        <div class="hidden md:block rounded-xl overflow-hidden border border-gray-100 bg-white shadow">
          <table class="min-w-full divide-y divide-gray-100">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Order</th>
                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Placed</th>
                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Status</th>
                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Total</th>
                <th class="px-6 py-3"></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="(order, index) in orders" :key="order.id" class="hover:bg-gray-50">
                <td class="px-6 py-4">
                  <div class="font-semibold text-gray-900">#{{ Number(index)+1 }}</div>
                  <div class="text-xs text-gray-500">{{ (order.items?.length ?? 0) }} item(s)</div>
                </td>
                <td class="px-6 py-4 text-gray-700">
                  {{ formatDate(order.created_at) }}
                </td>
                <td class="px-6 py-4">
                  <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium" :class="statusBadgeClasses(order.status)">
                    {{ order.status ?? 'Unknown' }}
                  </span>
                </td>
                <td class="px-6 py-4 text-right font-semibold text-gray-900">
                  {{ formatMoney(order.total_price, 'USD') }}
                </td>
                <td class="px-6 py-4 text-right">
                  <Link :href="`/orders/${order.id}`" class="inline-flex items-center gap-1 text-indigo-600 hover:text-indigo-700">
                    View details
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M10 17l5-5-5-5v10z"/></svg>
                  </Link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Mobile cards -->
        <div class="grid md:hidden grid-cols-1 gap-4">
          <article v-for="(order, index) in orders" :key="order.id" class="rounded-xl border border-gray-100 bg-white p-5 shadow hover:shadow-md transition">
            <div class="flex items-start justify-between">
              <div>
                <h3 class="text-base font-bold text-gray-900">Order #{{ Number(index)+2 }}</h3>
                <p class="text-xs text-gray-500 mt-0.5">Placed {{ formatDate(order.created_at) }}</p>
              </div>
              <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium" :class="statusBadgeClasses(order.status)">
                {{ order.status ?? 'Unknown' }}
              </span>
            </div>

            <div class="mt-4 flex items-center gap-3">
              <div class="flex -space-x-2">
                <div
                  v-for="(it, idx) in (order.items ?? []).slice(0, 4)"
                  :key="it.id ?? idx"
                  class="w-9 h-9 rounded-full ring-2 ring-white overflow-hidden bg-gray-100"
                >
                  <img
                    v-if="it.product?.image"
                    :src="it.product.image.startsWith('http') ? it.product.image : `/storage/${it.product.image}`"
                    alt=""
                    class="w-full h-full object-cover"
                  />
                  <div v-else class="w-full h-full flex items-center justify-center text-[10px] text-gray-400">No img</div>
                </div>
                <div v-if="(order.items?.length ?? 0) > 4" class="w-9 h-9 rounded-full ring-2 ring-white bg-gray-100 flex items-center justify-center text-[10px] text-gray-600">
                  +{{ (order.items?.length ?? 0) - 4 }}
                </div>
              </div>

              <div class="ml-auto text-right">
                <p class="text-xs text-gray-500">Total</p>
                <p class="text-base font-semibold text-gray-900">
                  {{ formatMoney(order.total_price, 'USD') }}
                </p>
              </div>
            </div>

            <div class="mt-5 flex items-center justify-between">
              <p class="text-xs text-gray-500">{{ (order.items?.length ?? 0) }} item(s)</p>
              <Link :href="`/orders/${order.id}`" class="inline-flex items-center gap-1 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-700">
                View details
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M10 17l5-5-5-5v10z"/></svg>
              </Link>
            </div>
          </article>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Subtle hover for table rows on devices that support hover */
@media (hover: hover) {
  tbody tr:hover {
    background: rgb(249 250 251);
  }
}
</style>
