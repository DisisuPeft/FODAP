<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

class Calificaciones extends Model
{
    use HasFactory;

    protected $table = 'calificaciones';
    protected $primaryKey = 'id';


    protected $fillable = [
        'calificacion', 'docente_id', 'curso_id',
    ];

    public function docente_calificacion(){
        return $this->belongsTo(Docente::class, 'docente_id');
    }

    public function curso_calificaciones(){
        return $this->belongsTo(DeteccionNecesidades::class, 'curso_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('docente_calificacion', function ($builder) {
            $builder->with('docente_calificacion');
        });
    }

    public function add_calificacion($payload)
    {
        if (isset($payload->curso_id)) {
                DB::beginTransaction();
                $calificacion = Calificaciones::create($payload->validated());
                if ($calificacion){
                    DB::commit();
                    return ["Calificacion creada correctamente.", 200];
                }else{
                    DB::rollback();
                    return ["Calificacion no creada correctamente.", 500];
                }
        }else{
            return ["El ID curso no tiene ningun valor.", 500];
        }
    }

    public function update_calificacion($payload, $id)
    {
        if (isset($payload->curso_id) && isset($id)) {
            DB::beginTransaction();

            $calificacion = Calificaciones::where(function ($q) use ($payload, $id) {
                $q->where('docente_id', $id)
                  ->where('curso_id', $payload->curso_id);
            })->update($payload->validated());

            if ($calificacion > 0){
                DB::commit();
                return ["Calificacion actualizada correctamente.", 200];
            }else{
                DB::rollback();
                return ["La calificación no requirió ser actualizada correctamente.", 500];
            }
        }else{
            return ["El ID curso no tiene ningun valor asi como la calificacion asociada.", 500];
        }
    }
}
