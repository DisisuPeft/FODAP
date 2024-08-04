<?php

namespace App\Http\Controllers;

use App\Events\DatesEnableEvent;
use App\Http\Controllers\API\v1\DataResponseController;
use App\Http\Requests\StoreImageRequest;
use App\Mail\PermisosUserEdit;
use App\Models\Carrera;
use App\Models\ConfigDates;
use App\Models\Departamento;
use App\Models\DeteccionNecesidades;
use App\Models\Director;
use App\Models\Docente;
use App\Models\Lugar;
use App\Models\NombreInstituto;
use App\Models\Subdireccion;
use App\Models\User;
use App\Rules\ValidFileExtension;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\In;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use App\Models\FileFT;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;


class   GestionParametrosController extends Controller
{
    public function edit(Request $request)
    {
        $users =  User::where('id', '!=', auth()->user()->id)->with('docente', 'departamento', 'permissions')->get();
        $lugar = Lugar::with('curso')->get();
        $departamento = Departamento::with('jefe_docente')->get();
        $carrera = Carrera::with('departamento', 'presidente_academia')->get();
        $docente = Docente::all();
        $sub = Subdireccion::all();
        $director = Director::all();
        $fechas = ConfigDates::latest('id')->first();
        $instituto = NombreInstituto::all();
        $roles = Role::all();

        return Inertia::render('Views/desarrollo/GestionEdit', [
            'docente' => $docente,
            'carrera_relacion' => $carrera->pluck('departamento'),
            'departamento' => $departamento,
            'carrera' => $carrera->except('13'),
            'lugar' => $lugar,
            'users' => $users,
            'sub' => $sub,
            'fechas' => $fechas,
            'director' => $director,
            'instituto' => $instituto,
            'roles' => $roles->except([5,1,2,4])
        ]);
    }

    public function create_carrera(Request $request)
    {
        $departamento = Departamento::all();
        $carrera = Carrera::all()->except(['13']);
        $docente = Docente::all();
        return Inertia::render('Views/desarrollo/forms/CreateCarrera', [
            'docente' => $docente,
            'carrera' => $carrera,
            'departamento' => $departamento,
        ]);
    }

    public function store_carrera(Request $request)
    {
        $nameInput = trim($request->input('nombre_carrera'));
        $band = 0;
        $message = "";
        $rules = [
            'departamento_id' => 'required',
            'nombre_carrera' => 'required',
            'presidente_academia' => 'required',
        ];

        $validador = Validator::make($request->all(), $rules, [
            'required' => 'El :attribute es requerido para crear el recurso.',
        ]);

        if (!$validador->fails()) {
            DB::beginTransaction();

            $carreras = Carrera::all();

            foreach ($carreras as $carrera) {
                $nombre_carrera = strtolower($carrera->nameCarrera);
                $nombre_comp = strtolower($nameInput);
                if ($nombre_carrera == $nombre_comp){
                    $band++;
                    $message .= $carrera->nameCarrera . " ya existe, debes ingresar uno diferente.";
                }
            }
            if($band == 0){
                $newCarrera = Carrera::create([
                    'nameCarrera' => $nameInput,
                    'departamento_id' => $request->input('departamento_id'),
                    'presidente_academia' => $request->input('presidente_academia'),
                ]);

                if ($newCarrera){
                   DB::commit();
                   return redirect()->route('parametros.edit');
                }else{
                   DB::rollBack();
                   return back()->withErrors('La carrera tuvo detalles para crearse.');
                }
            }else{
                return back()->withErrors($message)->withInput();
            }
        }else{
            return back()->withErrors($validador)->withInput();
        }
    }
    public function edit_carrera(Request $request, $id)
    {
        $carrera = Carrera::find($id);
        $departamento = Departamento::all();
        $docente = Docente::all();

        return Inertia::render('Views/desarrollo/forms/EditCarrera', [
            'docente' => $docente,
            'carrera' => $carrera,
            'departamento' => $departamento,
        ]);
    }
    public function update_carrera(Request $request, $id)
    {
        $rules = [
            'departamento_id' => 'required',
            'nombre_carrera' => 'required',
            'presidente_academia' => 'required',
        ];

        $validador = Validator::make($request->all(), $rules, [
            'required' => 'El :attribute es requerido para crear el recurso.',
        ]);
        if (!$validador->fails()) {
            DB::beginTransaction();

            $carrera = Carrera::where('id', $id)->update([
                'departamento_id' => $request->departamento_id,
                'nameCarrera' => $request->nombre_carrera,
                'presidente_academia' => $request->presidente_academia
            ]);
//            dd($carrera);
            if ($carrera > 0){
                DB::commit();
                return redirect()->route('edit.carrera', ['id' => $id]);
            }else{
                return back()->withErrors('El área académica no fue actualizada ya que no se cambio ningun dato.');
            }
        }else{
            return back()->withErrors($validador)->withInput();
        }
    }

