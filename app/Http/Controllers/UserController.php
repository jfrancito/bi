<?php

namespace App\Http\Controllers;

use App\Models\Grupoopcion;
use App\Models\Opcion;
use App\Models\Rol;
use App\Models\RolOpcion;
use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\Rule;

use App\Traits\UserTraits;


class UserController extends Controller
{
    use UserTraits;
    public function actionLogin(Request $request)
    {
        if ($request->isMethod('post')) {

            /**** Validaciones laravel ****/
            $validated = $request->validate([
                'name' => 'required',
                'password' => 'required',
            ], [
                'name.required' => 'El campo Usuario es obligatorio',
                'password.required' => 'El campo Clave es obligatorio',
            ]);

            /**********************************************************/

            $usuario = strtoupper($request->input('name'));
            $clave = strtoupper($request->input('password'));
            //dd(Crypt::encrypt($clave));

            // En Laravel 11, first() retorna null si no encuentra resultados
            $tusuario = User::whereRaw('UPPER(name)=?', [$usuario])
                ->where('activo', '=', 1)
                ->first();

            if ($tusuario) {
                // En Laravel 11, Crypt::decrypt puede lanzar exceptions
                try {
                    $clavedesifrada = strtoupper(Crypt::decrypt($tusuario->password));
                } catch (\Exception $e) {
                    return redirect()->back()->withInput()->with('errorbd', 'Error en la clave encriptada');
                }

                if ($clavedesifrada === $clave) {

                    $listamenu = Grupoopcion::join('opciones', 'opciones.grupoopcion_id', '=', 'grupoopciones.id')
                        ->join('rolopciones', 'rolopciones.opcion_id', '=', 'opciones.id')
                        ->where('grupoopciones.activo', '=', 1)
                        ->where('opciones.activo', '=', 1)
                        ->where('rolopciones.rol_id', '=', $tusuario->rol_id)
                        ->where('rolopciones.ver', '=', 1)
                        ->groupBy('grupoopciones.id', 'grupoopciones.nombre', 'grupoopciones.icono', 'grupoopciones.orden')
                        ->select('grupoopciones.id', 'grupoopciones.nombre', 'grupoopciones.icono', 'grupoopciones.orden')
                        ->orderBy('grupoopciones.orden', 'asc')
                        ->get();

                    $listaopciones = RolOpcion::join('opciones', 'opciones.id', '=', 'rolopciones.opcion_id')
                        ->where('rolopciones.rol_id', '=', $tusuario->rol_id)
                        ->where('rolopciones.ver', '=', 1)
                        ->where('opciones.activo', '=', 1)
                        ->orderBy('rolopciones.orden', 'asc')
                        ->pluck('rolopciones.opcion_id')
                        ->toArray();


                    Session::put('usuario', $tusuario);
                    Session::put('listamenu', $listamenu);
                    Session::put('listaopciones', $listaopciones);

                    return Redirect::to('bienvenido');

                } else {
                    return redirect()->back()->withInput()->with('errorbd', 'Usuario o clave incorrecto');
                }
            } else {
                return redirect()->back()->withInput()->with('errorbd', 'Usuario o clave incorrecto');
            }

        } else {
            return view('pages.login');
        }
    }

    public function actionBienvenido(Request $request)
    {
        View::share('titulo', 'Bienvenido Sistema BI');
        return view('pages.bienvenido');
    }
    public function actionCerrarSesion()
    {
        Session::forget('usuario');
        Session::forget('listamenu');
        Session::forget('listaopciones');
        return Redirect::to('/login');

    }

    public function actionListarUsuarios($idopcion)
    {
        /******************* validar url **********************/
        $validarurl = $this->user_url($idopcion, 'ver');
        if ($validarurl != 'true') {
            return $validarurl;
        }
        /******************************************************/
        View::share('titulo', 'Lista de usuarios');
        $listausuarios = User::where('id', '<>', $this->prefijomaestro . '00000001')->orderBy('id', 'asc')->get();

        return View::make(
            'pages/usuario/listausuarios',
            [
                'listausuarios' => $listausuarios,
                'idopcion' => $idopcion,
            ]
        );
    }

    public function actionListarRoles($idopcion)
    {
        /******************* validar url **********************/
        $validarurl = $this->user_url($idopcion, 'ver');
        if ($validarurl != 'true') {
            return $validarurl;
        }
        /******************************************************/
        View::share('titulo', 'Lista de Roles');
        $listaroles = Rol::where('id', '<>', $this->prefijomaestro . '00000001')->orderBy('id', 'asc')->get();


        return View::make(
            'pages/usuario/listaroles',
            [
                'listaroles' => $listaroles,
                'idopcion' => $idopcion,
            ]
        );

    }

