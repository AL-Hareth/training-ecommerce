<script setup lang="ts">
import { defineProps, computed, ref } from 'vue'
import { Link, useForm, router } from '@inertiajs/vue3'
import DiscountCountdown from '@/Components/DiscountCountdown.vue'

const props = defineProps<{
  product: {
    id: string | number
    name: string
    price?: number | string | null
    discount_type?: string | null
    discount_value?: number | null
    discounted_price?: number | null
    discount_expiration?: string | null
    image?: string | null
    category?: { id?: string | number; name?: string } | null
    description?: string | null
    stock?: number | null
    vendor?: { id?: string | number; name?: string } | null
    attribute_values?: Array<{
      id: string | number
      value: string
      attribute: { id: string | number; name: string; slug: string } | null
    }> | null
    variants?: Array<{
      id: string
      price: number
      stock: number
      attributes: Record<string, string>
    }>
  }
}>()

// --- Variant Selection Logic ---
const selectedAttributes = ref<Record<string, string>>({})

// Initialize selection if there are variants
if (props.product.variants?.length) {
  const firstVariant = props.product.variants[0]
  Object.keys(firstVariant.attributes).forEach(key => {
    selectedAttributes.value[key] = firstVariant.attributes[key]
  })
}

const activeVariant = computed(() => {
  if (!props.product.variants?.length) return null
  
  return props.product.variants.find(variant => {
    return Object.keys(selectedAttributes.value).every(key => {
      return variant.attributes[key] === selectedAttributes.value[key]
    })
  })
})

const displayPrice = computed(() => {
  if (activeVariant.value) return activeVariant.value.price
  return props.product.discounted_price ?? props.product.price ?? 0
})

const originalPrice = computed(() => {
  if (activeVariant.value) return activeVariant.value.price
  return props.product.price ?? 0
})

const displayStock = computed(() => {
  if (activeVariant.value) return activeVariant.value.stock
  return props.product.stock ?? 0
})

const isOutOfStock = computed(() => displayStock.value <= 0)

// Helper to get available values for each attribute from the variants
const variantAttributes = computed(() => {
  const attrs: Record<string, Set<string>> = {}
  props.product.variants?.forEach(v => {
    Object.entries(v.attributes).forEach(([key, val]) => {
      if (!attrs[key]) attrs[key] = new Set()
      attrs[key].add(val)
    })
  })
  
  const result: Record<string, string[]> = {}
  Object.keys(attrs).forEach(key => {
    result[key] = Array.from(attrs[key])
  })
  return result
})

const groupedAttributes = computed(() => {
  const groups: Record<string, { name: string; values: string[] }> = {}

  if (!props.product.attribute_values?.length) return groups

  for (const attrValue of props.product.attribute_values) {
    if (!attrValue.attribute) continue

    const attrName = attrValue.attribute.name
    if (!groups[attrName]) {
      groups[attrName] = { name: attrName, values: [] }
    }
    groups[attrName].values.push(attrValue.value)
  }

  return groups
})

const hasAttributes = computed(() => Object.keys(groupedAttributes.value).length > 0)
const hasVariants = computed(() => props.product.variants && props.product.variants.length > 0)

const form = useForm({
  product_id: String(props.product.id),
  variant_id: computed(() => activeVariant.value?.id ?? null),
  quantity: 1,
  attributes: selectedAttributes
})

function addToCart() {
  form.post('/cart/add')
}

function handleExpired() {
  router.reload({ preserveScroll: true })
}
</script>

