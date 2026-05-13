<script setup lang="ts">
import { ref } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'

const form = useForm({
  name: '',
  slug: '',
  values: [] as string[],
})

const newValue = ref('')

function addValue() {
  const v = newValue.value.trim()
  if (v && !form.values.includes(v)) {
    form.values.push(v)
  }
  newValue.value = ''
}

function removeValue(index: number) {
  form.values.splice(index, 1)
}

function submitForm() {
  form.post('/admin/attributes')
}
</script>

<template>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center p-6">
    <div class="w-full max-w-2xl">
      <div class="bg-white shadow-md rounded-lg p-8">
        <header class="mb-6">
          <div class="flex items-start gap-4">
            <Link href="/admin/attributes" class="inline-flex items-center px-3 py-2 rounded-md bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 shadow">
              <svg class="-ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
              <span class="text-sm font-medium">Back</span>
            </Link>
            <div>
              <h1 class="text-2xl font-semibold text-gray-800">New Attribute</h1>
              <p class="text-sm text-gray-500 mt-1">Define a filterable attribute and its allowed values.</p>
            </div>
          </div>
        </header>

        <form @submit.prevent="submitForm" novalidate class="space-y-6">
          <!-- Name -->
          <div>
            <label for="attr-name" class="block text-sm font-medium text-gray-700">Attribute Name</label>
            <input
              id="attr-name"
              v-model="form.name"
              type="text"
              placeholder="e.g. Color"
              class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
          </div>

          <!-- Slug (optional) -->
          <div>
            <label for="attr-slug" class="block text-sm font-medium text-gray-700">
              Slug <span class="text-gray-400 font-normal">(optional — auto-generated from name)</span>
            </label>
            <input
              id="attr-slug"
              v-model="form.slug"
              type="text"
              placeholder="e.g. color"
              class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
            <p v-if="form.errors.slug" class="mt-1 text-sm text-red-600">{{ form.errors.slug }}</p>
          </div>

          <!-- Values -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Values</label>
            <div class="flex gap-2 mb-3">
              <input
                v-model="newValue"
                type="text"
                placeholder="e.g. Red"
                class="flex-1 rounded-md border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                @keydown.enter.prevent="addValue"
              />
              <button
                type="button"
                @click="addValue"
                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm font-medium"
              >
                Add
              </button>
            </div>
            <div class="flex flex-wrap gap-2">
              <span
                v-for="(val, i) in form.values"
                :key="i"
                class="inline-flex items-center gap-1 px-3 py-1 bg-indigo-50 text-indigo-700 rounded-full text-sm"
              >
                {{ val }}
                <button type="button" @click="removeValue(i)" class="ml-1 text-indigo-400 hover:text-indigo-700 leading-none">&times;</button>
              </span>
              <span v-if="form.values.length === 0" class="text-sm text-gray-400">No values added yet</span>
            </div>
          </div>

          <div class="flex items-center justify-end gap-3 pt-2">
            <Link href="/admin/attributes" class="px-4 py-2 rounded-md bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 text-sm">Cancel</Link>
            <button
              type="submit"
              :disabled="form.processing"
              class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-60 text-sm font-medium"
            >
              {{ form.processing ? 'Saving...' : 'Create Attribute' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<style scoped></style>
