<?php

namespace App\Models;

class StatusPengadaan extends BaseModel
{
    protected $table = 'status_pengadaan';
    protected $guarded = [];
    public $timestamps = false;

    public const DRAFT = 'draft';
    public const MENUNGGU_PEMBELIAN = 'menunggu_pembelian';
    public const DALAM_PROSES = 'dalam_proses';
    public const MENUNGGU_PENERIMAAN = 'menunggu_penerimaan';
    public const DITERIMA_SEBAGIAN = 'diterima_sebagian';
    public const SELESAI = 'selesai';
    public const DIBATALKAN = 'dibatalkan';

    public static function idByKode(string $kode): int
    {
        return (int) static::where('kode_status', $kode)->value('id');
    }

    public static function kodeById($id): ?string
    {
        return static::find($id)?->kode_status;
    }
}
