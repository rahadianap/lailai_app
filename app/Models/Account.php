<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'mst_account';

    protected $primaryKey = 'id';

    protected $fillable = [
        'nomor_account',
        'nama_account',
        'nama_kelompok_account',
        'level',
        'kas_bank',
        'tipe_account',
        'is_aktif',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
}
