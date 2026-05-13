<script setup lang="ts">
import { ref, watch, computed } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import { defineProps } from 'vue'

// props from server
const props = defineProps<{
  category: {
	id: string | number
	name: string
	slug: string
	description?: string | null
  }
}>()

// store initial values so we can reset to them
const initial = {
  name: props.category.name ?? '',
  slug: props.category.slug ?? '',
  description: props.category.description ?? '',
}

const form = useForm({
  name: initial.name,
  slug: initial.slug,
  description: initial.description,
})

function slugify(value: string) {
  return value
	.toString()
	.normalize('NFKD')
	.replace(/\s+/g, '-')
	.replace(/[^\w\-]+/g, '')
	.replace(/--+/g, '-')
	.replace(/^-+/, '')
	.replace(/-+$/, '')
	.toLowerCase()
}

const manualSlug = ref(false)
watch(
  () => form.name,
  (val) => {
	if (!manualSlug.value) {
	  form.slug = slugify(val)
	}
  }
)

function onSlugInput() {
  manualSlug.value = true
}

function submitForm() {
  form.put(`/admin/categories/${props.category.id}`)
}

function resetToInitial() {
  form.name = initial.name
  form.slug = initial.slug
  form.description = initial.description
  manualSlug.value = false
}

const descriptionCount = computed(() => (form.description || '').length)

</script>

<template>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center p-6">
	<div class="w-full max-w-2xl">
	  <div class="bg-white shadow-md rounded-lg p-8">
		<header class="mb-6">
		  <div class="flex items-start gap-4">
			<Link href="/admin/categories" class="inline-flex items-center px-3 py-2 rounded-md bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 shadow">
			  <svg class="-ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
			  </svg>
			  <span class="text-sm font-medium">Back</span>
			</Link>

			<div>
			  <h1 class="text-2xl font-semibold text-gray-800">Edit Category</h1>
			  <p class="text-sm text-gray-500 mt-1">Update the category details.</p>
			</div>
		  </div>
		</header>

		<form @submit.prevent="submitForm" novalidate>
		  <div class="grid grid-cols-1 gap-6">
			<div>
			  <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
			  <div class="mt-1">
				<input
				  id="name"
				  name="name"
				  type="text"
				  v-model="form.name"
				  class="block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
				  placeholder="e.g. Summer Collection"
				/>
			  </div>
			  <p v-if="form.errors.name" class="mt-2 text-sm text-red-600">{{ form.errors.name }}</p>
			</div>

			<div>
			  <label for="slug" class="block text-sm font-medium text-gray-700">Slug</label>
			  <div class="mt-1 flex items-center gap-3">
				<input
				  id="slug"
				  name="slug"
				  type="text"
				  v-model="form.slug"
				  @input="onSlugInput"
				  class="block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
				  placeholder="auto-generated-from-name"
				/>
				<button
				  type="button"
				  class="text-xs px-3 py-1 rounded-md bg-gray-100 text-gray-700 hover:bg-gray-200"
				  @click.prevent="form.slug = slugify(form.name); manualSlug = false"
				>
				  Regenerate
				</button>
			  </div>
			  <p v-if="form.errors.slug" class="mt-2 text-sm text-red-600">{{ form.errors.slug }}</p>
			</div>

			<div>
			  <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
			  <div class="mt-1">
				<textarea
				  id="description"
				  name="description"
				  v-model="form.description"
				  rows="4"
				  class="block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
				  placeholder="Short description for the category (optional)"
				></textarea>
			  </div>
			  <div class="flex justify-between items-center mt-2">
				<p v-if="form.errors.description" class="text-sm text-red-600">{{ form.errors.description }}</p>
				<p class="text-sm text-gray-400 ml-auto">{{ descriptionCount }} characters</p>
			  </div>
			</div>

			<div class="flex items-center justify-end gap-3 mt-2">
			  <button
				type="button"
				class="px-4 py-2 rounded-md bg-white border border-gray-200 text-gray-700 hover:bg-gray-50"
				@click.prevent="resetToInitial"
			  >
				Reset
			  </button>

			  <button
				type="submit"
				:disabled="form.processing"
				class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-60"
			  >
				<svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
				  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
				  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
				</svg>
				<span>{{ form.processing ? 'Saving...' : 'Update Category' }}</span>
			  </button>
			</div>
		  </div>
		</form>
	  </div>
	</div>
  </div>
</template>
