<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MutasiMasuk extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'trx_mutasi_masuk';

    protected $primaryKey = 'id';

    protected $fillable = [
        'kode_mutasi_masuk',
        'asal_gudang',
        'tujuan_gudang',
        'keterangan',
        'status',
        'is_aktif',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function details()
    {
        return $this->hasMany(DetailMutasiMasuk::class, 'mutasi_masuk_id');
    }
}
