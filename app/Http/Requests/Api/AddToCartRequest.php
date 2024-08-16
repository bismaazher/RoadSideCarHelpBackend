<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class AddToCartRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth('api')->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'productVariations' => 'nullable',
            'productVariations.*.product_variation_id' => 'nullable|integer|exists:product_variations,id',
            'productVariations.*.variation_value_id' => 'nullable|integer|exists:product_variation_values,id',
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'The product ID is required.',
            'product_id.exists' => 'The selected product ID is invalid.',
            'quantity.required' => 'The quantity is required.',
            'quantity.integer' => 'The quantity must be an integer.',
            'quantity.min' => 'The quantity must be at least 1.',
            // 'productVariations.required' => 'The product variations are required.',
            'productVariations.array' => 'The product variations must be an array.',
            'productVariations.*.variation_id.required' => 'The variation ID is required.',
            'productVariations.*.variation_id.integer' => 'The variation ID must be an integer.',
            'productVariations.*.variation_id.exists' => 'The selected variation ID is invalid.',
            // 'productVariations.*.variation_option_id.required' => 'The variation option ID is required.',
            'productVariations.*.variation_option_id.integer' => 'The variation option ID must be an integer.',
            'productVariations.*.variation_option_id.exists' => 'The selected variation option ID is invalid.',
        ];
    }
}
