<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DetailPurchaseOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'trx_detail_purchase_order';

    protected $primaryKey = 'id';

    protected $fillable = [
        'purchase_order_id',
        'kode_po',
        'nomor_faktur',
        'kode_barcode',
        'nama_barang',
        'qty',
        'nama_satuan',
        'isi',
        'harga',
        'jumlah',
        'harga_satuan_kecil',
        'hpp_avg_satuan',
        'hpp_avg_perbiji',
        'nilai_dpp',
        'nilai_ppn',
        'harga_jual',
        'diskon',
        'diskon_global',
        'rebate',
        'is_taxable',
        'is_aktif',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function po()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }
}
