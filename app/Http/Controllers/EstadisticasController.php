<?php

namespace App\Http\Controllers;

use App\Exports\EstadisticasExport;
use App\Models\DeteccionNecesidades;
use App\Models\Docente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class EstadisticasController extends Controller
{
    public function index_estadisticas()
    {
        return Inertia::render('Views/desarrollo/estadisticas/Estadisticas', [
            'cursos_anio' => $this->estadisticas_de_curso_por_anio(),
            'cursos_periodos' => $this->estadistica_cursos_periodo(),
            'cursos_tipo' => $this->estadistica_cursos_tipo(),
            'docente_carrera' => $this->docente_carrera(),
            'total_cursos_ap_fd' => $this->fd_ap_cursos(),
        ]);
    }

    public static function estadisticas_de_curso_por_anio()
    {
        //            Cuantos cursos en general por anio
        $anio = date('Y');
        return DeteccionNecesidades::with('inscritos')
            ->whereYear('fecha_F', '=', $anio)
            ->where('estado', '=', 2)->count();
    }

    public static function estadistica_cursos_periodo()
    {
        $anio = date('Y');
        $cursos_enero_junio = DeteccionNecesidades::with('inscritos')->whereYear('fecha_F', '=', $anio)
            ->where(function ($query) {
                $query->where('estado', '=', 2)
                    ->where('periodo', '=', 1);
            })->count();
        $cursos_agosto_diciembre = DeteccionNecesidades::with('inscritos')->whereYear('fecha_F', '=', $anio)
            ->where(function ($query) {
                $query->where('estado', '=', 2)
                    ->where('periodo', '=', 2);
            })->count();

        return array(
            array("periodo" => "Enero-Junio", "total" => $cursos_enero_junio),
            array("periodo" => "Agosto-Diciembre", "total" => $cursos_agosto_diciembre),
        );
    }
    public static function estadistica_cursos_tipo()
    {
        $anio = date('Y');

        $cursos_taller = DeteccionNecesidades::with('inscritos')->whereYear('fecha_F', '=', $anio)
            ->where(function ($query) {
                $query->where('estado', '=', 2)
                    ->where('tipo_actividad', '=', 1);
            })->count();
        $cursos_curso = DeteccionNecesidades::with('inscritos')->whereYear('fecha_F', '=', $anio)
            ->where(function ($query) {
                $query->where('estado', '=', 2)
                    ->where('tipo_actividad', '=', 2);
            })->count();
        $cursos_curso_taller = DeteccionNecesidades::with('inscritos')->whereYear('fecha_F', '=', $anio)
            ->where(function ($query) {
                $query->where('estado', '=', 2)
                    ->where('tipo_actividad', '=', 3);
            })->count();
        $cursos_foro = DeteccionNecesidades::with('inscritos')->whereYear('fecha_F', '=', $anio)
            ->where(function ($query) {
                $query->where('estado', '=', 2)
                    ->where('tipo_actividad', '=', 4);
            })->count();
        $cursos_seminario = DeteccionNecesidades::with('inscritos')->whereYear('fecha_F', '=', $anio)
            ->where(function ($query) {
                $query->where('estado', '=', 2)
                    ->where('tipo_actividad', '=', 5);
            })->count();
        $cursos_diplomado = DeteccionNecesidades::with('inscritos')->whereYear('fecha_F', '=', $anio)
            ->where(function ($query) {
                $query->where('estado', '=', 2)
                    ->where('tipo_actividad', '=', 6);
            })->count();

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
    public static function docente_carrera()
    {
        return DeteccionNecesidades::docente_carrera_consult();
    }

    public static function fd_ap_cursos()
    {
        $fd = DeteccionNecesidades::where('tipo_FDoAP', 1)->get();
        $ap = DeteccionNecesidades::where('tipo_FDoAP', 2)->get();

        return array(
            array("tipo" => "Formación Docente", "total" => $fd->count()),
            array("tipo" => "Actualización Profesional", "total" => $ap->count()),
        );
    }

    public function export_excel_tipo()
    {
        return Excel::store(new EstadisticasExport(), '/public/estadisticas_tipo.xlsx');
    }
}