    public function create_departamento()
    {
        $departamento = Departamento::with('jefe_docente')->get();
        $carrera = Carrera::all()->except(['13']);
        $docente = Docente::all();
        return Inertia::render('Views/desarrollo/forms/CreateDepartamento', [
            'docente' => $docente,
            'carrera' => $carrera,
            'departamento' => $departamento,
        ]);
    }

    public function store_departamento(Request $request)
    {
        $nameInput = trim($request->input('nombre_departamento'));
        $band = 0;
        $message = "";
        $rules = [
//            'carrera_id' => 'required',
            'nombre_departamento' => 'required',
            'jefe_id' => 'required',
        ];

        $validador = Validator::make($request->all(), $rules, [
            'required' => 'El :attribute es requerido para crear el recurso.',
        ]);

        if (!$validador->fails()) {
            DB::beginTransaction();

            $departamentos = Departamento::all();

            foreach ($departamentos as $departamento) {
                $nombre_departamento = strtolower($departamento->nameDepartamento);
                $nombre_comp = strtolower($nameInput);
                if ($nombre_departamento == $nombre_comp){
                    $band++;
                    $message .= $departamento->nameDepartamento . " ya existe, debes ingresar uno diferente.";
                }
            }
            if($band == 0){
                $newDepartamento = Departamento::create([
//                    'carrera_id' => $request->carrera_id,
                    'nameDepartamento' => $nameInput,
                    'jefe_id' => $request->jefe_id
                ]);

                if ($newDepartamento){
                    DB::commit();
                    return redirect()->route('create.departamento');
                }else{
                    DB::rollBack();
                    return back()->withErrors('La carrera tuvo detalles para crearse.');
                }
            }else{
                return back()->withErrors($message)->withInput();
            }
        }else{
            return back()->withErrors($validador)->withInput();
        }
    }

    public function edit_departamento(Request $request, $id)
    {
        $departamento = Departamento::find($id);
        $carrera = Carrera::all();
        $docente = Docente::all();

        return Inertia::render('Views/desarrollo/forms/EditDepartamento', [
            'docente' => $docente,
            'carrera' => $carrera,
            'departamento' => $departamento,
        ]);
    }
    public function update_departamento(Request $request, $id)
    {
        $rules = [
//            'carrera_id' => 'required',
            'nombre_departamento' => 'required',
            'jefe_id' => 'required',
        ];

        $validador = Validator::make($request->all(), $rules, [
            'required' => 'El :attribute es requerido para crear el recurso.',
        ]);
        if (!$validador->fails()) {
            DB::beginTransaction();
//            dd($id);
            $departamento = Departamento::where('id', $id)->update([
//                'carrera_id' => $request->carrera_id,
                'nameDepartamento' => $request->nombre_departamento,
                'jefe_id' => $request->jefe_id
            ]);
//            dd($departamento);
            if ($departamento > 0){
                DB::commit();
                return redirect()->route('edit.departamento', ['id' => $id]);
            }else{
                return back()->withErrors('El departamento consultado no requirió ser actualizado.');
            }
        }else{
            return back()->withErrors($validador)->withInput();
        }

//        return redirect()->route('edit.departamento', ['id' => $id]);
    }

