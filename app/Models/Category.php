<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'mstkategoribarang';
    protected $primaryKey = 'id';
    protected $fillable = [
        'kode_kategori',
        'nama_kategori',
        'is_aktif',
        'created_by',
        'updated_by',
        'deleted_by'
    ];
}
