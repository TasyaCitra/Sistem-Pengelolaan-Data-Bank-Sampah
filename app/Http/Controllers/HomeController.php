<?php

namespace App\Http\Controllers;

use App\Beranda;
use App\Exports\NasabahPenarikan;
use App\Exports\NasabahTabungan;
use App\Galery;
use App\JenisSampah;
use App\SampahBotolKaca;
use App\SampahElektrik;
use App\SampahKertas;
use App\SampahLogam;
use App\SampahMenu;
use App\SampahPlastik;
use App\Tabungan;
use App\Transaksi;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use PDF;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');

        $this->page = [
            'active' => 'dashboard'
        ];
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        if(Auth::user()->role == 4){
            return redirect()->route('nasabah-home');
        }
        $totalberat = Transaksi::sum('totalberat');
        $totaltransaksi = Transaksi::sum('totalharga');
        $totalnasabah = User::where('role', 4)->count();
        $hari = Transaksi::where('tanggal', 'LIKE', date('Y-m-') . '%')
            ->select(DB::raw('YEAR(tanggal) year, MONTH(tanggal) month, MONTHNAME(tanggal) month_name, DAY(tanggal) day'), 'tanggal')
            ->distinct()
            ->orderBy('day', 'asc')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();
        $label = [];
        $data = [];
        foreach ($hari as $key => $value) {
            $label[$key] = $value->tanggal;
            $data[$key] = Transaksi::where('tanggal',$value->tanggal)->sum('totalberat');
        }
        return view('admin.index', [
            'page' => $this->page,
            'totalberat' => $totalberat,
            'totaltransaksi' => $totaltransaksi,
            'totalnasabah' => $totalnasabah,
            'label' => $label,
            'data' => $data,
        ]);
    }

    public function profile()
    {
        $user = User::findOrFail(Auth::user()->id);

        return view('admin.profile', [
            'page' => ['active' => 'profile'],
            'user' => $user
        ]);
    }

    public function change(Request $request, User $user)
    {
        $request->validate([
            'old_password' => 'required|string|max:255',
            'password' => 'required|string|max:255|min:8|confirmed'
        ]);

        $user = User::findOrFail(Auth::user()->id);

        if (Hash::check($request->old_password, $user->password)) {

            $user->password = Hash::make($request->password);

            try {
                $user->save();

                return redirect()->back()->with('message', 'Password Berhasil Diubah');
            } catch (\Throwable $th) {
                return redirect()->back()->withErrors($th->getMessage());
            }
        } else {
            return redirect()->back()->withErrors('Password Salah');
        }
    }
    public function galery()
    {
        return view('galery', [
            'page' => ['active' => 'galery'],
            'galery' => Galery::get()
        ]);
    }
    public function galery_tambah(Request $request)
    {
        $file = $request->file('gambar');
        $namafile = $file->getClientOriginalName();
        $file->move('img/galery', $namafile);
        Galery::insert([
            'judul' => $request->judul,
            'gambar' => $namafile
        ]);
        return redirect()->back()->with('message', 'Galery berhasil diupload');
    }
    public function galery_hapus($id)
    {
        Galery::where('id', $id)->delete();
        return redirect()->back()->with('message', 'Galery berhasil dihapus');
    }
    public function edit_welcome()
    {
        return view('editwelcome', [
            'page' => ['active' => 'Edit Welcome'],
            'beranda' => Beranda::first(),
            'sampahlogam' => SampahMenu::where('tipe',1)->join('jenis_sampahs','jenis_sampahs.id','=','sampah_menus.jenis_sampah')->select('jenis_sampahs.jenis', 'sampah_menus.*')->get(),
            'sampahplastik' => SampahMenu::where('tipe',2)->join('jenis_sampahs','jenis_sampahs.id','=','sampah_menus.jenis_sampah')->select('jenis_sampahs.jenis', 'sampah_menus.*')->get(),
            'sampahkertas' => SampahMenu::where('tipe',3)->join('jenis_sampahs','jenis_sampahs.id','=','sampah_menus.jenis_sampah')->select('jenis_sampahs.jenis', 'sampah_menus.*')->get(),
            'sampahbotolkaca' => SampahMenu::where('tipe',4)->join('jenis_sampahs','jenis_sampahs.id','=','sampah_menus.jenis_sampah')->select('jenis_sampahs.jenis', 'sampah_menus.*')->get(),
            'sampahelektrik' => SampahMenu::where('tipe',5)->join('jenis_sampahs','jenis_sampahs.id','=','sampah_menus.jenis_sampah')->select('jenis_sampahs.jenis', 'sampah_menus.*')->get(),
            'jenissampah' => JenisSampah::get(),
        ]);
    }
    public function edit_beranda(Request $request)
    {
        Beranda::where('id', 1)->update([
            'beranda' => $request->beranda,
        ]);
        return redirect()->back()->with('message', 'Beranda berhasil diupdate');
    }
    public function edit_tentang(Request $request)
    {
        Beranda::where('id', 1)->update([
            'tentang1' => $request->tentang1,
            'tentang2' => $request->tentang2
        ]);
        return redirect()->back()->with('message', 'Tentang berhasil diupdate');
    }
    public function edit_kontak(Request $request)
    {
        Beranda::where('id', 1)->update([
            'email' => $request->email,
            'telpon' => $request->telpon,
            'alamat' => $request->alamat,
        ]);
        return redirect()->back()->with('message', 'Kontak berhasil diupdate');
    }
    public function edit_sampah($id, Request $request)
    {
        SampahMenu::create([
            'tipe' => $id,
            'jenis_sampah' => $request->jenis,
            'keterangan' => $request->keterangan
        ]);
        return redirect()->back()->with('message', 'Sampah berhasil ditambah');
    }
    public function hapus_sampah($sampah, $id)
    {
        SampahMenu::where('id',$id)->delete();
        return redirect()->back()->with('message', 'Sampah berhasil dihapus');
    }
    public function nasabah()
    {
        $debit = Tabungan::where('debit','>',0)->where('user_id',Auth::user()->id)->sum('debit');
        $kredit = Tabungan::where('kredit','>',0)->where('user_id',Auth::user()->id)->sum('kredit');
        $saldo = $debit-$kredit;
        $transaksi = Transaksi::where('user',Auth::user()->id)->get();
        $penarikan = Tabungan::where('kredit','>',0)->where('user_id',Auth::user()->id)->get();
        return view('nasabah_home', [
            'page' => ['active' => 'dashboard'],
            'saldo' => $saldo,
            'transaksi' => $transaksi,
            'penarikan' => $penarikan
        ]);
    }
    public function nasabah_tabungan_excel($id)
    {
        return Excel::download(new NasabahTabungan($id), 'Laporan tabungan id :' . $id . '.xlsx');
    }
    public function nasabah_tabungan_pdf($id)
    {
        $transaksi = $transaksi = Transaksi::join('users', 'users.id', '=', 'user')->where('transaksis.id', $id)->select('users.name', 'users.id as idu', 'transaksis.*')->first();
        $pdf = PDF::loadview('nasabah_tabungan_pdf', [
            'transaksi' => $transaksi,
        ]);
        return $pdf->download('Laporan Tabungan id : ' . $id . '.pdf');
    }
    public function nasabah_penarikan_excel($id)
    {
        return Excel::download(new NasabahPenarikan($id), 'Laporan penarikan id :' . $id . '.xlsx');
    }
    public function nasabah_penarikan_pdf($id)
    {
        $penarikan = Tabungan::join('users', 'users.id', '=', 'user_id')->where('tabungans.id', $id)->select('users.name', 'users.id as idu', 'tabungans.*')->first();
        $pdf = PDF::loadview('nasabah_penarikan_pdf', [
            'penarikan' => $penarikan,
        ]);
        return $pdf->download('Laporan Penarikan id : ' . $id . '.pdf');
    }
}
