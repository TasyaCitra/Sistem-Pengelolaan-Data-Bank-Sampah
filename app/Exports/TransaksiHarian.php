<?php

namespace App\Exports;

use App\Sampah;
use App\Tabungan;
use App\Transaksi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class TransaksiHarian implements FromView
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function __construct($id)
    {
        $this->id = $id;
    }
    public function view(): View
    {
        $transaksi = Transaksi::join('users', 'users.id', '=', 'user')->where('transaksis.id', $this->id)->select('users.name', 'users.id as idu', 'transaksis.*')->first();
        $sampah = Sampah::join('jenis_sampahs', 'jenis_sampahs.id', '=', 'id_jenis')->where('id_transaksi', $this->id)->select('jenis_sampahs.jenis', 'sampahs.*')->get();
        $penarikan = Tabungan::where('kredit', '>', 0)->where('user_id', $transaksi->idu)->where('tanggal', $transaksi->tanggal)->get();
        $kredit = Tabungan::where('user_id', $transaksi->idu)->sum('kredit');
        $jumlah = Tabungan::where('user_id', $transaksi->idu)->sum('debit');
        $sisah = $jumlah - $kredit;
        return view('laporan.transaksi-harian-excel', [
            'transaksi' => $transaksi,
            'sampah' => $sampah,
            'penarikan' => $penarikan,
            'jumlah' => $jumlah,
            'sisah' => $sisah
        ]);
    }
}
