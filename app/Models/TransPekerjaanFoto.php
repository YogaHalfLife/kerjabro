<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransPekerjaanFoto extends Model
{
    use SoftDeletes;

    protected $table = 'trans_pekerjaan_foto'; // penting

    protected $fillable = ['pekerjaan_id', 'kategori', 'path', 'caption', 'sort'];

    public function pekerjaan()
    {
        return $this->belongsTo(TransPekerjaan::class, 'pekerjaan_id');
    }

    public function getUrlAttribute()
    {
        return $this->path ? asset('storage/' . $this->path) : null;
    }
}
