<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DetailMutasiKeluar extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'trx_detail_mutasi_keluar';

    protected $primaryKey = 'id';

    protected $fillable = [
        'mutasi_keluar_id',
        'kode_mutasi_keluar',
        'kode_barcode',
        'nama_barang',
        'qty',
        'nama_satuan',
        'is_aktif',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function mutasikeluar()
    {
        return $this->belongsTo(MutasiKeluar::class);
    }
}
