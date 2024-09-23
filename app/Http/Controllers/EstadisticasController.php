<?php

namespace App\Http\Controllers;

use App\Exports\DocenteCapacitadosExports;
use App\Exports\DocentesCapExport;
use App\Exports\EstadisticasExport;
use App\Exports\FDAPExport;
use App\Models\DeteccionNecesidades;
use App\Models\Docente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class EstadisticasController extends Controller
{
    public function index_estadisticas(Request $request)
    {
//        dd($request->all());
        return Inertia::render('Views/desarrollo/estadisticas/Estadisticas', [
            'cursos_anio' => $this->estadisticas_de_curso($request),
            'cursos_periodos' => $this->estadistica_cursos_periodo($request),
            'cursos_tipo' => $this->estadistica_cursos_tipo($request),
            'docente_carrera' => $this->docente_carrera($request),
            'total_cursos_ap_fd' => $this->fd_ap_cursos($request),
            'docentes_genero' => $this->docente_genero_consulta($request)
        ]);
    }

    public static function estadisticas_de_curso($payload)
    {
        //            Cuantos cursos en general por anio
        return DeteccionNecesidades::with('inscritos')
            ->whereYear('fecha_F', '=', $payload->year)
            ->where('estado', '=', 2)->count();
    }

    public static function estadistica_cursos_periodo($payload)
    {
//        $anio = date('Y');
        $cursos_enero_junio = DeteccionNecesidades::with('inscritos')
            ->whereYear('fecha_F', '=', $payload->year)
            ->where('estado', '=', 2)
            ->where('periodo', '=', 1)
            ->count();
        $cursos_agosto_diciembre = DeteccionNecesidades::with('inscritos')
            ->whereYear('fecha_F', '=', $payload->year)
            ->where('estado', '=', 2)
            ->where('periodo', '=', 2)
            ->count();

        return array(
            array("periodo" => "Enero-Junio", "total" => $cursos_enero_junio),
            array("periodo" => "Agosto-Diciembre", "total" => $cursos_agosto_diciembre),
        );
    }
    public static function estadistica_cursos_tipo($payload)
    {
//        $anio = date('Y');
//        dd($payload);
        $cursos_taller = DeteccionNecesidades::with('inscritos')
            ->whereYear('fecha_F', '=', $payload->year)
            ->where('estado', '=', 2)
            ->where('tipo_actividad', '=', 1)
            ->count();
        $cursos_curso = DeteccionNecesidades::with('inscritos')
            ->whereYear('fecha_F', '=', $payload->year)
            ->where('estado', '=', 2)
            ->where('tipo_actividad', '=', 2)
            ->count();
        $cursos_curso_taller = DeteccionNecesidades::with('inscritos')
            ->whereYear('fecha_F', '=', $payload->year)
            ->where('estado', '=', 2)
            ->where('tipo_actividad', '=', 3)
            ->count();
        $cursos_foro = DeteccionNecesidades::with('inscritos')
            ->whereYear('fecha_F', '=', $payload->year)
            ->where('estado', '=', 2)
            ->where('tipo_actividad', '=', 4)
            ->count();
        $cursos_seminario = DeteccionNecesidades::with('inscritos')
            ->whereYear('fecha_F', '=', $payload->year)
            ->where('estado', '=', 2)
            ->where('tipo_actividad', '=', 5)
            ->count();
        $cursos_diplomado = DeteccionNecesidades::with('inscritos')
            ->whereYear('fecha_F', '=', $payload->year)
            ->where('estado', '=', 2)
            ->where('tipo_actividad', '=', 6)
            ->count();

        return array(
            array("tipo" => "Taller", "total" => $cursos_taller),
            array("tipo" => "Curso", "total" => $cursos_curso),
            array("tipo" => "Curso/taller", "total" => $cursos_curso_taller),
            array("tipo" => "Foro", "total" => $cursos_foro),
            array("tipo" => "Seminario", "total" => $cursos_seminario),
            array("tipo" => "Diplomado", "total" => $cursos_diplomado),
        );
    }
    //perdon por el spageti
    public static function docente_carrera($payload)
    {
        $curso = new DeteccionNecesidades();
        return $curso->docente_carrera_consult($payload);
    }

    public static function fd_ap_cursos($payload)
    {
        $fd = DeteccionNecesidades::where('tipo_FDoAP', 1)->whereYear('fecha_F', '=', $payload->year)->get();
        $ap = DeteccionNecesidades::where('tipo_FDoAP', 2)->whereYear('fecha_F', '=', $payload->year)->get();

        return array(
            array("tipo" => "Formación Docente", "total" => $fd->count()),
            array("tipo" => "Actualización Profesional", "total" => $ap->count()),
        );
    }

    public function export_excel_tipo(Request $request)
    {
        return Excel::store(new EstadisticasExport($request), '/public/estadisticas_tipo.xlsx');
    }

    public function docente_genero_consulta($request){
        $n = [];
        $deteccion = new DeteccionNecesidades();
        $docentes = $deteccion->docentes_sexo($request);
//        dd($docentes);
        foreach ($docentes as $docente) {
            if ($docente->sexo == null){
                $n [] = $docente;
            }
        }

        return $n;
    }

    public static function export_excel_FDAP(Request $request)
    {
        return Excel::store(new FDAPExport($request), '/public/estadisticas_FDAP.xlsx');
    }

    public function export_excel_capacitados(Request $request){
        $docente = new Docente();
        return Excel::store(new DocentesCapExport($docente->docentes_capacitados($request->id)), '/public/capacitadosDocentes.xlsx');
    }

}
