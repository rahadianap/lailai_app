<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Store extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'mst_store';

    protected $primaryKey = 'id';

    protected $fillable = [
        'kode_toko',
        'nama_toko',
        'alamat_toko',
        'is_aktif',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
}
