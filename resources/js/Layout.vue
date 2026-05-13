<script setup lang="ts">

import { Link, usePage } from "@inertiajs/vue3";
import { watch } from "vue";
import { toast, ToastifyContainer } from "vue3-toastify";
import "vue3-toastify/dist/index.css";

const page = usePage();
const user = (page.props as any).auth?.user ?? null;

watch(
  () => (page.props as any).flash?.toast,
  (toastData) => {
    if (toastData) {
      toast(toastData.message, { autoClose: 1000, type: toastData.type ?? 'success' });
    }
  },
  { immediate: true }
);
</script>

<template>
    <!-- Navbar -->
    <nav class="bg-white border-b border-gray-100 sticky top-0 z-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <div class="flex items-center">
            <Link href="/" class="text-2xl font-black text-indigo-600 tracking-tighter">
              Storefront.
            </Link>
          </div>
          <div class="flex items-center space-x-4">
            <template v-if="user">
                <Link href="/cart" class="inline-flex items-center px-3 py-2 rounded-lg border border-gray-200 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                    Cart
                </Link>
                <Link href="/orders" class="inline-flex items-center px-3 py-2 rounded-lg border border-gray-200 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                    Orders
                </Link>
              <Link
                href="/logout"
                method="post"
                as="button"
                class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors"
              >
                Log out
              </Link>
            </template>
            <template v-else>
              <Link href="/login" class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">Log in</Link>
              <Link href="/register" class="text-sm font-medium bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors shadow-sm">Sign up</Link>
            </template>
          </div>
        </div>
      </div>
    </nav>
    <slot />
    <ToastifyContainer />
</template>

<style scoped>

</style>
