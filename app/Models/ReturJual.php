<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReturJual extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'trx_retur_jual';

    protected $primaryKey = 'id';

    protected $fillable = [
        'kode_retur_jual',
        'kode_penjualan',
        'keterangan',
        'status',
        'is_aktif',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function details()
    {
        return $this->hasMany(DetailReturJual::class, 'retur_jual_id');
    }
}
