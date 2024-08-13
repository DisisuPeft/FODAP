<?php

namespace App\Http\Controllers;

use App\Events\InscripcionEvent;
use App\Models\Carrera;
use App\Models\Departamento;
use App\Models\DeteccionNecesidades;
use App\Models\Docente;
use App\Models\Lugar;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class AcademicosController extends Controller
{
    public function index()
    {
        date_default_timezone_set('America/Mexico_City');
        CoursesController::state_curso();

        $detecciones = DeteccionNecesidades::with(['carrera', 'deteccion_facilitador', 'departamento', 'jefe'])
            ->where('id_jefe', auth()->user()->docente_id)
            ->where('aceptado', '=', 0)
            ->orderBy('id', 'desc')->get();

        $carrera = Carrera::where('departamento_id', auth()->user()->departamento_id)->get();
        return Inertia::render('Views/academicos/IndexDetecciones', [
            'detecciones' => $detecciones,
            'carrera' => $carrera,
        ]);
    }

    public function create()
    {
        $docentes = Docente::select('nombre_completo', 'id')->get();
        $carrera = Carrera::where('departamento_id', auth()->user()->departamento_id)->select('nameCarrera', 'id', 'departamento_id')->get();
        $c = $carrera->toArray();

        $c[] = [
            'nameCarrera' => 'TODAS LAS CARRERAS',
            'id' => 13,
            'departamento_id' => null,
        ];

        $lugar = Lugar::with('curso')->get();
        $departamento = Departamento::where('id', auth()->user()->departamento_id)->get();
        $tipoPlaza = DB::table('tipo_plaza')->get();
        $puesto = DB::table('puesto')->select('id', 'nombre')->get();
        $posgrado = DB::table('posgrado')->select('id', 'nombre')->get();
        return Inertia::render('Views/academicos/CreateDetecciones', [
            'base_docente' => $docentes,
            'carrera_filtro' => collect($carrera),
            'todos_los_departamentos' => $departamento,
            'lugar' => $lugar,
            'carrera' => Carrera::all(),
            'departamento' => Departamento::all(),
            'tipo_plaza' => $tipoPlaza,
            'puesto' => $puesto,
            'posgrado' => $posgrado,
        ]);
    }




    public function show(string $id)
    {
        $curso = new DeteccionNecesidades();
        return Inertia::render('Views/academicos/ShowDetecciones', [
            'deteccion' => $curso->consult_view($id),
        ]);
    }


    public function edit(string $id)
    {
        $curso = new DeteccionNecesidades();
        $carrera = Carrera::where('departamento_id', auth()->user()->departamento_id)->select('nameCarrera', 'id', 'departamento_id')->get();
        $docente = Docente::all();
        $lugar = Lugar::with('curso')->get();
        return Inertia::render('Views/academicos/EditDetecciones', [
            'deteccion' => $curso->consult_view($id),
            'carrera' => $carrera,
            'docentes' => $docente,
            'lugar' => $lugar,
        ]);
    }

    public function registros()
    {
        $detecciones = DeteccionNecesidades::with(['carrera', 'deteccion_facilitador'])
            ->where('id_jefe', auth()->user()->docente_id)
            ->where(function ($query) {
                $query->where('aceptado', '=', 1)
                    ->where('estado', '=', 2);
            })
            ->orderBy('id', 'desc')
            ->get();
        return Inertia::render('Views/academicos/IndexRegistros', [
            'detecciones' => $detecciones
        ]);
    }

    public function index_cursos_academico()
    {
        //Actualiza el estado del curso
        date_default_timezone_set('America/Mexico_City');
        CoursesController::state_curso();

        $cursos = DeteccionNecesidades::with(['carrera', 'deteccion_facilitador', 'docente_inscrito', 'departamento', 'jefe'])
            ->where('id_jefe', '=', auth()->user()->docente_id)
            ->where('aceptado', '=', 1)
            ->where(function ($query) {
                $query->where('estado', '=', 0)
                    ->orWhere('estado', '=', 1);
            })
            ->orderBy('id', 'desc')
            ->get();




        return Inertia::render('Views/cursos/academicos/CursosAcademicos', [
            'cursos' => $cursos,
        ]);
    }

    public function index_cursos_inscritos($id)
    {
        date_default_timezone_set('America/Mexico_City');
        CoursesController::state_curso();

        $curso = new DeteccionNecesidades();
//        $inscritos = DB::table('docente')
//            ->orderBy('nombre', 'asc')
//            ->join('inscripcion', 'inscripcion.docente_id', '=', 'docente.id')
//            ->leftJoin('calificaciones', function ($join) {
//                $join->on('calificaciones.docente_id', '=', 'docente.id')
//                    ->on('calificaciones.curso_id', '=', 'inscripcion.curso_id');
//            })
//            ->where('inscripcion.curso_id', '=', $id)
//            ->select('docente.*', 'calificaciones.calificacion', 'inscripcion.curso_id AS inscripcion_curso_id')
//            ->distinct() // Agregar el método distinct aquí
//            ->get();

        return Inertia::render('Views/cursos/academicos/ShowInscritos', [
            'curso' => $curso->consult_view($id),
            'docente' => Docente::orderBy('nombre', 'asc')->get(),
            'inscritos' => $curso->inscritos_view_academicos($id),
        ]);
    }

    public function inscripcion_academicos(Request $request, $id)
    {
        $deteccion = DeteccionNecesidades::find($id);

        $num = count($deteccion->docente_inscrito) + 1;


        if ($num <= $deteccion->numeroProfesores) {



            // foreach ($request->id_docente as $docente){
            if (!$deteccion->docente_inscrito()->where('docente_id', $request->id_docente)->exists()) {
                $deteccion->docente_inscrito()->attach($request->id_docente);
            } else {
                return back()->withErrors('Este docente ya esta inscrito');
            }
            // }



            $syncDeteccion = DB::table('docente')
                ->join('inscripcion', 'inscripcion.docente_id', '=', 'docente.id')
                ->leftjoin('calificaciones', 'calificaciones.docente_id', '=', 'docente.id')
                ->where('inscripcion.curso_id', '=', $id)
                ->select('docente.*', 'calificaciones.calificacion', 'inscripcion.id AS inscripcion')
                ->get();

            event(new InscripcionEvent($syncDeteccion));

            return redirect()->route('show.inscritos.academicos', ['id' => $deteccion->id]);
        } else {
            return redirect()->route('show.inscritos.academicos', ['id' => $deteccion->id])->withErrors('Cupo completo');
        }
    }

    public function index_docentes_academicos()
    {
        CoursesController::state_curso();
        $carrera = Carrera::where('departamento_id', auth()->user()->departamento_id)->get();
        $departamento = Departamento::where('id', auth()->user()->departamento_id)->get();
        $tipoPlaza = DB::table('tipo_plaza')->get();
        $puesto = DB::table('puesto')->select('id', 'nombre')->get();
        $posgrado = DB::table('posgrado')->select('id', 'nombre')->get();
        $docentes = Docente::with('usuario')
            ->where('departamento_id', '=', auth()->user()->departamento_id)
            ->orderBy('nombre')
            ->get();
        $user = User::with('docente')->get();
        return Inertia::render('Views/academicos/docentes/DocentesA', [
            'docentes' => $docentes,
            'user' => $user,
            'carrera' => $carrera,
            'departamento' => $departamento,
            'tipo_plaza' => $tipoPlaza,
            'puesto' => $puesto,
            'posgrado' => $posgrado,
        ]);
    }
    public function docente_created_from_form(Request $request, $type)
    {
        $docente = new Docente();
        $docente->create_instance_docente($request, $type);
//        return redirect()->route('detecciones.create');
    }

    public function edit_docente_academico($id)
    {
        $carrera = Carrera::all();
        $departamento = Departamento::select('nameDepartamento', 'id')->get();
        $tipoPlaza = DB::table('tipo_plaza')->select('id', 'nombre')->get();
        $puesto = DB::table('puesto')->select('id', 'nombre')->get();
        $posgrado = DB::table('posgrado')->select('id', 'nombre')->get();
        $docente = Docente::with('carrera', 'plaza', 'puesto', 'departamento', 'posgrado')->find($id);
        return Inertia::render('Views/academicos/docentes/EditDocenteA', [
            'carrera' => $carrera,
            'departamento' => $departamento,
            'tipo_plaza' => $tipoPlaza,
            'puesto' => $puesto,
            'posgrado' => $posgrado,
            'docente' => $docente
        ]);
    }
    public function update_docente_academico(Request $request, $id, $type)
    {
        $docente = new Docente();
        $docente->updated_instance_docente($request, $id, $type);
        return Redirect::route('edit.docentes.academicos', ['id' => $id]);
    }
}
