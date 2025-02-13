<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DetailMutasiMasuk extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'trx_detail_mutasi_masuk';

    protected $primaryKey = 'id';

    protected $fillable = [
        'mutasi_masuk_id',
        'kode_mutasi_masuk',
        'kode_barcode',
        'nama_barang',
        'qty',
        'nama_satuan',
        'is_aktif',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function mutasimasuk()
    {
        return $this->belongsTo(MutasiMasuk::class);
    }
}
