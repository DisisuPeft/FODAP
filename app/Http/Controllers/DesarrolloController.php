<?php

namespace App\Http\Controllers;

use App\Events\CalificacionEvent;
use App\Events\CursosAceptados;
use App\Events\DeleteDeteccionEvent;
use App\Events\InscripcionEvent;
use App\Events\ObservacionEvent;
use App\Http\Requests\CalificacionesRequest;
use App\Http\Requests\CursoRequest;
use App\Http\Requests\DocenteRequest;
use App\Models\Calificaciones;
use App\Models\Carrera;
use App\Models\ClaveCurso;
use App\Models\CursoObservaciones;
use App\Models\Departamento;
use App\Models\DeteccionNecesidades;
use App\Models\Docente;
use App\Models\FichaTecnica;
use App\Models\Lugar;
use App\Models\Plaza;
use App\Models\Posgrado;
use App\Models\Puesto;
use App\Models\User;
use App\Notifications\AceptadoNotification;
use App\Notifications\DocenteInscripcion;
use App\Notifications\ObservacionNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Symfony\Component\HttpKernel\Event\RequestEvent;


class DesarrolloController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $carrera = Carrera::all();
        $detecciones = DeteccionNecesidades::with('carrera', 'deteccion_facilitador', 'jefe', 'departamento')
            ->where(function ($query) {
                $query->where('aceptado', '=', 0)
                    ->where('tipo_FDoAP', '=', 1);
            })
            ->orderBy('id', 'desc')
            ->get();
        $deteccionesAP = DeteccionNecesidades::with('carrera', 'deteccion_facilitador', 'jefe', 'departamento')
            ->where(function ($query) {
                $query->where('aceptado', '=', 0)
                    ->where('tipo_FDoAP', '=', 2);
            })
            ->orderBy('id', 'desc')
            ->get();

        return Inertia::render('Views/desarrollo/coordinacion/DeteccionCoordinacion', [
            'deteccionesFD' => $detecciones,
            'deteccionesAP' => $deteccionesAP,
            'carrera' => $carrera
        ]);
    }

    public function index_registros()
    {
//        $cursos_fd = DeteccionNecesidades::with('carrera', 'deteccion_facilitador', 'docente_inscrito')
//            ->where(function ($query) {
//                $query->where('estado', '=', 2)
//                    ->orWhere('aceptado', '=', 1);
//            })
//            ->where('tipo_FDoAP', '=', 1)
//            ->orderBy('id', 'desc')
//            ->orderByRaw('deteccion_necesidades.estado ASC, ABS(DATEDIFF(NOW(), deteccion_necesidades.fecha_I)) ASC')
//            ->get();
//
//        $cursos_ap = DeteccionNecesidades::with('carrera', 'deteccion_facilitador', 'docente_inscrito')
//            ->where(function ($query) {
//                $query->where('estado', '=', 2)
//                    ->orWhere('aceptado', '=', 1);
//            })
//            ->where('tipo_FDoAP', '=', 2)
//            ->orderBy('id', 'desc')
//            ->orderByRaw('deteccion_necesidades.estado ASC, ABS(DATEDIFF(NOW(), deteccion_necesidades.fecha_I)) ASC')
//            ->get();
        $cursos = DeteccionNecesidades::with('carrera', 'deteccion_facilitador', 'docente_inscrito')
            ->where('aceptado', '=', 1)
            ->where('estado', '=', 2)
            ->orderBy('id', 'desc')
            ->orderByRaw('deteccion_necesidades.estado ASC, ABS(DATEDIFF(NOW(), deteccion_necesidades.fecha_I)) ASC')
            ->get();
        $departamento = Departamento::all();
        $carrera = Carrera::all();
        $todas_carreras = DeteccionNecesidades::with('docente_inscrito')->get();

        return Inertia::render('Views/desarrollo/coordinacion/ShowRegistrosC', [
            'cursos' => $cursos,
            'departamento' => $departamento,
            'carrera' => $carrera,
            'todas' => $todas_carreras,
        ]);
    }

    public function desarrollo_cursos()
    {
        CoursesController::state_curso();
        date_default_timezone_set('America/Mexico_City');

        $cursos = DeteccionNecesidades::with(['carrera', 'deteccion_facilitador', 'docente_inscrito', 'departamento', 'jefe'])
            ->where('aceptado', '=', 1)
            ->where(function ($query) {
                $query->where('estado', '=', 0)
                    ->orWhere('estado', '=', 1);
            })
            ->orderBy('id', 'desc')
            ->get();
        $carrera = Carrera::all();
        $departamento = Departamento::all();

        return Inertia::render('Views/cursos/desarrollo/DesarrolloCursos', [
            'cursos' => $cursos,
            'carrera' => $carrera,
            'departamento' => $departamento
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $docentes = Docente::select('nombre_completo', 'id')->get();
        $carrera = Carrera::all();
        $departamento = Departamento::all();
        $posgrado = Posgrado::all();
        $puesto = Puesto::all();
        $tipoplaza = Plaza::all();
        $lugar = Lugar::with('curso')->get();
        return Inertia::render('Views/cursos/desarrollo/registros/CreateCurso', [
            'docente' => $docentes,
            'carrera' => $carrera,
            'todos_los_departamentos' => $departamento,
            'lugar' => $lugar,
            'posgrado' => $posgrado,
            'puesto' => $puesto,
            'tipo_plaza' => $tipoplaza,

        ]);
    }
    public function store_cursos(CursoRequest $request)
    {
        $departamento = $this->query_carrera($request->carrera_dirigido);
        $totalHoras = CoursesController::total_horas($request->fecha_I, $request->fecha_F, $request->hora_I, $request->hora_F);
        if ($departamento->nameCarrera == 'Todas las carreras') {
            $deteccion = DeteccionNecesidades::create($request->validated() + [
                'aceptado' => 1,
                'obs' => $request->observaciones != null ? 1 : 0,
                'total_horas' => $totalHoras,
                'facilitador_externo' =>  $request->facilitador_externo,
                'observaciones' => $request->observaciones,
                'id_lugar' => $request->id_lugar,
            ]);
        } else {
            //            $departamento = $this->query_carrera($request->carrera_dirigido);
            $deteccion = DeteccionNecesidades::create($request->validated() + [
                'id_jefe' => $departamento->departamento->jefe_id,
                'aceptado' => 1,
                'obs' => $request->observaciones != null ? 1 : 0,
                'total_horas' => $totalHoras,
                'id_departamento' => $departamento->departamento->id,
                'facilitador_externo' =>  $request->facilitador_externo,
                'observaciones' => $request->observaciones,
                'id_lugar' => $request->id_lugar,
            ]);
        }
        $deteccion->deteccion_facilitador()->toggle($request->input('facilitadores', []));
        $deteccion->save();
        return Redirect::route('index.desarrollo.cursos');
    }

    public function edit_curso($id)
    {
        $curso = DeteccionNecesidades::with(['carrera', 'deteccion_facilitador', 'docente_inscrito', 'jefe', 'departamento'])->find($id);
        $carrera = Carrera::all();
        $docente = Docente::all();
        $departamento = Departamento::all();
        $lugar = Lugar::with('curso')->get();
        $posgrado = Posgrado::all();
        $puesto = Puesto::all();
        $tipoplaza = Plaza::all();

        return Inertia::render('Views/cursos/desarrollo/registros/EditCurso', [
            'curso' => $curso,
            'carrera' => $carrera,
            'docentes' => $docente,
            'todos_los_departamentos' => $departamento,
            'lugar' => $lugar,
            'puesto' => $puesto,
            'posgrado' => $posgrado,
            'tipo_plaza' => $tipoplaza,
        ]);
    }

    public function update_curso(CursoRequest $request, $id)
    {
        DB::beginTransaction();
        $totalHoras = CoursesController::total_horas($request->fecha_I, $request->fecha_F, $request->hora_I, $request->hora_F);
        // $departamento = $this->query_carrera($request->carrera_dirigido);
        $facilitadores = $request->input('facilitadores', []);
        $departamento = Departamento::with('jefe_docente')->find($request->departamento);
        // dd($request);
        $curso = DeteccionNecesidades::find($id);
        $curso->id_jefe = $request->jefe == null ? $departamento->jefe_docente->id : $request->jefe_id;
        $curso->total_horas = $totalHoras;
        $curso->id_departamento = $request->departamento;
        $curso->facilitador_externo = $request->facilitador_externo;
        $curso->observaciones = $request->observaciones;
        $curso->obs = $request->observaciones != null ? 1 : 0;
        $curso->id_lugar = $request->id_lugar;

        $curso->deteccion_facilitador()->sync([]);

        if (count($facilitadores) > 3) {
            DB::rollBack();
            return Redirect::back()->withErrors('Excede el limite de docentes');
        } else {
            $curso->deteccion_facilitador()->sync(
                $facilitadores,
                false
            );
            $curso->update($request->validated());

            $curso->save();

            DB::commit();

            return Redirect::route('index.desarrollo.inscritos', ['id' => $curso->id]);
        }
    }

    public static function query_carrera($query)
    {
        return Carrera::with('departamento')->where('id', '=', $query)->first();
    }
    public static function query_consult_carrera()
    {
        return Carrera::with('departamento')->get();
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $id)
    {
        $request->validate([
            'aceptado' => ['required']
        ]);


        $detecciones = DeteccionNecesidades::find($id);

        $detecciones->aceptado = $request->aceptado;

        $detecciones->save();

        event(new CursosAceptados($detecciones));

        User::where('departamento_id', $detecciones->id_departamento)->role(['Jefes Academicos'])->each(function (User $user) use ($detecciones) {
            $user->notify(new AceptadoNotification($detecciones, $user));
        });

        CoursesController::clave_generar($id);
        CoursesController::clave_validacion($id);

        return Redirect::route('index.detecciones');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $deteccion = DeteccionNecesidades::with('carrera', 'deteccion_facilitador', 'jefe')->find($id);

        return Inertia::render('Views/desarrollo/coordinacion/ShowDeteccionCoordinacion', [
            'deteccion' => $deteccion
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validador = Validator::make($request->all(), [
            'observaciones' => ['required']
        ], [
            'observaciones.required' => 'El campo Observaciones es obligatorio.',
        ]);

        if (!$validador){
            $deteccion = DeteccionNecesidades::where('id', $id)->first();

            if ($deteccion){
                $update =  $deteccion->update([
                    'observaciones' => $request->observaciones,
                    'obs' => 1
                ]);
                if ($update){
                    //        User::where('departamento_id', $deteccion->id_departamento)->role(['Jefes Academicos'])->each(function (User $user) use ($deteccion) {
//            $user->notify(new ObservacionNotification($deteccion, $user));
//        });

//        event(new ObservacionEvent($deteccion));
                    //preguntar al jefe si en este punto se debe aumentar la revision

                    return Redirect::route('show.Cdetecciones', ['id' => $id]);
                }else{
                    return back()->withErrors('No se pudo agregar la observacion');
                }
            }else{
                return back()->withErrors('El curso no existe.');
            }
        }else{
            return back()->withErrors($validador);
        }
    }

    public function addCorreccion($id, Request $request){
        $cursoObs = new CursoObservaciones();
        $validador = Validator::make($request->all(), [
            'correccion' => ['required']
        ], [
            'correccion.required' => 'El campo Correccion es obligatorio.',
        ]);
        if (!$validador->fails()) {
            $add = $cursoObs->addObservaciones($id, $request);
            if ($add){
                return Redirect::route('show.Cdetecciones', ['id' => $id]);
            }else{
                return back()->withErrors('Ocurrio un error al crear las correcciones.');
            }
        }else{
            return Redirect::back()->withErrors($validador);
        }

    }

    public function updateCorreccion($id, Request $request){
        $cursoObsU = new CursoObservaciones();
        $validador = Validator::make($request->all(), [
            'correccion' => ['required']
        ], [
            'correccion.required' => 'El campo Correccion es obligatorio.',
        ]);
        if (!$validador->fails()) {
            $add = $cursoObsU->updateObservaciones($id, $request);
            if ($add){
                return Redirect::route('show.Cdetecciones', ['id' => $id]);
            }else{
                return back()->withErrors('Ocurrio un error al actualizar las correcciones.');
            }
        }else{
            return Redirect::back()->withErrors($validador);
        }
    }
    public function index_curso_inscrito_desarrollo($id)
    {
        $docente = Docente::orderBy('nombre', 'asc')->get();
        $curso = DeteccionNecesidades::with(['carrera', 'deteccion_facilitador', 'docente_inscrito', 'clave_curso', 'clave_validacion', 'lugar', 'ficha_tecnica'])->find($id);
        $inscritos = DB::table('docente')
            ->orderBy('nombre', 'asc')
            ->join('inscripcion', 'inscripcion.docente_id', '=', 'docente.id')
            ->leftJoin('calificaciones', function ($join) {
                $join->on('calificaciones.docente_id', '=', 'docente.id')
                    ->on('calificaciones.curso_id', '=', 'inscripcion.curso_id');
            })
            ->where('inscripcion.curso_id', '=', $id)
            ->select('docente.*', 'calificaciones.calificacion', 'inscripcion.curso_id AS inscripcion_curso_id')
            ->distinct()
            ->get();


        return Inertia::render('Views/cursos/desarrollo/InscritosDesarrollo', [
            'curso' => $curso,
            'docente' => $docente,
            'inscritos' => $inscritos,
        ]);
    }

    public function delete($id)
    {
        $deteccion = DeteccionNecesidades::find($id);

        event(new DeleteDeteccionEvent($deteccion));

        $delete = $deteccion->delete();

        if ($delete){
            if ($deteccion->aceptado == 1) {
                return Redirect::route('index.desarrollo.cursos');
            }

            return Redirect::route('index.detecciones');
        }else{
            return back()->withErrors('El curso no se elimino, debe informar al área de computo.');
        }
    }


    public function inscripcion_por_desarrollo($id, Request $request)
    {
        $deteccion = DeteccionNecesidades::find($id);
        $num = count($deteccion->docente_inscrito) + 1;


        if ($num <= $deteccion->numeroProfesores) {


            // foreach ($request->id_docente as $docente) {
            if (!$deteccion->docente_inscrito()->where('docente_id', $request->id_docente)->exists()) {
                $deteccion->docente_inscrito()->attach($request->id_docente);
            } else {
                return back()->withErrors('Este docente ya esta inscrito');
            }
            // }

            event(new InscripcionEvent($request->id_docente));
            return redirect()->route('index.desarrollo.inscritos', ['id' => $deteccion->id]);
        } else {
            return redirect()->route('index.desarrollo.inscritos', ['id' => $deteccion->id])->withErrors('Llego al maximo de docentes que el curso permite inscribir');
        }
    }

    public function desinscribirse(Request $request, $docente)
    {
        if($request->curso){
            $curso = $request->curso;
            $teacher = Docente::with(['inscrito' => function ($query) use ($curso) {
                $query->where('inscripcion.curso_id', '=', $curso);
            }])->find($docente);
            if ($teacher){
                $inscrito = $teacher->inscrito()->sync([]);
                if (count($inscrito['detached']) > 0){
                    return redirect()->route('index.desarrollo.inscritos', ['id' => $curso])->with('message', 'Docente eliminado del curso');
                }else{
                    return back()->withErrors('Si el docente no desaparece, actualice la pagina e intentelo de nuevo.');
                }
            }else{
                return back()->withErrors('No se encontro al docente inscrito en la base de datos.');
            }
        }else{
            return back()->withErrors('No se compartio el ID del curso.');
        }
    }

    public function docentes()
    {
        $docentes = Docente::with('usuario', 'carrera', 'departamento')->orderBy('nombre')->get();
        $user = User::with('docente')->get();
        $carrera = Carrera::all();
        $departamento = Departamento::select('nameDepartamento', 'id')->get();
        $tipoPlaza = DB::table('tipo_plaza')->select('id', 'nombre')->get();
        $puesto = DB::table('puesto')->select('id', 'nombre')->get();
        $posgrado = DB::table('posgrado')->select('id', 'nombre')->get();

        $totales = DB::table('docente')
            ->leftjoin('inscripcion', 'inscripcion.docente_id', '=', 'docente.id')
            ->whereColumn('inscripcion.docente_id', '=', 'docente.id')
            ->select('docente.id', 'docente.nombre', 'docente.apellidoPat', 'docente.apellidoMat')
            ->distinct()
            ->get();
        // ->count();

        return Inertia::render('Views/desarrollo/Docentes', [
            'docentes' => $docentes,
            'user' => $user,
            'carrera' => $carrera->except(['13', '12', '11']),
            'departamento' => $departamento,
            'tipo_plaza' => $tipoPlaza,
            'puesto' => $puesto,
            'posgrado' => $posgrado,
            'totales' => $totales
        ]);
    }
    public function create_docentes()
    {
        $carrera = Carrera::all();
        $departamento = Departamento::select('nameDepartamento', 'id')->get();
        $tipoPlaza = DB::table('tipo_plaza')->select('id', 'nombre')->get();
        $puesto = DB::table('puesto')->select('id', 'nombre')->get();
        $posgrado = DB::table('posgrado')->select('id', 'nombre')->get();


        return Inertia::render('Views/desarrollo/docente/CreateDocente', [
            'carrera' => $carrera->except(['13']),
            'departamento' => $departamento,
            'tipo_plaza' => $tipoPlaza,
            'puesto' => $puesto,
            'posgrado' => $posgrado,
        ]);
    }
    public function store_docentes(Request $request, $type)
    {
//        dd($request, $type);
        $docente = new Docente();
        $docente->create_instance_docente($request, $type);
        return Redirect::route('index.docentes');
    }

    public function delete_docente_desarrollo($id)
    {
        $docente = new Docente();
        $docente->delete_docente($id);
        return redirect()->route('index.docentes');

    }
    public function edit_docente($id)
    {
        $carrera = $this->query_consult_carrera();
        $tipoPlaza = DB::table('tipo_plaza')->select('id', 'nombre')->get();
        $puesto = DB::table('puesto')->select('id', 'nombre')->get();
        $posgrado = DB::table('posgrado')->select('id', 'nombre')->get();
        $docente = Docente::with('carrera', 'plaza', 'puesto', 'departamento', 'posgrado', 'usuario')->find($id);
        $departamento = Departamento::select('nameDepartamento', 'id')->get();
        return Inertia::render('Views/desarrollo/docente/EditDocente', [
            'carrera' => $carrera->except(['13']),
            'departamento' => $departamento,
            'tipo_plaza' => $tipoPlaza,
            'puesto' => $puesto,
            'posgrado' => $posgrado,
            'docente' => $docente
        ]);
    }

    public function update_docente(Request $request, $id, $type)
    {
        $docente = new Docente();
        $docente->updated_instance_docente($request, $id, $type);
        return Redirect::route('edit.docentes', ['id' => $id]);
    }
    //revisar este otro metodo
    public function calificaciones_desarrollo(CalificacionesRequest $request)
    {
        $calificaciones = new Calificaciones();


        $data = $calificaciones->add_calificacion($request);

        if ($data[1] == 200){
            $syncCalificacion = $this->consult_to_sync($request->curso_id, $request->docente_id);
            event(new CalificacionEvent($syncCalificacion));

            return Redirect::route('index.desarrollo.inscritos', ['id' => $request->curso_id]);
        }else{
            return back()->withErrors($data[0]);
        }
    }

    public function update_calificaciones_desarrollo(CalificacionesRequest $request)
    {
        $calificaciones = new Calificaciones();

        $update = $calificaciones->update_calificacion($request, $request->docente_id);

        if ($update[1] == 200){
            return Redirect::route('index.desarrollo.inscritos', ['id' => $request->curso_id]);
        }else{
            return back()->withErrors($update[0]);
        }
    }
    public static function consult_to_sync($curso_id, $docente_id)
    {
        $syncCalificacion = DB::table('docente')
            ->join('inscripcion', 'inscripcion.docente_id', '=', 'docente.id')
            ->leftJoin('calificaciones', function ($join) {
                $join->on('calificaciones.docente_id', '=', 'docente.id')
                    ->on('calificaciones.curso_id', '=', 'inscripcion.curso_id'); // Agregar esta condición
            })
            ->where('inscripcion.curso_id', '=', $curso_id)
            ->where('docente.id', '=', $docente_id)
            ->select('docente.*', 'calificaciones.calificacion', 'inscripcion.id AS inscripcion')
            ->get();
        return $syncCalificacion;
    }

    public static function formato($request)
    {
        return DeteccionNecesidades::contancia_export($request);
    }

    public static function formato_reconocimiento($request)
    {
        return DeteccionNecesidades::constancia_reconocimiento($request);
    }
}
