<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'trx_purchase_order';

    protected $primaryKey = 'id';
}
