<script setup lang="ts">
import { defineProps, computed } from 'vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps<{
  cart?: {
	id: string | number
	items?: Array<{
	  id: string | number
	  quantity: number
	  product?: {
		id: string | number
		name: string
		price?: number | string | null
		image?: string | null
	  }
	}>
  }
}>()

const items = computed(() => props.cart?.items ?? [])

const subtotal = computed(() => items.value.reduce((sum, it) => {
  const price = it.product?.price != null ? Number(it.product.price) : 0
  return sum + price * (it.quantity ?? 0)
}, 0))

</script>

<template>
  <div class="min-h-screen bg-gray-50 p-6">
	<div class="max-w-4xl mx-auto">
	  <header class="mb-6 flex items-center justify-between">
		<h1 class="text-2xl font-bold text-gray-800">Your Cart</h1>
		<Link href="/products" class="text-sm text-gray-500 hover:underline">Continue shopping</Link>
	  </header>

	  <div class="bg-white rounded-lg shadow overflow-hidden">
		<div class="p-6">
		  <div v-if="items.length === 0" class="text-center text-gray-500 py-12">Your cart is empty.</div>

		  <div v-else class="space-y-4">
			<ul class="divide-y">
			  <li v-for="item in items" :key="item.id" class="py-4 flex gap-4 items-center">
				<div class="w-20 h-20 bg-gray-100 rounded overflow-hidden flex-shrink-0">
				  <img v-if="item.product?.image" :src="item.product.image.startsWith('http') ? item.product.image : `/storage/${item.product.image}`" alt="" class="w-full h-full object-cover" />
				  <div v-else class="w-full h-full flex items-center justify-center text-gray-400">No image</div>
				</div>

				<div class="flex-1">
				  <h3 class="text-md font-semibold text-gray-800">{{ item.product?.name ?? 'Product' }}</h3>
				  <p class="text-sm text-gray-500 mt-1">Price: <span class="font-medium text-gray-800">{{ item.product?.price != null ? (`$${Number(item.product.price).toFixed(2)}`) : '—' }}</span></p>
				  <p class="text-sm text-gray-500 mt-1">Quantity: <span class="font-medium text-gray-800">{{ item.quantity }}</span></p>
				</div>

				<div class="text-right">
				  <p class="text-md font-semibold">{{ item.product?.price != null ? (`$${(Number(item.product.price) * item.quantity).toFixed(2)}`) : '—' }}</p>
				  <div class="mt-2 flex justify-end gap-2">
					<Link :href="`/cart/${item.id}`" method="delete" as="button" class="text-sm px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600">Remove</Link>
				  </div>
				</div>
			  </li>
			</ul>

			<div class="pt-4 border-t flex items-center justify-between">
			  <div>
				<p class="text-sm text-gray-500">Subtotal</p>
				<p class="text-xl font-bold">${{ subtotal.toFixed(2) }}</p>
			  </div>
			  <div class="flex items-center gap-3">
				<Link href="/cart/clear" method="post" as="button" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 border border-gray-300">Clear Cart</Link>
				<Link href="/checkout" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Checkout</Link>
			  </div>
			</div>
		  </div>
		</div>
	  </div>
	</div>
  </div>
</template>

<style scoped>
.text-right p { margin: 0 }
</style>
