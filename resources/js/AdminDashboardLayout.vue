<script setup lang="ts">
import { ref, computed } from 'vue'
import { Link, usePage } from "@inertiajs/vue3";

const page = usePage()
const user = (page.props as any).auth?.user ?? null
const role = computed(() => user?.role ?? null)

const sidebarOpen = ref(false)

const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value
}

const canSeeCategories = computed(() => role.value === 'admin')
const canSeeProducts = computed(() => role.value === 'admin' || role.value === 'vendor')
const canSeeOrders = computed(() => role.value === 'admin' || role.value === 'vendor')
const canSeeUsers = computed(() => role.value === 'admin')
const canSeeAttributes = computed(() => role.value === 'admin')
const canSeeVouchers = computed(() => role.value === 'admin' || role.value === 'vendor')

const navigationItems = computed(() => [
  {
    name: 'Dashboard',
    href: '/admin',
    icon: 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z',
    show: true
  },
  {
    name: 'Categories',
    href: '/admin/categories',
    icon: 'M3 7h18M3 12h18M3 17h18',
    show: canSeeCategories.value
  },
  {
    name: 'Products',
    href: '/admin/products',
    icon: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10',
    show: canSeeProducts.value
  },
  {
    name: 'Orders',
    href: '/admin/orders',
    icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
    show: canSeeOrders.value
  },
  {
    name: 'Users',
    href: '/admin/users',
    icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-4.596a3 3 0 11-6 0 3 3 0 016 0z',
    show: canSeeUsers.value
  },
  {
      name: 'Attributes',
      href: '/admin/attributes',
      icon: 'M20 7H4a2 2 0 00-2 2v6a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2zM12 3v4M8 3v4M16 3v4M8 12h.01M12 12h4',
      show: canSeeAttributes.value
  },
  {
      name: 'Vouchers',
      href: '/admin/vouchers',
      icon: 'M2 7a1 1 0 0 1 1-1h16a1 1 0 0 1 1 1v3a2 2 0 0 0 0 4v3a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1v-3a2 2 0 0 0 0-4V7z M8 6 L8 8.5 M8 10.5 L8 13.5 M8 15.5 L8 18',
      show: canSeeAttributes.value
  }
].filter(item => item.show))
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Admin Navbar -->
    <nav class="bg-white border-b border-gray-200 fixed w-full top-0 z-30">
      <div class="px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">
          <div class="flex items-center">
            <!-- Sidebar toggle button -->
            <button
              @click="toggleSidebar"
              class="p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500 lg:hidden"
            >
              <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              </svg>
            </button>
            <Link href="/admin" class="text-xl font-bold text-indigo-600 ml-4 lg:ml-0">Admin Panel</Link>
          </div>

          <div class="flex items-center space-x-3">
            <Link href="/" class="text-sm text-gray-500 hover:text-gray-700">← Back to Store</Link>
            <template v-if="user">
              <span class="text-sm text-gray-700">{{ user.name }}</span>
              <Link
                href="/logout"
                method="post"
                as="button"
                class="inline-flex items-center px-3 py-2 bg-white border border-gray-200 rounded-md text-gray-700 hover:bg-gray-50"
              >Logout</Link>
            </template>
          </div>
        </div>
      </div>
    </nav>

    <!-- Mobile sidebar overlay -->
    <div
      v-if="sidebarOpen"
      @click="toggleSidebar"
      class="fixed inset-0 bg-gray-600 bg-opacity-75 z-40 lg:hidden"
    ></div>

    <!-- Sidebar -->
    <div class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-lg transform transition-transform duration-300 ease-in-out lg:translate-x-0"
         :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">

      <!-- Sidebar content -->
      <div class="flex flex-col h-full bg-white pt-16">
        <div class="flex-1 flex flex-col pt-5 pb-4 overflow-y-auto">
          <nav class="mt-5 flex-1 px-2 space-y-1">
            <Link
              v-for="item in navigationItems"
              :key="item.name"
              :href="item.href"
              @click="sidebarOpen = false"
              class="group flex items-center px-2 py-2 text-sm font-medium rounded-md text-gray-700 hover:text-gray-900 hover:bg-gray-50 transition-colors duration-150"
              :class="{ 'bg-gray-100 text-gray-900': $page.url.startsWith(item.href) && item.href !== '/admin' || ($page.url === '/admin' && item.href === '/admin') }"
            >
              <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-gray-500"
                   :class="{ 'text-gray-500': $page.url.startsWith(item.href) && item.href !== '/admin' || ($page.url === '/admin' && item.href === '/admin') }"
                   fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
              </svg>
              {{ item.name }}
            </Link>
          </nav>
        </div>
      </div>
    </div>

    <!-- Main content -->
    <div class="lg:ml-64">
      <main class="pt-16 min-h-screen">
        <div class="p-6">
          <slot />
        </div>
      </main>
    </div>
  </div>
</template>

<style scoped>
/* Additional responsive styles if needed */
</style>
