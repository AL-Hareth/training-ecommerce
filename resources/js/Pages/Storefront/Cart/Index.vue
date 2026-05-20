<script setup lang="ts">
import { defineProps, computed } from 'vue'
import { Link, useForm, router } from '@inertiajs/vue3'
import DiscountCountdown from '@/Components/DiscountCountdown.vue'

const props = defineProps<{
  cart?: {
    id: string | number
    items?: Array<{
      id: string | number
      quantity: number
      variant_id?: string | null
      attributes?: Record<string, string> | null
      product?: {
        id: string | number
        name: string
        price?: number | string | null
        discounted_price?: number | string | null
        discount_expiration?: string | null
        image?: string | null
        vendor_id?: string | number
      }
    }>
  }
  appliedVouchers?: Array<{
      id: string | number
      code: string
      vendor_id: string | number
      discount_type: string
      discount_value: number
      min_spend?: number | null
  }>
}>()

const items = computed(() => props.cart?.items ?? [])

const vendorSubtotals = computed(() => {
    const subtotals: Record<string, number> = {}
    items.value.forEach(it => {
        if (!it.product) return
        const vendorId = it.product.vendor_id?.toString() || 'unknown'
        // If it's a variant, we should ideally have the variant price here. 
        // For now, let's assume the backend provides the correct price or we use the product price.
        const price = it.product.discounted_price != null ? Number(it.product.discounted_price) : (it.product.price != null ? Number(it.product.price) : 0)
        subtotals[vendorId] = (subtotals[vendorId] || 0) + (price * it.quantity)
    })
    return subtotals
})

const discounts = computed(() => {
    const amounts: Record<string, number> = {}
    if (!props.appliedVouchers) return amounts

    props.appliedVouchers.forEach(voucher => {
        const vendorId = voucher.vendor_id.toString()
        const subtotal = vendorSubtotals.value[vendorId] || 0
        if (subtotal >= (voucher.min_spend || 0)) {
            let discount = 0
            if (voucher.discount_type === 'percentage') {
                discount = subtotal * (voucher.discount_value / 100)
            } else {
                discount = voucher.discount_value
            }
            amounts[vendorId] = Math.min(subtotal, discount)
        }
    })
    return amounts
})

const subtotal = computed(() => {
    let total = 0
    Object.values(vendorSubtotals.value).forEach(val => total += val)
    return total
})

const totalDiscount = computed(() => {
    let total = 0
    Object.values(discounts.value).forEach(val => total += val)
    return total
})

const grandTotal = computed(() => Math.max(0, subtotal.value - totalDiscount.value))

const voucherForm = useForm({
  code: ''
})

function applyVoucher() {
  voucherForm.post('/cart/voucher', {
    preserveScroll: true,
    onSuccess: () => voucherForm.reset()
  })
}

function handleExpired() {
  router.reload({ preserveScroll: true })
}
</script>