<template>
  <div class="min-h-screen bg-gray-50 p-6">
    <div class="max-w-6xl mx-auto">
      <div class="mb-6 flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-gray-800">{{ props.product.name }}</h1>
          <p class="text-sm text-gray-500">{{ props.product.category?.name ?? 'Uncategorized' }}</p>
        </div>
        <div>
          <Link href="/products" class="inline-flex items-center px-3 py-2 rounded-md bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 shadow">Back to products</Link>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 p-8">
          <!-- Image Section -->
          <div class="md:col-span-1">
            <div class="bg-gray-100 rounded-xl overflow-hidden aspect-w-1 aspect-h-1 shadow-inner">
              <img v-if="props.product.image" :src="props.product.image.startsWith('http') ? props.product.image : `/storage/${props.product.image}`" alt="" class="w-full h-full object-cover" />
              <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
              </div>
            </div>
            
            <div class="mt-6 space-y-4">
              <div class="p-4 bg-gray-50 rounded-lg border border-gray-100">
                <div v-if="props.product.discount_type && !activeVariant" class="text-xs text-red-600 font-bold mb-1 uppercase tracking-wider">
                  Special Offer: {{ props.product.discount_type === 'percentage' ? `${props.product.discount_value}% OFF` : `$${props.product.discount_value} OFF` }}
                </div>
                <div v-if="props.product.discount_expiration && !activeVariant" class="mb-3">
                  <DiscountCountdown :expiration="props.product.discount_expiration" @expired="handleExpired" />
                </div>
                <div class="flex items-baseline gap-3">
                  <span class="text-3xl font-extrabold text-gray-900">${{ Number(displayPrice).toFixed(2) }}</span>
                  <span v-if="originalPrice > displayPrice" class="line-through text-lg text-gray-400">${{ Number(originalPrice).toFixed(2) }}</span>
                </div>
                <div class="mt-2 flex items-center gap-2">
                  <span :class="[isOutOfStock ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700', 'px-2 py-0.5 rounded text-xs font-bold uppercase tracking-wide']">
                    {{ isOutOfStock ? 'Out of Stock' : 'In Stock' }}
                  </span>
                  <span v-if="!isOutOfStock" class="text-xs text-gray-500">{{ displayStock }} available</span>
                </div>
              </div>

              <div v-if="props.product.vendor" class="flex items-center gap-3 p-3 border border-gray-100 rounded-lg">
                <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold">
                  {{ props.product.vendor.name?.charAt(0) }}
                </div>
                <div>
                  <p class="text-xs text-gray-500">Sold by</p>
                  <p class="text-sm font-semibold text-gray-800">{{ props.product.vendor.name }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Content Section -->
          <div class="md:col-span-2">
            <div class="prose prose-indigo max-w-none">
              <h2 class="text-xl font-bold text-gray-900 mb-4">Description</h2>
              <p class="text-gray-600 leading-relaxed whitespace-pre-line">{{ props.product.description ?? 'No description provided.' }}</p>
            </div>

            <!-- Variant Selection -->
            <div v-if="hasVariants" class="mt-8 space-y-6">
              <h3 class="text-lg font-bold text-gray-900 border-b pb-2">Options</h3>
              <div v-for="(values, attrName) in variantAttributes" :key="attrName" class="space-y-3">
                <p class="text-sm font-semibold text-gray-700">{{ attrName }}</p>
                <div class="flex flex-wrap gap-2">
                  <button
                    v-for="val in values"
                    :key="val"
                    type="button"
                    @click="selectedAttributes[attrName] = val"
                    :class="[
                      selectedAttributes[attrName] === val 
                        ? 'bg-indigo-600 text-white border-indigo-600 shadow-md' 
                        : 'bg-white text-gray-700 border-gray-200 hover:border-indigo-300',
                      'px-4 py-2 rounded-md border text-sm font-medium transition-all duration-200'
                    ]"
                  >
                    {{ val }}
                  </button>
                </div>
              </div>
            </div>

            <!-- Specifications -->
            <div v-if="hasAttributes" class="mt-10">
              <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Specifications</h3>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div v-for="(group, attrName) in groupedAttributes" :key="attrName" class="flex flex-col p-3 bg-gray-50 rounded-lg">
                  <span class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">{{ group.name }}</span>
                  <div class="flex flex-wrap gap-1">
                    <span v-for="(val, idx) in group.values" :key="idx" class="text-sm text-gray-800 font-medium">
                      {{ val }}{{ idx < group.values.length - 1 ? ',' : '' }}
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Add to Cart Section -->
            <div class="mt-10 pt-8 border-t flex flex-col sm:flex-row items-center gap-4">
              <div class="flex items-center border border-gray-200 rounded-lg overflow-hidden h-12">
                <button @click="form.quantity = Math.max(1, form.quantity - 1)" class="px-4 h-full hover:bg-gray-100 text-gray-600 border-r border-gray-200">-</button>
                <input type="number" min="1" v-model.number="form.quantity" class="w-16 text-center border-none focus:ring-0 text-sm font-bold" />
                <button @click="form.quantity++" class="px-4 h-full hover:bg-gray-100 text-gray-600 border-l border-gray-200">+</button>
              </div>
              
              <button 
                :disabled="form.processing || isOutOfStock || (hasVariants && !activeVariant)" 
                @click.prevent="addToCart" 
                class="flex-1 sm:flex-none inline-flex items-center justify-center px-8 h-12 bg-indigo-600 text-white rounded-lg font-bold text-lg hover:bg-indigo-700 active:scale-95 transition-all shadow-lg shadow-indigo-200 disabled:opacity-50 disabled:shadow-none disabled:scale-100"
              >
                <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
                <span v-if="isOutOfStock">Sold Out</span>
                <span v-else-if="hasVariants && !activeVariant">Select Options</span>
                <span v-else>{{ form.processing ? 'Adding to Cart...' : 'Add to Cart' }}</span>
              </button>
            </div>
            
            <p v-if="hasVariants && !activeVariant" class="mt-2 text-sm text-amber-600 italic">This specific combination is currently unavailable.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.aspect-w-1 { position: relative; padding-top: 100%; }
.aspect-w-1 > img, .aspect-w-1 > div { position: absolute; top: 0; left: 0; width: 100%; height: 100%; }

/* Hide arrows for number input */
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
input[type=number] {
  -moz-appearance: textfield;
}
</style>
