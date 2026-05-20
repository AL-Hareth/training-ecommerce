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
  discount_type: null as string | null,
  discount_value: null as number | null,
  image: null as File | null,
  attribute_value_ids: [] as string[],
  variants: [] as Array<{
    price: number | null;
    stock: number | null;
    attributes: Record<string, string>;
    attribute_list: Array<{ name: string; value: string }>;
  }>,
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

function addVariant() {
  form.variants.push({
    price: form.price ?? 0,
    stock: form.stock ?? 0,
    attributes: {},
    attribute_list: [{ name: '', value: '' }]
  })
}

function removeVariant(index: number) {
  form.variants.splice(index, 1)
}

function addAttribute(variantIndex: number) {
  form.variants[variantIndex].attribute_list.push({ name: '', value: '' })
}

function removeAttribute(variantIndex: number, attrIndex: number) {
  form.variants[variantIndex].attribute_list.splice(attrIndex, 1)
}

function onImageChange(e: Event) {
  const input = e.target as HTMLInputElement
  form.image = input.files && input.files[0] ? input.files[0] : null
}

function submitForm() {
  // Sync attribute_list to attributes object for the backend
  form.variants.forEach(v => {
    v.attributes = {}
    v.attribute_list.forEach(attr => {
      if (attr.name.trim()) {
        v.attributes[attr.name.trim()] = attr.value
      }
    })
  })
  
  form.post('/admin/products')
}

function resetForm() {
  form.reset('name', 'description', 'category_id', 'price', 'stock', 'image')
  form.attribute_value_ids = []
  form.variants = []
}

const descriptionCount = computed(() => (form.description || '').length)
</script>

