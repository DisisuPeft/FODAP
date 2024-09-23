<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;

class DocentesCapExport implements FromView
{
    public $q;
    public function __construct($q)
    {
        $this->q = $q;
    }

    public function view(): View
    {
        return view('exports.CapacitadosExport', [
            'data' => $this->q
        ]);
    }
}
