<script setup lang="ts">
import { defineProps, ref, computed } from 'vue'
import { Link } from '@inertiajs/vue3'

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
    subtotal?: number | string | null
    discount_amount?: number | string | null
    created_at?: string | null
    payment_method?: string | null
    shipping_address?: string | null
    shipping_phone?: string | null
    items?: OrderItem[] | null
}

const props = defineProps<{
    order: Order
}>()

const showDelete = ref(false)
const isPending = computed(() => ((props.order.status ?? '') as string).toLowerCase().includes('pending'))
function openDeleteModal() { showDelete.value = true }
function closeDeleteModal() { showDelete.value = false }
function confirmDelete() {
    // TODO: integrate delete API; for now just close
    showDelete.value = false
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
</script>

<template>
    <div class="min-h-screen bg-gradient-to-b from-indigo-50 to-white py-10 px-6">
        <div class="max-w-5xl mx-auto">

            <!-- Back Navigation -->
            <div class="mb-6">
                <Link href="/orders" class="inline-flex items-center gap-2 text-sm font-medium text-indigo-600 hover:text-indigo-800 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Orders
                </Link>
            </div>

            <!-- Delete Confirmation Modal -->
            <div v-if="showDelete" class="fixed inset-0 z-50 flex items-center justify-center">
                <div class="absolute inset-0 bg-black/40" @click="closeDeleteModal"></div>
                <div class="relative z-10 w-full max-w-md rounded-2xl bg-white shadow-xl ring-1 ring-black/5">
                    <div class="px-6 py-5 border-b border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-900">Delete order?</h3>
                        <p class="mt-1 text-sm text-gray-600">This action cannot be undone. Are you sure you want to delete this order?</p>
                    </div>
                    <div class="px-6 py-4 flex items-center justify-end gap-3">
                        <button @click="closeDeleteModal" type="button" class="inline-flex items-center rounded-lg px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-300">Cancel</button>
                        <button @click="confirmDelete" type="button" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold text-white bg-rose-600 hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 opacity-90" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M9 3h6a1 1 0 0 1 1 1v1h5v2h-1l-1 13a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 7H3V5h5V4a1 1 0 0 1 1-1zm1 4v12h2V7h-2zm4 0v12h2V7h-2z"/>
                            </svg>
                            Yes, delete
                        </button>
                    </div>
                </div>
            </div>

            <!-- Header Section -->
            <header class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-gray-900">
                        Order Details
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
                    <button
                        v-if="isPending"
                        type="button"
                        @click="openDeleteModal"
                        class="inline-flex items-center gap-2 rounded-lg bg-rose-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2"
                        title="Delete this order"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 opacity-90" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M9 3h6a1 1 0 0 1 1 1v1h5v2h-1l-1 13a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 7H3V5h5V4a1 1 0 0 1 1-1zm1 4v12h2V7h-2zm4 0v12h2V7h-2z"/>
                        </svg>
                        Delete order
                    </button>
                </div>
            </header>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <!-- Left Column: Items -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="rounded-2xl border border-gray-100 bg-white shadow-sm overflow-hidden">
                        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50">
                            <h2 class="text-lg font-semibold text-gray-800">Purchased Items ({{ order.items?.length ?? 0 }})</h2>
                        </div>

                        <ul class="divide-y divide-gray-100">
                            <li v-for="item in order.items" :key="item.id" class="p-6 flex flex-col sm:flex-row sm:items-center gap-6 hover:bg-gray-50/50 transition">
                                <!-- Product Image -->
                                <div class="h-24 w-24 flex-shrink-0 overflow-hidden rounded-xl bg-gray-100 border border-gray-200">
                                    <img
                                        v-if="item.product?.image"
                                        :src="item.product.image.startsWith('http') ? item.product.image : `/storage/${item.product.image}`"
                                        :alt="item.product?.name"
                                        class="h-full w-full object-cover object-center"
                                    />
                                    <div v-else class="flex h-full items-center justify-center text-gray-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                </div>

                                <!-- Product Info -->
                                <div class="flex flex-1 flex-col justify-between">
                                    <div>
                                        <h3 class="text-base font-medium text-gray-900">
                                            {{ item.product?.name ?? 'Unknown Product' }}
                                        </h3>
                                        <p class="mt-1 text-sm text-gray-500">
                                            Qty: {{ item.quantity }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Pricing -->
                                <div class="text-right flex-shrink-0">
                                    <p class="text-sm text-gray-500 mb-1">
                                        {{ formatMoney(item.ordering_price) }} each
                                    </p>
                                    <p class="text-lg font-bold text-gray-900">
                                        {{ formatMoney(Number(item.ordering_price) * item.quantity) }}
                                    </p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Right Column: Summary & Info -->
                <div class="space-y-6">

                    <!-- Order Summary Card -->
                    <div class="rounded-2xl border border-gray-100 bg-white shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4">Order Summary</h2>
                        <dl class="space-y-3 text-sm text-gray-600">
                            <div v-if="order.subtotal" class="flex justify-between border-t border-gray-100 pt-3 text-sm text-gray-600">
                                <dt>Subtotal</dt>
                                <dd>{{ formatMoney(order.subtotal) }}</dd>
                            </div>
                            <div v-if="order.discount_amount" class="flex justify-between text-sm text-green-600">
                                <dt>Discount</dt>
                                <dd>-{{ formatMoney(order.discount_amount) }}</dd>
                            </div>
                            <div v-if="order.subtotal && order.total_price" class="flex justify-between text-sm text-gray-600">
                                <dt>Shipping</dt>
                                <dd>{{ formatMoney(Number(order.total_price) - (Number(order.subtotal) - Number(order.discount_amount || 0))) }}</dd>
                            </div>
                            <div class="flex justify-between border-t border-gray-100 pt-3 text-base font-bold text-gray-900">
                                <dt>Total Amount</dt>
                                <dd class="text-indigo-600">{{ formatMoney(order.total_price) }}</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Customer Info Card -->
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

                    <!-- Payment Info Card -->
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
