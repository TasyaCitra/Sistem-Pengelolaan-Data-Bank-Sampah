<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sampah extends Model
{
    protected $fillable = ['id_jenis', 'id_transaksi', 'harga','jumlah','total'];
}
