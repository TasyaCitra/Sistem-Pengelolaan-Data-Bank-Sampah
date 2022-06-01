<?php

namespace App\Http\Controllers;

use App\Beranda;
use App\Galery;
use App\SampahBotolKaca;
use App\SampahElektrik;
use App\SampahKertas;
use App\SampahLogam;
use App\SampahMenu;
use App\SampahPlastik;
use Illuminate\Http\Request;

class MainController extends Controller
{
    public function index(){
        return view('main',[
            'beranda' => Beranda::first(),
            'galery' => Galery::get(),
            'sampahlogam' => SampahMenu::where('tipe',1)->join('jenis_sampahs','jenis_sampahs.id','=','sampah_menus.jenis_sampah')->select("jenis_sampahs.jenis", "sampah_menus.*")->get(),
            'sampahplastik' => SampahMenu::where('tipe',2)->join('jenis_sampahs','jenis_sampahs.id','=','sampah_menus.jenis_sampah')->select("jenis_sampahs.jenis", "sampah_menus.*")->get(),
            'sampahkertas' => SampahMenu::where('tipe',3)->join('jenis_sampahs','jenis_sampahs.id','=','sampah_menus.jenis_sampah')->select("jenis_sampahs.jenis", "sampah_menus.*")->get(),
            'sampahbotolkaca' => SampahMenu::where('tipe',4)->join('jenis_sampahs','jenis_sampahs.id','=','sampah_menus.jenis_sampah')->select("jenis_sampahs.jenis", "sampah_menus.*")->get(),
            'sampahelektrik' => SampahMenu::where('tipe',5)->join('jenis_sampahs','jenis_sampahs.id','=','sampah_menus.jenis_sampah')->select("jenis_sampahs.jenis", "sampah_menus.*")->get(),
        ]);
    }
}
