<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'
import {computed} from "vue";

const page = usePage()
const user = (page.props as any).auth?.user ?? null

const role = computed(() => user?.role ?? null)

const canSeeCategories = computed(() => role.value === 'admin')
const canSeeProducts = computed(() => role.value === 'admin' || role.value === 'vendor')
const canSeeOrders = computed(() => role.value === 'admin' || role.value === 'vendor')
const canSeeUsers = computed(() => role.value === 'admin')

// You can add props here if you want to pass statistics from the backend
defineProps<{
    totalProducts?: number
    totalOrders?: number
    totalUsers?: number
    totalCategories?: number
}>()
</script>

<template>
  <div>
    <!-- Dashboard Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-800">Dashboard</h1>
      <p class="text-sm text-gray-500 mt-1">Welcome back{{ user ? `, ${user.name}` : '' }} — manage your store from here.</p>
    </div>

    <!-- Dashboard Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div v-if="canSeeProducts" class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-indigo-500 rounded-md flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
              </svg>
            </div>
          </div>
          <div class="ml-5 w-0 flex-1">
            <dl>
              <dt class="text-sm font-medium text-gray-500 truncate">Total Products</dt>
              <dd class="text-lg font-medium text-gray-900">{{ totalProducts || 0 }}</dd>
            </dl>
          </div>
        </div>
      </div>

      <div v-if="canSeeOrders" class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
              </svg>
            </div>
          </div>
          <div class="ml-5 w-0 flex-1">
            <dl>
              <dt class="text-sm font-medium text-gray-500 truncate">Total Orders</dt>
              <dd class="text-lg font-medium text-gray-900">{{ totalOrders || 0 }}</dd>
            </dl>
          </div>
        </div>
      </div>

      <div v-if="canSeeUsers" class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-4.596a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
            </div>
          </div>
          <div class="ml-5 w-0 flex-1">
            <dl>
              <dt class="text-sm font-medium text-gray-500 truncate">Total Users</dt>
              <dd class="text-lg font-medium text-gray-900">{{ totalUsers || 0 }}</dd>
            </dl>
          </div>
        </div>
      </div>

      <div v-if="canSeeCategories" class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18"/>
              </svg>
            </div>
          </div>
          <div class="ml-5 w-0 flex-1">
            <dl>
              <dt class="text-sm font-medium text-gray-500 truncate">Categories</dt>
              <dd class="text-lg font-medium text-gray-900">{{ totalCategories || 0 }}</dd>
            </dl>
          </div>
        </div>
      </div>
    </div>

    <!-- Dashboard Content -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Recent Activity Card -->
      <div class="bg-white rounded-lg shadow">
        <div class="px-4 py-5 sm:p-6">
          <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Recent Activity</h3>
          <div class="text-sm text-gray-500">
            <p class="mb-2">No recent activity to display.</p>
            <p>Start managing your store using the navigation menu.</p>
          </div>
        </div>
      </div>

      <!-- Quick Actions Card -->
      <div class="bg-white rounded-lg shadow">
        <div class="px-4 py-5 sm:p-6">
          <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Quick Actions</h3>
          <div class="space-y-3">
            <p class="text-sm text-gray-500">Use the sidebar navigation to:</p>
            <ul class="text-sm text-gray-600 space-y-1 list-disc list-inside">
              <li>Manage product categories</li>
              <li>Add or edit products</li>
              <li>View and process orders</li>
              <li>Manage user accounts</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
