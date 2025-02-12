<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DetailReturJual extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'trx_detail_retur_jual';

    protected $primaryKey = 'id';

    protected $fillable = [
        'retur_jual_id',
        'kode_retur_jual',
        'kode_barcode',
        'nama_barang',
        'qty_jual',
        'nama_satuan_jual',
        'qty_retur',
        'nama_satuan_retur',
        'harga',
        'jumlah',
        'is_aktif',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function returbeli()
    {
        return $this->belongsTo(ReturBeli::class);
    }
}
