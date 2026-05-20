<script setup lang="ts">
import {computed, ref, onMounted, onUnmounted, reactive} from 'vue'
import { Link, router } from '@inertiajs/vue3'
import DiscountCountdown from '@/Components/DiscountCountdown.vue'

const props = defineProps<{
  products: Array<{
    id: string | number
    name: string
    price?: number | string | null
    discount_type?: string | null
    discount_value?: number | null
    discounted_price?: number | null
    discount_expiration?: string | null
    image?: string
    category?: { id?: string | number; name?: string } | null
    variants?: Array<{ price: number }>
  }>
  page?: number
  limit?: number
  total?: number
  q?: string
  attributes?: Array<{
    id: string
    name: string
    values: Array<{ id: string; value: string, count: number }>
  }>
  activeAttributes?: string[]
}>()

const showFullAttributeValues = reactive<Record<string, boolean>>({}); 
const NUMBER_OF_SHOWN_ATTRIBUTES = 5;

// ── Pagination ────────────────────────────────────────────────────────────────
const currentPage = computed(() => Number(props.page ?? 1))
const perPage     = computed(() => Number(props.limit ?? 12))
const totalItems  = computed(() => Number(props.total ?? (props.products || []).length))
const lastPage    = computed(() => Math.max(1, Math.ceil(totalItems.value / perPage.value)))
const hasNext     = computed(() => currentPage.value < lastPage.value)

// ── Mobile Sidebar ────────────────────────────────────────────────────────────
const showMobileFilters = ref(false)
const isMobile = ref(false)

function checkMobile() {
  isMobile.value = window.innerWidth < 768
  if (!isMobile.value) {
    showMobileFilters.value = false
  }
}

function openMobileFilters() {
  showMobileFilters.value = true
  document.body.style.overflow = 'hidden'
}

function closeMobileFilters() {
  showMobileFilters.value = false
  document.body.style.overflow = ''
}

onMounted(() => {
    (props.attributes ?? []).forEach(function (attr) {
        showFullAttributeValues[attr.id] = false;
    });
  checkMobile()
  window.addEventListener('resize', checkMobile)
})

onUnmounted(() => {
  window.removeEventListener('resize', checkMobile)
})

// ── Filters ───────────────────────────────────────────────────────────────────
const searchTerm       = ref(props.q ?? '')
const selectedAttributes = ref<string[]>([...(props.activeAttributes ?? [])])

function toggleAttribute(valueId: string) {
  const idx = selectedAttributes.value.indexOf(valueId)
  if (idx === -1) {
    selectedAttributes.value.push(valueId)
  } else {
    selectedAttributes.value.splice(idx, 1)
  }
  applyFilters(1)
}

function clearAllFilters() {
  selectedAttributes.value = []
  searchTerm.value = ''
  applyFilters(1)
}

function applyFilters(page = currentPage.value) {
  const q = searchTerm.value.trim()
  router.get('/products', {
    ...(q ? { q } : {}),
    ...(selectedAttributes.value.length ? { attributes: selectedAttributes.value } : {}),
    page,
    limit: perPage.value,
  }, {
    preserveScroll: true,
    replace: true,
  })
}

// ── Pagination helpers ────────────────────────────────────────────────────────
function makePageRange() {
  const maxButtons = 5
  let start = Math.max(1, currentPage.value - 2)
  let end   = Math.min(lastPage.value, start + maxButtons - 1)
  if (end - start < maxButtons - 1) start = Math.max(1, end - maxButtons + 1)
  const pages: number[] = []
  for (let i = start; i <= end; i++) pages.push(i)
  return pages
}

function buildUrl(page: number) {
  if (typeof window === 'undefined') return `/products?page=${page}&limit=${perPage.value}`
  const url    = new URL(window.location.href)
  const params = url.searchParams
  params.set('page', String(page))
  params.set('limit', String(perPage.value))
  
  // Clear existing attributes to avoid duplicates if appending
  params.delete('attributes[]')
  selectedAttributes.value.forEach(id => params.append('attributes[]', id))
  
  const q = searchTerm.value.trim()
  if (q) params.set('q', q); else params.delete('q')
  return `${url.pathname}?${params.toString()}${url.hash ?? ''}`
}