    public function create_lugar()
    {
        return Inertia::render('Views/desarrollo/forms/CreateLugar');
    }

    public function store_lugar(Request $request)
    {
        $band = 0;
        $message = "";
        $nameLugar = trim($request->input('nombreAula'));
        $validador = Validator::make($request->all(), [
           'nombreAula' => 'required'
        ], [
            'nombreAula.required' => 'El nombre del aula es requerido.'
        ]);

        if (!$validador->fails()) {
            DB::beginTransaction();
            $lugares = Lugar::all();
            foreach ($lugares as $lugar){
                $n = strtolower($lugar->nombreAula);
                $comp = strtolower($nameLugar);
                if ($n == $comp){
                    $band++;
                    $message .= $lugar->nombreAula . " ya existe, debes ingresar uno diferente.";
                }
            }
            if($band == 0){
                $lugar = Lugar::create([
                    'nombreAula' => $nameLugar,
                ]);
                if ($lugar){
                    DB::commit();
                    return Redirect::route('create.lugar');
                }else{
                    DB::rollBack();
                    return back()->withErrors('El lugar no pudo ser creado.');
                }
            }else {
                DB::rollBack();
                return back()->withErrors($message);
            }
        }else{
            return back()->withErrors($validador)->withInput();
        }
    }

    public function edit_lugar($id)
    {
        return Inertia::render('Views/desarrollo/forms/EditLugar', [
            'lugar' => Lugar::find($id)
        ]);
    }

    public function update_lugar($id, Request $request)
    {
        $nameLugar = trim($request->input('nombreAula'));
        $validador = Validator::make($request->all(), [
            'nombreAula' => 'required',
        ], [
            'nombreAula.required' => 'El nombre del lugar es requerido.'
        ]);

        if (!$validador->fails()) {
            DB::beginTransaction();
            $lugar = Lugar::where('id', $id)->update([
                'nombreAula' => $nameLugar,
            ]);
            if ($lugar > 0){
                DB::commit();
                return Redirect::route('edit.lugar', ['id' => $id]);
            }else {
                DB::rollBack();
                return back()->withErrors('El lugar no requirió ser actualizado.');
            }
        }else {
            return back()->withErrors($validador)->withInput();
        }
//        return Redirect::route('parametros.edit');
    }

    public function delete_lugar($id)
    {
        if ($id){
            $lugar = Lugar::find($id);
            $lugar->delete();
            return Redirect::route('parametros.edit');
        }else{
            return back()->withErrors('El lugar no pudo ser eliminado, hace falta pasar el ID.');
        }
    }

    public function dates_detecciones(Request $request)
    {
        $validador = Validator::make($request->all(), [
            'fecha_Inicio' => 'required',
            'fecha_Final' => 'required',
        ]);

        if($validador->fails()){
            return Redirect::back()->withErrors('Las fechas no fueron ingresadas.');
        }else {
            if ($request->fecha_Inicio <= $request->fecha_Final) {
                DB::beginTransaction();
                date_default_timezone_set('America/Mexico_City');

                $dates = ConfigDates::latest('id')->first();
                if ($dates){
                    $dates->delete();
                }
                $newDates = ConfigDates::create([
                    'fecha_inicio' => $request->fecha_Inicio,
                    'fecha_final' => $request->fecha_Final,
                ]);
                if ($newDates){
                    DB::commit();
                    return Redirect::route('parametros.edit');
                }else{
                    DB::rollBack();
                    return back()->withErrors('Las fechas no pudieron ser establecidas.');
                }
            } else {
                return back()->withErrors('La fecha final no puede ser menor que la fecha inicial.');
            }
        }
    }

