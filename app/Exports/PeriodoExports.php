<?php

namespace App\Exports;

use App\Http\Controllers\EstadisticasController;
use App\Models\DeteccionNecesidades;
use Maatwebsite\Excel\Concerns\FromCollection;

class PeriodoExports implements FromCollection
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
        return collect(EstadisticasController::estadistica_cursos_periodo($this->payload));
    }
}