function addToCart(product: { id: string | number } | any) {
  router.post('/cart/add', { product_id: product.id, quantity: 1 }, { preserveScroll: true })
}

function handleExpired() {
  router.reload({ preserveScroll: true })
}

const hasActiveFilters = computed(
  () => selectedAttributes.value.length > 0 || (props.q ?? '').trim() !== ''
)

function getImageUrl(product: { image?: string | null }): string {
  const img = product.image
  if (!img) return ''
  if (typeof img === 'string' && img.startsWith('http')) return img
  return `/storage/${img}`
}

function getPriceDisplay(product: any) {
  if (product.variants?.length) {
    const prices = product.variants.map((v: any) => v.price)
    const min = Math.min(...prices)
    const max = Math.max(...prices)
    if (min === max) return `$${min.toFixed(2)}`
    return `$${min.toFixed(2)} - $${max.toFixed(2)}`
  }
  const price = product.discounted_price ?? product.price ?? 0
  return `$${Number(price).toFixed(2)}`
}
</script>

<template>
  <div class="min-h-screen bg-gray-50 flex flex-col">
    <!-- Hero Banner -->
    <div class="bg-indigo-600 py-12 px-6 text-center text-white mb-8">
      <h1 class="text-4xl font-black tracking-tight mb-2">SHOP OUR COLLECTION</h1>
      <p class="text-indigo-100 text-lg">Quality products for your everyday needs</p>
    </div>

    <div class="max-w-7xl mx-auto w-full px-6 pb-12">
      <!-- Search & Mobile Filter Toggle -->
      <div class="flex flex-col md:flex-row gap-4 mb-8">
        <form @submit.prevent="applyFilters(1)" class="flex-1 relative group">
          <input
            v-model="searchTerm"
            type="search"
            placeholder="Search for products..."
            class="w-full h-12 pl-12 pr-4 rounded-xl border-none shadow-sm ring-1 ring-gray-200 focus:ring-2 focus:ring-indigo-500 transition-all text-sm"
          />
          <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-indigo-500 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
        </form>

        <button
          v-if="(props.attributes ?? []).length > 0"
          type="button"
          @click="openMobileFilters"
          class="md:hidden h-12 px-6 rounded-xl bg-white border border-gray-200 flex items-center justify-center gap-2 font-bold text-gray-700 shadow-sm active:scale-95 transition-all"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
          </svg>
          Filters
          <span v-if="selectedAttributes.length > 0" class="w-5 h-5 bg-indigo-600 text-white rounded-full text-[10px] flex items-center justify-center">{{ selectedAttributes.length }}</span>
        </button>
      </div>

      <div class="flex gap-8">
        <!-- Desktop Sidebar Filters -->
        <aside v-if="(props.attributes ?? []).length > 0" class="hidden md:block w-64 shrink-0">
          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-6">
            <div class="flex items-center justify-between mb-6">
              <h2 class="text-sm font-black text-gray-900 uppercase tracking-widest">Filters</h2>
              <button v-if="hasActiveFilters" @click="clearAllFilters" class="text-[10px] font-bold text-indigo-600 hover:text-indigo-800 uppercase underline">Clear</button>
            </div>

            <div class="space-y-8">
              <div v-for="attr in props.attributes" :key="attr.id">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-4">{{ attr.name }}</p>
                <div class="space-y-3">
                  <label
                    v-for="val in (showFullAttributeValues[attr.id] ? attr.values : attr.values.slice(0, NUMBER_OF_SHOWN_ATTRIBUTES))"
                    :key="val.id"
                    class="flex items-center group cursor-pointer"
                  >
                    <div class="relative flex items-center">
                      <input
                        type="checkbox"
                        :value="val.id"
                        :checked="selectedAttributes.includes(val.id)"
                        @change="toggleAttribute(val.id)"
                        class="peer h-5 w-5 rounded-md border-gray-200 text-indigo-600 focus:ring-indigo-500 transition-all cursor-pointer"
                      />
                    </div>
                    <span class="ml-3 text-sm text-gray-600 group-hover:text-gray-900 transition-colors" :class="{'font-bold text-gray-900': selectedAttributes.includes(val.id)}">
                      {{ val.value }}
                    </span>
                    <span class="ml-auto text-[10px] font-bold text-gray-300">{{ val.count }}</span>
                  </label>
                  
                  <button
                    v-if="attr.values.length > NUMBER_OF_SHOWN_ATTRIBUTES"
                    @click="showFullAttributeValues[attr.id] = !showFullAttributeValues[attr.id]"
                    class="text-[10px] font-bold text-indigo-500 hover:text-indigo-700 uppercase tracking-wide"
                  >
                    {{ showFullAttributeValues[attr.id] ? 'Show Less' : `+ ${attr.values.length - NUMBER_OF_SHOWN_ATTRIBUTES} More` }}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </aside>

        <!-- Product Grid Area -->
        <div class="flex-1 min-w-0">
          <!-- Active Chips -->
          <div v-if="selectedAttributes.length > 0" class="flex flex-wrap gap-2 mb-6">
            <template v-for="attr in props.attributes" :key="attr.id">
              <template v-for="val in attr.values" :key="val.id">
                <button
                  v-if="selectedAttributes.includes(val.id)"
                  @click="toggleAttribute(val.id)"
                  class="inline-flex items-center gap-2 px-3 py-1.5 bg-white border border-indigo-100 text-indigo-700 rounded-full text-xs font-bold shadow-sm hover:border-red-200 hover:text-red-600 transition-all group"
                >
                  {{ attr.name }}: {{ val.value }}
                  <span class="text-indigo-300 group-hover:text-red-400">×</span>
                </button>
              </template>
            </template>
          </div>

          <div v-if="(props.products || []).length === 0" class="bg-white rounded-3xl border-2 border-dashed border-gray-100 p-20 text-center">
            <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 9.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-800">No products found</h3>
            <p class="text-gray-500 mt-2">Try adjusting your search or filters to find what you're looking for.</p>
            <button @click="clearAllFilters" class="mt-6 text-indigo-600 font-bold underline">Clear all filters</button>
          </div>

          <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <div v-for="product in props.products" :key="product.id" class="group bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
              <!-- Image Container -->
              <Link :href="`/products/${product.id}`" class="relative aspect-w-1 aspect-h-1 bg-gray-50 overflow-hidden">
                <img
                  v-if="product.image"
                  :src="getImageUrl(product)"
                  class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                />
                <div v-else class="w-full h-full flex items-center justify-center text-gray-200">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                </div>
                
                <!-- Badge -->
                <div v-if="product.discount_type" class="absolute top-3 left-3 px-2 py-1 bg-red-500 text-white text-[10px] font-black rounded uppercase shadow-lg">
                  {{ product.discount_type === 'percentage' ? `${product.discount_value}% OFF` : 'Sale' }}
                </div>
                <div v-if="product.variants?.length" class="absolute top-3 right-3 px-2 py-1 bg-indigo-600 text-white text-[10px] font-black rounded uppercase shadow-lg opacity-0 group-hover:opacity-100 transition-opacity">
                  Multiple Options
                </div>
              </Link>

              <!-- Content -->
              <div class="p-5 flex-1 flex flex-col">
                <div class="mb-4">
                  <p class="text-[10px] font-bold text-indigo-400 uppercase tracking-widest mb-1">{{ product.category?.name ?? 'General' }}</p>
                  <Link :href="`/products/${product.id}`">
                    <h3 class="text-sm font-bold text-gray-900 group-hover:text-indigo-600 transition-colors line-clamp-2 min-h-[2.5rem] leading-tight">{{ product.name }}</h3>
                  </Link>
                  <div v-if="product.discount_expiration" class="mt-2">
                    <DiscountCountdown :expiration="product.discount_expiration" @expired="handleExpired" />
                  </div>
                </div>

                <div class="mt-auto pt-4 border-t border-gray-50 flex items-center justify-between">
                  <div class="flex flex-col">
                    <span class="text-lg font-black text-gray-900">{{ getPriceDisplay(product) }}</span>
                    <span v-if="product.discounted_price && product.discounted_price < (product.price || 0)" class="text-xs text-gray-400 line-through">
                      ${{ Number(product.price).toFixed(2) }}
                    </span>
                  </div>
                  
                  <button
                    @click.prevent="addToCart(product)"
                    class="w-10 h-10 rounded-full bg-gray-50 text-gray-900 flex items-center justify-center hover:bg-indigo-600 hover:text-white transition-all shadow-sm active:scale-90"
                    title="Add to Cart"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Pagination -->
          <div v-if="lastPage > 1" class="mt-12 flex justify-center">
            <nav class="flex items-center gap-1">
              <Link
                :href="buildUrl(Math.max(1, currentPage - 1))"
                class="w-10 h-10 rounded-full flex items-center justify-center border border-gray-200 bg-white text-gray-500 hover:border-indigo-500 hover:text-indigo-500 transition-all"
                :class="{'opacity-50 pointer-events-none': currentPage === 1}"
              >
                ←
              </Link>
              <div class="flex items-center gap-1 mx-2">
                <template v-for="p in makePageRange()" :key="p">
                  <Link
                    :href="buildUrl(p)"
                    :class="[
                      'w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold transition-all',
                      p === currentPage 
                        ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-100' 
                        : 'bg-white border border-gray-200 text-gray-600 hover:border-indigo-500 hover:text-indigo-500'
                    ]"
                  >
                    {{ p }}
                  </Link>
                </template>
              </div>
              <Link
                :href="buildUrl(Math.min(lastPage, currentPage + 1))"
                class="w-10 h-10 rounded-full flex items-center justify-center border border-gray-200 bg-white text-gray-500 hover:border-indigo-500 hover:text-indigo-500 transition-all"
                :class="{'opacity-50 pointer-events-none': currentPage === lastPage}"
              >
                →
              </Link>
            </nav>
          </div>
        </div>
      </div>
    </div>

    <!-- Mobile Filters Drawer -->
    <Teleport to="body">
      <div v-if="showMobileFilters" class="fixed inset-0 z-[100] md:hidden">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="closeMobileFilters"></div>
        <div class="absolute inset-y-0 right-0 w-80 max-w-full bg-white flex flex-col shadow-2xl animate-in slide-in-from-right duration-300">
          <div class="p-6 border-b flex items-center justify-between">
            <h2 class="text-xl font-black text-gray-900 uppercase tracking-widest">Filters</h2>
            <button @click="closeMobileFilters" class="p-2 -mr-2 text-gray-400 hover:text-gray-900 transition-colors">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div class="flex-1 overflow-y-auto p-6 space-y-10">
            <div v-for="attr in props.attributes" :key="attr.id">
              <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-4">{{ attr.name }}</p>
              <div class="flex flex-wrap gap-2">
                <button
                  v-for="val in attr.values"
                  :key="val.id"
                  @click="toggleAttribute(val.id)"
                  :class="[
                    'px-4 py-2 rounded-xl text-xs font-bold border transition-all',
                    selectedAttributes.includes(val.id)
                      ? 'bg-indigo-600 border-indigo-600 text-white shadow-lg shadow-indigo-100'
                      : 'bg-gray-50 border-gray-100 text-gray-600'
                  ]"
                >
                  {{ val.value }} ({{ val.count }})
                </button>
              </div>
            </div>
          </div>

          <div class="p-6 border-t bg-gray-50 flex gap-3">
            <button @click="clearAllFilters" class="flex-1 h-12 bg-white border border-gray-200 rounded-xl text-sm font-bold text-gray-600">Reset</button>
            <button @click="closeMobileFilters" class="flex-1 h-12 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-lg shadow-indigo-100">Show Results</button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<style scoped>
.aspect-w-1 { position: relative; padding-top: 100%; }
.aspect-w-1 > img, .aspect-w-1 > div { position: absolute; top: 0; left: 0; width: 100%; height: 100%; }

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

@keyframes slide-in-right {
  from { transform: translateX(100%); }
  to { transform: translateX(0); }
}
.animate-in { animation: slide-in-right 0.3s ease-out; }
</style>
