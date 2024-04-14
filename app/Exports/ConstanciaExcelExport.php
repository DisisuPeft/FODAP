<?php

namespace App\Exports;

use App\Http\Controllers\DesarrolloController;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;

class ConstanciaExcelExport implements FromCollection
{
    public $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

//    public function view(): View
//    {
//        return view('exports.constanciaExport', [
//            'data' => $this->query
//        ]);
//    }

    public function collection(): \Illuminate\Support\Collection
    {
        return collect($this->query);
    }
}
