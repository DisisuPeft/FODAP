<?php

namespace App\Http\Controllers;

use App\Http\Requests\PIFDAPRequest;
use App\Http\Requests\RequestPDFDeteccion;
use App\Models\ClaveDocumentos;
use App\Models\Departamento;
use App\Models\DeteccionNecesidades;
use App\Models\Director;
use App\Models\Docente;
use App\Models\Documentos;
use App\Models\FichaTecnica;
use App\Models\NombreInstituto;
use App\Models\Subdireccion;
use App\Models\User;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Validator;
use Webklex\PDFMerger\Facades\PDFMergerFacade as PDFMerger;
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfReader;

class PDFController extends Controller
{
    public static function pdf_request_deteccion($data): \Illuminate\Database\Eloquent\Collection|array
    {
        return DeteccionNecesidades::with('carrera', 'deteccion_facilitador', 'jefe', 'departamento')
            ->whereYear('fecha_I', '=', $data->anio)
            ->where('periodo', '=', $data->periodo)
            ->where('carrera_dirigido', '=', $data->carrera)
            ->get();
    }

    public static function FD_request($data)
    {
        return DeteccionNecesidades::with(['carrera', 'deteccion_facilitador', 'jefe', 'departamento', 'lugar'])
            ->where('periodo', '=', $data->input('periodo'))
            ->where('tipo_FDoAP', '=', 1)
            ->whereYear('fecha_I', '=', $data->input('anio'))->get();
    }
    public static function AP_request($data)
    {
        return DeteccionNecesidades::with(['carrera', 'deteccion_facilitador', 'jefe', 'departamento', 'lugar'])
            ->where('periodo', '=', $data->input('periodo'))
            ->where('tipo_FDoAP', '=', 2)
            ->whereYear('fecha_I', '=', $data->input('anio'))->get();
    }
    public static function save_file($file, $path): bool
    {
        return Storage::disk('public')->put($path, $file);
    }
    public static function download_file($file): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        return Storage::download($file);
    }

    public function deteccion_pdf(RequestPDFDeteccion $request)
    {
        $clave_documentos = new ClaveDocumentos();
        $request->validated();
        $key = $clave_documentos->getClaveDocumento($request->tipo_documento);
        $cursos = $this->pdf_request_deteccion($request);
        $subdireccion = Subdireccion::all();
        $presidente = DB::table('carreras')
            ->selectRaw("docente.nombre_completo AS nombre")
            ->join('docente', 'carreras.presidente_academia', '=', 'docente.id')
            ->where('carreras.id', '=', $request->carrera)
            ->first();

        if (count($cursos) == 0) {
            return response()->json([
                'message' => 'No se encontro ningun dato con ese criterio de busqueda'
            ]);
        } else {
            $pdf = Pdf::loadView('pdf.deteccion', compact('cursos', 'subdireccion', 'presidente', 'key'))->output();
            $path = "Deteccion.pdf";
            $this->save_file($pdf, $path);
            //            return $this->download_file($path);
            if (Storage::disk('public')->exists($path)){
                return response()->json([
                    'message' => 'Documento generado.',
                    'status' => 200
                ]);
            }else{
//                return ["No se almaceno el archivo de manera correcta, intente generarlo nuevamente. Si el problema persiste se debe revisar el codigo.", "error"];
                return response()->json([
                    'message' => 'No se almaceno el archivo de manera correcta, intente generarlo nuevamente. Si el problema persiste se debe revisar el codigo.',
                    'status' => 500,
                ]);
            }
        }
    }
    public function PIFDAP_pdf(PIFDAPRequest $request)
    {
        $request->validated();
        $clave_documentos = new ClaveDocumentos();
        $key = $clave_documentos->getClaveDocumento($request->tipo_documento);
        $FD = $this->FD_request($request);
        $AP = $this->AP_request($request);
        $subdireccion = Subdireccion::all();
        $departamento = Departamento::with('jefe_docente')
            ->where('nameDepartamento', '=', 'Departamento de Desarrollo Académico')
            ->first();
        $anio = $request->anio;
        $periodo = $request->periodo;
        if (count($FD) == 0 && count($AP) == 0) {
            return ['No se encontro ningun dato con ese criterio de busqueda', "error"];
        } else {
            $pdf = Pdf::loadView('pdf.PIFDAP', compact('FD', 'AP', 'anio', 'periodo', 'subdireccion', 'departamento', 'key'))
                ->setPaper('letter', 'landscape')
                ->output();
            $path = 'PIFDAP.pdf';
            $this->save_file($pdf, $path);
            //            return $this->download_file($path);
            if (Storage::disk('public')->exists($path)){
                return ["PDF descargado.", "success"];
            }else{
                return ["No se almaceno el archivo de manera correcta, intente generarlo nuevamente. Si el problema persiste se debe revisar el codigo.", "error"];
            }
        }
    }

    public function cdi_pdf(Request $request)
    {
//        dd($request);
        $clave_documentos = new ClaveDocumentos();
        $key = $clave_documentos->getClaveDocumento($request->tipo_documento);
        dd($key);
        $band = 0;
        $null_values = [];
        $string_out = "";
        $arr_final = [];
        $request->validate([
            'docente' => 'required',
            'id_curso' => 'required',
        ]);
        $curso = DeteccionNecesidades::with('deteccion_facilitador', 'carrera')->find($request->id_curso);
        $docente = Docente::with('usuario', 'posgrado', 'plaza', 'departamento', 'puesto')->find($request->docente);
        if ($curso && $docente){
            $r2 = ['usuario', 'posgrado', 'plaza', 'departamento', 'puesto'];
            $teacher = $docente->getAttributes();
//            dd($teacher);
            $null_values[] = 'El docente no cuenta con la siguiente información: ';
            foreach ($teacher as $key => $value) {
                if(is_null($value)){
//                    dd($key, $value);
////                    dd($docente->getAttributes());
                    if ($key != "interno" && "clavePresup" && "horarioEntrada" && "horarioSalida" && "prodep" && "empresa" && "tipodecurso"){
                        $band++;
                        $null_values[] = "'".$key."'";
                    }
                    break;
                }
            }
            $null_values[] = 'Tampoco cuenta con las siguientes relaciones: ';
            foreach ($r2 as $relacion) {
                if (is_null($docente->{$relacion})) {
//                    dd($relacion);
                    $band++;
                    $null_values[] = '"'.$relacion.'"';
                }
            }
            $null_values[] = '. El docente debe verificar sus datos.';
            $mensajeConcatenado = implode(" ", $null_values);
//            dd($mensajeConcatenado);
            $pdf = Pdf::loadView('pdf.CDI', compact('curso', 'docente', 'key'))
                        ->output();
            $path = 'CDI.pdf';
            $this->save_file($pdf, $path);
            if (Storage::disk('public')->exists($path)){
                return [$band, $mensajeConcatenado];
            }else{
                return "No se almaceno el archivo de manera correcta, intente generarlo nuevamente. Si el problema persiste se debe revisar el codigo.";
            }
        }else{
            return "No existe un curso o docente asociado a esta cedula";
        }
    }

    public function ficha_tecnica_pdf(Request $request)
    {
        $ficha = FichaTecnica::find($request->id_ficha);
        if ($ficha){
            $name_instituto = NombreInstituto::all();
            if ($name_instituto){
                $departamento = Departamento::with('jefe_docente')->where('nameDepartamento', '=', 'Departamento de Desarrollo Académico')->first();
                if($departamento){
                    $pdf = Pdf::loadView('pdf.fichatecnica', compact('ficha', 'name_instituto', 'departamento'))
                        ->output();
                    if ($pdf){
                        $path = 'ficha.pdf';
                        $this->save_file($pdf, $path);
                        if (Storage::disk('public')->exists($path)){
                            return "PDF descargado.";
                        }else{
                            return "No se almaceno el archivo de manera correcta, intente generarlo nuevamente. Si el problema persiste se debe revisar el codigo.";
                        }
                    }else{
                        return "No se genero el PDF de la ficha técnica. Se debe revisar en codigo fuente.";
                    }
                }else{
                    return "No existe un departamento académico relacionado con la ficha técnica.";
                }
            }else{
                return "No existe un nombre de instituto tecnologico por lo que no se puede generar el PDF de la ficha técnica.";
            }
        } else{
            return "No existe la ficha técnica. Si ya fue generada y el problema persiste, actualice la página";
        }
//        return $this->save_file($pdf, $path);
    }
    public function acta_calificaciones_pdf(Request $request)
    {
        $year = date('Y');
        if ($request->id){
            $curso = DeteccionNecesidades::with('calificaciones_curso', 'carrera', 'deteccion_facilitador')->find($request->id);
            $teachers = DB::table('docente')
                ->orderBy('nombre', 'asc')
                ->join('inscripcion', 'inscripcion.docente_id', '=', 'docente.id')
                ->leftJoin('calificaciones', function ($join) {
                    $join->on('calificaciones.docente_id', '=', 'docente.id')
                        ->on('calificaciones.curso_id', '=', 'inscripcion.curso_id');
                })
                ->where('inscripcion.curso_id', '=', $curso->id)
                ->select('docente.nombre_completo', 'calificaciones.calificacion AS calificacion')
                ->distinct()
                ->orderBy('docente.nombre_completo')
                ->get();
//            dd($teachers);
            if ($curso){
                $facilitadores = $curso->deteccion_facilitador;
                $pdf = Pdf::loadView('pdf.actacalificaciones', compact('year', 'curso', 'facilitadores', 'teachers'))
                    ->output();
                $path = 'acta_de_calificaciones.pdf';

                $this->save_file($pdf, $path);
                if (Storage::disk('public')->exists($path)){
                    return ["PDF descargado.", "success"];
                }else{
                    return ["No se almaceno el archivo de manera correcta, intente generarlo nuevamente. Si el problema persiste se debe revisar el codigo.", "error"];
                }
            }else{
                return ["La consulta a la base de datos del curso retorno con un valor vacío.", "error"];
            }
        }else{
            return ["No se envío en ID para buscar el curso.", "error"];
        }


    }
    public function constancia_pdf(Request $request)
    {
        $year = date('Y');
        $instituto = DB::table('nombre_instituto')->get();
        $docente = Docente::with('inscrito', 'posgrado', 'carrera', 'puesto')->find($request->id_docente);
        $curso = DeteccionNecesidades::with('deteccion_facilitador',  'clave_curso', 'clave_validacion')->find($request->id);
        $coordinacion = User::with('docente')->where('email', 'cformacion@tuxtla.tecnm.mx')->first();
        $ficha = FichaTecnica::where('id_curso', $curso->id)->first();
        $band = 0;
        $errors = [];

        $facilitador = $curso->deteccion_facilitador;
        $instituto = DB::table('nombre_instituto')->get();
        if ($instituto->isEmpty()) {
            $band++;
            $errors[] = 'La consulta a la base de datos para obtener el nombre del insituto tecnológico retorno con un valor vacio.';
        }

// Verificar la consulta $docente
        $docente = Docente::with('inscrito', 'posgrado', 'carrera', 'puesto')->find($request->id_docente);
        if (is_null($docente)) {
            $band++;
            $errors[] = 'El docente consultado en la base de datos no existe.';
        } else {
            foreach ($docente->getAttributes() as $key => $value) {
                if (is_null($value)) {
                    if ($key != "interno" && "clavePresup" && "horarioEntrada" && "horarioSalida" && "prodep" && "empresa" && "tipodecurso"){
                        $band++;
                        $errors[] = 'La propiedad ' .'"'. $key .'"'. ' está vacía.';
                    }
                    break;
                }
            }
            foreach (['inscrito', 'posgrado', 'carrera', 'puesto'] as $relation) {
                if (is_null($docente->{$relation})) {
                    $band++;
                    $errors[] = 'La relación ' .'"'. $relation .'"'. ' esta vacía.';
                }
            }
        }

// Verificar la consulta $curso
        $curso = DeteccionNecesidades::with('deteccion_facilitador', 'clave_curso', 'clave_validacion')->find($request->id);
        if (is_null($curso)) {
            $band++;
            $errors[] = 'El curso consultado en la base de datos retorno un valor vacio.';
        } else {
            foreach ($curso->getAttributes() as $key => $value) {
                if (is_null($value)) {
                    if ($key != "anio_realizacion" && $key != "observaciones" && $key != "facilitador_externo"){
                        $band++;
                        $errors[] = 'La propiedad ' .'"'. $key .'"'. ' del curso está vacía. Se debe verificar la información del curso';
                    }
                    break;
                }
            }
            foreach (['deteccion_facilitador', 'clave_curso', 'clave_validacion'] as $relation) {
                if (is_null($curso->{$relation})) {
                    $band++;
                    $errors[] = 'La relación ' .'"'. $relation .'"'. ' del curso retorno un valor vacío. Verifique la información del curso.';
                }
            }
        }

// Verificar la consulta $coordinacion
        $coordinacion = User::with('docente')->where('email', 'cformacion@tuxtla.tecnm.mx')->first();
        if (is_null($coordinacion)) {
            $band++;
            $errors[] = 'La consulta en la base de datos para obetner al usuario que pertenece a la coordinación de formación docente retornó un valor vacio. Ingrese al apartado de "Configuracion" y verificar la información';
        } else {
            foreach ($coordinacion->getAttributes() as $key => $value) {
                if (is_null($value)) {
                    if ($key != "name" && "email_verified_at" && "created_at"){
                        $band++;
                        $errors[] = 'La propiedad ' .'"'. $key .'"'. ' del usuario de la coordinación de formación docente retornó un valor vacio. Ingrese al apartado de "Configuracion" y verificar la información';
                    }
                    break;
                }
            }
            if (is_null($coordinacion->docente)) {
                $band++;
                $errors[] = 'La relación del usuario con el docente está vacía. Ingrese al apartado de "Configuracion" y verifique la información';
            }
        }

// Verificar la consulta $ficha
        if (!is_null($curso)) {
            $ficha = FichaTecnica::where('id_curso', $curso->id)->first();
            if (is_null($ficha)) {
                $band++;
                $errors[] = 'La consulta a la ficha tecnica relacionada con el curso retornó vacia. Es probable que no ha sido creada, verifique en el apartado de "Ficha técnica".';
            } else {
                foreach ($ficha->getAttributes() as $key => $value) {
                    if (is_null($value)) {
                        if ($key != "descripcion_servicio"){
                            $band++;
                            $errors[] = 'La propiedad ' .'"'. $key .'"'. ' de la ficha tecnica está vacía. Verifique la información.';
                        }
                        break;
                    }
                }
            }
        }else {
            $band++;
            $errors[] = 'La consulta para obtener el curso retornó un valor vacio. Si persisite, revise la información del curso.';
        }
        $director = DB::table('director')->where('id', 1)->get();
//        dd($director);
        if (is_null($director)){
            $band++;
            $errors[] = 'La consulta para obtener el nombre del director de la institucion retorno un valor vacio. Ingrese al apartado de "Configuracion" y verificar la información.';
        }else {
            foreach ($director as $key => $value) {
                if (is_null($value)) {
//                    if ($key != "descripcion_servicio"){
                        $band++;
                        $errors[] = 'La propiedad ' .'"'. $key .'"'. ' tiene un valor vacío. Ingrese al apartado de "Configuracion" y verificar la información.';
//                    }
                    break;
                }
            }
        }
        $clean = implode(" ", $errors);
        if ($band != 0) {
            return [$clean, $band];
        }
        $actual_date = date('Y-m-d');
        $day = date('d');
        $anio = date('Y');
        $month_get = $this->parse_date($actual_date);

        $fecha = $curso->fecha_I;
        $fecha2 = $curso->fecha_F;
        $formatFechasI = explode("-", $fecha);
        $formatFechasF = explode("-", $fecha2);
//            $director = Director::all();
        $month = $this->parse_date($fecha);

        $pdf_1 = Pdf::loadView('pdf.constancia', compact('year', 'instituto', 'docente', 'formatFechasI', 'formatFechasF', 'month', 'facilitador', 'director', 'curso'))
            ->setOptions([
                'fontDir' => storage_path('app/public/fonts/'),
            ])
            ->output();

        $path = 'constancia1.pdf';

        $pdf_2 = Pdf::loadView('pdf.constancia_2', compact('year', 'curso', 'docente', 'day', 'anio', 'month_get', 'coordinacion', 'ficha'))
            ->setPaper('a4', 'landscape')
            ->output();

        $path_2 = 'constancia2.pdf';

        $this->save_file($pdf_1, $path);
        $this->save_file($pdf_2, $path_2);

        if (Storage::disk('public')->exists($path) && Storage::disk('public')->exists($path_2)){
            $this->merge_pdf('constancia1', 'constancia2', 'constancia.pdf');
            return [$clean, $band];
        }else{
            $band++;
            $errors[] = "No se almaceno el archivo de manera correcta, intente generarlo nuevamente. Si el problema persiste se debe revisar el codigo.";
            return [$errors, $band];
        }
    }

    public function reconocimiento(Request $request){
        $year = date('Y');
        $instituto = DB::table('nombre_instituto')->get();
        $docente = Docente::with('inscrito', 'posgrado', 'carrera', 'puesto')->find($request->id_docente);
        $curso = DeteccionNecesidades::with('deteccion_facilitador',  'clave_curso', 'clave_validacion')->find($request->id);
        $coordinacion = User::with('docente')->where('email', 'cformacion@tuxtla.tecnm.mx')->first();
        $ficha = FichaTecnica::where('id_curso', $curso->id)->first();
        $band = 0;
        $errors = [];

        $facilitador = $curso->deteccion_facilitador;
        $instituto = DB::table('nombre_instituto')->get();
        if ($instituto->isEmpty()) {
            $band++;
            $errors[] = 'La consulta a la base de datos para obtener el nombre del insituto tecnológico retorno con un valor vacio.';
        }

// Verificar la consulta $docente
        $docente = Docente::with('inscrito', 'posgrado', 'carrera', 'puesto')->find($request->id_docente);
        if (is_null($docente)) {
            $band++;
            $errors[] = 'El docente consultado en la base de datos no existe.';
        } else {
            foreach ($docente->getAttributes() as $key => $value) {
                if (is_null($value)) {
                    if ($key != "interno" && "clavePresup" && "horarioEntrada" && "horarioSalida" && "prodep" && "empresa" && "tipodecurso"){
                        $band++;
                        $errors[] = 'La propiedad ' .'"'. $key .'"'. ' está vacía.';
                    }
                    break;
                }
            }
            foreach (['inscrito', 'posgrado', 'carrera', 'puesto'] as $relation) {
                if (is_null($docente->{$relation})) {
                    $band++;
                    $errors[] = 'La relación ' .'"'. $relation .'"'. ' esta vacía.';
                }
            }
        }

// Verificar la consulta $curso
        $curso = DeteccionNecesidades::with('deteccion_facilitador', 'clave_curso', 'clave_validacion')->find($request->id);
        if (is_null($curso)) {
            $band++;
            $errors[] = 'El curso consultado en la base de datos retorno un valor vacio.';
        } else {
            foreach ($curso->getAttributes() as $key => $value) {
                if (is_null($value)) {
                    if ($key != "anio_realizacion" && $key != "observaciones" && $key != "facilitador_externo"){
                        $band++;
                        $errors[] = 'La propiedad ' .'"'. $key .'"'. ' del curso está vacía. Se debe verificar la información del curso';
                    }
                    break;
                }
            }
            foreach (['deteccion_facilitador', 'clave_curso', 'clave_validacion'] as $relation) {
                if (is_null($curso->{$relation})) {
                    $band++;
                    $errors[] = 'La relación ' .'"'. $relation .'"'. ' del curso retorno un valor vacío. Verifique la información del curso.';
                }
            }
        }

// Verificar la consulta $coordinacion
        $coordinacion = User::with('docente')->where('email', 'cformacion@tuxtla.tecnm.mx')->first();
        if (is_null($coordinacion)) {
            $band++;
            $errors[] = 'La consulta en la base de datos para obetner al usuario que pertenece a la coordinación de formación docente retornó un valor vacio. Ingrese al apartado de "Configuracion" y verificar la información';
        } else {
            foreach ($coordinacion->getAttributes() as $key => $value) {
                if (is_null($value)) {
                    if ($key != "name" && "email_verified_at" && "created_at"){
                        $band++;
                        $errors[] = 'La propiedad ' .'"'. $key .'"'. ' del usuario de la coordinación de formación docente retornó un valor vacio. Ingrese al apartado de "Configuracion" y verificar la información';
                    }
                    break;
                }
            }
            if (is_null($coordinacion->docente)) {
                $band++;
                $errors[] = 'La relación del usuario con el docente está vacía. Ingrese al apartado de "Configuracion" y verifique la información';
            }
        }

// Verificar la consulta $ficha
        if (!is_null($curso)) {
            $ficha = FichaTecnica::where('id_curso', $curso->id)->first();
            if (is_null($ficha)) {
                $band++;
                $errors[] = 'La consulta a la ficha tecnica relacionada con el curso retornó vacia. Es probable que no ha sido creada, verifique en el apartado de "Ficha técnica".';
            } else {
                foreach ($ficha->getAttributes() as $key => $value) {
                    if (is_null($value)) {
                        if ($key != "descripcion_servicio"){
                            $band++;
                            $errors[] = 'La propiedad ' .'"'. $key .'"'. ' de la ficha tecnica está vacía. Verifique la información.';
                        }
                        break;
                    }
                }
            }
        }else {
            $band++;
            $errors[] = 'La consulta para obtener el curso retornó un valor vacio. Si persisite, revise la información del curso.';
        }
        $director = DB::table('director')->where('id', 1)->get();
//        dd($director);
        if (is_null($director)){
            $band++;
            $errors[] = 'La consulta para obtener el nombre del director de la institucion retorno un valor vacio. Ingrese al apartado de "Configuracion" y verificar la información.';
        }else {
            foreach ($director as $key => $value) {
                if (is_null($value)) {
//                    if ($key != "descripcion_servicio"){
                    $band++;
                    $errors[] = 'La propiedad ' .'"'. $key .'"'. ' tiene un valor vacío. Ingrese al apartado de "Configuracion" y verificar la información.';
//                    }
                    break;
                }
            }
        }
        $clean = implode(" ", $errors);
        if ($band != 0) {
            return [$clean, $band];
        }
        $actual_date = date('Y-m-d');
        $day = date('d');
        $anio = date('Y');
        $month_get = $this->parse_date($actual_date);

        $fecha = $curso->fecha_I;
        $fecha2 = $curso->fecha_F;
//        dd($fecha, $fecha2);
        $formatFechasI = explode("-", $fecha);
        $formatFechasF = explode("-", $fecha2);
//            $director = Director::all();
        $month = $this->parse_date($fecha);
//        dd($formatFechasI, $formatFechasF, $month);
        $pdf_1 = Pdf::loadView('pdf.reconocimiento', compact('year', 'instituto', 'docente', 'formatFechasI', 'formatFechasF', 'month', 'facilitador', 'director', 'curso'))
            ->setOptions([
                'fontDir' => storage_path('app/public/fonts/'),
            ])
            ->output();

        $path = 'reconocimiento1.pdf';

        $pdf_2 = Pdf::loadView('pdf.reconocimiento_2', compact('year', 'curso', 'docente', 'day', 'anio', 'month_get', 'coordinacion', 'ficha'))
            ->setPaper('a4', 'landscape')
            ->output();

        $path_2 = 'reconocimiento2.pdf';

        $this->save_file($pdf_1, $path);
        $this->save_file($pdf_2, $path_2);

        if (Storage::disk('public')->exists($path) && Storage::disk('public')->exists($path_2)){
            $merge = $this->merge_pdf('reconocimiento1', 'reconocimiento2', 'reconocimiento.pdf');
            if ($merge[1] == 200){
                return [$clean, $band];
            }else{
                $band++;
                return [$merge[0], $band];
            }
        }else{
            $band++;
            $errors[] = "No se almaceno el archivo de manera correcta, intente generarlo nuevamente. Si el problema persiste se debe revisar el codigo.";
            return [$errors, $band];
        }
    }

    public static function merge_pdf($pdf1, $pdf2, $fileName)
    {
        $name = $fileName;
        $ruta = storage_path('app/public/' . $name);
        $pdf_1 = storage_path('app/public/' . $pdf1 . '.pdf');
        $pdf_2 = storage_path('app/public/' . $pdf2 . '.pdf');

        if (!file_exists($pdf_1) || !file_exists($pdf_2)) {
            // Manejar la falta de archivos, lanzar excepción o generar los PDFs previamente si es necesario
//            dd('app/public/' . $pdf1 . '.pdf', file_exists(storage_path('app/public/reconocimiento1.pdf')));
            return ["Los PDF's no se generaron y no se almacenaron en la carpeta de archivos del sistema, se debe revisar en el codigo fuente.", 500];
        } else {
            $pdf = new Fpdi();
//            dd('her');
            $pageCount1 = $pdf->setSourceFile($pdf_1);
            $template1 = $pdf->importPage(1);
            $pdf->addPage();
            $pdf->useTemplate($template1);

            // Añade la primera página del segundo PDF
            $pageCount2 = $pdf->setSourceFile($pdf_2);


            // Añade la primera página del segundo PDF con la misma orientación del papel del primer PDF
            $template2 = $pdf->importPage(1);
            $pdf->addPage('landscape');
            $pdf->useTemplate($template2);

            $pdf->Output($ruta, 'F');
            return [$ruta, 200];
        }
    }

    public static function parse_date($fecha1)
    {
        $fechaActual = date("Y-m-d");
        $componentes2 = date_parse($fechaActual);
        $componentes = date_parse($fecha1);

        $anio = $componentes['year'];
        $anio_2 = $componentes2['year'];

        $mesesEnEspanol = [
            1 => 'enero',
            2 => 'febrero',
            3 => 'marzo',
            4 => 'abril',
            5 => 'mayo',
            6 => 'junio',
            7 => 'julio',
            8 => 'agosto',
            9 => 'septiembre',
            10 => 'octubre',
            11 => 'noviembre',
            12 => 'diciembre'
        ];

        $mesNumero = $componentes['month'];

        $month_number = $componentes2['month'];
        $day = $componentes2['day'];
        return [$mesesEnEspanol[$mesNumero], $mesesEnEspanol[$month_number], $day];
    }
}
