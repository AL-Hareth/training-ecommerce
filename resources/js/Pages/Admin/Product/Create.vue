<script setup lang="ts">
import { computed, ref } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'

const props = defineProps<{
  categories?: Array<{ id: string | number; name: string }>
  attributes?: Array<{
    id: string
    name: string
    values: Array<{ id: string; value: string }>
  }>
}>()

const form = useForm({
  name: '',
  description: '',
  category_id: null as string | number | null,
  price: null as number | null,
  stock: null as number | null,
  image: null as File | null,
  attribute_value_ids: [] as string[],
})

function toggleValue(valueId: string) {
  const idx = form.attribute_value_ids.indexOf(valueId)
  if (idx === -1) {
    form.attribute_value_ids.push(valueId)
  } else {
    form.attribute_value_ids.splice(idx, 1)
  }
}

function isChecked(valueId: string) {
  return form.attribute_value_ids.includes(valueId)
}

function onImageChange(e: Event) {
  const input = e.target as HTMLInputElement
  form.image = input.files && input.files[0] ? input.files[0] : null
}

function submitForm() {
  form.post('/admin/products')
}

function resetForm() {
  form.reset('name', 'description', 'category_id', 'price', 'stock', 'image')
  form.attribute_value_ids = []
}

const descriptionCount = computed(() => (form.description || '').length)
</script>

<template>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center p-6">
	<div class="w-full max-w-3xl">
	  <div class="bg-white shadow-md rounded-lg p-8">
		<header class="mb-6">
		  <div class="flex items-start gap-4">
			<Link href="/admin/products" class="inline-flex items-center px-3 py-2 rounded-md bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 shadow">
			  <svg class="-ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
			  </svg>
			  <span class="text-sm font-medium">Back</span>
			</Link>

			<div>
			  <h1 class="text-2xl font-semibold text-gray-800">Create Product</h1>
			  <p class="text-sm text-gray-500 mt-1">Add a new product to your catalog.</p>
			</div>
		  </div>
		</header>

		<form @submit.prevent="submitForm" novalidate>
		  <div class="grid grid-cols-1 gap-6">
			<div>
			  <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
			  <div class="mt-1">
				<input id="name" name="name" type="text" v-model="form.name" class="block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Product name" />
			  </div>
			  <p v-if="form.errors.name" class="mt-2 text-sm text-red-600">{{ form.errors.name }}</p>
			</div>

			<div>
			  <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
			  <div class="mt-1">
				<select id="category" v-model="form.category_id" class="block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
				  <option :value="null">Select a category</option>
				  <option v-for="cat in props.categories ?? []" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
				</select>
			  </div>
			  <p v-if="form.errors.category_id" class="mt-2 text-sm text-red-600">{{ form.errors.category_id }}</p>
			</div>

			<div class="grid grid-cols-2 gap-4">
			  <div>
				<label for="price" class="block text-sm font-medium text-gray-700">Price</label>
				<div class="mt-1">
				  <input id="price" name="price" type="number" step="0.01" v-model.number="form.price" class="block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="0.00" />
				</div>
				<p v-if="form.errors.price" class="mt-2 text-sm text-red-600">{{ form.errors.price }}</p>
			  </div>

			  <div>
				<label for="stock" class="block text-sm font-medium text-gray-700">Stock</label>
				<div class="mt-1">
				  <input id="stock" name="stock" type="number" v-model.number="form.stock" class="block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="0" />
				</div>
				<p v-if="form.errors.stock" class="mt-2 text-sm text-red-600">{{ form.errors.stock }}</p>
			  </div>
			</div>

			<div>
			  <label for="image" class="block text-sm font-medium text-gray-700">Image</label>
			  <div class="mt-1">
				<input id="image" name="image" type="file" accept="image/*" @change="onImageChange" class="block w-full text-sm text-gray-500" />
			  </div>
			  <p v-if="form.errors.image" class="mt-2 text-sm text-red-600">{{ form.errors.image }}</p>
			</div>

			<div>
			  <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
			  <div class="mt-1">
				<textarea id="description" name="description" v-model="form.description" rows="4" class="block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Short description "></textarea>
			  </div>
			  <div class="flex justify-between items-center mt-2">
				<p v-if="form.errors.description" class="text-sm text-red-600">{{ form.errors.description }}</p>
				<p class="text-sm text-gray-400 ml-auto">{{ descriptionCount }} characters</p>
			  </div>
			</div>

			<!-- Attribute Values -->
			<div v-if="(props.attributes ?? []).length > 0">
			  <label class="block text-sm font-medium text-gray-700 mb-2">Attributes</label>
			  <div class="space-y-4">
				<div
				  v-for="attr in props.attributes"
				  :key="attr.id"
				  class="rounded-md border border-gray-200 p-4"
				>
				  <p class="text-sm font-semibold text-gray-700 mb-2">{{ attr.name }}</p>
				  <div class="flex flex-wrap gap-2">
					<label
					  v-for="val in attr.values"
					  :key="val.id"
					  class="inline-flex items-center gap-1.5 cursor-pointer"
					>
					  <input
						type="checkbox"
						:value="val.id"
						:checked="isChecked(val.id)"
						@change="toggleValue(val.id)"
						class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
					  />
					  <span class="text-sm text-gray-700">{{ val.value }}</span>
					</label>
				  </div>
				</div>
			  </div>
			</div>

			<div class="flex items-center justify-end gap-3 mt-2">
			  <button type="button" class="px-4 py-2 rounded-md bg-white border border-gray-200 text-gray-700 hover:bg-gray-50" @click.prevent="resetForm">Reset</button>

			  <button type="submit" :disabled="form.processing" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-60">
				<svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
				  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
				  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
				</svg>
				<span>{{ form.processing ? 'Saving...' : 'Create Product' }}</span>
			  </button>
			</div>
		  </div>
		</form>
	  </div>
	</div>
  </div>
</template>

<style scoped></style>
