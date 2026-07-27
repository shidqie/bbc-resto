<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateQrisPaymentRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'amount' => 'required|integer|min:1',
            'din_number' => 'required|string|max:255',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:50',
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'amount.required' => 'Nominal tagihan pembayaran wajib diisi.',
            'amount.integer' => 'Nominal tagihan pembayaran harus berupa angka bulat.',
            'amount.min' => 'Nominal tagihan minimal Rp 1.',
            'din_number.required' => 'Nomor order / DIN number wajib diisi.',
        ];
    }
}
