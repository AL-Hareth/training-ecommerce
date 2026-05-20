<script setup lang="ts">
import { usePage, Link } from '@inertiajs/vue3'
import {computed} from "vue";

const page = usePage()
const user = (page.props as any).auth?.user ?? null

const role = computed(() => user?.role ?? null)

const canSeeCategories = computed(() => role.value === 'admin')
const canSeeProducts = computed(() => role.value === 'admin' || role.value === 'vendor')
const canSeeOrders = computed(() => role.value === 'admin' || role.value === 'vendor')
const canSeeUsers = computed(() => role.value === 'admin')
const canSeeVouchers = computed(() => role.value === 'admin' || role.value === 'vendor')

defineProps<{
    totalProducts?: number
    totalOrders?: number
    totalUsers?: number
    totalCategories?: number
}>()
</script>

<template>
  <div class="min-h-screen bg-gray-50 p-8">
    <!-- Dashboard Header -->
    <header class="mb-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-4xl font-black text-gray-900 tracking-tight uppercase">Admin Console</h1>
        <p class="text-sm font-medium text-gray-500 mt-1 uppercase tracking-widest">
          Welcome back, <span class="text-indigo-600">{{ user?.name }}</span> • Overview & Management
        </p>
      </div>
      
      <div class="flex items-center gap-3">
        <Link href="/" class="px-4 py-2 bg-white border border-gray-200 rounded-lg text-xs font-bold text-gray-600 hover:bg-gray-50 transition-all shadow-sm">
          Visit Storefront
        </Link>
      </div>
    </header>

    <!-- Dashboard Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
      <div v-if="canSeeProducts" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-lg transition-all group">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
            </svg>
          </div>
          <div>
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Products</p>
            <p class="text-2xl font-black text-gray-900">{{ totalProducts || 0 }}</p>
          </div>
        </div>
      </div>

      <div v-if="canSeeOrders" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-lg transition-all group">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
          </div>
          <div>
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Orders</p>
            <p class="text-2xl font-black text-gray-900">{{ totalOrders || 0 }}</p>
          </div>
        </div>
      </div>

      <div v-if="canSeeUsers" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-lg transition-all group">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-4.596a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
          </div>
          <div>
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Users</p>
            <p class="text-2xl font-black text-gray-900">{{ totalUsers || 0 }}</p>
          </div>
        </div>
      </div>

      <div v-if="canSeeCategories" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-lg transition-all group">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18"/>
            </svg>
          </div>
          <div>
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Categories</p>
            <p class="text-2xl font-black text-gray-900">{{ totalCategories || 0 }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Dashboard Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Main Content Area -->
      <div class="lg:col-span-2 space-y-8">
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 min-h-[400px] flex flex-col items-center justify-center text-center">
          <div class="w-24 h-24 bg-gray-50 rounded-full flex items-center justify-center mb-6">
            <svg class="w-10 h-10 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
          </div>
          <h3 class="text-xl font-bold text-gray-800">Business Analytics</h3>
          <p class="text-gray-500 mt-2 max-w-sm mx-auto">Real-time charts and metrics will appear here once you have more transaction data.</p>
        </div>
      </div>

      <!-- Quick Actions Sidebar -->
      <div class="space-y-6">
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
          <h3 class="text-sm font-black text-gray-900 uppercase tracking-widest mb-6 border-b pb-4">Quick Navigation</h3>
          <nav class="space-y-2">
            <Link v-if="canSeeProducts" href="/admin/products" class="flex items-center justify-between p-3 rounded-xl hover:bg-gray-50 transition-all group">
              <span class="text-sm font-bold text-gray-700 group-hover:text-indigo-600 transition-colors">Manage Products</span>
              <svg class="w-4 h-4 text-gray-300 group-hover:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </Link>
            <Link v-if="canSeeOrders" href="/admin/orders" class="flex items-center justify-between p-3 rounded-xl hover:bg-gray-50 transition-all group">
              <span class="text-sm font-bold text-gray-700 group-hover:text-emerald-600 transition-colors">Process Orders</span>
              <svg class="w-4 h-4 text-gray-300 group-hover:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </Link>
            <Link v-if="canSeeCategories" href="/admin/categories" class="flex items-center justify-between p-3 rounded-xl hover:bg-gray-50 transition-all group">
              <span class="text-sm font-bold text-gray-700 group-hover:text-purple-600 transition-colors">Product Categories</span>
              <svg class="w-4 h-4 text-gray-300 group-hover:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </Link>
            <Link v-if="canSeeVouchers" href="/admin/vouchers" class="flex items-center justify-between p-3 rounded-xl hover:bg-gray-50 transition-all group">
              <span class="text-sm font-bold text-gray-700 group-hover:text-pink-600 transition-colors">Vouchers & Promos</span>
              <svg class="w-4 h-4 text-gray-300 group-hover:text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </Link>
            <Link v-if="canSeeUsers" href="/admin/users" class="flex items-center justify-between p-3 rounded-xl hover:bg-gray-50 transition-all group">
              <span class="text-sm font-bold text-gray-700 group-hover:text-amber-600 transition-colors">User Management</span>
              <svg class="w-4 h-4 text-gray-300 group-hover:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </Link>
          </nav>
        </div>

        <div class="bg-indigo-600 rounded-3xl shadow-lg p-8 text-white relative overflow-hidden">
          <div class="relative z-10">
            <h4 class="text-lg font-black uppercase tracking-widest mb-2">Need Help?</h4>
            <p class="text-indigo-100 text-xs leading-relaxed mb-6">Check out our documentation or contact technical support for assistance.</p>
            <button class="w-full py-3 bg-white text-indigo-600 font-black rounded-xl text-xs uppercase tracking-widest hover:bg-indigo-50 transition-all">Documentation</button>
          </div>
          <!-- Decorative SVG -->
          <svg class="absolute -bottom-10 -right-10 w-40 h-40 text-indigo-500/20" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
          </svg>
        </div>
      </div>
    </div>
  </div>
</template>
