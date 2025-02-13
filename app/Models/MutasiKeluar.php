<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MutasiKeluar extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'trx_mutasi_keluar';

    protected $primaryKey = 'id';

    protected $fillable = [
        'kode_mutasi_keluar',
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
        return $this->hasMany(DetailMutasiKeluar::class, 'mutasi_keluar_id');
    }
}
