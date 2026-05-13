<script setup>
import { useForm, usePage, Head } from '@inertiajs/vue3';

// 1. Define the props coming from the Laravel Controller
const props = defineProps({
    checkoutData: {
        type: Object,
        required: true,
    }
});

// Grab the authenticated user from Inertia's shared data
const user = usePage().props.auth.user;

// 2. Initialize the Inertia Form
// Notice how perfectly this matches your StoreOrderRequest validation!
const form = useForm({
    user_id: user.id,
    total_price: props.checkoutData.total_price,
    payment_method: 'cod', // Default selection
    shipping_address: '',
    shipping_phone: '',

    // We pass the deeply nested vendors array directly into the form!
    vendors: props.checkoutData.vendors,
});

// 3. Submit handler
const submitOrder = () => {
    form.post('/checkout');
};
</script>

<template>
    <Head title="Checkout" />

    <div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold mb-8">Secure Checkout</h1>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

            <div class="lg:col-span-7 bg-white p-6 rounded-lg shadow">
                <form @submit.prevent="submitOrder" class="space-y-6">

                    <div>
                        <h2 class="text-xl font-semibold mb-4">Shipping Information</h2>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Full Address</label>
                            <textarea
                                v-model="form.shipping_address"
                                rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                placeholder="123 Main St, Apt 4B, City, Country"
                                required
                            ></textarea>
                            <span v-if="form.errors.shipping_address" class="text-red-500 text-sm">
                                {{ form.errors.shipping_address }}
                            </span>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Phone Number</label>
                            <input
                                type="tel"
                                v-model="form.shipping_phone"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                required
                            />
                            <span v-if="form.errors.shipping_phone" class="text-red-500 text-sm">
                                {{ form.errors.shipping_phone }}
                            </span>
                        </div>
                    </div>

                    <hr class="border-gray-200" />

                    <div>
                        <h2 class="text-xl font-semibold mb-4">Payment Method</h2>
                        <div class="space-y-3">
                            <label class="flex items-center p-3 border rounded cursor-pointer hover:bg-gray-50">
                                <input type="radio" v-model="form.payment_method" value="stripe" class="h-4 w-4 text-blue-600">
                                <span class="ml-3 font-medium">Credit/Debit Card (Stripe)</span>
                            </label>
                            <label class="flex items-center p-3 border rounded cursor-pointer hover:bg-gray-50">
                                <input type="radio" v-model="form.payment_method" value="paypal" class="h-4 w-4 text-blue-600">
                                <span class="ml-3 font-medium">PayPal</span>
                            </label>
                            <label class="flex items-center p-3 border rounded cursor-pointer hover:bg-gray-50">
                                <input type="radio" v-model="form.payment_method" value="cod" class="h-4 w-4 text-blue-600">
                                <span class="ml-3 font-medium">Cash on Delivery</span>
                            </label>
                        </div>
                        <span v-if="form.errors.payment_method" class="text-red-500 text-sm">
                            {{ form.errors.payment_method }}
                        </span>
                    </div>

                    <div v-if="Object.keys(form.errors).length > 0" class="p-4 bg-red-50 text-red-700 rounded">
                        Please check the form for errors before continuing.
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full bg-black text-white font-bold py-3 px-4 rounded hover:bg-gray-800 disabled:opacity-50"
                    >
                        {{ form.processing ? 'Processing...' : 'Place Order' }}
                    </button>
                </form>
            </div>

            <div class="lg:col-span-5">
                <div class="bg-gray-50 p-6 rounded-lg shadow border border-gray-100">
                    <h2 class="text-xl font-semibold mb-6">Order Summary</h2>

                    <div
                        v-for="vendor in checkoutData.vendors"
                        :key="vendor.vendor_id"
                        class="mb-6 pb-6 border-b border-gray-200 last:border-0"
                    >
                        <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-3">
                            Sold by: {{ vendor.vendor_name }}
                        </h3>

                        <ul class="space-y-4">
                            <li v-for="item in vendor.items" :key="item.product_id" class="flex justify-between">
                                <div class="flex flex-col">
                                    <span class="font-medium">{{ item.product_name }}</span>
                                    <span class="text-sm text-gray-500">Qty: {{ item.quantity }}</span>
                                </div>
                                <span class="font-medium">${{ item.price.toFixed(2) }}</span>
                            </li>
                        </ul>

                        <div class="mt-4 pt-4 border-t border-gray-100 text-sm text-gray-600 flex justify-between">
                            <span>Vendor Subtotal:</span>
                            <span>${{ vendor.subtotal.toFixed(2) }}</span>
                        </div>
                        <div class="mt-1 text-sm text-gray-600 flex justify-between">
                            <span>Shipping:</span>
                            <span>${{ vendor.shipping_fee.toFixed(2) }}</span>
                        </div>
                    </div>

                    <div class="pt-4 border-t-2 border-gray-900 mt-4">
                        <div class="flex justify-between text-xl font-bold">
                            <span>Grand Total</span>
                            <span>${{ checkoutData.total_price.toFixed(2) }}</span>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</template>
