<?php
namespace App\Biblioteca;
use PDO;
use DB;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Session;
use Hashids;

use Illuminate\Support\Facades\Redirect;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\WEB\WEBRolOpciones;
use App\Models\WEB\WEBRol;

class Funcion
{

	public function getCreateTablaIdISL($tabla, $prefijo, $ceros = 17)
	{
		$cantidad = strlen($prefijo);
		$id = "";
		$id = DB::table($tabla)
			->select(DB::raw('max(SUBSTRING(id,' . ($cantidad + 1) . ',' . $ceros . ')) as id'))
			->whereRaw("LEFT(Id," . $cantidad . ")='" . $prefijo . "'")
			->get();

		$idsuma = (int) $id[0]->id + 1;
		$idopcioncompleta = str_pad($idsuma, $ceros, "0", STR_PAD_LEFT);
		$idopcioncompleta = $prefijo . $idopcioncompleta;
		return $idopcioncompleta;
	}

	public function getUrl($idopcion, $accion)
	{

		//decodificar variable
		$decidopcion = Hashids::decode($idopcion);

		//ver si viene con letras la cadena codificada
		if (count($decidopcion) == 0) {
			return Redirect::back()->withInput()->with('errorurl', 'Indices de la url con errores');
		}

		//concatenar con ceros
		$idopcioncompleta = str_pad($decidopcion[0], 8, "0", STR_PAD_LEFT);
		//concatenar prefijo

		// hemos hecho eso porque ahora el prefijo va hacer fijo en todas las empresas que 1CIX
		//$prefijo = Local::where('activo', '=', 1)->first();
		//$idopcioncompleta = $prefijo->prefijoLocal.$idopcioncompleta;
		$idopcioncompleta = '1CIX' . $idopcioncompleta;

		// ver si la opcion existe
		$opcion = WEBRolOpciones::where('opcion_id', '=', $idopcioncompleta)
			->where('rol_id', '=', Session::get('usuario')->rol_id)
			->where($accion, '=', 1)
			->first();

		if (!$opcion) {
			return Redirect::back()->withInput()->with('error', 'No tiene autorización para ' . $accion . ' aquí');
		}
		return 'true';

	}


	public function que_rol_que_tiene_usuario($idusuario)
	{

		$nombre_rol = '-';
		$usuario = User::where('usuarioisl_id', '=', $idusuario)->first();

		if ($usuario) {
			$rol = WEBRol::where('id', '=', $usuario->rol_id)->first();
			$nombre_rol = $rol->nombre;
		}

		return $nombre_rol;
	}

	public function prefijomaestra()
	{

		$prefijo = '1CIX';
		return $prefijo;
	}

	public function decodificarmaestra($id)
	{

		//decodificar variable
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
		$prefijo = $this->prefijomaestra();
		$idopcioncompleta = $prefijo . $idopcioncompleta;
		return $idopcioncompleta;

	}

	public function getCreateIdMaestra($tabla)
	{

		$id = "";

		// maximo valor de la tabla referente
		$id = DB::table($tabla)
			->select(DB::raw('max(SUBSTRING(id,5,8)) as id'))
			->get();

		//conversion a string y suma uno para el siguiente id
		$idsuma = (int) $id[0]->id + 1;

		//concatenar con ceros
		$idopcioncompleta = str_pad($idsuma, 8, "0", STR_PAD_LEFT);

		//concatenar prefijo
		$prefijo = $this->prefijomaestra();

		$idopcioncompleta = $prefijo . $idopcioncompleta;

		return $idopcioncompleta;

	}

	public function decodificarOpcion($id)
	{
		//decodificar variable
		$iddeco = Hashids::decode($id);
		//ver si viene con letras la cadena codificada
		if (count($iddeco) == 0) {
			return '';
		}

		$idopcioncompleta = str_pad($iddeco[0], 8, "0", STR_PAD_LEFT);
		$prefijo = $this->prefijomaestra();
		$idopcioncompleta = $prefijo . $idopcioncompleta;

		// return $iddeco[0];
		return $idopcioncompleta;

	}

	public function decodificarId($id)
	{
		$iddeco = Hashids::decode($id);
		//ver si viene con letras la cadena codificada
		if (count($iddeco) == 0) {
			return '';
		}
		$idopcioncompleta = str_pad($iddeco[0], 16, "0", STR_PAD_LEFT);
		$prefijo = $this->prefijomaestra();
		//concatenar prefijo
		$idopcioncompleta = $prefijo . $idopcioncompleta;
		// return $iddeco[0];
		return $idopcioncompleta;

	}

	public function decodificarIdMaestra($id)
	{

		//decodificar variable
		// $idopcioncompleta='';
		$iddeco = Hashids::decode($id);
		//ver si viene con letras la cadena codificada
		if (count($iddeco) == 0) {
			return '';
		}
		//concatenar prefijo
		// apunta ahi en tu cuaderno porque esto solo va a permitir decodifcar  cuando sea el contrato del locl en donde estas del resto no 
		//¿cuando sea el contrato del local?
		$idopcioncompleta = str_pad($iddeco[0], 8, "0", STR_PAD_LEFT);
		$prefijo = $this->prefijomaestra();
		//concatenar prefijo
		$idopcioncompleta = $prefijo . $idopcioncompleta;
		// return $iddeco[0];
		return $idopcioncompleta;

	}


}