<?php

namespace App\Http\Controllers;

use App\Http\Requests\PIFDAPRequest;
use App\Http\Requests\RequestPDFDeteccion;
use App\Models\Departamento;
use App\Models\DeteccionNecesidades;
use App\Models\Director;
use App\Models\Docente;
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

    public function deteccion_pdf(RequestPDFDeteccion $request): \Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\JsonResponse
    {
        $request->validated();
        $cursos = $this->pdf_request_deteccion($request);
        $subdireccion = Subdireccion::all();
        $presidente = DB::table('carreras')
            ->selectRaw("docente.nombre_completo AS nombre")
            ->join('docente', 'carreras.presidente_academia', '=', 'docente.id')
            ->where('carreras.id', '=', $request->carrera)
            ->first();
        if (count($cursos) == 0) {
            return response()->json([
                'mensaje' => 'No se encontro ningun dato con ese criterio de busqueda'
            ]);
        } else {
            $pdf = Pdf::loadView('pdf.deteccion', compact('cursos', 'subdireccion', 'presidente'))->output();
            $path = "Deteccion.pdf";
            $this->save_file($pdf, $path);
            //            return $this->download_file($path);
            return response()->json([
                'status' => 'Ok'
            ]);
        }
    }
    public function PIFDAP_pdf(PIFDAPRequest $request)
    {
        $request->validated();
        $FD = $this->FD_request($request);
        $AP = $this->AP_request($request);
        $subdireccion = Subdireccion::all();
        $departamento = Departamento::with('jefe_docente')
            ->where('nameDepartamento', '=', 'Departamento de Desarrollo Académico')
            ->first();
        $anio = $request->anio;
        $periodo = $request->periodo;
        if (count($FD) == 0 && count($AP) == 0) {
            return response()->json([
                'mensaje' => 'No se encontro ningun dato con ese criterio de busqueda',
            ]);
        } else {
            $pdf = Pdf::loadView('pdf.PIFDAP', compact('FD', 'AP', 'anio', 'periodo', 'subdireccion', 'departamento'))
                ->setPaper('letter', 'landscape')
                ->output();
            $path = 'PIFDAP.pdf';
            $this->save_file($pdf, $path);
            //            return $this->download_file($path);
            return response()->json([
                'status' => 'OK'
            ]);
        }
    }

    public function cdi_pdf(Request $request)
    {
//        dd($request);
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
//            $relaciones = ['deteccion_facilitador', 'carrera'];
//            foreach ($curso->getAttributes() as $key => $value) {
//                if(is_null($value)){
//                    $band++;
//                    $null_values[] = 'La propiedad del curso: '.$key.' esta vacia';
//                }
//            }
//            foreach ($relaciones as $relacion) {
//                if (is_null($curso->{$relacion})) {
//                    $band++;
//                    $null_values[] = 'La relacion del curso'.$relacion.' esta vacia';
//                }
//            }
            $r2 = ['usuario', 'posgrado', 'plaza', 'departamento', 'puesto'];
            $teacher = $docente->getAttributes();
//            dd($teacher);
            foreach ($teacher as $key => $value) {
                if(is_null($value)){
//                    dd($key, $value);
////                    dd($docente->getAttributes());
                    if ($key != "interno" && "clavePresup" && "horarioEntrada" && "horarioSalida" && "prodep" && "empresa" && "tipodecurso"){
                        $band++;
                        $null_values[] = "Falta información del docente: '".$key."'. Esta vacia";
                    }
                    break;
                }
            }
            foreach ($r2 as $relacion) {
                if (is_null($docente->{$relacion})) {
                    $band++;
                    $null_values[] = 'El docente no cuenta con la relacion de "'.$relacion.'", esta vacio. Informe al administrador del sistema';
                }
            }
            $mensajeConcatenado = implode(". ", $null_values);
            $pdf = Pdf::loadView('pdf.CDI', compact('curso', 'docente'))
                        ->output();
            $path = 'CDI.pdf';
            $this->save_file($pdf, $path);
            return [$band, $mensajeConcatenado];
        }else{
            return "No existe un curso o docente asociado a esta cedula";
        }
    }

    public function ficha_tecnica_pdf(Request $request)
    {
        $ficha = FichaTecnica::find($request->id_ficha);
        $name_instituto = NombreInstituto::all();
        $departamento = Departamento::with('jefe_docente')->where('nameDepartamento', '=', 'Departamento de Desarrollo Académico')->first();
        $pdf = Pdf::loadView('pdf.fichatecnica', compact('ficha', 'name_instituto', 'departamento'))
            ->output();
        //
        $path = 'ficha.pdf';

        return $this->save_file($pdf, $path);

        //        return response()->json([
        //            'chinga tu puta madre' => $ficha,
        //            'ya me tienes hasta la verga' => $name_instituto,
        //            'hijo de tu puta madre' => $departamento,
        ////            'pdf' => $pdf
        //        ]);
    }
    public function acta_calificaciones_pdf(Request $request)
    {
        $year = date('Y');
        $curso = DeteccionNecesidades::with('calificaciones_curso', 'carrera', 'deteccion_facilitador')->find($request->id);
        $facilitadores = $curso->deteccion_facilitador;
//        dd($facilitadores, $curso);
        $pdf = Pdf::loadView('pdf.actacalificaciones', compact('year', 'curso', 'facilitadores'))
            ->output();

        $path = 'acta_de_calificaciones.pdf';

        return $this->save_file($pdf, $path);
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
            $errors[] = 'La consulta $instituto no retornó ningún resultado.';
        }

