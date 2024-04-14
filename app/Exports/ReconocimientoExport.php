<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;

class ReconocimientoExport implements FromCollection
{
    public $query;

    public function __construct($query){
        $this->query = $query;
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection(): \Illuminate\Support\Collection
    {
        return collect($this->query);
    }
}
