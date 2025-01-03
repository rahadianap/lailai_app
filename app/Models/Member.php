<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Member extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'mst_member';

    protected $primaryKey = 'id';

    protected $fillable = [
        'kode_member',
        'nik',
        'nama_member',
        'email',
        'no_hp',
        'alamat',
        'point',
        'tgl_daftar',
        'exp_date',
        'is_aktif',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
}
