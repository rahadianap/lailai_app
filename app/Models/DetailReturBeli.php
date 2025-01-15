<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DetailReturBeli extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'trx_detail_retur_beli';

    protected $primaryKey = 'id';

    protected $fillable = [
        'retur_beli_id',
        'kode_barcode',
        'nama_barang',
        'qty',
        'nama_satuan',
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
