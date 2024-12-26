<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DetailPurchasing extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'trx_detail_pembelian';

    protected $primaryKey = 'id';

    protected $fillable = [
        'pembelian_id',
        'kode_pembelian',
        'nomor_faktur',
        'kode_barcode',
        'nama_barang',
        'qty',
        'satuan_id',
        'nama_satuan',
        'isi',
        'harga',
        'jumlah',
        'harga_satuan_kecil',
        // 'current_hpp_satuan_besar',
        // 'current_hpp_satuan_kecil',
        'nilai_dpp',
        'nilai_ppn',
        'harga_jual',
        'diskon',
        'diskon_global',
        'exp_date',
        'rebate',
        'is_taxable',
        'is_aktif',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function pembelian()
    {
        return $this->belongsTo(Purchasing::class);
    }
}
