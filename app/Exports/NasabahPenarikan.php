<?php

namespace App\Exports;

use App\Tabungan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;

class NasabahPenarikan implements FromView
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
        $penarikan = Tabungan::where('id',$this->id)->first();
        return view('nasabah_penarikan_excel', [
            'penarikan' => $penarikan,
        ]);
    }
}
