<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterPegawai extends Model
{
    use SoftDeletes;

    protected $table = 'master_pegawai';

    protected $fillable = [
        'kode_pegawai',
        'nama_pegawai',
        'id_divisi',
        'isactive',
    ];

    protected $casts = [
        'isactive' => 'boolean',
    ];

    public function divisi()
    {
        return $this->belongsTo(MasterDivisi::class, 'id_divisi', 'id_divisi');
    }
}
