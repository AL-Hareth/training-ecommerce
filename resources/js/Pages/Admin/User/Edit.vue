<script setup lang="ts">
import { ref } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import { defineProps } from 'vue'

const props = defineProps<{
  user: {
	id: string | number
	name: string
	email: string
	role?: string | null
  }
}>()

const availableRoles = ref(['user', 'vendor', 'admin'])

const form = useForm({
  role: props.user.role ?? 'user',
})

function submitForm() {
  form.put(`/admin/users/${props.user.id}`)
}

function resetForm() {
  form.role = props.user.role ?? 'user'
}
</script>

<template>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center p-6">
	<div class="w-full max-w-xl">
	  <div class="bg-white shadow-md rounded-lg p-8">
		<header class="mb-6">
		  <div class="flex items-start gap-4">
			<Link href="/admin/users" class="inline-flex items-center px-3 py-2 rounded-md bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 shadow">
			  <svg class="-ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
			  </svg>
			  <span class="text-sm font-medium">Back</span>
			</Link>

			<div>
			  <h1 class="text-2xl font-semibold text-gray-800">Edit User Role</h1>
			  <p class="text-sm text-gray-500 mt-1">Change only the role for this user.</p>
			</div>
		  </div>
		</header>

		<form @submit.prevent="submitForm" novalidate>
		  <div class="space-y-4">
			<div>
			  <label class="block text-sm font-medium text-gray-700">Name</label>
			  <div class="mt-1 text-gray-800">{{ props.user.name }}</div>
			</div>

			<div>
			  <label class="block text-sm font-medium text-gray-700">Email</label>
			  <div class="mt-1 text-gray-800 break-words">{{ props.user.email }}</div>
			</div>

			<div>
			  <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
			  <div class="mt-1">
				<select id="role" v-model="form.role" class="block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
				  <option v-for="r in availableRoles" :key="r" :value="r">{{ r }}</option>
				</select>
			  </div>
			  <p v-if="form.errors.role" class="mt-2 text-sm text-red-600">{{ form.errors.role }}</p>
			</div>

			<div class="flex items-center justify-end gap-3 mt-2">
			  <button type="button" class="px-4 py-2 rounded-md bg-white border border-gray-200 text-gray-700 hover:bg-gray-50" @click.prevent="resetForm">Reset</button>

			  <button type="submit" :disabled="form.processing" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-60">
				<svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
				  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
				  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
				</svg>
				<span>{{ form.processing ? 'Saving...' : 'Update Role' }}</span>
			  </button>
			</div>
		  </div>
		</form>
	  </div>
	</div>
  </div>
</template>

<style scoped></style>
