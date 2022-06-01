<?php

namespace App\Exports;

use App\Tabungan;
use App\Transaksi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;

class TransaksiBulanan implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function __construct($tgl)
    {
        $this->tgl= $tgl;
    }
    public function view(): View
    {
        $transaksi = Transaksi::join('users', 'users.id', '=', 'user')->where('tanggal','LIKE', $this->tgl.'%')->select('users.name', 'users.id as idu', 'transaksis.*')->get();
        $totalberat = Transaksi::where('tanggal','LIKE', $this->tgl.'%')->sum('totalberat');
        $totalharga = Transaksi::where('tanggal','LIKE', $this->tgl.'%')->sum('totalharga');
        $penarikan = Tabungan::join('users', 'users.id', '=', 'user_id')->where('kredit', '>', 0)->where('tanggal','LIKE', $this->tgl.'%')->select('users.name', 'users.id as idu', 'tabungans.*')->get();
        $totalkredit = Tabungan::where('kredit', '>', 0)->where('tanggal','LIKE', $this->tgl.'%')->sum('kredit');
        return view('laporan.transaksi-bulanan-excel', [
            'transaksi' => $transaksi,
            'penarikan' => $penarikan,
            'tgl' => $this->tgl,
            'totalberat' => $totalberat,
            'totalharga' => $totalharga,
            'totalkredit' => $totalkredit
        ]);
    }
}
