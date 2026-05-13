<script setup lang="ts">
import { usePage, Link } from '@inertiajs/vue3'


const page = usePage()
const user = (page.props as any).auth?.user ?? null
</script>

<template>
  <div class="min-h-screen bg-gray-50 flex flex-col font-sans text-gray-900">

    <!-- Hero Section -->
    <main class="flex-grow">
      <div class="relative bg-white overflow-hidden">
        <div class="max-w-7xl mx-auto">
          <div class="relative z-10 pb-8 bg-white sm:pb-16 md:pb-20 lg:max-w-2xl lg:w-full lg:pb-28 xl:pb-32 pt-12 sm:pt-16 lg:pt-24">
            <div class="mt-10 mx-auto max-w-7xl px-4 sm:mt-12 sm:px-6 md:mt-16 lg:mt-20 lg:px-8 xl:mt-28">
              <div class="sm:text-center lg:text-left">
                <h1 class="text-4xl tracking-tight font-extrabold text-gray-900 sm:text-5xl md:text-6xl">
                  <span class="block xl:inline">Discover amazing </span>
                  <span class="block text-indigo-600 xl:inline">products today</span>
                </h1>
                <p class="mt-3 text-base text-gray-500 sm:mt-5 sm:text-lg sm:max-w-xl sm:mx-auto md:mt-5 md:text-xl lg:mx-0">
                  Shop the latest trends, explore unique collections, and find exactly what you're looking for with our curated selection of high-quality items.
                </p>
                <div class="mt-5 sm:mt-8 sm:flex sm:justify-center lg:justify-start">
                  <div class="rounded-md shadow">
                    <Link href="/products" class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 md:py-4 md:text-lg md:px-10 transition-all duration-200">
                      Browse Products
                    </Link>
                  </div>
                  <div class="mt-3 sm:mt-0 sm:ml-3">
                    <Link
                      v-if="user && (user.role === 'admin' || user.role === 'vendor')"
                      href="/admin"
                      class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-lg text-indigo-700 bg-indigo-100 hover:bg-indigo-200 md:py-4 md:text-lg md:px-10 transition-all duration-200"
                    >
                      Manage Store
                    </Link>
                    <Link
                      v-else
                      href="/about"
                      class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-lg text-indigo-700 bg-indigo-100 hover:bg-indigo-200 md:py-4 md:text-lg md:px-10 transition-all duration-200"
                    >
                      Learn More
                    </Link>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="lg:absolute lg:inset-y-0 lg:right-0 lg:w-1/2 bg-indigo-50">
          <img class="h-56 w-full object-cover sm:h-72 md:h-96 lg:w-full lg:h-full opacity-90 mix-blend-multiply" src="https://images.unsplash.com/photo-1523275335684-37898b6baf30?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80" alt="Premium products display">
        </div>
      </div>

      <!-- Features Section -->
      <div class="bg-gray-50 py-16 sm:py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="text-center">
            <h2 class="text-base font-semibold text-indigo-600 tracking-wide uppercase">Why choose us</h2>
            <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
              A better way to shop
            </p>
            <p class="mt-4 max-w-2xl text-xl text-gray-500 mx-auto">
              We provide the best shopping experience with fast delivery, secure payments, and top-notch customer support.
            </p>
          </div>

          <div class="mt-16">
            <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
              <!-- Feature 1 -->
              <div class="bg-white pt-6 px-6 pb-8 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center mb-6">
                  <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Premium Quality</h3>
                <p class="text-gray-500">Every product is carefully vetted to ensure it meets our strict quality standards.</p>
              </div>

              <!-- Feature 2 -->
              <div class="bg-white pt-6 px-6 pb-8 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center mb-6">
                  <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Fast Delivery</h3>
                <p class="text-gray-500">Get your items quickly with our expedited shipping options across the globe.</p>
              </div>

              <!-- Feature 3 -->
              <div class="bg-white pt-6 px-6 pb-8 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center mb-6">
                  <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Secure Payments</h3>
                <p class="text-gray-500">Shop with confidence knowing your payment details are encrypted and secure.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-auto">
      <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
        <p class="text-sm text-gray-400">
          &copy; {{ new Date().getFullYear() }} Storefront, Inc. All rights reserved.
        </p>
        <div class="flex space-x-6">
          <Link href="/terms" class="text-sm text-gray-400 hover:text-gray-500">Terms</Link>
          <Link href="/privacy" class="text-sm text-gray-400 hover:text-gray-500">Privacy</Link>
        </div>
      </div>
    </footer>
  </div>
</template>
