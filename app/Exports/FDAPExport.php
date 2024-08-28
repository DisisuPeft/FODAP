<?php

namespace App\Exports;

use App\Http\Controllers\EstadisticasController;
use Maatwebsite\Excel\Concerns\FromCollection;

class FDAPExport implements FromCollection
{
    public $payload;

    public function __construct($payload){
        $this->payload = $payload;
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return collect(EstadisticasController::fd_ap_cursos($this->payload));
    }
}
