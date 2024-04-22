<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;

class ReconocimientoExport implements FromView
{
    public $query;

    public function __construct($query)
    {
        $this->query = $query;
    }
    public function view(): View
    {
        return view('exports.reconocimientoExport', [
            'data' => $this->query
        ]);
    }
    // /**
    //  * @return \Illuminate\Support\Collection
    //  */
    // public function collection(): \Illuminate\Support\Collection
    // {
    //     return collect($this->query);
    // }
}
