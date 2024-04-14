<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Posgrado extends Model
{
    use HasFactory;

    protected $table = 'posgrado';

    protected $fillable = [
        'nombre'
    ];

    public function docente_has_posgrado(){
        return $this->belongsTo(Docente::class, 'id_posgrado', 'id');
    }

}
