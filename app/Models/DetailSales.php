<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DetailSales extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'trx_detail_penjualan';

    protected $primaryKey = 'id';

    protected $fillable = [
        'penjualan_id',
        'kode_penjualan',
        'kode_barcode',
        'nama_barang',
        'qty',
        'diskon',
        'dpp',
        'ppn',
        'subtotal',
        'harga',
        'is_aktif',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function sale()
    {
        return $this->belongsTo(Sales::class);
    }
}
