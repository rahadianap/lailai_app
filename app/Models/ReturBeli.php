<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReturBeli extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'trx_retur_beli';

    protected $primaryKey = 'id';

    protected $fillable = [
        'kode_retur_beli',
        'purchasing_id',
        'nama_supplier',
        'keterangan',
        'status',
        'is_aktif',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function details()
    {
        return $this->hasMany(DetailReturBeli::class, 'purchase_order_id');
    }
}
