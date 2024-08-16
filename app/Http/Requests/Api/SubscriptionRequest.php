<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class SubscriptionRequest extends FormRequest
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
            'subscription_id' => 'required|exists:subscriptions,id',
            'productVariations' => 'required|array',
            'productVariations.*.product_variation_id' => 'required|integer|exists:product_variations,id',
            'productVariations.*.variation_value_id' => 'required|integer|exists:product_variation_values,id',

        ];
    }
}
