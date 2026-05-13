<script setup lang="ts">
import { defineProps, ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'

const props = defineProps<{
  orders: Array<{
	id: string | number
	user?: { id?: string | number; name?: string; email?: string } | null
	total_price?: number | string | null
	status?: string | null
	items_count?: number | null
	created_at?: string | null
  }>
  q?: string
}>()

const searchTerm = ref(props.q ?? '')

function submitSearch() {
  const q = searchTerm.value.trim()
  router.get('/admin/orders', q ? { q } : {}, {
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
  <div class="min-h-screen bg-gray-50 p-6">
	<div class="max-w-6xl mx-auto">
	  <div class="flex items-center justify-between mb-6">
		<div class="flex items-center gap-4">
		  <Link href="/admin" class="inline-flex items-center px-3 py-2 rounded-md bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 shadow">
			<svg class="-ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
			  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
			</svg>
			<span class="text-sm font-medium">Back</span>
		  </Link>

		  <h1 class="text-3xl font-bold text-gray-800">Orders</h1>
		</div>
	  </div>

	  <form @submit.prevent="submitSearch" class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center">
		<label for="order-search" class="sr-only">Search orders</label>
		<input
		  id="order-search"
		  v-model="searchTerm"
		  type="search"
		  name="q"
		  placeholder="Search orders..."
		  class="w-full rounded-md border border-gray-300 bg-white px-4 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 sm:max-w-md"
		/>
		<div class="flex gap-2">
		  <button type="submit" class="rounded-md bg-blue-500 px-4 py-2 text-sm font-medium text-white shadow hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
			Search
		  </button>
		  <button v-if="props.q" type="button" @click="clearSearch" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
			Clear
		  </button>
		</div>
	  </form>

	  <div class="bg-white rounded-lg shadow overflow-hidden">
		<table class="min-w-full table-auto">
		  <thead class="bg-gray-100 text-left">
			<tr>
			  <th class="px-6 py-3 text-sm text-gray-700">#</th>
			  <th class="px-6 py-3 text-sm text-gray-700">Customer</th>
			  <th class="px-6 py-3 text-sm text-gray-700">Items</th>
			  <th class="px-6 py-3 text-sm text-gray-700">Total</th>
			  <th class="px-6 py-3 text-sm text-gray-700">Status</th>
			  <th class="px-6 py-3 text-sm text-gray-700">Placed</th>
			  <th class="px-6 py-3 text-sm text-gray-700">Actions</th>
			</tr>
		  </thead>
		  <tbody>
			<tr v-for="(order, index) in orders" :key="order.id" class="border-t hover:bg-gray-50">
			  <td class="px-6 py-4 align-middle">{{ index + 1 }}</td>
			  <td class="px-6 py-4 align-middle">
				<div class="text-sm font-medium text-gray-900">{{ order.user?.name ?? 'Guest' }}</div>
				<div class="text-xs text-gray-500">{{ order.user?.email ?? '' }}</div>
			  </td>
			  <td class="px-6 py-4 align-middle">{{ order.items_count ?? '—' }}</td>
			  <td class="px-6 py-4 align-middle">{{ order.total_price != null ? (`$${Number(order.total_price).toFixed(2)}`) : '—' }}</td>
			  <td class="px-6 py-4 align-middle">
				<span :class="{
				  'inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold': true,
				  'bg-green-100 text-green-800': order.status === 'completed',
				  'bg-yellow-100 text-yellow-800': order.status === 'pending',
				  'bg-gray-100 text-gray-800': !order.status
				}">{{ order.status ?? 'pending' }}</span>
			  </td>
			  <td class="px-6 py-4 align-middle text-sm text-gray-500">{{ order.created_at ? new Date(order.created_at).toLocaleString() : '—' }}</td>
			  <td class="px-6 py-4 align-middle">
				<div class="flex gap-2">
				  <Link :href="`/admin/orders/${order.id}`" class="px-3 py-1 bg-indigo-600 text-white rounded text-sm">View</Link>
				  <Link :href="`/admin/orders/${order.id}/edit`" class="px-3 py-1 bg-green-500 text-white rounded text-sm">Edit</Link>
				</div>
			  </td>
			</tr>
			<tr v-if="orders.length === 0">
			  <td colspan="7" class="px-6 py-8 text-center text-gray-500">No orders found.</td>
			</tr>
		  </tbody>
		</table>
	  </div>
	</div>
  </div>
</template>

<style scoped>
.align-middle { vertical-align: middle; }
</style>
