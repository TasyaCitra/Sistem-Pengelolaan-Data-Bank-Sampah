<?php

namespace App\Http\Controllers;

use App\Exports\TransaksiBulanan;
use App\Exports\TransaksiHarian;
use App\Sampah;
use App\Tabungan;
use App\Transaksi;
use App\JenisSampah;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PDF;

class LaporanController extends Controller
{
    public function transaksi()
    {
        $bulanan = Transaksi::select(DB::raw('YEAR(tanggal) year, MONTH(tanggal) month, MONTHNAME(tanggal) month_name'))
            ->distinct()
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->latest()->get();
        $bulanantb = [];
        $bulananth = [];
        foreach ($bulanan as $key => $value) {
            $tanggal = date('Y-m', strtotime($value->year . '-' . $value->month));
            $bulanantb[$key] = Transaksi::where('tanggal', 'like', $tanggal . '%')->sum('totalberat');
            $bulananth[$key] = Transaksi::where('tanggal', 'like', $tanggal . '%')->sum('totalharga');
        }
        $harian = Transaksi::join('users', 'users.id', '=', 'user')->select('users.name', 'users.id as idu', 'transaksis.*')->latest()->get();
        $sampah = [];
        $penarikan = [];
        $jumlah = [];
        $sisah = [];
        foreach ($harian as $key => $value) {
            $sampah[$key] = Sampah::join('jenis_sampahs', 'jenis_sampahs.id', '=', 'id_jenis')->where('id_transaksi', $value->id)->select('jenis_sampahs.jenis', 'sampahs.*')->get();
            $penarikan[$key] = Tabungan::where('kredit', '>', 0)->where('user_id', $value->idu)->where('tanggal', $value->tanggal)->get();
            $kredit = Tabungan::where('user_id', $value->idu)->sum('kredit');
            $jumlah[$key] = Tabungan::where('user_id', $value->idu)->sum('debit');
            $sisah[$key] = $jumlah[$key] - $kredit;
        }
        return view('laporan.transaksai', [
            'page' => ['active' => 'Laporan Transaksi'],
            'harian' => $harian,
            'sampah' => $sampah,
            'bulanan' => $bulanan,
            'bulanantb' => $bulanantb,
            'bulananth' => $bulananth,
            'penarikan' => $penarikan,
            'jumlah' => $jumlah,
            'sisah' => $sisah
        ]);
    }
    public function transaksi_harian_excel($id)
    {
        return Excel::download(new TransaksiHarian($id), 'Laporan Transaksi id' . $id . ' harian.xlsx');
    }
    public function transaksi_harian_pdf($id)
    {

        $transaksi = Transaksi::join('users', 'users.id', '=', 'user')->where('transaksis.id', $id)->select('users.name', 'users.id as idu', 'transaksis.*')->first();
        $sampah = Sampah::join('jenis_sampahs', 'jenis_sampahs.id', '=', 'id_jenis')->where('id_transaksi', $id)->select('jenis_sampahs.jenis', 'sampahs.*')->get();
        $penarikan = Tabungan::where('kredit', '>', 0)->where('user_id', $transaksi->idu)->where('tanggal', $transaksi->tanggal)->get();
        $kredit = Tabungan::where('user_id', $transaksi->idu)->sum('kredit');
        $jumlah = Tabungan::where('user_id', $transaksi->idu)->sum('debit');
        $sisah = $jumlah - $kredit;
        $pdf = PDF::loadview('laporan.transaksi-harian-excel', [
            'transaksi' => $transaksi,
            'sampah' => $sampah,
            'penarikan' => $penarikan,
            'jumlah' => $jumlah,
            'sisah' => $sisah
        ]);
        return $pdf->download('Laporan Transaksi id' . $id . ' harian.pdf');
    }
    public function transaksi_bulanan_excel($tgl)
    {
        return Excel::download(new TransaksiBulanan($tgl), 'Laporan Transaksi bulanan tahun' . $tgl . '.xlsx');
    }
    public function transaksi_bulanan_pdf($tgl)
    {
        $transaksi = Transaksi::join('users', 'users.id', '=', 'user')->where('tanggal', 'LIKE', $tgl . '%')->select('users.name', 'users.id as idu', 'transaksis.*')->get();
        $totalberat = Transaksi::where('tanggal', 'LIKE', $tgl . '%')->sum('totalberat');
        $totalharga = Transaksi::where('tanggal', 'LIKE', $tgl . '%')->sum('totalharga');
        $penarikan = Tabungan::join('users', 'users.id', '=', 'user_id')->where('kredit', '>', 0)->where('tanggal', 'LIKE', $tgl . '%')->select('users.name', 'users.id as idu', 'tabungans.*')->get();
        $totalkredit = Tabungan::where('kredit', '>', 0)->where('tanggal', 'LIKE', $tgl . '%')->sum('kredit');
        $jenis_sampah = JenisSampah::get();
        foreach ($jenis_sampah as $key => $value) {
            $jumlah_sampah[$key] = Sampah::where('id_jenis', $value->id)->where('created_at', 'LIKE', $tgl . '%')->sum('jumlah');
        }
        $pdf = PDF::loadview('laporan.transaksi-bulanan-excel', [
            'transaksi' => $transaksi,
            'penarikan' => $penarikan,
            'tgl' => $tgl,
            'totalberat' => $totalberat,
            'totalharga' => $totalharga,
            'totalkredit' => $totalkredit,
            'jenis_sampah' => $jenis_sampah,
            'jumlah_sampah' => $jumlah_sampah,
        ]);
        return $pdf->download('Laporan Transaksi bulanan tahun' . $tgl . '.pdf');
    }
    public function keuangan(Request $request)
    {
        if ($request->bulan) {
            $bulan = date('F Y', strtotime($request->bulan));
            $tgl = $request->bulan;
        } else {
            $bulan = date('F Y');
            $tgl = date('Y-m-');
        }
        $jenis_sampah = JenisSampah::orderBy('jenis', 'asc')->get();
        foreach ($jenis_sampah as $key => $value) {
            $jumlah_sampah[$key] = Sampah::where('id_jenis', $value->id)->where('created_at', 'LIKE', $tgl . '%')->sum('jumlah');
        }
        $hari = Tabungan::where('debit', '>', 0)
            ->where('tanggal', 'LIKE', $tgl . '%')
            ->select(DB::raw('YEAR(tanggal) year, MONTH(tanggal) month, MONTHNAME(tanggal) month_name, DAY(tanggal) day'), 'tanggal')
            ->distinct()
            ->orderBy('day', 'asc')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();
        $label = [];
        $data = [];
        $pemasukan = [];
        foreach ($hari as $key => $value) {
            $hp = 0;
            $hb = 0;
            // $trx = Sampah::where('created_at', $value->tanggal)->get();
            // foreach ($trx as $k => $v) {
            //     $harga = JenisSampah::where('id',$v->id_jenis)->first();
            //     $hp += $harga->pengepul*$v->jumlah;
            //     $hb += $harga->harga*$v->jumlah;
            // }
            foreach ($jenis_sampah as $k => $v) {
                $js[$k] = Sampah::where('id_jenis', $v->id)->where('created_at', 'LIKE', $value->tanggal . '%')->sum('jumlah');
                $hp += $v->pengepul * $js[$k];
                $hb += $v->harga * $js[$k];
            }
            $label[$key] = $value->tanggal;
            $data[$key] = $hp-$hb;
            
        }
        if ($request->pdf) {
            $pdf = PDF::loadview('laporan.keuangan-pdf', [
                'tanggal' => $label,
                'keuntungan' => $data,
                'pemasukan' => $pemasukan,
                'periode' => $bulan,
                'jenis_sampah' => $jenis_sampah,
                'jumlah_sampah' => $jumlah_sampah
            ])->setPaper('A4', 'landscape');;
            return $pdf->download('Laporan keuangan bulanan tahun' . $bulan . '.pdf');
        }
        return view('laporan.keuangan', [
            'page' => ['active' => 'Laporan Keuntungan'],
            'label' => $label,
            'data' => $data,
            'bulan' => $bulan
        ]);
    }
}
