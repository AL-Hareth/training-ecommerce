
<script setup lang="ts">
    import { defineProps, ref } from 'vue';
    import {Link, router} from "@inertiajs/vue3";

    // Define props passed from the server
    const props = defineProps<{
        categories: Array<{
            id: string;
            name: string;
            slug: string;
            description: string | null;
        }>;
        q?: string;
    }>();

    const searchTerm = ref(props.q ?? '');

    function submitSearch() {
        const q = searchTerm.value.trim();
        router.get('/admin/categories', q ? { q } : {}, {
            preserveState: true,
            replace: true,
        });
    }

    function clearSearch() {
        searchTerm.value = '';
        submitSearch();
    }
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex flex-col p-6">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center gap-4">
                <Link href="/admin" class="inline-flex items-center px-3 py-2 rounded-md bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 shadow">
                    <svg class="-ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    <span class="text-sm font-medium">Back</span>
                </Link>

                <h1 class="text-3xl font-bold text-gray-800">Category Management</h1>
            </div>

            <Link
                href="/admin/categories/create"
                class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded shadow"
            >
                Add Category
            </Link>
        </div>

        <form @submit.prevent="submitSearch" class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center">
            <label for="category-search" class="sr-only">Search categories</label>
            <input
                id="category-search"
                v-model="searchTerm"
                type="search"
                name="q"
                placeholder="Search categories..."
                class="w-full rounded-md border border-gray-300 bg-white px-4 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 sm:max-w-md"
            />
            <div class="flex gap-2">
                <button type="submit" class="rounded-md bg-blue-500 px-4 py-2 text-sm font-medium text-white shadow hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Search
                </button>
                <button v-if="props.q" type="button" @click="clearSearch" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Clear
                </button>
            </div>
        </form>

        <!-- Categories Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="table-auto w-full border-collapse">
                <thead>
                <tr class="bg-gray-100 text-left">
                    <th class="px-6 py-2 text-gray-700">#</th>
                    <th class="px-6 py-2 text-gray-700">Name</th>
                    <th class="px-6 py-2 text-gray-700">Slug</th>
                    <th class="px-6 py-2 text-gray-700">Description</th>
                    <th class="px-6 py-2 text-gray-700">Actions</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="(category, index) in categories" :key="category.id" class="hover:bg-gray-50">
                    <td class="px-6 py-4">{{ parseInt(index) + 1 }}</td>
                    <td class="px-6 py-4">{{ category.name }}</td>
                    <td class="px-6 py-4">{{ category.slug }}</td>
                    <td class="px-6 py-4">{{ category.description || 'N/A' }}</td>
                    <td class="px-6 py-4">
                        <div class="flex gap-2">
                            <!-- Edit Button -->
                            <Link
                                :href="`/admin/categories/${category.id}/edit`"
                                class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded shadow"
                            >
                                Edit
                            </Link>

                            <!-- Delete Button -->
                            <Link
                                :href="`/admin/categories/${category.id}`"
                                method="delete"
                                as="button"
                                class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded shadow"
                            >
                                Delete
                            </Link>
                        </div>
                    </td>
                </tr>
                <tr v-if="categories.length === 0">
                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">No categories found.</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