<template>
  <div class="min-h-screen bg-gray-50 flex flex-col p-6">
    <div class="max-w-4xl mx-auto w-full">
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
          <div class="grid grid-cols-1 gap-8">
            <!-- Basic Info -->
            <section>
              <h2 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Basic Information</h2>
              <div class="grid gap-6">
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

                <div>
                  <label for="image" class="block text-sm font-medium text-gray-700">Image</label>
                  <div class="mt-1">
                    <input id="image" name="image" type="file" accept="image/*" @change="onImageChange" class="block w-full text-sm text-gray-500" />
                  </div>
                  <p v-if="form.errors.image" class="mt-2 text-sm text-red-600">{{ form.errors.image }}</p>
                </div>
              </div>
            </section>

            <!-- Pricing & Inventory -->
            <section>
              <h2 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Default Pricing & Inventory</h2>
              <p class="text-sm text-gray-500 mb-4">These serve as the default if no variants are selected.</p>
              
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
            </section>

            <!-- Attribute Values (For global filtering) -->
            <section v-if="(props.attributes ?? []).length > 0">
              <h2 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Search Filters</h2>
              <p class="text-sm text-gray-500 mb-4">Select the attribute values that apply to this product overall for faceted search.</p>
              
              <div class="space-y-4">
                <div v-for="attr in props.attributes" :key="attr.id" class="rounded-md border border-gray-200 p-4">
                  <p class="text-sm font-semibold text-gray-700 mb-2">{{ attr.name }}</p>
                  <div class="flex flex-wrap gap-2">
                    <label v-for="val in attr.values" :key="val.id" class="inline-flex items-center gap-1.5 cursor-pointer">
                      <input type="checkbox" :value="val.id" :checked="isChecked(val.id)" @change="toggleValue(val.id)" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                      <span class="text-sm text-gray-700">{{ val.value }}</span>
                    </label>
                  </div>
                </div>
              </div>
            </section>

            <!-- Variants -->
            <section>
              <div class="flex items-center justify-between mb-4 border-b pb-2">
                <div>
                  <h2 class="text-lg font-medium text-gray-900">Product Variants</h2>
                  <p class="text-sm text-gray-500">Create specific variations of this product (e.g. Color: Red, Size: XL).</p>
                </div>
                <button type="button" @click="addVariant" class="px-3 py-1.5 bg-indigo-600 text-white hover:bg-indigo-700 rounded-md text-sm font-bold shadow-sm transition-all active:scale-95">
                  + Add Variant
                </button>
              </div>

              <div v-if="form.variants.length === 0" class="text-center py-10 bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200">
                <p class="text-sm text-gray-400 font-medium">No variants added yet.</p>
              </div>

              <div v-else class="space-y-6">
                <div v-for="(variant, index) in form.variants" :key="index" class="p-6 bg-white border border-gray-200 rounded-2xl shadow-sm relative group hover:border-indigo-200 transition-all">
                  <button type="button" @click="removeVariant(index)" class="absolute top-4 right-4 text-gray-300 hover:text-red-500 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                  </button>
                  
                  <div class="flex items-center gap-2 mb-6">
                    <span class="w-6 h-6 bg-indigo-600 text-white rounded-full flex items-center justify-center text-[10px] font-black uppercase tracking-widest">{{ index + 1 }}</span>
                    <h3 class="text-sm font-black text-gray-900 uppercase tracking-widest">Variant Details</h3>
                  </div>
                  
                  <div class="grid grid-cols-2 gap-6 mb-8">
                    <div>
                      <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Variant Price</label>
                      <input type="number" step="0.01" v-model.number="variant.price" class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition-all" placeholder="0.00" />
                      <p v-if="form.errors[`variants.${index}.price`]" class="mt-1 text-xs text-red-600 font-medium">{{ form.errors[`variants.${index}.price`] }}</p>
                    </div>
                    <div>
                      <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Variant Stock</label>
                      <input type="number" v-model.number="variant.stock" class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition-all" placeholder="0" />
                      <p v-if="form.errors[`variants.${index}.stock`]" class="mt-1 text-xs text-red-600 font-medium">{{ form.errors[`variants.${index}.stock`] }}</p>
                    </div>
                  </div>

                  <div class="border-t border-gray-100 pt-6">
                    <div class="flex items-center justify-between mb-4">
                      <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest">Variant Attributes</label>
                      <button type="button" @click="addAttribute(index)" class="text-[10px] font-black text-indigo-600 uppercase tracking-widest hover:text-indigo-800">+ Add Property</button>
                    </div>
                    
                    <div class="space-y-3">
                      <div v-for="(attr, attrIndex) in variant.attribute_list" :key="attrIndex" class="flex items-center gap-3">
                        <div class="flex-1">
                          <input v-model="attr.name" type="text" placeholder="Key (e.g. Color)" class="w-full rounded-lg border-gray-200 text-xs focus:ring-indigo-500 transition-all" />
                        </div>
                        <div class="flex-1">
                          <input v-model="attr.value" type="text" placeholder="Value (e.g. Blue)" class="w-full rounded-lg border-gray-200 text-xs focus:ring-indigo-500 transition-all" />
                        </div>
                        <button type="button" @click="removeAttribute(index, attrIndex)" class="p-2 text-gray-300 hover:text-red-500 transition-colors">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                          </svg>
                        </button>
                      </div>
                      <p v-if="form.errors[`variants.${index}.attributes`]" class="mt-1 text-xs text-red-600 font-medium">{{ form.errors[`variants.${index}.attributes`] }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </section>

            <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-100">
              <button type="button" class="px-5 py-2.5 rounded-xl bg-white border border-gray-200 text-gray-700 font-bold text-sm hover:bg-gray-50 transition-all shadow-sm" @click.prevent="resetForm">Reset Form</button>
              <button type="submit" :disabled="form.processing" class="inline-flex items-center px-8 py-2.5 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-100 transition-all active:scale-95 disabled:opacity-50">
                <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
                <span>{{ form.processing ? 'Creating Product...' : 'Create Product' }}</span>
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<style scoped>
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
