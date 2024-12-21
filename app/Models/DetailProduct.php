<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DetailProduct extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'mst_detail_barang';

    protected $primaryKey = 'id';

    protected $fillable = [
        'barang_id',
        'nama_barang',
        'saldo_awal',
        'harga_jual_karton',
        'harga_jual_eceran',
        'harga_beli_karton',
        'harga_beli_eceran',
        'hpp_avg_karton',
        'hpp_avg_eceran',
        'hpp_fifo_karton',
        'hpp_fifo_eceran',
        'current_stock',
        'nilai_akhir',
        'is_aktif',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
