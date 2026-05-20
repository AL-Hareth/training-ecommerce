<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'uuid', 'exists:users,id'],
            'total_price' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['required', 'string', 'in:stripe,paypal,cod'], // Limit allowed methods
            'shipping_address' => ['required', 'string', 'max:255'],
            'shipping_phone' => ['required', 'string', 'max:50'],

            // 2. Vendors Array Validation
            'vendors' => ['required', 'array', 'min:1'], // Must have at least one vendor

            // Validate the keys inside EACH vendor array
            'vendors.*.subtotal' => ['required', 'numeric', 'min:0'],
            'vendors.*.status' => ['sometimes', 'string', 'in:pending'], // Usually set by backend, but safe to validate if passed

            // 3. Items Array Validation (Nested inside each vendor)
            'vendors.*.items' => ['required', 'array', 'min:1'], // Each vendor must have items

            // Validate the keys inside EACH item array
            'vendors.*.items.*.product_id' => ['required', 'uuid', 'exists:products,id'],
            'vendors.*.items.*.variant_id' => ['nullable', 'uuid', 'exists:product_variants,id'],
            'vendors.*.items.*.price' => ['required', 'numeric', 'min:0'], // Ordering price
            'vendors.*.items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}
