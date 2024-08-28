<?php

namespace App\Exports;

use App\Http\Controllers\EstadisticasController;
use App\Models\DeteccionNecesidades;
use Maatwebsite\Excel\Concerns\FromCollection;

class EstadisticasExport implements FromCollection
{
    public $payload;

    public function __construct($payload){
        $this->payload = $payload;
    }
    /**
    * @return \Illuminate\Support\Collection
     */
    public function collection(): \Illuminate\Support\Collection
    {
        return collect(EstadisticasController::estadistica_cursos_tipo($this->payload));
    }
}
