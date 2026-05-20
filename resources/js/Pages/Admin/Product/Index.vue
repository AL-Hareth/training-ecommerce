<script setup lang="ts">
import { defineProps, ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'

const props = defineProps<{
  products: Array<{
	id: string | number
	name: string
	slug?: string
	price?: number | string | null
	stock?: number | null
	category?: { id?: string | number; name?: string } | null
	vendor?: { id?: string | number; name?: string } | null
    variants_count?: number
  }>
  q?: string
}>()

const searchTerm = ref(props.q ?? '')

function submitSearch() {
  const q = searchTerm.value.trim()
  router.get('/admin/products', q ? { q } : {}, {
    preserveState: true,
    replace: true,
  })
}

function clearSearch() {
  searchTerm.value = ''
  submitSearch()
}
</script>

<template>
  <div class="min-h-screen bg-gray-50 flex flex-col p-6">
	<div class="flex justify-between items-center mb-8">
	  <div class="flex items-center gap-4">
		<Link href="/admin" class="inline-flex items-center px-3 py-2 rounded-lg bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 shadow-sm transition-all">
		  <svg class="-ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
			<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
		  </svg>
		  <span class="text-sm font-medium">Dashboard</span>
		</Link>

		<h1 class="text-3xl font-black text-gray-900 tracking-tight">Products</h1>
	  </div>
	  <Link href="/admin/products/create" class="inline-flex items-center px-5 py-2.5 bg-indigo-600 text-white font-bold rounded-lg hover:bg-indigo-700 shadow-lg shadow-indigo-100 transition-all active:scale-95">+ Add Product</Link>
	</div>

	<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
      <div class="p-4 border-b border-gray-100 bg-gray-50/50">
        <form @submit.prevent="submitSearch" class="flex flex-col gap-3 sm:flex-row sm:items-center">
          <div class="relative flex-1 max-w-md">
            <input
              v-model="searchTerm"
              type="search"
              placeholder="Search products by name..."
              class="w-full pl-10 pr-4 py-2 rounded-lg border-gray-200 text-sm focus:ring-indigo-500 focus:border-indigo-500 shadow-sm"
            />
            <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
          </div>
          <div class="flex gap-2">
            <button type="submit" class="px-4 py-2 bg-gray-900 text-white text-sm font-bold rounded-lg hover:bg-black transition-colors">Search</button>
            <button v-if="props.q" type="button" @click="clearSearch" class="px-4 py-2 bg-white border border-gray-200 text-sm font-bold text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">Clear</button>
          </div>
        </form>
      </div>

	  <table class="w-full border-collapse">
		<thead>
		  <tr class="text-left bg-gray-50/50">
			<th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Product</th>
			<th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Category</th>
			<th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Pricing</th>
			<th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Inventory</th>
			<th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Actions</th>
		  </tr>
		</thead>
		<tbody class="divide-y divide-gray-100">
		  <tr v-for="product in products" :key="product.id" class="group hover:bg-indigo-50/30 transition-colors">
			<td class="px-6 py-4">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-gray-100 flex-shrink-0 flex items-center justify-center text-gray-400">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                </div>
                <div>
                  <div class="text-sm font-bold text-gray-900 group-hover:text-indigo-600 transition-colors">{{ product.name }}</div>
                  <div class="text-[10px] text-gray-400 mt-0.5 uppercase tracking-wide">ID: {{ String(product.id).split('-')[0] }}...</div>
                </div>
              </div>
			</td>
			<td class="px-6 py-4">
              <span class="px-2 py-1 bg-gray-100 text-gray-600 text-[10px] font-bold rounded uppercase tracking-wider">{{ product.category?.name ?? '—' }}</span>
			</td>
			<td class="px-6 py-4">
              <div class="text-sm font-bold text-gray-900">{{ product.price != null ? (`$${Number(product.price).toFixed(2)}`) : '—' }}</div>
              <div v-if="product.variants_count" class="text-[10px] text-indigo-500 font-bold uppercase tracking-tight mt-0.5">{{ product.variants_count }} Variants</div>
			</td>
			<td class="px-6 py-4">
              <div class="flex items-center gap-2">
                <span :class="[Number(product.stock) > 0 ? 'bg-green-500' : 'bg-red-500', 'w-1.5 h-1.5 rounded-full']"></span>
                <span class="text-sm font-medium text-gray-700">{{ product.stock != null ? product.stock : '0' }} units</span>
              </div>
			</td>
			<td class="px-6 py-4">
			  <div class="flex justify-end gap-2">
				<Link :href="`/admin/products/${product.id}/edit`" class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all" title="Edit Product">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                </Link>
				<Link :href="`/admin/products/${product.id}`" method="delete" as="button" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all" title="Delete Product">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </Link>
			  </div>
			</td>
		  </tr>
		  <tr v-if="products.length === 0">
			<td colspan="5" class="px-6 py-12 text-center">
              <div class="text-gray-400 mb-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto opacity-20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10" />
                </svg>
              </div>
              <p class="text-gray-500 font-medium">No products found.</p>
            </td>
		  </tr>
		</tbody>
	  </table>
	</div>
  </div>
</template>

<style scoped></style>
