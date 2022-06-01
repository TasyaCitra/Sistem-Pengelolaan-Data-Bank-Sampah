<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SampahMenu extends Model
{
    protected $fillable = ['jenis_sampah', 'keterangan','tipe'];
}
