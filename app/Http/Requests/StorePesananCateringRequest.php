<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class StorePesananCateringRequest extends FormRequest
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
            'lokasi_acara' => ['required', 'string'],
            'tanggal_acara' => ['required', 'date', 'after_or_equal:'.$minDate],
            'paket_id' => ['required', 'exists:paket_caterings,id'],
            'jumlah_porsi' => ['required', 'integer', 'min:1'],
            'komponen' => ['required', 'array'],
            'komponen.*' => ['required', 'exists:menus,id'],
            'layanan_tambahan' => ['nullable', 'array'],
            'layanan_tambahan.*' => ['exists:layanan_tambahans,id'],
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
            'tanggal_acara.after_or_equal' => 'Pemesanan catering minimal H-4 sebelum acara.',
            'jumlah_porsi.min' => 'Jumlah porsi minimal 1.',
            'komponen.required' => 'Pilihan menu komponen wajib diisi lengkap.',
            'paket_id.exists' => 'Paket tidak valid.',
        ];
    }
}
