<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class JenisSampah extends Model
{
    protected $fillable = ['jenis', 'harga','pengepul', 'keterangan'];


    public function catatan()
    {
        return $this->hasMany(Catatan::class);
    }

    public function tabungan()
    {
        return $this->hasMany(Tabungan::class);
    }
}
