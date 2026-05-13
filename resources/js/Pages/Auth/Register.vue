<script setup>
import { useForm, Link } from '@inertiajs/vue3'

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
})

const submit = () => {
    form.post('/register', {
        // Automatically clear passwords if submission fails
        onFinish: () => form.reset('password', 'password_confirmation'),
    })
}
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-100">
        <div class="bg-white p-8 rounded shadow-md w-96">
            <h1 class="text-2xl font-bold mb-6 text-center">Create an Account</h1>

            <form @submit.prevent="submit">
                <div class="mb-4">
                    <label class="block text-gray-700">Name</label>
                    <input v-model="form.name" type="text" class="w-full border rounded p-2 mt-1" required autofocus>
                    <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</div>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700">Email</label>
                    <input v-model="form.email" type="email" class="w-full border rounded p-2 mt-1" required>
                    <div v-if="form.errors.email" class="text-red-500 text-sm mt-1">{{ form.errors.email }}</div>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700">Password</label>
                    <input v-model="form.password" type="password" class="w-full border rounded p-2 mt-1" required>
                    <div v-if="form.errors.password" class="text-red-500 text-sm mt-1">{{ form.errors.password }}</div>
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700">Confirm Password</label>
                    <input v-model="form.password_confirmation" type="password" class="w-full border rounded p-2 mt-1" required>
                </div>

                <button type="submit" :disabled="form.processing" class="w-full bg-blue-600 text-white rounded p-2 hover:bg-blue-700 disabled:opacity-50">
                    Register
                </button>
            </form>

            <div class="mt-4 text-center text-sm">
                Already have an account?
                <Link href="/login" class="text-blue-600 hover:underline">Log in</Link>
            </div>
        </div>
    </div>
</template>
