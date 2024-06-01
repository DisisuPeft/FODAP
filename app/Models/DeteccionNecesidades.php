<?php

namespace App\Models;

use App\Http\Controllers\PDFController;
use GrahamCampbell\ResultType\Error;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;
use App\Models\User;
use GuzzleHttp\Psr7\Request;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class DeteccionNecesidades extends Model
{
    use HasFactory, Notifiable;
    protected $table = 'deteccion_necesidades';
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'asignaturaFA', 'contenidosTM', 'numeroProfesores', 'periodo',
        'nombreCurso', 'fecha_I', 'fecha_F', 'hora_I', 'hora_F', 'objetivoEvento', 'tipo_FDoAP', 'tipo_actividad',
        'carrera_dirigido', 'observaciones', 'id_jefe', 'obs', 'aceptado', 'modalidad', 'facilitador_externo', 'total_horas', 'id_departamento', 'id_lugar', 'estado', 'anio_realizacion', 'status'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [];

    protected $dates = [
        'created_at',
        'updated_at',
        'fecha_I',
        'fecha_F',
        'anio_realizacion'
    ];

    protected $with = ['clave_curso', 'clave_validacion'];
    //relaciones
    public function deteccion_facilitador()
    {
        return $this->belongsToMany(Docente::class, 'deteccion_has_facilitadores', 'deteccion_id', 'docente_id');
    }

    public function carrera(): HasOne
    {
        return $this->hasOne(Carrera::class, 'id', 'carrera_dirigido');
    }

    public function departamento(): HasOne
    {
        return $this->hasOne(Departamento::class, 'id', 'id_departamento');
    }

    public function jefe(): HasOne
    {
        return $this->hasOne(Docente::class, 'id', 'id_jefe');
    }

    public function docente_inscrito(): BelongsToMany
    {
        return $this->belongsToMany(Docente::class, 'inscripcion', 'curso_id', 'docente_id');
    }

    public function lugar()
    {
        return $this->hasOne(Lugar::class, 'id', 'id_lugar');
    }

    public function ficha_tecnica(): HasOne
    {
        return $this->hasOne(FichaTecnica::class, 'id_curso');
    }

    public function calificaciones_curso()
    {
        return $this->hasMany(Calificaciones::class, 'curso_id', 'id');
    }

    public function clave_curso()
    {
        return $this->hasOne(ClaveCurso::class, 'curso_id', 'id');
    }

    public function clave_validacion()
    {
        return $this->hasOne(ClaveValidacion::class, 'curso_id', 'id');
    }

    //seccion

    public function formacion_docente()
    {
        return DeteccionNecesidades::with('carrera', 'deteccion_facilitador', 'jefe', 'departamento')
            ->where('id_jefe', auth()->user()->docente_id)
            ->where(function ($query) {
                $query->where('aceptado', '=', 1)
                    ->where('estado', '=', 2)
                    ->where('tipo_FDoAP', '=', 1);
            })
            ->orderBy('id', 'desc')
            ->get();
    }

    public function actualizacion()
    {
        return DeteccionNecesidades::with('carrera', 'deteccion_facilitador', 'jefe', 'departamento')
            ->where('id_jefe', auth()->user()->docente_id)
            ->where(function ($query) {
                $query->where('aceptado', '=', 1)
                    ->where('estado', '=', 2)
                    ->where('tipo_FDoAP', '=', 2);
            })
            ->orderBy('id', 'desc')
            ->get();
    }

    //spageti

    public static function contancia_export($payload)
    {
        $year = date('Y');
        $instituto = DB::table('nombre_instituto')->get();
        $curso = DeteccionNecesidades::with('deteccion_facilitador', 'clave_curso', 'clave_validacion')->find($payload->id);
        $coordinacion = User::with('docente')->where('email', 'cformacion@tuxtla.tecnm.mx')->first();
        $ficha = FichaTecnica::where('id_curso', $payload->id)->first();
        $curso_id = $payload->input('id');
        //        $facilitadores = DeteccionNecesidades::constancia_reconocimiento($curso);
        $c = "CONSTANCIA";
        //        $r = "Reconocimiento";

        $fecha = $curso->fecha_I;
        $fecha2 = $curso->fecha_F;
        $formatFechasI = explode("-", $fecha);
        $formatFechasF = explode("-", $fecha2);
        $month = PDFController::parse_date($fecha);

        $query = DB::table('docente')
            ->orderBy('nombre_completo')
            ->join('posgrado', 'posgrado.id', '=', 'docente.id_posgrado')
            ->join('tipo_plaza', 'tipo_plaza.id', '=', 'docente.tipo_plaza')
            ->join('puesto', 'puesto.id', '=', 'docente.id_puesto')
            ->join('departamento', 'departamento.id', '=', 'docente.departamento_id')
            ->join('carreras', 'carreras.id', '=', 'docente.carrera_id')
            ->join('inscripcion', 'inscripcion.docente_id', '=', 'docente.id')
            ->where('inscripcion.curso_id', '=', $curso_id)
            ->join('deteccion_necesidades', function ($join) use ($curso_id) {
                $join->on('inscripcion.curso_id', '=', 'deteccion_necesidades.id')
                    ->where('deteccion_necesidades.id', '=', $curso_id);
            })
            ->select(
                'docente.nombre_completo',
                'posgrado.nombre AS posgrado',
                'tipo_plaza.nombre AS plaza',
                'puesto.nombre AS puesto',
                'carreras.nameCarrera AS carrera',
                'departamento.nameDepartamento AS departamento',
                'deteccion_necesidades.nombreCurso'
            )
            ->get();
        //        $query->push($facilitadores);
        for ($i = 0; $i <= count($query) - 1; $i++) {
            $query[$i]->tipo = $c;

            if ($curso->periodo == 1) {
                $query[$i]->periodo = "enero-junio " . $formatFechasF[0];
            } else {
                $query[$i]->periodo = "agosto-diciembre " . $formatFechasF[0];
            }

            $query[$i]->fecha_imparticion = $formatFechasI[2] . " al " . $formatFechasF[2] . " de " . $month[0] . " de " . $formatFechasF[0];

            $query[$i]->lugar_registro = "Tuxtla Gutierrez, Chiapas; " . $month[2] . " " . $month[1] . " de " . $formatFechasF[0];

            if (count($curso->deteccion_facilitador) == 1) {

                $query[$i]->facilitador_1 = $curso->deteccion_facilitador[0]->nombre_completo;

                if ($curso->deteccion_facilitador[0]->sexo == 1) {
                    $query[$i]->genero_1 = "FACILITADOR";
                } else {
                    $query[$i]->genero_1 = "FACILITADORA";
                }
            } elseif (count($curso->deteccion_facilitador) == 2) {
                $query[$i]->facilitador_1 = $curso->deteccion_facilitador[0]->nombre_completo;

                if ($curso->deteccion_facilitador[0]->sexo == 1) {
                    $query[$i]->genero_1 = "FACILITADOR";
                } else {
                    $query[$i]->genero_1 = "FACILITADORA";
                }

                $query[$i]->facilitador_2 = $curso->deteccion_facilitador[1]->nombre_completo;

                if ($curso->deteccion_facilitador[1]->sexo == 1) {
                    $query[$i]->genero_2 = "FACILITADOR";
                } else {
                    $query[$i]->genero_2 = "FACILITADORA";
                }
            } elseif (count($curso->deteccion_facilitador) == 3) {
                $query[$i]->facilitador_1 = $curso->deteccion_facilitador[0]->nombre_completo;

                if ($curso->deteccion_facilitador[0]->sexo == 1) {
                    $query[$i]->genero_1 = "FACILITADOR";
                } else {
                    $query[$i]->genero_1 = "FACILITADORA";
                }

                $query[$i]->facilitador_2 = $curso->deteccion_facilitador[1]->nombre_completo;

                if ($curso->deteccion_facilitador[1]->sexo == 1) {
                    $query[$i]->genero_2 = "FACILITADOR";
                } else {
                    $query[$i]->genero_2 = "FACILITADORA";
                }

                $query[$i]->facilitador_3 = $curso->deteccion_facilitador[2]->nombre_completo;
                if ($curso->deteccion_facilitador[2]->sexo == 1) {
                    $query[$i]->genero_3 = "FACILITADOR";
                } else {
                    $query[$i]->genero_3 = "FACILITADORA";
                }
            }

            $query[$i]->nivel_educativo = "Superior";
            switch ($curso->modalidad) {
                case 1:
                    $query[$i]->modalidad = "Virtual";
                    break;
                case 2:
                    $query[$i]->modalidad = "Presencial";
                    break;
                case 3:
                    $query[$i]->modalidad = "Híbrido";
                    break;
            }
            $query[$i]->duracion = $curso->total_horas . " horas";

            $query[$i]->clave_registro = $curso->clave_curso->clave;

            for ($j = 0; $j <= count($ficha->temas) - 1; $j++) {
                $numero = $j + 1;
                $name_tema = $ficha->temas[$j]->name_tema;

                $query[$i]->{"numero_tema_$numero"} = $numero;
                $query[$i]->{"nombre_tema_$name_tema"} = $name_tema;
            }
        }
        return $query;
        //        return $facilitador;
    }

    public static function constancia_reconocimiento($payload)
    {
        $curso = DeteccionNecesidades::with('deteccion_facilitador')->find($payload->f);
        $ficha = FichaTecnica::where('id_curso', $payload->f)->first();
        $r = "Reconocimiento";
        //        $year = date('Y');
        $fecha = $curso->fecha_I;
        $fecha2 = $curso->fecha_F;
        $formatFechasI = explode("-", $fecha);
        $formatFechasF = explode("-", $fecha2);
        $month = PDFController::parse_date($fecha);
        $ids = [];

        foreach ($curso->deteccion_facilitador as $facilitador) {
            $ids[] = $facilitador->id;
        }

        $facilitadores = DB::table('docente')
            ->orderBy('nombre_completo')
            ->join('posgrado', 'posgrado.id', '=', 'docente.id_posgrado')
            ->join('tipo_plaza', 'tipo_plaza.id', '=', 'docente.tipo_plaza')
            ->join('puesto', 'puesto.id', '=', 'docente.id_puesto')
            ->join('departamento', 'departamento.id', '=', 'docente.departamento_id')
            ->join('carreras', 'carreras.id', '=', 'docente.carrera_id')
            ->join('deteccion_has_facilitadores', 'deteccion_has_facilitadores.docente_id', '=', 'docente.id')
            ->join('deteccion_necesidades', 'deteccion_necesidades.id', '=', 'deteccion_has_facilitadores.deteccion_id')
            ->whereIn('docente.id', $ids)
            ->select(
                'docente.nombre_completo',
                'posgrado.nombre AS posgrado',
                'tipo_plaza.nombre AS plaza',
                'puesto.nombre AS puesto',
                'carreras.nameCarrera AS carrera',
                'departamento.nameDepartamento AS departamento',
                'deteccion_has_facilitadores.deteccion_id',
                'deteccion_necesidades.nombreCurso'
            )
            ->get();

        for ($i = 0; $i <= count($facilitadores) - 1; $i++) {
            $facilitadores[$i]->tipo = $r;

            if ($curso->periodo == 1) {
                $facilitadores[$i]->periodo = "enero-junio " . $formatFechasF[0];
            } else {
                $facilitadores[$i]->periodo = "agosto-diciembre " . $formatFechasF[0];
            }

            $facilitadores[$i]->fecha_imparticion = $formatFechasI[2] . " al " . $formatFechasF[2] . " de " . $month[0] . " de " . $formatFechasF[0];

            $facilitadores[$i]->lugar_registro = "Tuxtla Gutierrez, Chiapas; " . $month[2] . " " . $month[1] . " de " . $formatFechasF[0];

            $facilitadores[$i]->nivel_educativo = "Superior";
            switch ($curso->modalidad) {
                case 1:
                    $facilitadores[$i]->modalidad = "Virtual";
                    break;
                case 2:
                    $facilitadores[$i]->modalidad = "Presencial";
                    break;
                case 3:
                    $facilitadores[$i]->modalidad = "Híbrido";
                    break;
            }
            $facilitadores[$i]->duracion = $curso->total_horas . " horas";

            $facilitadores[$i]->clave_registro = $curso->clave_curso->clave;

            for ($j = 0; $j <= count($ficha->temas) - 1; $j++) {
                $numero = $j + 1;
                $name_tema = $ficha->temas[$j]->name_tema;

                $facilitadores[$i]->{"numero_tema_$numero"} = $numero;
                $facilitadores[$i]->{"nombre_tema_$name_tema"} = $name_tema;
            }
        }
        return $facilitadores;
    }
    public static function docente_carrera_consult()
    {
        // Mecanica
        $totales_mecanica = DB::table('inscripcion')
            ->join('docente', 'docente.id', '=', 'inscripcion.docente_id')
            ->join('deteccion_necesidades', 'deteccion_necesidades.id', '=', 'inscripcion.curso_id')
            ->where('deteccion_necesidades.carrera_dirigido', '=', 1)
            ->distinct()
            ->select('docente.id')
            ->count();
        $totales_mecanica_masculinos = DB::table('inscripcion')
            ->join('docente', 'docente.id', '=', 'inscripcion.docente_id')
            ->join('deteccion_necesidades', 'deteccion_necesidades.id', '=', 'inscripcion.curso_id')
            ->where(function ($query) {
                $query->where('deteccion_necesidades.carrera_dirigido', '=', 1)
                    ->where('docente.sexo', '=', 1);
            })
            ->distinct()
            ->select('docente.id')
            ->count();
        $totales_mecanica_femenino = DB::table('inscripcion')
            ->join('docente', 'docente.id', '=', 'inscripcion.docente_id')
            ->join('deteccion_necesidades', 'deteccion_necesidades.id', '=', 'inscripcion.curso_id')
            ->where(function ($query) {
                $query->where('deteccion_necesidades.carrera_dirigido', '=', 1)
                    ->where('docente.sexo', '=', 2);
            })
            ->distinct()
            ->select('docente.id')
            ->count();
        //Sistemas 
        $totales_sistemas = DB::table('inscripcion')
            ->join('docente', 'docente.id', '=', 'inscripcion.docente_id')
            ->join('deteccion_necesidades', 'deteccion_necesidades.id', '=', 'inscripcion.curso_id')
            ->where('deteccion_necesidades.carrera_dirigido', '=', 2)
            ->distinct()
            ->select('docente.id')
            ->count();
        $totales_sistemas_masculinos = DB::table('inscripcion')
            ->join('docente', 'docente.id', '=', 'inscripcion.docente_id')
            ->join('deteccion_necesidades', 'deteccion_necesidades.id', '=', 'inscripcion.curso_id')
            ->where(function ($query) {
                $query->where('deteccion_necesidades.carrera_dirigido', '=', 2)
                    ->where('docente.sexo', '=', 1);
            })
            ->distinct()
            ->select('docente.id')
            ->count();
        $totales_sistemas_femenino = DB::table('inscripcion')
            ->join('docente', 'docente.id', '=', 'inscripcion.docente_id')
            ->join('deteccion_necesidades', 'deteccion_necesidades.id', '=', 'inscripcion.curso_id')
            ->where(function ($query) {
                $query->where('deteccion_necesidades.carrera_dirigido', '=', 2)
                    ->where('docente.sexo', '=', 2);
            })
            ->distinct()
            ->select('docente.id')
            ->count();
        //Industrial
        $totales_industrial = DB::table('inscripcion')
            ->join('docente', 'docente.id', '=', 'inscripcion.docente_id')
            ->join('deteccion_necesidades', 'deteccion_necesidades.id', '=', 'inscripcion.curso_id')
            ->where('deteccion_necesidades.carrera_dirigido', '=', 3)
            ->distinct()
            ->select('docente.id')
            ->count();
        $totales_industrial_masculinos = DB::table('inscripcion')
            ->join('docente', 'docente.id', '=', 'inscripcion.docente_id')
            ->join('deteccion_necesidades', 'deteccion_necesidades.id', '=', 'inscripcion.curso_id')
            ->where(function ($query) {
                $query->where('deteccion_necesidades.carrera_dirigido', '=', 3)
                    ->where('docente.sexo', '=', 1);
            })
            ->distinct()
            ->select('docente.id')
            ->count();
        $totales_industrial_femenino = DB::table('inscripcion')
            ->join('docente', 'docente.id', '=', 'inscripcion.docente_id')
            ->join('deteccion_necesidades', 'deteccion_necesidades.id', '=', 'inscripcion.curso_id')
            ->where(function ($query) {
                $query->where('deteccion_necesidades.carrera_dirigido', '=', 3)
                    ->where('docente.sexo', '=', 2);
            })
            ->distinct()
            ->select('docente.id')
            ->count();
        //Electronica
        $totales_electronica = DB::table('inscripcion')
            ->join('docente', 'docente.id', '=', 'inscripcion.docente_id')
            ->join('deteccion_necesidades', 'deteccion_necesidades.id', '=', 'inscripcion.curso_id')
            ->where('deteccion_necesidades.carrera_dirigido', '=', 4)
            ->distinct()
            ->select('docente.id')
            ->count();
        $totales_electronica_masculinos = DB::table('inscripcion')
            ->join('docente', 'docente.id', '=', 'inscripcion.docente_id')
            ->join('deteccion_necesidades', 'deteccion_necesidades.id', '=', 'inscripcion.curso_id')
            ->where(function ($query) {
                $query->where('deteccion_necesidades.carrera_dirigido', '=', 4)
                    ->where('docente.sexo', '=', 1);
            })
            ->distinct()
            ->select('docente.id')
            ->count();
        $totales_electronica_femenino = DB::table('inscripcion')
            ->join('docente', 'docente.id', '=', 'inscripcion.docente_id')
            ->join('deteccion_necesidades', 'deteccion_necesidades.id', '=', 'inscripcion.curso_id')
            ->where(function ($query) {
                $query->where('deteccion_necesidades.carrera_dirigido', '=', 4)
                    ->where('docente.sexo', '=', 2);
            })
            ->distinct()
            ->select('docente.id')
            ->count();
        //electrica
        $totales_electrica = DB::table('inscripcion')
            ->join('docente', 'docente.id', '=', 'inscripcion.docente_id')
            ->join('deteccion_necesidades', 'deteccion_necesidades.id', '=', 'inscripcion.curso_id')
            ->where('deteccion_necesidades.carrera_dirigido', '=', 5)
            ->distinct()
            ->select('docente.id')
            ->count();
        $totales_electrica_masculinos = DB::table('inscripcion')
            ->join('docente', 'docente.id', '=', 'inscripcion.docente_id')
            ->join('deteccion_necesidades', 'deteccion_necesidades.id', '=', 'inscripcion.curso_id')
            ->where(function ($query) {
                $query->where('deteccion_necesidades.carrera_dirigido', '=', 5)
                    ->where('docente.sexo', '=', 1);
            })
            ->distinct()
            ->select('docente.id')
            ->count();
        $totales_electrica_femenino = DB::table('inscripcion')
            ->join('docente', 'docente.id', '=', 'inscripcion.docente_id')
            ->join('deteccion_necesidades', 'deteccion_necesidades.id', '=', 'inscripcion.curso_id')
            ->where(function ($query) {
                $query->where('deteccion_necesidades.carrera_dirigido', '=', 5)
                    ->where('docente.sexo', '=', 2);
            })
            ->distinct()
            ->select('docente.id')
            ->count();
        //bioquimica
        $totales_bioquimica = DB::table('inscripcion')
            ->join('docente', 'docente.id', '=', 'inscripcion.docente_id')
            ->join('deteccion_necesidades', 'deteccion_necesidades.id', '=', 'inscripcion.curso_id')
            ->where('deteccion_necesidades.carrera_dirigido', '=', 6)
            ->distinct()
            ->select('docente.id')
            ->count();
        $totales_bioquimica_masculinos = DB::table('inscripcion')
            ->join('docente', 'docente.id', '=', 'inscripcion.docente_id')
            ->join('deteccion_necesidades', 'deteccion_necesidades.id', '=', 'inscripcion.curso_id')
            ->where(function ($query) {
                $query->where('deteccion_necesidades.carrera_dirigido', '=', 6)
                    ->where('docente.sexo', '=', 1);
            })
            ->distinct()
            ->select('docente.id')
            ->count();
        $totales_bioquimica_femenino = DB::table('inscripcion')
            ->join('docente', 'docente.id', '=', 'inscripcion.docente_id')
            ->join('deteccion_necesidades', 'deteccion_necesidades.id', '=', 'inscripcion.curso_id')
            ->where(function ($query) {
                $query->where('deteccion_necesidades.carrera_dirigido', '=', 6)
                    ->where('docente.sexo', '=', 2);
            })
            ->distinct()
            ->select('docente.id')
            ->count();
        //quimica
        $totales_quimica = DB::table('inscripcion')
            ->join('docente', 'docente.id', '=', 'inscripcion.docente_id')
            ->join('deteccion_necesidades', 'deteccion_necesidades.id', '=', 'inscripcion.curso_id')
            ->where('deteccion_necesidades.carrera_dirigido', '=', 7)
            ->distinct()
            ->select('docente.id')
            ->count();
        $totales_quimica_masculinos = DB::table('inscripcion')
            ->join('docente', 'docente.id', '=', 'inscripcion.docente_id')
            ->join('deteccion_necesidades', 'deteccion_necesidades.id', '=', 'inscripcion.curso_id')
            ->where(function ($query) {
                $query->where('deteccion_necesidades.carrera_dirigido', '=', 7)
                    ->where('docente.sexo', '=', 1);
            })
            ->distinct()
            ->select('docente.id')
            ->count();
        $totales_quimica_femenino = DB::table('inscripcion')
            ->join('docente', 'docente.id', '=', 'inscripcion.docente_id')
            ->join('deteccion_necesidades', 'deteccion_necesidades.id', '=', 'inscripcion.curso_id')
            ->where(function ($query) {
                $query->where('deteccion_necesidades.carrera_dirigido', '=', 7)
                    ->where('docente.sexo', '=', 2);
            })
            ->distinct()
            ->select('docente.id')
            ->count();
        //IGE
        $totales_ige = DB::table('inscripcion')
            ->join('docente', 'docente.id', '=', 'inscripcion.docente_id')
            ->join('deteccion_necesidades', 'deteccion_necesidades.id', '=', 'inscripcion.curso_id')
            ->where('deteccion_necesidades.carrera_dirigido', '=', 8)
            ->distinct()
            ->select('docente.id')
            ->count();
        $totales_ige_masculinos = DB::table('inscripcion')
            ->join('docente', 'docente.id', '=', 'inscripcion.docente_id')
            ->join('deteccion_necesidades', 'deteccion_necesidades.id', '=', 'inscripcion.curso_id')
            ->where(function ($query) {
                $query->where('deteccion_necesidades.carrera_dirigido', '=', 8)
                    ->where('docente.sexo', '=', 1);
            })
            ->distinct()
            ->select('docente.id')
            ->count();
        $totales_ige_femenino = DB::table('inscripcion')
            ->join('docente', 'docente.id', '=', 'inscripcion.docente_id')
            ->join('deteccion_necesidades', 'deteccion_necesidades.id', '=', 'inscripcion.curso_id')
            ->where(function ($query) {
                $query->where('deteccion_necesidades.carrera_dirigido', '=', 8)
                    ->where('docente.sexo', '=', 2);
            })
            ->distinct()
            ->select('docente.id')
            ->count();
        //logistica
        $totales_logistica = DB::table('inscripcion')
            ->join('docente', 'docente.id', '=', 'inscripcion.docente_id')
            ->join('deteccion_necesidades', 'deteccion_necesidades.id', '=', 'inscripcion.curso_id')
            ->where('deteccion_necesidades.carrera_dirigido', '=', 9)
            ->distinct()
            ->select('docente.id')
            ->count();
        $totales_logistica_masculinos = DB::table('inscripcion')
            ->join('docente', 'docente.id', '=', 'inscripcion.docente_id')
            ->join('deteccion_necesidades', 'deteccion_necesidades.id', '=', 'inscripcion.curso_id')
            ->where(function ($query) {
                $query->where('deteccion_necesidades.carrera_dirigido', '=', 9)
                    ->where('docente.sexo', '=', 1);
            })
            ->distinct()
            ->select('docente.id')
            ->count();
        $totales_logistica_femenino = DB::table('inscripcion')
            ->join('docente', 'docente.id', '=', 'inscripcion.docente_id')
            ->join('deteccion_necesidades', 'deteccion_necesidades.id', '=', 'inscripcion.curso_id')
            ->where(function ($query) {
                $query->where('deteccion_necesidades.carrera_dirigido', '=', 9)
                    ->where('docente.sexo', '=', 2);
            })
            ->distinct()
            ->select('docente.id')
            ->count();
        //mecatronica
        $totales_mecatronica = DB::table('inscripcion')
            ->join('docente', 'docente.id', '=', 'inscripcion.docente_id')
            ->join('deteccion_necesidades', 'deteccion_necesidades.id', '=', 'inscripcion.curso_id')
            ->where('deteccion_necesidades.carrera_dirigido', '=', 10)
            ->distinct()
            ->select('docente.id')
            ->count();
        $totales_mecatronica_masculinos = DB::table('inscripcion')
            ->join('docente', 'docente.id', '=', 'inscripcion.docente_id')
            ->join('deteccion_necesidades', 'deteccion_necesidades.id', '=', 'inscripcion.curso_id')
            ->where(function ($query) {
                $query->where('deteccion_necesidades.carrera_dirigido', '=', 10)
                    ->where('docente.sexo', '=', 1);
            })
            ->distinct()
            ->select('docente.id')
            ->count();
        $totales_mecatronica_femenino = DB::table('inscripcion')
            ->join('docente', 'docente.id', '=', 'inscripcion.docente_id')
            ->join('deteccion_necesidades', 'deteccion_necesidades.id', '=', 'inscripcion.curso_id')
            ->where(function ($query) {
                $query->where('deteccion_necesidades.carrera_dirigido', '=', 10)
                    ->where('docente.sexo', '=', 2);
            })
            ->distinct()
            ->select('docente.id')
            ->count();
        //ciencias basicas
        $totales_cb = DB::table('inscripcion')
            ->join('docente', 'docente.id', '=', 'inscripcion.docente_id')
            ->join('deteccion_necesidades', 'deteccion_necesidades.id', '=', 'inscripcion.curso_id')
            ->where('deteccion_necesidades.carrera_dirigido', '=', 11)
            ->distinct()
            ->select('docente.id')
            ->count();
        $totales_cb_masculinos = DB::table('inscripcion')
            ->join('docente', 'docente.id', '=', 'inscripcion.docente_id')
            ->join('deteccion_necesidades', 'deteccion_necesidades.id', '=', 'inscripcion.curso_id')
            ->where(function ($query) {
                $query->where('deteccion_necesidades.carrera_dirigido', '=', 11)
                    ->where('docente.sexo', '=', 1);
            })
            ->distinct()
            ->select('docente.id')
            ->count();
        $totales_cb_femenino = DB::table('inscripcion')
            ->join('docente', 'docente.id', '=', 'inscripcion.docente_id')
            ->join('deteccion_necesidades', 'deteccion_necesidades.id', '=', 'inscripcion.curso_id')
            ->where(function ($query) {
                $query->where('deteccion_necesidades.carrera_dirigido', '=', 11)
                    ->where('docente.sexo', '=', 2);
            })
            ->distinct()
            ->select('docente.id')
            ->count();
        //Economico administrativo
        $totales_cea = DB::table('inscripcion')
            ->join('docente', 'docente.id', '=', 'inscripcion.docente_id')
            ->join('deteccion_necesidades', 'deteccion_necesidades.id', '=', 'inscripcion.curso_id')
            ->where('deteccion_necesidades.carrera_dirigido', '=', 12)
            ->distinct()
            ->select('docente.id')
            ->count();
        $totales_cea_masculinos = DB::table('inscripcion')
            ->join('docente', 'docente.id', '=', 'inscripcion.docente_id')
            ->join('deteccion_necesidades', 'deteccion_necesidades.id', '=', 'inscripcion.curso_id')
            ->where(function ($query) {
                $query->where('deteccion_necesidades.carrera_dirigido', '=', 12)
                    ->where('docente.sexo', '=', 1);
            })
            ->distinct()
            ->select('docente.id')
            ->count();
        $totales_cea_femenino = DB::table('inscripcion')
            ->join('docente', 'docente.id', '=', 'inscripcion.docente_id')
            ->join('deteccion_necesidades', 'deteccion_necesidades.id', '=', 'inscripcion.curso_id')
            ->where(function ($query) {
                $query->where('deteccion_necesidades.carrera_dirigido', '=', 12)
                    ->where('docente.sexo', '=', 2);
            })
            ->distinct()
            ->select('docente.id')
            ->count();

        //TOTALES
        $totales_todos = DB::table('inscripcion')
            ->join('docente', 'docente.id', '=', 'inscripcion.docente_id')
            ->join('deteccion_necesidades', 'deteccion_necesidades.id', '=', 'inscripcion.curso_id')
            ->distinct()
            ->select('docente.id')
            ->count();
        $totales_masculinos = DB::table('inscripcion')
            ->join('docente', 'docente.id', '=', 'inscripcion.docente_id')
            ->join('deteccion_necesidades', 'deteccion_necesidades.id', '=', 'inscripcion.curso_id')
            ->where(function ($query) {
                $query->where('docente.sexo', '=', 1);
            })
            ->distinct()
            ->select('docente.id')
            ->count();
        $totales_femenino = DB::table('inscripcion')
            ->join('docente', 'docente.id', '=', 'inscripcion.docente_id')
            ->join('deteccion_necesidades', 'deteccion_necesidades.id', '=', 'inscripcion.curso_id')
            ->where(function ($query) {
                $query->where('docente.sexo', '=', 2);
            })
            ->distinct()
            ->select('docente.id', 'docente.*')
            ->get();
        return array(
            array("carrera" => "Mecánica", "total" => $totales_mecanica, "Total_de_hombres_capacitados" => $totales_mecanica_masculinos, "Total_de_mujeres_capacitadas" => $totales_mecanica_femenino),
            array("carrera" => "Sistemas Computacionales", "total" => $totales_sistemas,  "Total_de_hombres_capacitados" => $totales_sistemas_masculinos, "Total_de_mujeres_capacitadas" => $totales_sistemas_femenino),
            array("carrera" => "Industrial", "total" => $totales_industrial,  "Total_de_hombres_capacitados" => $totales_industrial_masculinos, "Total_de_mujeres_capacitadas" => $totales_industrial_femenino),
            array("carrera" => "Electrónica", "total" => $totales_electronica,  "Total_de_hombres_capacitados" => $totales_electronica_masculinos, "Total_de_mujeres_capacitadas" => $totales_electronica_femenino),
            array("carrera" => "Electrica", "total" => $totales_electrica,  "Total_de_hombres_capacitados" => $totales_electrica_masculinos, "Total_de_mujeres_capacitadas" => $totales_electrica_femenino),
            array("carrera" => "Bioquimica", "total" => $totales_bioquimica,  "Total_de_hombres_capacitados" => $totales_bioquimica_masculinos, "Total_de_mujeres_capacitadas" => $totales_bioquimica_femenino),
            array("carrera" => "Quimica", "total" => $totales_quimica,  "Total_de_hombres_capacitados" => $totales_quimica_masculinos, "Total_de_mujeres_capacitadas" => $totales_quimica_femenino),
            array("carrera" => "Gestión Empresarial", "total" => $totales_ige,  "Total_de_hombres_capacitados" => $totales_ige_masculinos, "Total_de_mujeres_capacitadas" => $totales_ige_femenino),
            array("carrera" => "Logística", "total" => $totales_logistica,  "Total_de_hombres_capacitados" => $totales_logistica_masculinos, "Total_de_mujeres_capacitadas" => $totales_logistica_femenino),
            array("carrera" => "Mecatrónica", "total" => $totales_mecatronica,  "Total_de_hombres_capacitados" => $totales_mecatronica_masculinos, "Total_de_mujeres_capacitadas" => $totales_mecatronica_masculinos),
            array("carrera" => "Ciencias Basicas", "total" => $totales_cb,  "Total_de_hombres_capacitados" => $totales_cb_masculinos, "Total_de_mujeres_capacitadas" => $totales_cb_femenino),
            array("carrera" => "Ciencias Económico Administrativo", "total" => $totales_cea,  "Total_de_hombres_capacitados" => $totales_cea_masculinos, "Total_de_mujeres_capacitadas" => $totales_cea_femenino),
            array("carrera" => "Todas las carreras", "total" => $totales_todos,  "Total_de_hombres_capacitados" => $totales_masculinos, "Total_de_mujeres_capacitadas" => $totales_femenino)
        );
    }
}
