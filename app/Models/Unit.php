<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'mstsatuanbarang';
    protected $primaryKey = 'id';
    protected $fillable = [
        'kode_satuan',
        'nama_satuan',
        'is_aktif',
        'created_by',
        'updated_by',
        'deleted_by'
    ];
}
