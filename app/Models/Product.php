<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'mstbarang';

    protected $primaryKey = 'id';

    protected $fillable = [
        'kode_barang',
        'kode_barcode',
        'nama_barang',
        'nama_satuan',
        'nama_kategori',
        'is_taxable',
        'isi_barang',
        'is_aktif',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function details()
    {
        return $this->hasOne(DetailProduct::class, 'barang_id');
    }
}
