<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{

    protected $fillable = ['user', 'admin', 'totalberat', 'totalharga', 'tanggal', 'status'];
}
