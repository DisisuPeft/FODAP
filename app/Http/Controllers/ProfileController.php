<?php

namespace App\Http\Controllers;

use App\Http\Requests\DocenteRequest;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Carrera;
use App\Models\Departamento;
use App\Models\Docente;
use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        $carrera = Carrera::select('nameCarrera', 'id')->get();
        $departamento = Departamento::select('nameDepartamento', 'id')->get();
        $tipoPlaza = DB::table('tipo_plaza')->select('id', 'nombre')->get();
        $puesto = DB::table('puesto')->select('id', 'nombre')->get();
        $posgrado = DB::table('posgrado')->select('id', 'nombre')->get();

        $docente = Docente::with('carrera', 'plaza', 'puesto', 'departamento', 'posgrado')->find(auth()->user()->docente_id);
        $user = User::find(auth()->user()->id);



        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
            'docente' => $docente,
            'carrera' => $carrera->except(['13']),
            'departamento' => $departamento,
            'tipo_plaza' => $tipoPlaza,
            'puesto' => $puesto,
            'posgrado' => $posgrado,
            'permiso_to_edit' => $user->hasPermissionTo('edit profile'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit');
    }

    public function update_email(Request $request, $id, $from): RedirectResponse
    {
        try {
            // Validación
            $validator = Validator::make($request->all(), [
                'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $id],
            ], [
                'email.email' => 'No es válido el correo institucional',
                'email.unique' => 'El correo ya se encuentra registrado',
                'email.required' => 'El correo es requerido',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            DB::beginTransaction();
            
            $user = User::findOrFail($id);
            $user->email = $request->input('email');
            $user->save();
            
            DB::commit();

            // Redirección según el origen
            if ($from == "docentes") {
                return redirect()->route('edit.docentes', ['id' => $user->docente_id])
                            ->with('success', 'Correo actualizado correctamente');
            } else {
                return redirect()->route('edit.user', ['id' => $id])
                            ->with('success', 'Correo actualizado correctamente');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                            ->withErrors('Error al actualizar el correo: ' . $e->getMessage())
                            ->withInput();
        }
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    public function DocenteProfileCreate(DocenteRequest $request)
    {

//        $request->validated();
        $docente = new Docente();
        $existe = $docente->docente_existe($request);
//        DB::beginTransaction();
        if ($existe[0]){
            $user = User::where('id', $request->id)->update([
                'docente_id' => $existe[1]->id,
            ]);

            if ($user > 0){

                $docente = Docente::where('id', $existe[1]->id)->update($request->validated() + [
                        'nombre_completo' => $request->nombre . " " . $request->apellidoPat . " " . $request->apellidoMat,
                        'user_id' =>  $request->id,
                ]);

//                $update = $d->update([
//                    'user_id' => $request->id
//                ]);
                if ($docente > 0){
                    return redirect()->route('profile.edit');
                }else{
                    return back()->withErrors('No se actualizo el id que vincula al docente con su usuario.');
                }
            }else{
                return back()->withErrors('No se actualizo al usuario.');
            }
        }else {
            $d = $docente->create_instance_docente($request, null);

            $update = $d->update([
                'user_id' => $request->id
            ]);

            if ($update){
                $user = User::where('id', $request->id)->update([
                    'docente_id' => $d->id,
                ]);
                if ($user > 0){
                    return redirect()->route('profile.edit');
                }else{
                    return back()->withErrors('No se actualizo al usuario.');
                }
            }else{
                return back()->withErrors('No se actualizo el id que vincula al docente con su usuario.');
            }
        }
    }

    public function update_docente(DocenteRequest $request, $id)
    {
        $docente = new Docente();
        $docente->updated_instance_docente($request, $id, null);
        return Redirect::route('profile.edit');
    }
}
