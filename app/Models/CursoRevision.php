<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CursoRevision extends Model
{
    use HasFactory;

    protected $table = 'curso_revision';
    protected $fillable = [
        'folio', 'curso_id'
    ];
    public function curso(){
        return $this->belongsTo(DeteccionNecesidades::class, 'id', 'curso_id');
    }

    public function incrementRev($curso_id){
        
    }
}
