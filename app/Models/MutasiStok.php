<?php

namespace App\Models;

class MutasiStok extends BaseModel
{
    protected $table = 'mutasi_stok';

    protected $guarded = [];

    public $timestamps = false;

    protected $casts = [
        'tanggal_mutasi' => 'datetime',
        'dibuat_pada' => 'datetime',
        'jumlah' => 'float',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->jenis_mutasi_stok_id)) {
                $model->jenis_mutasi_stok_id = 2; // 2 = Keluar
            }
            if (empty($model->satuan_id) && ! empty($model->bahan_baku_id)) {
                $bahan = BahanBaku::find($model->bahan_baku_id);
                if ($bahan) {
                    $model->satuan_id = $bahan->satuan_id;
                }
            }
            if (empty($model->tanggal_mutasi)) {
                $model->tanggal_mutasi = now();
            }
            if (empty($model->dibuat_oleh)) {
                $model->dibuat_oleh = auth()->check() ? auth()->id() : 1;
            }
            if (empty($model->dibuat_pada)) {
                $model->dibuat_pada = now();
            }
        });
    }

    public function setJenisMutasiAttribute($value)
    {
        $this->attributes['jenis_mutasi_stok_id'] = ($value === 'masuk' || $value == 1) ? 1 : 2;
    }

    public function setUserIdAttribute($value)
    {
        $this->attributes['dibuat_oleh'] = $value;
    }

    public function setSisaStokAttribute($value)
    {
        // Virtual attribute ignored for SQL columns
    }

    public function setKeteranganAttribute($value)
    {
        $this->attributes['catatan'] = $value;
    }

    public function setReferensiAttribute($value)
    {
        if ($value) {
            $cat = $this->attributes['catatan'] ?? '';
            $this->attributes['catatan'] = trim("{$cat} [Ref: {$value}]");
        }
    }

    public function bahan_baku()
    {
        return $this->belongsTo(BahanBaku::class, 'bahan_baku_id');
    }

    public function jenis_mutasi_stok()
    {
        return $this->belongsTo(JenisMutasiStok::class, 'jenis_mutasi_stok_id');
    }

    public function satuan()
    {
        return $this->belongsTo(Satuan::class, 'satuan_id');
    }

    public function detail_pesanan()
    {
        return $this->belongsTo(DetailPesanan::class, 'detail_pesanan_id');
    }

    public function detail_penerimaan_bahan()
    {
        return $this->belongsTo(DetailPenerimaanBahan::class, 'detail_penerimaan_bahan_id');
    }

    public function detail_penyesuaian_stok()
    {
        return $this->belongsTo(DetailPenyesuaianStok::class, 'detail_penyesuaian_stok_id');
    }

    public function dibuat_oleh_pengguna()
    {
        return $this->belongsTo(Pengguna::class, 'dibuat_oleh');
    }
}
