<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sales extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'trx_penjualan';

    protected $primaryKey = 'id';

    protected $fillable = [
        'kode_penjualan',
        'kode_voucher',
        'kode_member',
        'payment_method',
        'card_number',
        'customer_type',
        'subtotal',
        'tax',
        'total',
        'cash_received',
        'change',
        'diskon_global',
        'is_aktif',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function details()
    {
        return $this->hasMany(DetailSales::class, 'penjualan_id');
    }
}