    public function actionAgregarRol($idopcion, Request $request)
    {
        /******************* validar url **********************/
        $validarurl = $this->user_url($idopcion, 'anadir');
        if ($validarurl != 'true') {
            return $validarurl;
        }
        /******************************************************/

        // Compartir título con la vista
        View::share('titulo', 'Agregar Rol');

        if ($request->isMethod('post')) {

            /**** Validaciones laravel ****/
            $validated = $request->validate([
                'nombre' => 'unique:rols,nombre',
            ], [
                'nombre.unique' => 'Rol ya registrado',
            ]);

            /******************************/
            $idrol = $this->user_CreateIdMaestra('rols');

            $cabecera = new Rol();
            $cabecera->id = $idrol;
            $cabecera->fecha_crea = $this->fechaactual;
            $cabecera->usuario_crea = session('usuario')->id;
            $cabecera->nombre = $request->input('nombre');
            $cabecera->save();

            $listaopcion = Opcion::orderBy('id', 'asc')->get();
            $count = 1;

            foreach ($listaopcion as $item) {
                $idrolopciones = $this->user_CreateIdMaestra('rolopciones');

                $detalle = new RolOpcion();
                $detalle->id = $idrolopciones;
                $detalle->opcion_id = $item->id;
                $detalle->fecha_crea = $this->fechaactual;
                $detalle->rol_id = $idrol;
                $detalle->orden = $count;
                $detalle->ver = 0;
                $detalle->anadir = 0;
                $detalle->modificar = 0;
                $detalle->eliminar = 0;
                $detalle->todas = 0;
                $detalle->fecha_crea = $this->fechaactual;
                $detalle->usuario_crea = session('usuario')->id;
                $detalle->save();

                $count++;
            }

            return redirect()->to('/gestion-de-roles/' . $idopcion)
                ->with('bienhecho', 'Rol ' . $request->input('nombre') . ' registrado con éxito');
        } else {

            return View::make('pages/usuario/form/rol', [
                'idopcion' => $idopcion,
            ]);
        }
    }

    public function actionModificarRol($idopcion, $idrol, Request $request)
    {

        /******************* validar url **********************/
        $validarurl = $this->user_url($idopcion, 'modificar');
        if ($validarurl != 'true') {
            return $validarurl;
        }
        /******************************************************/
        $idrol = $this->user_decodificarmaestra($idrol);
        View::share('titulo', 'Modificar Rol');
        if ($_POST) {

            /**** Validaciones laravel ****/
            $validated = $request->validate([
                'nombre' => [
                    'required',
                    Rule::unique('rols', 'nombre')->ignore($idrol, 'id')
                ],
            ], [
                'nombre.required' => 'El nombre del rol es obligatorio',
                'nombre.unique' => 'Rol ya registrado',
            ]);
            /******************************/

            $cabecera = Rol::find($idrol);
            $cabecera->nombre = $request['nombre'];
            $cabecera->fecha_mod = $this->fechaactual;
            $cabecera->usuario_mod = Session::get('usuario')->id;
            $cabecera->activo = $request['activo'];
            $cabecera->save();

            return Redirect::to('/gestion-de-roles/' . $idopcion)->with('bienhecho', 'Rol ' . $request['nombre'] . ' modificado con éxito');

        } else {

            $rol = Rol::where('id', $idrol)->first();
            return View::make(
                'pages/usuario/form/rolmodificar',
                [
                    'rol' => $rol,
                    'idopcion' => $idopcion,
                ]
            );

        }
    }


    public function actionAgregarUsuario($idopcion, Request $request)
    {
        /******************* validar url **********************/
        $validarurl = $this->user_url($idopcion, 'anadir');
        if ($validarurl != 'true') {
            return $validarurl;
        }
        /******************************************************/
        View::share('titulo', 'Agregar Usuario');
        if ($_POST) {

            /**** Validaciones laravel ****/
            $this->validate($request, [
                'name' => 'unique:users',
            ], [
                'name.unique' => 'Usuario ya registrado',
            ]);
            /******************************/



            $idusers = $this->user_CreateIdMaestra('users');

            $cabecera = new User;
            $cabecera->id = $idusers;
            $cabecera->nombre = $request['nombre'];
            $cabecera->name = $request['name'];
            $cabecera->password = Crypt::encrypt($request['password']);
            $cabecera->rol_id = $request['rol_id'];
            $cabecera->fecha_crea = $this->fechaactual;
            $cabecera->usuario_crea = Session::get('usuario')->id;
            $cabecera->save();


            return Redirect::to('/gestion-de-usuarios/' . $idopcion)->with('bienhecho', 'Usuario ' . $request['nombre'] . ' registrado con exito');

        } else {

            $rol = DB::table('rols')->where('id', '<>', $this->prefijomaestro . '00000001')->pluck('nombre', 'id')->toArray();
            $comborol = array('' => "Seleccione Rol") + $rol;

            return View::make(
                'pages/usuario/form/agregarusuario',
                [
                    'comborol' => $comborol,
                    'idopcion' => $idopcion,
                ]
            );
        }
    }

