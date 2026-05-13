<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3'

const props = defineProps<{
  attributes: Array<{
    id: string
    name: string
    slug: string
    values: Array<{ id: string; value: string }>
  }>
}>()

function destroy(id: string) {
  if (confirm('Delete this attribute and all its values?')) {
    router.delete(`/admin/attributes/${id}`)
  }
}
</script>

<template>
  <div class="min-h-screen bg-gray-50 p-6">
    <div class="max-w-4xl mx-auto">
      <header class="flex items-center justify-between mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-800">Attributes</h1>
          <p class="text-sm text-gray-500 mt-1">Manage filterable product attributes</p>
        </div>
        <Link
          href="/admin/attributes/create"
          class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm font-medium shadow-sm"
        >
          + New Attribute
        </Link>
      </header>

      <div v-if="props.attributes.length === 0" class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
        No attributes yet. Create one to enable faceted search.
      </div>

      <div v-else class="space-y-4">
        <div
          v-for="attr in props.attributes"
          :key="attr.id"
          class="bg-white rounded-lg shadow p-5"
        >
          <div class="flex items-start justify-between">
            <div>
              <h2 class="text-md font-semibold text-gray-800">{{ attr.name }}</h2>
              <p class="text-xs text-gray-400 mt-0.5">slug: {{ attr.slug }}</p>
              <div class="mt-3 flex flex-wrap gap-2">
                <span
                  v-for="val in attr.values"
                  :key="val.id"
                  class="inline-block px-2.5 py-1 bg-indigo-50 text-indigo-700 rounded-full text-xs font-medium"
                >
                  {{ val.value }}
                </span>
                <span v-if="attr.values.length === 0" class="text-xs text-gray-400">No values yet</span>
              </div>
            </div>
            <div class="flex gap-2 ml-4 shrink-0">
              <Link
                :href="`/admin/attributes/${attr.id}/edit`"
                class="inline-flex items-center px-3 py-1.5 rounded-md border border-gray-200 bg-white text-sm text-gray-700 hover:bg-gray-50"
              >
                Edit
              </Link>
              <button
                type="button"
                @click="destroy(attr.id)"
                class="inline-flex items-center px-3 py-1.5 rounded-md border border-red-200 bg-white text-sm text-red-600 hover:bg-red-50"
              >
                Delete
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped></style>
