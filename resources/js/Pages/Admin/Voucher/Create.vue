<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3'

const form = useForm({
  code: '',
  discount_type: 'percentage',
  discount_value: null as number | null,
  min_spend: null as number | null,
  usage_limit: null as number | null,
  starts_at: '',
  expires_at: '',
  is_active: true,
})

function submitForm() {
  form.post('/admin/vouchers')
}
</script>

<template>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center p-6">
    <div class="w-full max-w-2xl">
      <div class="bg-white shadow-md rounded-lg p-8">
        <header class="mb-6 flex items-center gap-4">
          <Link href="/admin/vouchers" class="inline-flex items-center px-3 py-2 rounded-md bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 shadow">
            Back
          </Link>
          <h1 class="text-2xl font-semibold text-gray-800">Create Voucher</h1>
        </header>

        <form @submit.prevent="submitForm" novalidate>
          <div class="space-y-4">
            <div>
              <label for="code" class="block text-sm font-medium text-gray-700">Code</label>
              <input id="code" v-model="form.code" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm uppercase" required />
              <p v-if="form.errors.code" class="mt-1 text-sm text-red-600">{{ form.errors.code }}</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label for="discount_type" class="block text-sm font-medium text-gray-700">Type</label>
                <select id="discount_type" v-model="form.discount_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                  <option value="percentage">Percentage</option>
                  <option value="fixed">Fixed Amount</option>
                </select>
                <p v-if="form.errors.discount_type" class="mt-1 text-sm text-red-600">{{ form.errors.discount_type }}</p>
              </div>

              <div>
                <label for="discount_value" class="block text-sm font-medium text-gray-700">Value</label>
                <input id="discount_value" v-model.number="form.discount_value" type="number" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required />
                <p v-if="form.errors.discount_value" class="mt-1 text-sm text-red-600">{{ form.errors.discount_value }}</p>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label for="min_spend" class="block text-sm font-medium text-gray-700">Min Spend (Optional)</label>
                <input id="min_spend" v-model.number="form.min_spend" type="number" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                <p v-if="form.errors.min_spend" class="mt-1 text-sm text-red-600">{{ form.errors.min_spend }}</p>
              </div>

              <div>
                <label for="usage_limit" class="block text-sm font-medium text-gray-700">Usage Limit (Optional)</label>
                <input id="usage_limit" v-model.number="form.usage_limit" type="number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                <p v-if="form.errors.usage_limit" class="mt-1 text-sm text-red-600">{{ form.errors.usage_limit }}</p>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label for="starts_at" class="block text-sm font-medium text-gray-700">Starts At (Optional)</label>
                <input id="starts_at" v-model="form.starts_at" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                <p v-if="form.errors.starts_at" class="mt-1 text-sm text-red-600">{{ form.errors.starts_at }}</p>
              </div>

              <div>
                <label for="expires_at" class="block text-sm font-medium text-gray-700">Expires At (Optional)</label>
                <input id="expires_at" v-model="form.expires_at" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                <p v-if="form.errors.expires_at" class="mt-1 text-sm text-red-600">{{ form.errors.expires_at }}</p>
              </div>
            </div>

            <div class="flex items-center">
              <input id="is_active" v-model="form.is_active" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" />
              <label for="is_active" class="ml-2 block text-sm text-gray-900">Active</label>
            </div>

            <div class="flex justify-end">
              <button type="submit" :disabled="form.processing" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Save Voucher
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
