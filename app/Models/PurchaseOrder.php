<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'trx_purchase_order';

    protected $primaryKey = 'id';

    protected $fillable = [
        'kode_po',
        'nama_supplier',
        'status',
        'keterangan',
        'is_aktif',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function details()
    {
        return $this->hasMany(DetailPurchaseOrder::class, 'purchase_order_id');
    }
}
