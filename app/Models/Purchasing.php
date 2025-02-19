<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Purchasing extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'trx_pembelian';

    protected $primaryKey = 'id';

    protected $fillable = [
        'kode_pembelian',
        'supplier_id',
        'nama_supplier',
        'po_id',
        'kode_po',
        'keterangan',
        'purchase_type',
        'rebate',
        'diskon_total',
        'dpp_total',
        'dpp_lain_total',
        'ppn_total',
        'total',
        'grand_total',
        'is_aktif',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function details()
    {
        return $this->hasMany(DetailPurchasing::class, 'pembelian_id');
    }
}
