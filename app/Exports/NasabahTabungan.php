<?php

namespace App\Exports;

use App\Transaksi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;

class NasabahTabungan implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function __construct($id)
    {
        $this->id= $id;
    }
    public function view(): View
    {
        $transaksi = Transaksi::where('id',$this->id)->first();
        return view('nasabah_tabungan_excel', [
            'transaksi' => $transaksi,
        ]);
    }
}
