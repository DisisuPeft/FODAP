<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Docente extends Model
{
    use HasFactory;
    protected $table = 'docente';

    protected $fillable = [
        'rfc', 'curp', 'nombre', 'apellidoPat', 'apellidoMat',
        'sexo', 'email', 'departamento_id', 'telefono', 'interno', 'carrera_id', 'user_id', 'id_puesto', 'tipo_plaza', 'licenciatura', 'id_posgrado', 'nombre_completo'
    ];
    protected $primaryKey = 'id';

    //    protected $with = ['inscrito', 'calificacion_docente'];
    public function facilitador_has_deteccion()
    {
        return $this->belongsToMany(DeteccionNecesidades::class, 'deteccion_has_facilitadores', 'docente_id', 'deteccion_id');
    }

    public function inscrito()
    {
        return $this->belongsToMany(DeteccionNecesidades::class, 'inscripcion', 'docente_id', 'curso_id');
    }

    public function deteccion()
    {
        return $this->hasMany(DeteccionNecesidades::class, 'id_jefe', 'id');
    }

    public function jefe_deteccion()
    {
        return $this->belongsTo(DeteccionNecesidades::class, 'id_jefe', 'id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function jefe_departamento()
    {
        return $this->belongsTo(Departamento::class, 'jefe_id', 'id');
    }

    public function academia_presidente(): BelongsTo
    {
        return $this->belongsTo(Carrera::class, 'presidente_academia', 'id');
    }

    public function posgrado()
    {
        return $this->hasOne(Posgrado::class, 'id', 'id_posgrado');
    }

    public function plaza()
    {
        return $this->hasOne(Plaza::class, 'id', 'tipo_plaza');
    }

    public function departamento()
    {
        return $this->hasOne(Departamento::class, 'id', 'departamento_id');
    }

    public function puesto()
    {
        return $this->hasOne(Puesto::class, 'id', 'id_puesto');
    }

    public function cvu()
    {
        return $this->hasOne(FilesCVU::class, 'id_docente', 'id');
    }
    public function carrera()
    {
        return $this->hasOne(Carrera::class, 'id', 'carrera_id');
    }

    public function calificacion_docente()
    {
        return $this->hasOne(Calificaciones::class, 'docente_id', 'id');
    }

    public function docente_existe($payload){
        $nombre = strtolower(trim($payload->nombre));
        $apellidoPat = strtolower(trim($payload->apellidoPat));
        $apellidoMat = strtolower(trim($payload->apellidoMat));
        $q = DB::table("docente")
            ->where(DB::raw('nombre'), '=', $nombre)
            ->where(DB::raw('apellidoPat'), '=', $apellidoPat)
            ->where(DB::raw('apellidoMat'), '=', $apellidoMat)->first();
        if ($q) {
            return [true, $q];
        }else{
            return [false, 'No existe el nombre del docente'];
        }
    }
    public function create_instance_docente($request, $type)
    {
        DB::beginTransaction();
        $existe = $this->docente_existe($request);
        if ($existe[0]){
            DB::rollBack();
            return back()->withErrors('El nombre que se ingreso ya existe en la base de datos.');
        }else{
            $docente = null;
            if (isset($type)){
                $docente = Docente::create($request->all() + [
                        'nombre_completo' => $request->nombre . " " . $request->apellidoPat . " " . $request->apellidoMat,
                    ]);
            }else{
//                dd('si entramos aqui');
                $docente = Docente::create($request->validated() + [
                        'nombre_completo' => $request->nombre . " " . $request->apellidoPat . " " . $request->apellidoMat,
                        'user_id' =>  $request->id,
                ]);

            }
            if($docente){
                DB::commit();
//                                dd('si entramos aqui', $docente);
                $docente->save();
                return $docente;
            }else{
                DB::rollBack();
                return back()->withErrors('Error al crear el docente.');
            }
        }
    }
    public static function updated_instance_docente($request, $id, $type)
    {
        DB::beginTransaction();
//        $request->validated();
        $docente = Docente::with('usuario')->where('id', $id)->first();
        if ($docente){
//            $update = $docente->update($request->validated() + [
//                    'nombre_completo' => $request->nombre . " " . $request->apellidoPat . " " . $request->apellidoMat,
//                    'user_id' => $docente->usuario->id ?? $request->id,
//                ]);
            $update = null;
            if (isset($type)){
                $update = $docente->update($request->all() + [
                        'nombre_completo' => $request->nombre . " " . $request->apellidoPat . " " . $request->apellidoMat,
                ]);
            }else{
                $update = $docente->update($request->validated() + [
                        'nombre_completo' => $request->nombre . " " . $request->apellidoPat . " " . $request->apellidoMat,
                        'user_id' => $docente->usuario->id ?? $request->id,
                    ]);
            }
            if ($update){
                DB::commit();
            }else{
                DB::rollBack();
                return back()->withErrors('El docente no se pudo actualizar');
            }
        }else{
            DB::rollBack();
            return back()->withErrors('El docente no existe');
        }
    }

    public function delete_docente($id)
    {
        DB::beginTransaction();
        if ($id){
            $docente = Docente::where('id', $id)->first();
            if ($docente){
                $delete = $docente->delete();
                if ($delete){
                    DB::commit();
                }else{
                    DB::rollBack();
                    return back()->withErrors('El docente no se pudo eliminar');
                }
            }else{
                DB::rollBack();
                return back()->withErrors('El docente no existe');
            }
        }else{
            DB::rollBack();
            return back()->withErrors('El ID que se compartió no existe en la base de datos.');
        }
    }
}
