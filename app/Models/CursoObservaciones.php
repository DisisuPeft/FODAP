<?php

namespace App\Models;

use App\Notifications\ObservacionNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CursoObservaciones extends Model
{
    use HasFactory;

    protected $table = 'curso_observaciones';
    protected $fillable = [
        'curso_id', 'observaciones'
    ];

    public function curso(){
        return $this->belongsTo(DeteccionNecesidades::class);
    }

    public function addObservaciones($id, $payload){
        DB::beginTransaction();
        $obs = CursoObservaciones::create([
            'curso_id' => $id,
            'observaciones' => $payload->correccion,
        ]);
//        dd($obs->curso);
        $curso = $obs->curso;
        if ($obs){
            User::where('departamento_id', $curso->id_departamento)->role(['Jefes Academicos'])->each(function (User $user) use ($curso) {
                $user->notify(new ObservacionNotification($curso, $user));
            });
            DB::commit();
            return true;
        }
        DB::rollBack();
        return false;
    }

    public function updateObservaciones($id, $payload)
    {
        DB::beginTransaction();
        $cursoObs = CursoObservaciones::with('curso')->where('curso_id', $id)->first();
//        dd($cursoObs->curso);
        $curso = $cursoObs->curso;
        if ($cursoObs){
            $update = $cursoObs->update([
               'observaciones' => $payload->correccion,
            ]);
            if($update){
                User::where('departamento_id', $curso->id_departamento)->role(['Jefes Academicos'])->each(function (User $user) use ($curso) {
                    $user->notify(new ObservacionNotification($curso, $user));
                });
                DB::commit();
                return true;
            }
            DB::rollBack();
            return false;
        }
        DB::rollBack();
        return false;
    }
}
