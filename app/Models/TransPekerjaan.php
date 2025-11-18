<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransPekerjaan extends Model
{
    use SoftDeletes;

    protected $table = 'trans_pekerjaan'; // penting kalau tabel kamu tidak plural default

    protected $fillable = [
        'judul_pekerjaan',
        'detail_pekerjaan',
        'pegawai_id',
        'id_divisi',
        'bulan',
    ];
    public function pegawai()
    {
        return $this->belongsTo(MasterPegawai::class, 'pegawai_id');
    }

    public function pegawais()
    {
        return $this->belongsToMany(MasterPegawai::class, 'trans_pekerjaan_pegawai', 'pekerjaan_id', 'pegawai_id')->withTimestamps();
    }

    public function divisi()
    {
        return $this->belongsTo(MasterDivisi::class, 'id_divisi', 'id_divisi');
    }
    public function fotos()
    {
        return $this->hasMany(TransPekerjaanFoto::class, 'pekerjaan_id')
            ->orderBy('kategori')->orderBy('sort');
    }

    public function fotosSebelum()
    {
        return $this->hasMany(TransPekerjaanFoto::class, 'pekerjaan_id')
            ->where('kategori', 'sebelum')->orderBy('sort');
    }

    public function fotosSesudah()
    {
        return $this->hasMany(TransPekerjaanFoto::class, 'pekerjaan_id')
            ->where('kategori', 'sesudah')->orderBy('sort');
    }
}
