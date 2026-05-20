<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3'

const props = defineProps<{
  vouchers: Array<{
    id: string
    code: string
    discount_type: string
    discount_value: number
    usage_limit?: number | null
    used_count: number
    starts_at?: string | null
    expires_at?: string | null
    is_active: boolean
  }>
}>()

function deleteVoucher(id: string) {
  if (confirm('Are you sure you want to delete this voucher?')) {
    router.delete(`/admin/vouchers/${id}`)
  }
}
</script>

<template>
  <div class="min-h-screen bg-gray-50 p-6">
    <div class="max-w-7xl mx-auto">
      <header class="mb-6 flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-800">Vouchers</h1>
          <p class="text-sm text-gray-500">Manage your store's promotional vouchers.</p>
        </div>
        <div>
            <Link href="/admin/dashboard" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 mr-2">
                Back to Dashboard
            </Link>
          <Link href="/admin/vouchers/create" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            Create Voucher
          </Link>
        </div>
      </header>

      <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <ul role="list" class="divide-y divide-gray-200">
          <li v-for="voucher in props.vouchers" :key="voucher.id">
            <div class="px-4 py-4 sm:px-6">
              <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-indigo-600 truncate uppercase">{{ voucher.code }}</p>
                <div class="ml-2 flex-shrink-0 flex">
                  <p :class="[voucher.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800', 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full']">
                    {{ voucher.is_active ? 'Active' : 'Inactive' }}
                  </p>
                </div>
              </div>
              <div class="mt-2 sm:flex sm:justify-between">
                <div class="sm:flex">
                  <p class="flex items-center text-sm text-gray-500">
                    <span v-if="voucher.discount_type === 'percentage'">{{ voucher.discount_value }}% OFF</span>
                    <span v-else>${{ voucher.discount_value }} OFF</span>
                  </p>
                  <p class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0 sm:ml-6">
                    Used: {{ voucher.used_count }} <span v-if="voucher.usage_limit"> / {{ voucher.usage_limit }}</span>
                  </p>
                </div>
                <div class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0 gap-3">
                  <Link :href="`/admin/vouchers/${voucher.id}/edit`" class="text-indigo-600 hover:text-indigo-900">Edit</Link>
                  <button @click="deleteVoucher(voucher.id)" class="text-red-600 hover:text-red-900">Delete</button>
                </div>
              </div>
            </div>
          </li>
          <li v-if="props.vouchers.length === 0" class="px-4 py-8 text-center text-gray-500">
            No vouchers created yet.
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>
