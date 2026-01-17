<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;
use App\Models\Rolopcion; // Namespace actualizado
use App\Facades\Hashids; // Namespace correcto para Hashids

trait UserTraits
{
    public function user_decodificarmaestra($id)
    {
        if (empty($id)) {
            return '';
        }
        //decodificar variable
        // Inicializar Hashids
        //$hashids = new Hashids(env('HASHIDS_SALT', 'default-salt'), 10);


        $iddeco = Hashids::decode($id);

        //ver si viene con letras la cadena codificada
        if (count($iddeco) == 0) {
            return '';
        }
        //concatenar con ceros
        $idopcioncompleta = str_pad($iddeco[0], 8, "0", STR_PAD_LEFT);
        //concatenar prefijo

        //$prefijo = Local::where('activo', '=', 1)->first();

        // apunta ahi en tu cuaderno porque esto solo va a permitir decodifcar  cuando sea el contrato del locl en donde estas del resto no
        //¿cuando sea el contrato del local?
        $prefijo = '1CIX';
        $idopcioncompleta = $prefijo . $idopcioncompleta;
        return $idopcioncompleta;

    }



    public function user_CreateIdMaestra($tabla)
    {

        $id = "";
        // maximo valor de la tabla referente
        $id = DB::table($tabla)
            ->select(DB::raw('COALESCE(MAX(SUBSTRING(id FROM 5 FOR 8))::integer, 0) as id'))
            ->first();
        //conversion a string y suma uno para el siguiente id
        $idsuma = (int) $id->id + 1;
        //concatenar con ceros
        $idopcioncompleta = str_pad($idsuma, 8, "0", STR_PAD_LEFT);
        //concatenar prefijo
        $prefijo = '1CIX';
        $idopcioncompleta = $prefijo . $idopcioncompleta;
        return $idopcioncompleta;

    }


    public function user_url($idopcion, $accion)
    {
        // Inicializar Hashids
        //$hashids = new Hashids(env('HASHIDS_SALT', 'default-salt'), 10);

        // Decodificar variable
        $decidopcion = Hashids::decode($idopcion);
        // Ver si viene con letras la cadena codificada
        if (count($decidopcion) == 0) {
            return Redirect::back()->withInput()->with('errorurl', 'Índices de la url con errores');
        }
        // Concatenar con ceros
        $idopcioncompleta = str_pad($decidopcion[0], 8, "0", STR_PAD_LEFT);

        // Concatenar prefijo
        $idopcioncompleta = '1CIX' . $idopcioncompleta;

        // Ver si la opción existe
        $opcion = Rolopcion::where('opcion_id', '=', $idopcioncompleta)
            ->where('rol_id', '=', session('usuario')->rol_id)
            ->where($accion, '=', 1)
            ->first();

        // En Laravel 11, first() retorna null si no encuentra resultados
        if (!$opcion) {
            return Redirect::back()->withInput()->with('errorurl', 'No tiene autorización para ' . $accion . ' aquí');
        }

        return 'true';
    }
}