// Verificar la consulta $docente
        $docente = Docente::with('inscrito', 'posgrado', 'carrera', 'puesto')->find($request->id_docente);
        if (is_null($docente)) {
            $band++;
            $errors[] = 'La consulta del docente retornó un valor vacio. Notifique al administrador del sistema.';
        } else {
            foreach ($docente->getAttributes() as $key => $value) {
                if (is_null($value)) {
                    if ($key != "interno" && "clavePresup" && "horarioEntrada" && "horarioSalida" && "prodep" && "empresa" && "tipodecurso"){
                        $band++;
                        $errors[] = 'La propiedad ' . $key . ' del docente está vacía. Notifique al administrador del sistema.';
                    }
                    break;
                }
            }
            foreach (['inscrito', 'posgrado', 'carrera', 'puesto'] as $relation) {
                if (is_null($docente->{$relation})) {
                    $band++;
                    $errors[] = 'La relación ' . $relation . ' del docente está vacía. Notifique al administrador del sistema.';
                }
            }
        }

// Verificar la consulta $curso
        $curso = DeteccionNecesidades::with('deteccion_facilitador', 'clave_curso', 'clave_validacion')->find($request->id);
        if (is_null($curso)) {
            $band++;
            $errors[] = 'La consulta al curso retornó un valor vacio. Notifique al administrador del sistema.';
        } else {
            foreach ($curso->getAttributes() as $key => $value) {
                if (is_null($value)) {
                    if ($key != "anio_realizacion" && $key != "observaciones" && $key != "facilitador_externo"){
                        $band++;
                        $errors[] = 'La propiedad ' . $key . ' del curso está vacía. Notifique al administrador del sistema.';
                    }
                    break;
                }
            }
            foreach (['deteccion_facilitador', 'clave_curso', 'clave_validacion'] as $relation) {
                if (is_null($curso->{$relation})) {
                    $band++;
                    $errors[] = 'La relación ' . $relation . ' del curso retorno un valor vacío. Notifique al administrador del sistema.';
                }
            }
        }

// Verificar la consulta $coordinacion
        $coordinacion = User::with('docente')->where('email', 'cformacion@tuxtla.tecnm.mx')->first();
        if (is_null($coordinacion)) {
            $band++;
            $errors[] = 'La consulta al usuario que pertenece a la coordinacion del departamento de desarrollo académico retornó un valor vacio. Notifique al administrador del sistema.';
        } else {
            foreach ($coordinacion->getAttributes() as $key => $value) {
                if (is_null($value)) {
                    if ($key != "name" && "email_verified_at" && "created_at"){
                        $band++;
                        $errors[] = 'La propiedad ' . $key . ' del usuario del departamento de desarrollo académico retornó un valor vacio. Notifique al administrador del sistema.';
                    }
                    break;
                }
            }
            if (is_null($coordinacion->docente)) {
                $band++;
                $errors[] = 'La relación del usuario con el docente está vacía.';
            }
        }

// Verificar la consulta $ficha
        if (!is_null($curso)) {
            $ficha = FichaTecnica::where('id_curso', $curso->id)->first();
            if (is_null($ficha)) {
                $band++;
                $errors[] = 'La consulta a la ficha tecnica relacionada con el curso retornó vacia. Es probable que no ha sido creada, verifique el icono en forma de documento ubicado en la parte superior derecha. Si persisite, notifique al administrador del sistema.';
            } else {
                foreach ($ficha->getAttributes() as $key => $value) {
                    if (is_null($value)) {
                        if ($key != "descripcion_servicio"){
                            $band++;
                            $errors[] = 'La propiedad ' . $key . ' de la ficha tecnica está vacía. Notifique al administrador del sistema.';
                        }
                        break;
                    }
                }
            }
        }else {
            $band++;
            $errors[] = 'La consulta al curso  retornó vacia. Si persisite, notifique al administrador del sistema.';
        }
        $director = DB::table('director')->where('id', 1)->get();
//        dd($director);
        if (is_null($director)){
            $band++;
            $errors[] = 'La consulta al director de la institucion retorno vacia, Notifique al departamento de desarrollo académico.';
        }else {
            foreach ($director as $key => $value) {
                if (is_null($value)) {
//                    if ($key != "descripcion_servicio"){
                        $band++;
                        $errors[] = 'La propiedad ' . $key . ' de la ficha tecnica está vacía. Notifique al administrador del sistema.';
//                    }
                    break;
                }
            }
        }
//        dd($band);
        if ($band != 0) {
            return [$errors, $band];
        }
        $actual_date = date('Y-m-d');
        $day = date('d');
        $anio = date('Y');
        $month_get = $this->parse_date($actual_date);

        $fecha = $docente->inscrito[0]->fecha_I;
        $fecha2 = $docente->inscrito[0]->fecha_F;
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

        $this->merge_pdf('constancia1', 'constancia2');
        return [$errors, $band];
//        return response()->json([
//            'docente' => $docente,
//            'year' => $year,
//            'facilitador' => $facilitador,
//            'coordinacion' => $coordinacion,
//        ]);
//        }else{
//            return [$errors, $band];
//        }
    }

    public static function merge_pdf($pdf1, $pdf2)
    {
        $name = "constancia.pdf";
        $ruta = storage_path('app/public/' . $name);
        $pdf_1 = storage_path('app/public/' . $pdf1 . '.pdf');
        $pdf_2 = storage_path('app/public/' . $pdf2 . '.pdf');

        if (!file_exists($pdf_1) || !file_exists($pdf_2)) {
            // Manejar la falta de archivos, lanzar excepción o generar los PDFs previamente si es necesario
            return "Los archivos no existen, notifica al administrador del sistema.";
        } else {
            $pdf = new Fpdi();

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
            return $ruta;
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
