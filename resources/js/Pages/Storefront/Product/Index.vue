<script setup lang="ts">
import {computed, ref, onMounted, onUnmounted, reactive} from 'vue'
import { Link, router } from '@inertiajs/vue3'

const props = defineProps<{
  products: Array<{
    id: string | number
    name: string
    price?: number | string | null
    image?: string
    category?: { id?: string | number; name?: string } | null
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

const showFullAttributeValues = reactive<Record<string, boolean>>({}); // key [attibute.id] => boolean value [attribute shown or not]
const NUMBER_OF_SHOWN_ATTRIBUTES = 3;

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
  // Keep attribute & q params
  selectedAttributes.value.forEach(id => params.append('attributes[]', id))
  const q = searchTerm.value.trim()
  if (q) params.set('q', q); else params.delete('q')
  return `${url.pathname}?${params.toString()}${url.hash ?? ''}`
}

// ── Cart ──────────────────────────────────────────────────────────────────────
function addToCart(product: { id: string | number } | any) {
  router.post('/cart/add', { product_id: product.id, quantity: 1 }, { preserveScroll: true })
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
</script>

<template>
  <div class="min-h-screen bg-gray-50 p-6">
    <div class="max-w-7xl mx-auto">
      <header class="flex items-center justify-between mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-800">Products</h1>
          <p class="text-sm text-gray-500">Browse our latest products</p>
        </div>
        <button
          v-if="hasActiveFilters"
          type="button"
          @click="clearAllFilters"
          class="text-sm text-indigo-600 hover:text-indigo-800 underline"
        >
          Clear all filters
        </button>
      </header>

      <!-- Search bar with mobile filter button -->
      <div class="flex gap-3 mb-6">
        <form @submit.prevent="applyFilters(1)" class="flex-1 flex flex-col gap-3 sm:flex-row sm:items-center">
          <label for="product-search" class="sr-only">Search products</label>
          <input
            id="product-search"
            v-model="searchTerm"
            type="search"
            name="q"
            placeholder="Search products..."
            class="w-full rounded-md border border-gray-300 bg-white px-4 py-2 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
          />
          <div class="flex gap-2">
            <button
              type="submit"
              class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
            >
              Search
            </button>
            <button
              v-if="props.q"
              type="button"
              @click="clearAllFilters"
              class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500"
            >
              Clear
            </button>
          </div>
        </form>

        <!-- Mobile filter toggle button -->
        <button
          v-if="(props.attributes ?? []).length > 0"
          type="button"
          @click="openMobileFilters"
          class="md:hidden inline-flex items-center justify-center px-4 py-2 rounded-md border border-gray-300 bg-white text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
          </svg>
          Filters
          <span v-if="selectedAttributes.length > 0" class="ml-1 bg-indigo-600 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">{{ selectedAttributes.length }}</span>
        </button>
      </div>

      <!-- Mobile filter modal -->
      <Teleport to="body">
        <div
          v-if="showMobileFilters"
          class="fixed inset-0 z-50 md:hidden"
        >
          <!-- Backdrop -->
          <div
            class="absolute inset-0 bg-black/50"
            @click="closeMobileFilters"
          ></div>

          <!-- Modal panel -->
          <div class="absolute inset-y-0 left-0 w-80 max-w-full bg-white shadow-xl flex flex-col">
            <div class="flex items-center justify-between p-4 border-b">
              <h2 class="text-lg font-semibold text-gray-800">Filters</h2>
              <button
                type="button"
                @click="closeMobileFilters"
                class="p-2 text-gray-500 hover:text-gray-700 rounded-md hover:bg-gray-100"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <div class="flex-1 overflow-y-auto p-4">
              <div class="space-y-5">
                <div
                  v-for="attr in props.attributes"
                  :key="attr.id"
                >
                  <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">{{ attr.name }}</p>
                  <div class="space-y-1.5">
                    <label
                      v-for="val in (showFullAttributeValues[attr.id] ? attr.values : attr.values.slice(0, NUMBER_OF_SHOWN_ATTRIBUTES))"
                      :key="val.id"
                      class="flex items-center gap-2 cursor-pointer group"
                    >
                      <input
                        type="checkbox"
                        :value="val.id"
                        :checked="selectedAttributes.includes(val.id)"
                        @change="toggleAttribute(val.id)"
                        class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                      />
                      <span
                        class="text-sm text-gray-700 group-hover:text-indigo-700 transition-colors"
                        :class="{ 'text-indigo-700 font-medium': selectedAttributes.includes(val.id) }"
                      >
                        {{ val.value }} ({{ val.count }})
                      </span>
                    </label>
                      <button
                          v-if="!showFullAttributeValues[attr.id]"
                          class="text-gray-700 cursor-pointer"
                          @click="showFullAttributeValues[attr.id] = !showFullAttributeValues[attr.id]"
                      >Show more...</button>
                      <button
                          v-else
                          class="text-gray-700 cursor-pointer"
                          @click="showFullAttributeValues[attr.id] = !showFullAttributeValues[attr.id]"
                      >Show less</button>
                  </div>
                </div>
              </div>
            </div>

            <div class="p-4 border-t bg-gray-50">
              <button
                v-if="selectedAttributes.length > 0"
                type="button"
                @click="clearAllFilters(); closeMobileFilters()"
                class="w-full mb-2 text-center text-sm text-gray-500 hover:text-red-600 transition-colors"
              >
                Clear all filters
              </button>
              <button
                type="button"
                @click="closeMobileFilters"
                class="w-full inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700"
              >
                Show results
              </button>
            </div>
          </div>
        </div>
      </Teleport>

      <div class="flex gap-6">
        <!-- ── Desktop Sidebar ─────────────────────────────────────────────── -->
        <aside
          v-if="(props.attributes ?? []).length > 0"
          class="hidden md:block w-64 shrink-0"
        >
          <div class="bg-white rounded-lg shadow p-5 sticky top-6">
            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-4">Filter by</h2>

            <div class="space-y-5">
              <div
                v-for="attr in props.attributes"
                :key="attr.id"
              >
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">{{ attr.name }}</p>
                <div class="space-y-1.5">
                  <label
                    v-for="val in (showFullAttributeValues[attr.id] ? attr.values : attr.values.slice(0, NUMBER_OF_SHOWN_ATTRIBUTES))"
                    :key="val.id"
                    class="flex items-center gap-2 cursor-pointer group"
                  >
                    <input
                      type="checkbox"
                      :value="val.id"
                      :checked="selectedAttributes.includes(val.id)"
                      @change="toggleAttribute(val.id)"
                      class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                    />
                    <span
                      class="text-sm text-gray-700 group-hover:text-indigo-700 transition-colors"
                      :class="{ 'text-indigo-700 font-medium': selectedAttributes.includes(val.id) }"
                    >
                      {{ val.value }} ({{ val.count }})
                    </span>
                  </label>
                    <button
                        v-if="!showFullAttributeValues[attr.id]"
                        class="text-gray-700 cursor-pointer"
                        @click="showFullAttributeValues[attr.id] = !showFullAttributeValues[attr.id]"
                    >Show more...</button>
                    <button
                        v-else
                        class="text-gray-700 cursor-pointer"
                        @click="showFullAttributeValues[attr.id] = !showFullAttributeValues[attr.id]"
                    >Show less</button>
                </div>
              </div>
            </div>

            <button
              v-if="selectedAttributes.length > 0"
              type="button"
              @click="clearAllFilters"
              class="mt-5 w-full text-center text-xs text-gray-500 hover:text-red-600 transition-colors"
            >
              Clear attribute filters
            </button>
          </div>
        </aside>

        <!-- ── Product Grid ─────────────────────────────────────────────────── -->
        <div class="flex-1 min-w-0">
          <!-- Active filter chips -->
          <div v-if="selectedAttributes.length > 0" class="flex flex-wrap gap-2 mb-4">
            <template v-for="attr in props.attributes" :key="attr.id">
              <template v-for="val in attr.values" :key="val.id">
                <span
                  v-if="selectedAttributes.includes(val.id)"
                  class="inline-flex items-center gap-1 px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-xs font-medium"
                >
                  {{ attr.name }}: {{ val.value }}
                  <button type="button" @click="toggleAttribute(val.id)" class="ml-1 text-indigo-400 hover:text-indigo-700 leading-none">&times;</button>
                </span>
              </template>
            </template>
          </div>

          <section>
            <div v-if="(props.products || []).length === 0" class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
              No products found matching your filters.
            </div>

            <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
              <article v-for="product in props.products" :key="product.id" class="bg-white rounded-lg shadow hover:shadow-md overflow-hidden transition-shadow">
                <Link :href="`/products/${product.id}`" class="block">
                  <div class="aspect-w-16 aspect-h-10 bg-gray-100">
                    <img
                      v-if="product.image"
                      :src="getImageUrl(product)"
                      alt=""
                      class="w-full h-full object-cover"
                    />
                    <div v-else class="w-full h-full flex items-center justify-center text-gray-400">No image</div>
                  </div>
                </Link>
                <div class="p-4">
                  <Link :href="`/products/${product.id}`" class="block">
                    <h3 class="text-md font-semibold text-gray-800 truncate">{{ product.name }}</h3>
                  </Link>
                  <p class="text-sm text-gray-500 mt-1">{{ product.category?.name ?? 'Uncategorized' }}</p>
                  <div class="mt-3 flex items-center justify-between">
                    <span class="text-lg font-bold text-gray-900">{{ product.price != null ? (`$${Number(product.price).toFixed(2)}`) : '—' }}</span>
                    <button
                      type="button"
                      @click.prevent="addToCart(product)"
                      class="inline-flex items-center px-3 py-2 rounded-md bg-indigo-600 text-white hover:bg-indigo-700 text-xs font-medium shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                      Add to cart
                    </button>
                  </div>
                </div>
              </article>
            </div>
          </section>

          <!-- Pagination -->
          <div v-if="currentPage > 1 || hasNext" class="mt-8 flex items-center justify-center">
            <nav class="inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
              <Link
                :href="buildUrl(Math.max(1, currentPage - 1))"
                class="inline-flex items-center px-3 py-2 rounded-l-md border border-gray-200 bg-white text-gray-700 hover:bg-gray-50"
                :aria-disabled="currentPage <= 1"
              >
                Prev
              </Link>

              <template v-for="p in makePageRange()" :key="p">
                <Link
                  :href="buildUrl(p)"
                  :class="['px-3 py-2 border border-gray-200 hover:bg-gray-50 hover:text-gray-700',
                    p === currentPage ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700']"
                >
                  {{ p }}
                </Link>
              </template>

              <Link
                :href="buildUrl(Math.min(lastPage, currentPage + 1))"
                class="inline-flex items-center px-3 py-2 rounded-r-md border border-gray-200 bg-white text-gray-700 hover:bg-gray-50"
                :aria-disabled="!hasNext && currentPage >= lastPage"
              >
                Next
              </Link>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.aspect-w-16 { position: relative; }
.aspect-w-16::before { content: ''; display: block; padding-top: calc(100% * 10 / 16); }
.aspect-h-10 > img, .aspect-h-10 > div { position: absolute; top: 0; left: 0; width: 100%; height: 100%; }
</style>
