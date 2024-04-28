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
        $mecanica = DeteccionNecesidades::with('docente_inscrito')
            ->where('carrera_dirigido', '=', 1)->get();

        // $docente_mecanica = DB::table('docente')->where(function ($query) {
        //     $query->where('sexo', '=', 1)
        //         ->where('carrera_id', '=', 1);
        // })->get();

        $sistemas = DeteccionNecesidades::with('docente_inscrito')
            ->where('carrera_dirigido', '=', 2)->get();
        $industrial = DeteccionNecesidades::with('docente_inscrito')
            ->where('carrera_dirigido', '=', 3)->get();
        $electronica = DeteccionNecesidades::with('docente_inscrito')
            ->where('carrera_dirigido', '=', 4)->get();
        $electrica = DeteccionNecesidades::with('docente_inscrito')
            ->where('carrera_dirigido', '=', 5)->get();
        $bio = DeteccionNecesidades::with('docente_inscrito')
            ->where('carrera_dirigido', '=', 6)->get();
        $quimica = DeteccionNecesidades::with('docente_inscrito')
            ->where('carrera_dirigido', '=', 7)->get();
        $gestion = DeteccionNecesidades::with('docente_inscrito')
            ->where('carrera_dirigido', '=', 8)->get();
        $logistica = DeteccionNecesidades::with('docente_inscrito')
            ->where('carrera_dirigido', '=', 9)->get();
        $mecatronica = DeteccionNecesidades::with('docente_inscrito')
            ->where('carrera_dirigido', '=', 10)->get();
        $cb = DeteccionNecesidades::with('docente_inscrito')
            ->where('carrera_dirigido', '=', 11)->get();
        $ciencias_ea = DeteccionNecesidades::with('docente_inscrito')
            ->where('carrera_dirigido', '=', 12)->get();
        $todas_carreras = DeteccionNecesidades::with('docente_inscrito')->get();

        //Cuantps dpcentes por carrera, cuantos fueron mujeres y cuantos fueron hombres
        $total_mecanica = 0;
        $total_m_mecanica = 0;
        $total_f_mecanica = 0;
        for ($i = 0; $i < count($mecanica); $i++) {
            $total_mecanica += $mecanica[$i]->docente_inscrito->uniqid()->count();
            $total_m_mecanica += $mecanica[$i]->docente_inscrito->where('sexo', 1)->uniqid()->count();
            $total_f_mecanica += $mecanica[$i]->docente_inscrito->where('sexo', 2)->uniqid()->count();
        }
        $total_sistemas = 0;
        $total_m_sistemas = 0;
        $total_f_sistemas = 0;
        for ($i = 0; $i < count($sistemas); $i++) {
            $total_sistemas += $sistemas[$i]->docente_inscrito->uniqid()->count();
            $total_m_sistemas += $sistemas[$i]->docente_inscrito->where('sexo', 1)->uniqid()->count();
            $total_f_sistemas += $sistemas[$i]->docente_inscrito->where('sexo', 2)->uniqid()->count();
        }
        $total_industrial = 0;
        $total_m_industrial = 0;
        $total_f_industrial = 0;
        for ($i = 0; $i < count($industrial); $i++) {
            $total_industrial += $industrial[$i]->docente_inscrito->uniqid()->count();
            $total_m_industrial += $industrial[$i]->docente_inscrito->where('sexo', 1)->uniqid()->count();
            $total_f_industrial += $industrial[$i]->docente_inscrito->where('sexo', 2)->uniqid()->count();
        }
        $total_electronica = 0;
        $total_m_electronica = 0;
        $total_f_electronica = 0;
        for ($i = 0; $i < count($electronica); $i++) {
            $total_electronica += $electronica[$i]->docente_inscrito->uniqid()->count();
            $total_m_electronica += $electronica[$i]->docente_inscrito->where('sexo', 1)->uniqid()->count();
            $total_f_electronica += $electronica[$i]->docente_inscrito->where('sexo', 2)->uniqid()->count();
        }
        $total_electrica = 0;
        $total_m_electrica = 0;
        $total_f_electrica = 0;
        for ($i = 0; $i < count($electrica); $i++) {
            $total_electrica += $electrica[$i]->docente_inscrito->uniqid()->count();
            $total_m_electrica += $electrica[$i]->docente_inscrito->where('sexo', 1)->uniqid()->count();
            $total_f_electrica += $electrica[$i]->docente_inscrito->where('sexo', 2)->uniqid()->count();
        }
        $total_bio = 0;
        $total_m_bio = 0;
        $total_f_bio = 0;
        for ($i = 0; $i < count($bio); $i++) {
            $total_bio += $bio[$i]->docente_inscrito->uniqid()->count();
            $total_m_bio += $bio[$i]->docente_inscrito->where('sexo', 1)->uniqid()->count();
            $total_f_bio += $bio[$i]->docente_inscrito->where('sexo', 2)->uniqid()->count();
        }
        $total_quimica  = 0;
        $total_m_quimica = 0;
        $total_f_quimica = 0;
        for ($i = 0; $i < count($quimica); $i++) {
            $total_quimica += $quimica[$i]->docente_inscrito->uniqid()->count();
            $total_m_quimica += $quimica[$i]->docente_inscrito->where('sexo', 1)->uniqid()->count();
            $total_f_quimica += $quimica[$i]->docente_inscrito->where('sexo', 2)->uniqid()->count();
        }
        $total_gestion = 0;
        $total_m_gestion = 0;
        $total_f_gestion = 0;
        for ($i = 0; $i < count($gestion); $i++) {
            $total_gestion += $gestion[$i]->docente_inscrito->uniqid()->count();
            $total_m_gestion += $gestion[$i]->docente_inscrito->where('sexo', 1)->uniqid()->count();
            $total_f_gestion += $gestion[$i]->docente_inscrito->where('sexo', 2)->uniqid()->count();
        }
        $total_logistica = 0;
        $total_m_logistica = 0;
        $total_f_logistica = 0;
        for ($i = 0; $i < count($logistica); $i++) {
            $total_logistica += $logistica[$i]->docente_inscrito->uniqid()->count();
            $total_m_logistica += $logistica[$i]->docente_inscrito->where('sexo', 1)->uniqid()->count();
            $total_f_logistica += $logistica[$i]->docente_inscrito->where('sexo', 2)->uniqid()->count();
        }
        $total_mecatronica = 0;
        $total_m_mecatronica = 0;
        $total_f_mecatronica = 0;
        for ($i = 0; $i < count($mecatronica); $i++) {
            $total_mecatronica += $mecatronica[$i]->docente_inscrito->uniqid()->count();
            $total_m_mecatronica += $mecatronica[$i]->docente_inscrito->where('sexo', 1)->uniqid()->count();
            $total_f_mecatronica += $mecatronica[$i]->docente_inscrito->where('sexo', 2)->uniqid()->count();
        }
        $total_cb = 0;
        $total_m_cb = 0;
        $total_f_cb = 0;
        for ($i = 0; $i < count($cb); $i++) {
            $total_cb += $cb[$i]->docente_inscrito->uniqid()->count();
            $total_m_cb += $cb[$i]->docente_inscrito->where('sexo', 1)->uniqid()->count();
            $total_f_cb += $cb[$i]->docente_inscrito->where('sexo', 2)->uniqid()->count();
        }
        $total_ciencias_ea = 0;
        $total_m_ciencias_ea = 0;
        $total_f_ciencias_ea = 0;
        for ($i = 0; $i < count($ciencias_ea); $i++) {
            $total_ciencias_ea += $ciencias_ea[$i]->docente_inscrito->uniqid()->count();
            $total_m_ciencias_ea += $ciencias_ea[$i]->docente_inscrito->where('sexo', 1)->uniqid()->count();
            $total_f_ciencias_ea += $ciencias_ea[$i]->docente_inscrito->where('sexo', 2)->uniqid()->count();
        }
        $total_carreras = 0;
        $total_m_carreras = 0;
        $total_f_carreras = 0;
        for ($i = 0; $i < count($todas_carreras); $i++) {
            $total_carreras += $todas_carreras[$i]->docente_inscrito->uniqid()->count();
            $total_m_carreras += $todas_carreras[$i]->docente_inscrito->where('sexo', 1)->uniqid()->count();
            $total_f_carreras += $todas_carreras[$i]->docente_inscrito->where('sexo', 2)->uniqid()->count();
        }
        return array(
            array("carrera" => "Mecánica", "total" => $total_mecanica, "Total_de_hombres_capacitados" => $total_m_mecanica, "Total_de_mujeres_capacitadas" => $total_f_mecanica),
            array("carrera" => "Sistemas Computacionales", "total" => $total_sistemas,  "Total_de_hombres_capacitados" => $total_m_sistemas, "Total_de_mujeres_capacitadas" => $total_f_sistemas),
            array("carrera" => "Industrial", "total" => $total_industrial,  "Total_de_hombres_capacitados" => $total_m_industrial, "Total_de_mujeres_capacitadas" => $total_f_industrial),
            array("carrera" => "Electrónica", "total" => $total_electronica,  "Total_de_hombres_capacitados" => $total_m_electronica, "Total_de_mujeres_capacitadas" => $total_f_electronica),
            array("carrera" => "Electrica", "total" => $total_electrica,  "Total_de_hombres_capacitados" => $total_m_electrica, "Total_de_mujeres_capacitadas" => $total_f_electrica),
            array("carrera" => "Bioquimica", "total" => $total_bio,  "Total_de_hombres_capacitados" => $total_m_bio, "Total_de_mujeres_capacitadas" => $total_f_bio),
            array("carrera" => "Quimica", "total" => $total_quimica,  "Total_de_hombres_capacitados" => $total_m_quimica, "Total_de_mujeres_capacitadas" => $total_f_quimica),
            array("carrera" => "Gestión Empresarial", "total" => $total_gestion,  "Total_de_hombres_capacitados" => $total_m_gestion, "Total_de_mujeres_capacitadas" => $total_f_gestion),
            array("carrera" => "Logística", "total" => $total_logistica,  "Total_de_hombres_capacitados" => $total_m_logistica, "Total_de_mujeres_capacitadas" => $total_f_logistica),
            array("carrera" => "Mecatrónica", "total" => $total_mecatronica,  "Total_de_hombres_capacitados" => $total_m_mecatronica, "Total_de_mujeres_capacitadas" => $total_f_mecatronica),
            array("carrera" => "Ciencias Basicas", "total" => $total_cb,  "Total_de_hombres_capacitados" => $total_m_cb, "Total_de_mujeres_capacitadas" => $total_f_cb),
            array("carrera" => "Ciencias Económico Administrativo", "total" => $total_ciencias_ea,  "Total_de_hombres_capacitados" => $total_m_ciencias_ea, "Total_de_mujeres_capacitadas" => $total_f_ciencias_ea),
            array("carrera" => "Todas las carreras", "total" => $total_carreras,  "Total_de_hombres_capacitados" => $total_m_carreras, "Total_de_mujeres_capacitadas" => $total_f_carreras),
        );
        return $mecanica;
    }
}