    public function create_user_academico(Request $request)
    {
        $rules = [
          'email' => 'required|email|unique:users',
          'password' => 'required|min:8',
          'docente_id' => 'required',
          'departamento_id' => 'required',
          'role' => 'required',
        ];
        $validador = Validator::make($request->all(), $rules, [
            'email.required' => 'El correo institucional es requerido.',
            'email.unique' => 'El correo institucional ya esta registrado.',
            'email.email' => 'Debe ser un correo institucional valido.',
            'password.required' => 'La contraseña es requerida.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'docente_id.required' => 'El docente asociado es requerido.',
            'departamento_id.required' => 'El departamento asociado es requerido.',
            'role.required' => 'El rol es requerido.',
        ]);

        if (!$validador->fails()) {
            DB::beginTransaction();

            $user = User::create([
                'email' => trim($request->input('email')),
                'password' => bcrypt($request->input('password')),
                'role' => $request->input('role'),
                'departamento_id' => $request->input('departamento_id'),
                'docente_id' => $request->input('docente_id'),
            ]);

            if ($user){
                DB::commit();
                return Redirect::route('parametros.edit');
            }else{
                DB::rollBack();
                return back()->withErrors('El usuario no pudo ser creado.');
            }
        }else{
//            DB::rollBack();
            return back()->withErrors($validador)->withInput();
        }
    }
    public function destoy_users(Request $request)
    {
        $request->validate([
            'id' => ['required'],
        ]);

        $user = User::find($request->id);

        $user->delete();
    }

    public function edit_users($id)
    {
        $departamento = Departamento::all();
        $roles = Role::all();
        $docente = Docente::all();
        $user = User::with('docente', 'departamento')->find($id);
        return Inertia::render('Views/desarrollo/Users/EditUsers', [
            'user' => $user,
            'role' => $user->hasExactRoles(Role::all()),
            'docente' => $docente,
            'departamento' => $departamento,
            'rol' => $roles,
        ]);
    }
    public function edit_email(Request $request, $id)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::find($id);
        $user->update([
            'email' => $request->email
        ]);

        $user->save();

