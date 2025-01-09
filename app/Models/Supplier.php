<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'mst_supplier';

    protected $primaryKey = 'id';

    protected $fillable = [
        'kode_supplier',
        'no_ktp',
        'npwp',
        'nama_supplier',
        'alamat',
        'tgl_lahir',
        'no_hp1',
        'no_hp2',
        'email',
        'keterangan',
        'is_retur',
        'is_aktif',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
}
