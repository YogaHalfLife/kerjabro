<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterDivisi extends Model
{
    use SoftDeletes;

    protected $table = 'master_divisi';
    protected $primaryKey = 'id_divisi';

    protected $fillable = [
        'nama_divisi',
        'isactive',
    ];

    protected $casts = [
        'isactive' => 'boolean',
    ];
}