        return redirect()->route('edit.docentes', ['id' => $user->docente_id]);
    }
    public function update_user(Request $request, $id)
    {
        $rules = [
            'docente_id' => 'required',
            'departamento_id' => 'required',
            'role' => 'required'
        ];
        $validador = Validator::make($request->all(), $rules, [
            'docente_id.required' => 'El docente asociado es requerido.',
            'departamento_id.required' => 'El departamento asociado es requerido.',
            'role.required' => 'El rol es requerido.',
        ]);

        if (!$validador->fails()) {
            DB::beginTransaction();
            $user = User::where('id', $id)->update([
                'docente_id' => $request->docente_id,
                'departamento_id' => $request->departamento_id,
                'role' => $request->role,
            ]);
            if ($user > 0){
                $rol = Role::where('id', $request->role)->first();
                $users = User::find($id);
                if ($rol){
                    $users->syncRoles([]);
                    $users->assignRole($rol->name);
                    if ($users->isDirty('email')) {
                        $users->email_verified_at = null;
                    }
                    DB::commit();
                    return Redirect::route('edit.user', ['id' => $id]);
                }
            }else{
                DB::rollBack();
                return back()->withErrors('El usuario no requirió ser actualizado.');
            }
            //        Docente::where('id', $request->docente_id)->update([//actualiza el user id, es decir, que si cristina se logea
            //            'user_id' => $user->id
            //        ]);
//            $rol = Role::where('id', $request->role)->first();
//            $user->syncRoles([]);
//            $user->assignRole($rol->name);
//
//            if ($user->isDirty('email')) {
//                $user->email_verified_at = null;
//            }
//            $user->save();
        }else{
            return back()->withErrors($validador)->withInput();
        }
    }

    public function update_password(Request $request, $id)
    {
        $request->validate([
            'password' => [Password::defaults(), 'confirmed', 'required'],
        ]);

        $user = User::find($id);
        $user->password = Hash::make($request->password);
        $user->save();
        if (auth()->user()->role == 3 || auth()->user()->role == 4) {
            Auth::guard('web')->logout();
            return redirect('/');
        }
    }
    //en el instalador preguntar que acepta tener permisos!
    public function set_permission($id)
    {
        $user = User::find($id);
        $user->givePermissionTo('edit profile');
    }

    public function revoke_permissions($id)
    {
        $user = User::find($id);
        $user->revokePermissionTo('edit profile');
    }

    public static function admin()
    {
        return User::find(auth()->user()->id);
    }

    public function create_subdireccion(Request $request)
    {
        $request->validate([
            'name' => 'required'
        ]);

        $subdireccion = Subdireccion::create([
            'name' => $request->name
        ]);

        $subdireccion->save();
    }
    public function update_subdireccion($id, Request $request)
    {
        $subdireccion = Subdireccion::find($id);

        $subdireccion->delete();

        $subdireccion->name = $request->name;

        $subdireccion->save();
    }

    public function subir_cvu(Request $request)
    {
        $request->file('file')->storeAs('/CVUdownload/', 'CVU_editable.docx', 'public');
        return redirect()->route('parametros.edit');
    }
    public function subir_img_acta(StoreImageRequest $request)
    {
        $request->validated();
        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();
        $year = date('Y');
        $path = '/Membretado/' . $year;
        if(Storage::disk('public')->exists($path.'/img_acta_calificaciones.'.$extension)){
            Storage::disk('public')->delete($path.'/img_acta_calificaciones.'.$extension);
        }
        $request->file('file')->storeAs($path, 'img_acta_calificaciones.'.$extension, 'public');
        return redirect()->route('parametros.edit');
    }
    public function subir_img_constancia(StoreImageRequest $request)
    {
        $request->validated();
        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();
//        dd($file, $extension);
        $year = date('Y');
        $path = '/Membretado/' . $year;
        if(Storage::disk('public')->exists($path.'/img_constancia.'.$extension)){
            Storage::disk('public')->delete($path.'/img_constancia.'.$extension);
        }
        $request->file('file')->storeAs($path, 'img_constancia.'.$extension, 'public');

        return redirect()->route('parametros.edit');
    }
    public function subir_img_constancia_2(Request $request)
    {
        $request->validate([
            'file' => ['required', 'mimes:jpg,jpeg,png'],
        ]);
        $year = date('Y');
        $path = '/Membretado/' . $year;
        $request->file('file')->storeAs($path, 'logo_constancia_page_2.png', 'public');
        return redirect()->route('parametros.edit');
    }

    public function create_director(Request $request)
    {
        $request->validate([
            'nameDirector' => 'required'
        ]);

        $director = Director::create($request->all());

        $director->save();
    }
    public function update_director($id, Request $request)
    {
        $director = Director::find($id);

        $director->delete();

        $director->nameDirector = $request->nameDirector;

        $director->save();
    }

    public function create_instituto(Request $request)
    {
        $request->validate([
            'nameInstituto'
        ]);
        $instituto = NombreInstituto::create([
            'name' => 'nameInstituto'
        ]);

        $instituto->save();
    }
    public function update_instituto($id, Request $request)
    {
        $instituto = NombreInstituto::find($id);

        $instituto->delete();

        $instituto->name = $request->nameInstituto;

        $instituto->save();
    }

    public function upLogo(StoreImageRequest $request)
    {
        $request->validated();
        $request->file('file')->storeAs('/img/', 'logo.jpg', 'public');
    }
    public function upLogo_tecnm(StoreImageRequest $request)
    {
        $request->validated();
        $request->file('file')->storeAs('/img/', 'logoTecnm.jpg', 'public');
    }
    public function upLogo_educacion(StoreImageRequest $request): void
    {
        $request->validated();
        $request->file('file')->storeAs('/img/', 'educacion.jpg', 'public');
    }
    public function subir_imagen(Request $request): void
    {
        $request->validate([
            'file' => ['required', 'mimes:jpg,jpeg,png'],
        ]);
        $request->file('file')->storeAs('/img/', 'educacion.jpg', 'public');
    }
    public static function if_enable_deteccion()
    {
        $dates = ConfigDates::latest('id')->first();

        if (empty($dates)) {
            return null;
        } else {
            $startDate = Carbon::parse($dates->fecha_inicio);
            $endDate = Carbon::parse($dates->fecha_final);
            $currentDate = Carbon::now('GMT-6');
            $tiemporestante = $currentDate->diff($endDate);
            $fechas = [$currentDate->between($startDate, $endDate), $tiemporestante];
            return $fechas;
        }
    }
}