<template>
  <div class="min-h-screen bg-gray-50 p-6">
    <div class="max-w-4xl mx-auto">
      <header class="mb-8 flex items-center justify-between">
        <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Shopping Cart</h1>
        <Link href="/products" class="text-sm font-medium text-indigo-600 hover:text-indigo-800 flex items-center gap-1 transition-colors">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Continue Shopping
        </Link>
      </header>

      <div v-if="items.length === 0" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
        <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
          </svg>
        </div>
        <h2 class="text-xl font-bold text-gray-800">Your cart is empty</h2>
        <p class="text-gray-500 mt-2 mb-6">Looks like you haven't added anything to your cart yet.</p>
        <Link href="/products" class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white font-bold rounded-lg hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-100">
          Browse Products
        </Link>
      </div>

      <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Items List -->
        <div class="lg:col-span-2 space-y-4">
          <div v-for="item in items" :key="item.id" class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex gap-4 transition-all hover:shadow-md">
            <div class="w-24 h-24 bg-gray-50 rounded-lg overflow-hidden flex-shrink-0 border border-gray-100">
              <img v-if="item.product?.image" :src="item.product.image.startsWith('http') ? item.product.image : `/storage/${item.product.image}`" alt="" class="w-full h-full object-cover" />
              <div v-else class="w-full h-full flex items-center justify-center text-gray-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
              </div>
            </div>

            <div class="flex-1 min-w-0 flex flex-col justify-between">
              <div>
                <h3 class="text-lg font-bold text-gray-900 truncate">{{ item.product?.name ?? 'Product' }}</h3>
                <div v-if="item.product?.discount_expiration" class="mt-0.5">
                  <DiscountCountdown :expiration="item.product.discount_expiration" @expired="handleExpired" />
                </div>
                <div v-if="item.attributes" class="mt-1 flex flex-wrap gap-1">
                  <span v-for="(val, key) in item.attributes" :key="key" class="text-[10px] uppercase font-bold bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded">
                    {{ key }}: {{ val }}
                  </span>
                </div>
              </div>
              
              <div class="flex items-center justify-between mt-4">
                <div class="text-sm text-gray-500">
                  Qty: <span class="font-bold text-gray-800">{{ item.quantity }}</span>
                </div>
                <div class="text-lg font-black text-gray-900">
                  ${{ (Number(item.product?.discounted_price ?? item.product?.price ?? 0) * item.quantity).toFixed(2) }}
                </div>
              </div>
            </div>

            <div class="flex flex-col justify-between items-end">
              <Link :href="`/cart/${item.id}`" method="delete" as="button" class="p-2 text-gray-400 hover:text-red-500 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
              </Link>
            </div>
          </div>

          <div class="pt-4">
            <Link href="/cart/clear" method="post" as="button" class="text-sm text-gray-500 hover:text-red-600 font-medium transition-colors">
              Clear All Items
            </Link>
          </div>
        </div>

        <!-- Summary Column -->
        <div class="space-y-6">
          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-6">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Order Summary</h2>
            
            <div class="space-y-4 mb-6">
              <div class="flex justify-between text-gray-600">
                <span>Subtotal</span>
                <span class="font-bold">${{ subtotal.toFixed(2) }}</span>
              </div>
              
              <div v-if="totalDiscount > 0" class="flex justify-between text-green-600">
                <span>Discounts</span>
                <span class="font-bold">-${{ totalDiscount.toFixed(2) }}</span>
              </div>
              
              <div class="flex justify-between text-gray-600">
                <span>Shipping</span>
                <span class="text-xs italic">Calculated at checkout</span>
              </div>
            </div>

            <div v-if="props.appliedVouchers?.length" class="mb-6 space-y-2">
              <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Active Vouchers</p>
              <div class="flex flex-wrap gap-2">
                <div v-for="voucher in props.appliedVouchers" :key="voucher.id" class="flex items-center gap-2 bg-green-50 text-green-700 px-3 py-1 rounded-full text-xs font-bold border border-green-100">
                  {{ voucher.code }}
                  <Link :href="`/cart/voucher/${voucher.vendor_id}`" method="delete" as="button" class="hover:text-green-900 font-black">×</Link>
                </div>
              </div>
            </div>

            <form @submit.prevent="applyVoucher" class="mb-8">
              <div class="flex gap-2">
                <input type="text" v-model="voucherForm.code" placeholder="Voucher Code" class="flex-1 rounded-lg border-gray-200 text-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                <button type="submit" :disabled="voucherForm.processing" class="px-4 py-2 bg-gray-900 text-white text-sm font-bold rounded-lg hover:bg-black transition-colors disabled:opacity-50">
                  Apply
                </button>
              </div>
              <p v-if="voucherForm.errors.code" class="mt-1 text-xs text-red-600">{{ voucherForm.errors.code }}</p>
            </form>

            <div class="pt-6 border-t border-gray-100">
              <div class="flex justify-between items-end mb-8">
                <span class="text-gray-900 font-bold">Total</span>
                <span class="text-3xl font-black text-indigo-600 leading-none">${{ grandTotal.toFixed(2) }}</span>
              </div>

              <Link href="/checkout" class="w-full flex items-center justify-center py-4 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100 active:scale-95">
                Proceed to Checkout
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped></style>