    public function actionModificarUsuario($idopcion, $idusuario, Request $request)
    {

        /******************* validar url **********************/
        $validarurl = $this->user_url($idopcion, 'modificar');
        if ($validarurl != 'true') {
            return $validarurl;
        }
        /******************************************************/
        $idusuario = $this->user_decodificarmaestra($idusuario);

        View::share('titulo', 'Modificar Usuario');
        if ($_POST) {

            $cabecera = User::find($idusuario);
            $cabecera->nombre = $request['nombre'];
            $cabecera->name = $request['name'];
            $cabecera->fecha_mod = $this->fechaactual;
            $cabecera->usuario_mod = Session::get('usuario')->id;
            $cabecera->password = Crypt::encrypt($request['password']);
            $cabecera->activo = $request['activo'];
            $cabecera->rol_id = $request['rol_id'];
            $cabecera->save();

            return Redirect::to('/gestion-de-usuarios/' . $idopcion)->with('bienhecho', 'Usuario ' . $request['nombre'] . ' modificado con exito');

        } else {



            $usuario = User::where('id', $idusuario)->first();
            $rol = DB::table('rols')->where('id', '!=', $this->prefijomaestro . '00000001')->pluck('nombre', 'id')->toArray();

            $comborol = array($usuario->rol_id => $usuario->rol->nombre) + $rol;
            $funcion = $this;

            return View::make(
                'pages/usuario/form/modificarusuario',
                [
                    'usuario' => $usuario,
                    'comborol' => $comborol,
                    'idopcion' => $idopcion,
                    'funcion' => $funcion,
                ]
            );
        }
    }
    public function actionListarPermisos($idopcion)
    {

        /******************* validar url **********************/
        $validarurl = $this->user_url($idopcion, 'ver');
        if ($validarurl != 'true') {
            return $validarurl;
        }
        /******************************************************/
        View::share('titulo', 'Lista Permisos');
        $listaroles = Rol::where('id', '<>', $this->prefijomaestro . '00000001')->orderBy('id', 'asc')->get();

        return View::make(
            'pages/usuario/listapermisos',
            [
                'listaroles' => $listaroles,
                'idopcion' => $idopcion,
            ]
        );
    }

    public function actionAjaxListarOpciones(Request $request)
    {
        try {

            $idrol = $request->input('idrol');
            $idrol = $this->user_decodificarmaestra($idrol);

            $listaopciones = RolOpcion::with('opcion') // Cargar relación si existe
                ->where('rol_id', $idrol)
                ->get();

            // Para AJAX, es mejor retornar JSON o una vista parcial
            return view('pages/usuario/ajax/listaopciones', [
                'listaopciones' => $listaopciones
            ]);

        } catch (\Exception $e) {
            // Log del error
            logger()->error('Error en actionAjaxListarOpciones: ' . $e->getMessage());

            // Retornar error en formato JSON para AJAX
            return response()->json([
                'error' => true,
                'message' => 'Error al cargar las opciones'
            ], 500);
        }
    }

    public function actionAjaxActivarPermisos(Request $request)
    {
        try {
            $idrolopcion = $request->input('idrolopcion');
            $idrolopcion = $this->user_decodificarmaestra($idrolopcion);

            $cabecera = RolOpcion::find($idrolopcion);

            if (!$cabecera) {
                return response()->json([
                    'error' => true,
                    'message' => 'Permiso no encontrado (ID: ' . $idrolopcion . ')'
                ], 404);
            }

            $cabecera->ver = $request['ver'];
            $cabecera->anadir = $request['anadir'];
            $cabecera->fecha_mod = $this->fechaactual;
            $cabecera->usuario_mod = Session::get('usuario')->id;
            $cabecera->modificar = $request['modificar'];
            $cabecera->todas = $request['todas'];
            $cabecera->save();

            return response()->json([
                'success' => true,
                'message' => 'Permisos actualizados correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }



}