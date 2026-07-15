<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class StorePesananNasiBoxRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $minDate = Carbon::today()->addDays(2)->format('Y-m-d');
        
        return [
            'nama_pemesan' => ['required', 'string', 'max:255'],
            'kontak' => ['required', 'string', 'max:20'],
            'alamat' => ['required', 'string'],
            'tanggal_acara' => ['required', 'date', 'after_or_equal:' . $minDate],
            'menu_id' => ['required', 'exists:menus,id'], // Harus dicek di controller bahwa kategorinya "Nasi Box"
            'jumlah_box' => ['required', 'integer', 'min:10'],
            'catatan' => ['nullable', 'string'],
            'metode_pengiriman' => ['required', 'in:pickup,delivery'],
            'latitude' => ['required_if:metode_pengiriman,delivery', 'nullable', 'string'],
            'longitude' => ['required_if:metode_pengiriman,delivery', 'nullable', 'string'],
            'jarak_km' => ['required_if:metode_pengiriman,delivery', 'nullable', 'numeric', 'min:0']
        ];
    }

    public function messages(): array
    {
        return [
            'tanggal_acara.after_or_equal' => 'Pesanan nasi box maksimal H-2 sebelum acara.',
            'jumlah_box.min' => 'Minimal order 10 box.',
            'menu_id.exists' => 'Varian Nasi Box tidak valid.'
        ];
    }
}
