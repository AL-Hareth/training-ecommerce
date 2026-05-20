<script setup lang="ts">
import { defineProps, ref } from 'vue'
import {Link, router, usePage} from '@inertiajs/vue3'

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

const user = usePage().props.auth?.user ?? null;

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

function getStatusClasses(status?: string | null) {
  const s = (status ?? 'pending').toLowerCase()
  if (s.includes('delivered') || s.includes('completed') || s.includes('paid')) {
    return 'bg-emerald-50 text-emerald-700 border-emerald-100'
  }
  if (s.includes('pending') || s.includes('processing')) {
    return 'bg-amber-50 text-amber-700 border-amber-100'
  }
  if (s.includes('cancel') || s.includes('fail')) {
    return 'bg-rose-50 text-rose-700 border-rose-100'
  }
  return 'bg-gray-50 text-gray-700 border-gray-100'
}
</script>

<template>
  <div class="min-h-screen bg-gray-50 p-6">
	<div class="max-w-7xl mx-auto">
	  <div class="flex items-center justify-between mb-8">
		<div class="flex items-center gap-4">
		  <Link href="/admin" class="inline-flex items-center px-3 py-2 rounded-lg bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 shadow-sm transition-all">
			<svg class="-ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
			  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
			</svg>
			<span class="text-sm font-medium">Dashboard</span>
		  </Link>

		  <h1 class="text-3xl font-black text-gray-900 tracking-tight">Orders</h1>
		</div>
	  </div>

      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-4 border-b border-gray-100 bg-gray-50/50">
          <form @submit.prevent="submitSearch" class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="relative flex-1 max-w-md">
              <input
                v-model="searchTerm"
                type="search"
                placeholder="Search by order ID or customer..."
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

		<table class="min-w-full table-auto">
		  <thead class="bg-gray-50/50 text-left">
			<tr>
			  <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Order</th>
			  <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Customer</th>
			  <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Items</th>
			  <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Total</th>
			  <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Status</th>
			  <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Date</th>
			  <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Actions</th>
			</tr>
		  </thead>
		  <tbody class="divide-y divide-gray-100">
			<tr v-for="(order, index) in orders" :key="order.id" class="group hover:bg-indigo-50/30 transition-all">
			  <td class="px-6 py-4 align-middle">
                <div class="text-sm font-bold text-gray-900">#{{ String(order.id).split('-')[0] }}</div>
              </td>
			  <td class="px-6 py-4 align-middle">
				<div class="text-sm font-bold text-gray-900">{{ order.user?.name ?? 'Guest Customer' }}</div>
				<div class="text-[10px] text-gray-400 uppercase font-medium tracking-tight">{{ order.user?.email ?? 'No Email' }}</div>
			  </td>
			  <td class="px-6 py-4 align-middle text-center">
                <span class="text-sm font-medium text-gray-600">{{ order.items_count ?? '0' }}</span>
              </td>
			  <td class="px-6 py-4 align-middle">
                <div class="text-sm font-black text-gray-900">{{ order.total_price != null ? (`$${Number(order.total_price).toFixed(2)}`) : '—' }}</div>
              </td>
			  <td class="px-6 py-4 align-middle">
				<span :class="[getStatusClasses(order.status), 'inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border']">
                  {{ order.status ?? 'pending' }}
                </span>
			  </td>
			  <td class="px-6 py-4 align-middle text-xs text-gray-500 font-medium">
                {{ order.created_at ? new Date(order.created_at).toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' }) : '—' }}
              </td>
			  <td class="px-6 py-4 align-middle text-right">
				<div class="flex justify-end gap-2">
				  <Link :href="`/admin/orders/${order.id}`" class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all" title="View Details">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </Link>
				  <Link :href="`/admin/orders/${order.id}/edit`" class="p-2 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-all" v-if="user.role === 'admin'" title="Edit Order">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </Link>
				</div>
			  </td>
			</tr>
			<tr v-if="orders.length === 0">
			  <td colspan="7" class="px-6 py-20 text-center">
                <div class="text-gray-200 mb-2">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                  </svg>
                </div>
                <p class="text-gray-500 font-medium italic">No orders recorded yet.</p>
              </td>
			</tr>
		  </tbody>
		</table>
	  </div>
	</div>
  </div>
</template>

<style scoped></style>
