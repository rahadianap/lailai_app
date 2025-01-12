<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KelompokAccount extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'mst_kelompok_account';

    protected $primaryKey = 'id';

    protected $fillable = [
        'kode_kelompok_account',
        'kelompok',
        'nama_kelompok_account',
        'jenis_kelompok_account',
        'is_aktif',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
}
