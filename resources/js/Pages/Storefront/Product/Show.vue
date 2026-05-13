<script setup lang="ts">
import {defineProps, computed} from 'vue'
import { Link, useForm } from '@inertiajs/vue3'

const props = defineProps<{
  product: {
    id: string | number
    name: string
    price?: number | string | null
    image?: string | null
    category?: { id?: string | number; name?: string } | null
    description?: string | null
    stock?: number | null
    vendor?: { id?: string | number; name?: string } | null
    attribute_values?: Array<{
      id: string | number
      value: string
      attribute: { id: string | number; name: string; slug: string } | null
    }> | null
  }
}>()

const priceText = computed(() => props.product.price != null ? `$${Number(props.product.price).toFixed(2)}` : '—')

const groupedAttributes = computed(() => {
  const groups: Record<string, { name: string; values: string[] }> = {}

  if (!props.product.attribute_values?.length) return groups

  for (const attrValue of props.product.attribute_values) {
    if (!attrValue.attribute) continue

    const attrName = attrValue.attribute.name
    if (!groups[attrName]) {
      groups[attrName] = { name: attrName, values: [] }
    }
    groups[attrName].values.push(attrValue.value)
  }

  return groups
})

const hasAttributes = computed(() => Object.keys(groupedAttributes.value).length > 0)

const form = useForm({
  product_id: String(props.product.id),
  quantity: 1,
})

function addToCart() {
  form.post('/cart/add')
}
</script>

<template>
  <div class="min-h-screen bg-gray-50 p-6">
	<div class="max-w-6xl mx-auto">
	  <div class="mb-6 flex items-center justify-between">
		<div>
		  <h1 class="text-2xl font-semibold text-gray-800">{{ props.product.name }}</h1>
		  <p class="text-sm text-gray-500">{{ props.product.category?.name ?? 'Uncategorized' }}</p>
		</div>
		<div>
		  <Link href="/products" class="inline-flex items-center px-3 py-2 rounded-md bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 shadow">Back to products</Link>
		</div>
	  </div>

	  <div class="bg-white rounded-lg shadow overflow-hidden">
		<div class="grid grid-cols-1 md:grid-cols-3 gap-6 p-6">
		  <div class="md:col-span-1">
			<div class="bg-gray-100 rounded-lg overflow-hidden aspect-w-4 aspect-h-3">
			  <img v-if="props.product.image" :src="props.product.image.startsWith('http') ? props.product.image : `/storage/${props.product.image}`" alt="" class="w-full h-full object-cover" />
			  <div v-else class="w-full h-full flex items-center justify-center text-gray-400">No image</div>
			</div>
			<div class="mt-4">
			  <p class="text-lg font-bold text-gray-900">{{ priceText }}</p>
			  <p class="text-sm text-gray-500 mt-1">Stock: <span class="font-medium text-gray-700">{{ props.product.stock ?? '—' }}</span></p>
			  <p v-if="props.product.vendor" class="text-sm text-gray-500 mt-2">Sold by <span class="font-medium text-gray-700">{{ props.product.vendor.name }}</span></p>
			</div>
		  </div>

		  <div class="md:col-span-2">
			<h2 class="text-lg font-medium text-gray-800 mb-2">Product details</h2>
			<p class="text-gray-700 whitespace-pre-line">{{ props.product.description ?? 'No description provided.' }}</p>

			<!-- Attribute Table -->
			<div v-if="hasAttributes" class="mt-6">
			  <h3 class="text-lg font-medium text-gray-800 mb-3">Specifications</h3>
			  <table class="min-w-full divide-y divide-gray-200 border border-gray-200 rounded-lg overflow-hidden">
				<thead class="bg-gray-50">
				  <tr>
					<th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Attribute</th>
					<th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Value</th>
				  </tr>
				</thead>
				<tbody class="bg-white divide-y divide-gray-200">
				  <tr v-for="(group, attrName) in groupedAttributes" :key="attrName">
					<td class="px-4 py-3 text-sm text-gray-600">{{ group.name }}</td>
					<td class="px-4 py-3 text-sm text-gray-900">
					  <span v-for="(val, idx) in group.values" :key="idx" class="inline-block bg-gray-100 rounded px-2 py-1 mr-1 text-sm">
						{{ val }}
					  </span>
					</td>
				  </tr>
				</tbody>
			  </table>
			</div>

			<div class="mt-6 flex items-center gap-3">
			  <div class="flex items-center gap-2">
				<label for="quantity" class="sr-only">Quantity</label>
				<input id="quantity" type="number" min="1" v-model.number="form.quantity" class="w-20 px-3 py-2 border border-gray-200 rounded-md text-sm" />
				<button :disabled="form.processing" @click.prevent="addToCart" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-60">
				  <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
					<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
					<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
				  </svg>
				  <span>{{ form.processing ? 'Adding...' : 'Add to cart' }}</span>
				</button>
			  </div>

			</div>
		  </div>
		</div>
	  </div>
	</div>
  </div>
</template>

<style scoped>
.aspect-w-4 { position: relative; }
.aspect-w-4::before { content: ''; display: block; padding-top: calc(100% * 3 / 4); }
.aspect-h-3 > img, .aspect-h-3 > div { position: absolute; top: 0; left: 0; width: 100%; height: 100%; }
</style>
