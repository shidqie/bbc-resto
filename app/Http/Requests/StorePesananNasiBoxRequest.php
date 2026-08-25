<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class StorePesananNasiBoxRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $minDate = Carbon::today()->addDays(4)->format('Y-m-d');

        return [
            'nama_pemesan' => ['required', 'string', 'max:255'],
            'kontak' => ['required', 'string', 'max:20'],
            'alamat' => ['required', 'string'],
            'tanggal_acara' => ['required', 'date', 'after_or_equal:'.$minDate],
            'paket_id' => ['required', 'exists:paket_caterings,id'],
            'komponen' => ['required', 'array'],
            'komponen.*' => ['required', 'exists:menus,id'],
            'jumlah_box' => ['required', 'integer', 'min:10'],
            'catatan' => ['nullable', 'string'],
            'metode_pengiriman' => ['required', 'in:pickup,delivery'],
            'latitude' => ['required_if:metode_pengiriman,delivery', 'nullable', 'string'],
            'longitude' => ['required_if:metode_pengiriman,delivery', 'nullable', 'string'],
            'jarak_km' => ['required_if:metode_pengiriman,delivery', 'nullable', 'numeric', 'min:0'],
            'opsi_pembayaran' => ['required', 'in:dp,lunas'],
        ];
    }

    public function messages(): array
    {
        return [
            'tanggal_acara.after_or_equal' => 'Pesanan nasi box minimal H-4 sebelum acara.',
            'jumlah_box.min' => 'Minimal order 10 box.',
            'paket_id.exists' => 'Paket Nasi Box tidak valid.',
            'komponen.required' => 'Pilihan menu komponen wajib diisi lengkap.',
        ];
    }
}
