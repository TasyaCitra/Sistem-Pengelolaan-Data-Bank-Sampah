<?php

namespace App\Http\Controllers;

use App\JenisSampah;
use App\Sampah;
use App\Tabungan;
use App\Transaksi;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransaksiController extends Controller
{
    public function transaksi()
    {
        $cek = Transaksi::latest()->first();
        if($cek){
            $id = $cek->id;
        }else{
            $id = 1;
        }
        $sampah = [];
        $transaksi = Transaksi::get();
        foreach($transaksi as $key => $value){
            $sampah[$key] = Sampah::join('jenis_sampahs','jenis_sampahs.id','=','id_jenis')->where('id_transaksi',$value->id)->select('jenis_sampahs.jenis','sampahs.*')->get();
        }
        return view('transaksi.transaksi',[
            'page' => ['active' => 'Tabungan'],
            'user' => User::where('role',4)->orderBy('name')->get(),
            'jenis' => JenisSampah::get(),
            'idtrx' => $id,
            'transaksi' => $transaksi,
            'sampah' => $sampah
        ]);
    }
    public function transaksi_tambah(Request $request)
    {
        $trx = Transaksi::create([
            "admin" => Auth::user()->id,
            "user" => $request->user,
            "totalberat" => $request->totalberat,
            "totalharga" => $request->totalharga,
            "tanggal" => $request->tanggal,
        ]);
        foreach($request->jenis as $key => $value){
            Sampah::insert([
                "id_jenis" => $value,
                "id_transaksi" => $trx->id,
                "harga" => $request->harga[$key],
                "jumlah" => $request->berat[$key],
                "total" => $request->total[$key],
                "created_at" => $request->tanggal
            ]);
        }
        Tabungan::create([
            'user_id' =>$request->user,
            'debit' =>$request->totalharga,
            'tanggal' => $request->tanggal
        ]);
        return redirect()->back()->with('message', 'Transaksi Berhasil Ditambah');
    }
    public function transaksi_hapus($id)
    {
        Sampah::where('id_transaksi',$id)->delete();
        Transaksi::where('id',$id)->delete();
        return redirect()->back()->with('message', 'Transaksi Berhasil Dihapus');
    }
    public function penarikan()
    {
        $user = User::where('role',4)->get();
        $sisahtabungan = [];
        $jumlahtabungan = [];
        foreach ($user as $key => $value) {
            $jumlahtabungan[$key] =  Tabungan::where('user_id',$value->id)->sum('debit');
            $kredit =  Tabungan::where('user_id',$value->id)->sum('kredit');
            $sisahtabungan[$key] = $jumlahtabungan[$key]-$kredit;
        }
        //dd($jumlahtabungan);
        return view('transaksi.penarikan',[
            'page' => ['active' => 'penarikan'],
            'user' => $user,
            'jumlahtabungan' => $jumlahtabungan,
            'sisahtabungan' => $sisahtabungan
        ]);
    }
    public function penarikan_tambah(Request $request)
    {
        $oke = Tabungan::create([
            'user_id' =>$request->user,
            'kredit' =>$request->kredit,
            'tanggal' => $request->tanggal,
            'keterangan' => $request->keterangan,
        ]);
        return redirect()->back()->with('message', 'Transaksi Berhasil Ditambah');
    }

}
