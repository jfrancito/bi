<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Crypt;

use App\Models\WEBCuentaContable;
use App\Models\ALMProducto;
use App\Models\WEBRol;
use App\Models\PER\PERPersonaDocumento;
use App\Models\SPC\SPCImagenesDocumento;
use App\Models\Whatsapp;
use App\Models\NumerosWhatsapp;
use App\Models\LogSession;
use App\Models\WEB\WEBPeriodoInventarioVehiculos;
use App\Models\WEB\WEBInventarioVehiculos;
use App\Models\WEB\WEBSedeWeb;
use App\Models\WEB\WEBPeriodoWeb;
use App\Models\WEB\WEBArchivosBuk;

use App\Models\WEBTrabajadorIsl;
use App\Models\WEB\WEBIlog;
use App\Models\RespaldoDocSunat;
use App\User;

use App\Models\OPE\OPEViaje;
use App\Models\STD\STDEstado;
use App\Biblioteca\BotTest;
use App\Models\NEU\NEUCategoriaInspeccionFoto;
use Datetime;
use stdClass;
use View;
use Illuminate\Support\Facades\Session;
use Hashids;
Use Nexmo;
use Keygen;

use Storage;
use File;
use ZipArchive;
use DateInterval;
use DatePeriod;
use Illuminate\Queue\Console\RetryCommand;

use PDF;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use TPDF;
use SplFileInfo;

trait GeneralesTraits
{
		private function sunatlista() {

		$ruc = '20479729141';
		$usuario = 'SYST1NDU';
		$password='1ndu4m3r1c@';
		$ffin = date('Y-m-d');
		// 4 días antes de hoy
		$finicio = date('Y-m-d', strtotime('-4 days'));


		$bot = new BotTest();
		$bot->setUp($ruc, $usuario, $password);

		$start = new DateTime($finicio);
		$end   = new DateTime($ffin);
		$end->modify('+1 day'); // Para incluir el último día

		$interval = new DateInterval('P1D'); // Intervalo de 1 día
		$period   = new DatePeriod($start, $interval, $end);

		foreach ($period as $date) {
			$fechaActual = $date->format('d/m/Y');
			
			// Obtener documentos de la fecha actual
			$listadocumento = $bot->testGetList($fechaActual, $fechaActual);

			foreach ($listadocumento as $item) {
				$respaldo = RespaldoDocSunat::where('TipoDocumento', '=', '01')
					->where('Serie', '=', $item->nroSerie)
					->where('Numero', '=', $item->nroFactura)
					->where('FechaEmision', '=', $item->fechaEmisionDesc)
					->first();

				if (!$respaldo) {
					$dcontrol = new RespaldoDocSunat;
					$dcontrol->TipoDocumento     = '01';
					$dcontrol->Serie             = $item->nroSerie;
					$dcontrol->Numero            = $item->nroFactura;
					$dcontrol->FechaEmision      = $item->fechaEmisionDesc;
					$dcontrol->Proveedor         = $item->nroRucEmisorDesc;
					$dcontrol->Total             = $item->importeTotalDesc;
					$dcontrol->Indpdf            = 0;
					$dcontrol->Indxml            = 0;
					$dcontrol->UsuarioCreacion   = 'SISTEMAS';
					$dcontrol->save();
				}
			}
		}


	}

	private function sunatarchivos() {


		$hoy = date('d/m/Y');
		// 4 días antes de hoy, en formato d/m/Y
		$finicio = date('d/m/Y', strtotime('-4 days'));

		$documentos     =   RespaldoDocSunat::where(function ($query) {
							$query->where('indpdf', 0)
								  ->orWhere('indxml', 0);
							})
							->where('FechaEmision','>=',$finicio)
							->where('FechaEmision','<=',$hoy )
							->orderBy('FechaEmision', 'asc')
							->get();


		$fetoken        =   DB::table('FE_TOKEN')->first();

		foreach($documentos as $index=>$item){

			$indpdf = $item->indpdf;
			$indxml = $item->indxml;
			$fechaemision = $item->FechaEmision;


			$texto  = $item->Proveedor;
			list($ruc, $proveedor) = explode(' - ', $texto);
			$ruc    = trim($ruc);
			$serie  = trim($item->Serie);
			$correlativo    = trim($item->Numero);
			$td     = '01';

			if($indxml==0){
				$urlxml                     =   'https://api-cpe.sunat.gob.pe/v1/contribuyente/consultacpe/comprobantes/'.$ruc.'-'.$td.'-'.$serie.'-'.$correlativo.'-2/02';
				$respuetaxml                =   $this->buscar_archivo_sunat($urlxml,$fetoken);

				if($respuetaxml['cod_error'] == '0'){

					$response_array     =   json_decode($respuetaxml['archivo'], true);
					$fileName           =   $response_array['nomArchivo'];
					$base64File         =   $response_array['valArchivo'];
					$anio               =   substr($fechaemision, 0, 4);  // Extrae desde el índice 0, 4 caracteres (año)
					$mes                =   substr($fechaemision, 5, 2);   // Extrae desde el índice 5, 2 caracteres (me)
					//$rutafile             =   "//10.1.0.20/bkcontafls$/CPEClientes/FACTURASUNAT/".$anio;
					$rutafile        =      '\\\\10.1.0.20\\bkcontafls$\\CPEClientes\\FACTURASUNAT\\'.$anio;
					$valor              =    $this->versicarpetanoexiste($rutafile);
					$rutafile           =   "//10.1.0.20/bkcontafls$/CPEClientes/FACTURASUNAT/".$anio."/".$mes;
					$valor              =    $this->versicarpetanoexiste($rutafile);
					$destino            =   "//10.1.0.20/bkcontafls$/CPEClientes/FACTURASUNAT/".$anio."/".$mes."/";
					// Asegúrate que la carpeta destino exista, si no, la creas
					if (!file_exists($destino)) {
						mkdir($destino, 0777, true); // true para crear directorios recursivamente
					}
					// Decodificamos el contenido base64
					$fileData = base64_decode($base64File);
					// Guardamos el archivo
					$filePath = $destino . $fileName;
					file_put_contents($filePath, $fileData);
					//print_r("xml");

					DB::table('RespaldoDocSunat')
						->where('Proveedor', $texto)
						->where('Serie', $serie)
						->where('Numero', $correlativo)
						->where('TipoDocumento', '01')
						->update([
							'rutaxml' => $filePath,
							'indxml' => 1
						]);



				}

			}
			if($indpdf==0){

				$urlxml                     =   'https://api-cpe.sunat.gob.pe/v1/contribuyente/consultacpe/comprobantes/'.$ruc.'-'.$td.'-'.$serie.'-'.$correlativo.'-2/01';
				$respuetapdf                =   $this->buscar_archivo_sunat($urlxml,$fetoken);

				if($respuetapdf['cod_error'] == '0'){

					$response_array     =   json_decode($respuetapdf['archivo'], true);
					$fileName           =   $response_array['nomArchivo'];
					$base64File         =   $response_array['valArchivo'];
					$anio               =   substr($fechaemision, 0, 4);  // Extrae desde el índice 0, 4 caracteres (año)
					$mes                =   substr($fechaemision, 5, 2);   // Extrae desde el índice 5, 2 caracteres (me)
					//$rutafile             =   "//10.1.0.20/bkcontafls$/CPEClientes/FACTURASUNAT/".$anio;
					$rutafile        =      '\\\\10.1.0.20\\bkcontafls$\\CPEClientes\\FACTURASUNAT\\'.$anio;
					$valor              =    $this->versicarpetanoexiste($rutafile);
					$rutafile           =   "//10.1.0.20/bkcontafls$/CPEClientes/FACTURASUNAT/".$anio."/".$mes;
					$valor              =    $this->versicarpetanoexiste($rutafile);
					$destino            =   "//10.1.0.20/bkcontafls$/CPEClientes/FACTURASUNAT/".$anio."/".$mes."/";
					// Asegúrate que la carpeta destino exista, si no, la creas
					if (!file_exists($destino)) {
						mkdir($destino, 0777, true); // true para crear directorios recursivamente
					}
					// Decodificamos el contenido base64
					$fileData = base64_decode($base64File);
					// Guardamos el archivo
					$filePath = $destino . $fileName;
					file_put_contents($filePath, $fileData);
					//print_r("pdf");
					DB::table('RespaldoDocSunat')
						->where('Proveedor', $texto)
						->where('Serie', $serie)
						->where('Numero', $correlativo)
						->where('TipoDocumento', '01')
						->update([
							'rutapdf' => $filePath,
							'indpdf' => 1
						]);

				}

			}


		}


	}

	private function versicarpetanoexiste($ruta) {
		$valor = false;
		if (!file_exists($ruta)) {
			mkdir($ruta, 0777, true);
			$valor=true;
		}
		return $valor;
	}
	
	private function buscar_archivo_sunat($urlxml,$fetoken) {

		$array_nombre_archivo = array();
		$curl = curl_init();
		// curl_setopt_array($curl, array(
		//   CURLOPT_URL => $urlxml,
		//   CURLOPT_RETURNTRANSFER => true,
		//   CURLOPT_ENCODING => '',
		//   CURLOPT_MAXREDIRS => 10,
		//   CURLOPT_TIMEOUT => 0,
		//   CURLOPT_FOLLOWLOCATION => true,
		//   CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		//   CURLOPT_CUSTOMREQUEST => 'GET',
		//   CURLOPT_HTTPHEADER => array(
		//     'Authorization: Bearer '.$fetoken->TOKEN_MASIVO
		//   ),
		// ));
		curl_setopt_array($curl, array(
			CURLOPT_URL => $urlxml,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 15, // máximo 15 segundos para la respuesta
			CURLOPT_CONNECTTIMEOUT => 10, // máximo 10 segundos para conectar
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'GET',
			CURLOPT_HTTPHEADER => array(
				'Accept: application/json, text/plain, */*',
				'Accept-Encoding: gzip, deflate, br, zstd',
				'Accept-Language: es-ES,es;q=0.9',
				'Origin: https://e-factura.sunat.gob.pe',
				'Referer: https://e-factura.sunat.gob.pe/',
				'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) '
					. 'AppleWebKit/537.36 (KHTML, like Gecko) '
					. 'Chrome/141.0.0.0 Safari/537.36',
				'Authorization: Bearer '.$fetoken->TOKEN_MASIVO
			),
		));

		
		$response = curl_exec($curl);
		curl_close($curl);
		$response_array = json_decode($response, true);
		if (!isset($response_array['nomArchivo'])) {
			$array_nombre_archivo = [
				'cod_error' => 1,
				'nombre_archivo' => '',
				'mensaje' => 'Hubo un problema de sunat buscar nuevamente'
			];
		}else{
			$fileName = $response_array['nomArchivo'];
			$base64File = $response_array['valArchivo'];
			$array_nombre_archivo = [
				'cod_error' => 0,
				'nombre_archivo' => $response_array['nomArchivo'],
				'archivo' => $response,
				'mensaje' => 'encontrado con exito'
			];
		}

		return  $array_nombre_archivo;

	}

	private function gn_getDatosModal($titulo,$mensaje,$tipo=0){
		$aClsModal = [
				'0'=>'colored-header-primary',
				'1'=>'colored-header-success',
				'2'=>'colored-header-warning',
				'3'=>'colored-header-danger',
				'4'=>'colored-header-dark'
			];
	}

	private function ge_url()
	{
		$url    =   'http://localhost:8080/islmovil/public/';
		return $url;
	}

	private function gn_generacion_combo_array($titulo, $todo , $array)
	{
		if($todo=='TODO'){
			$combo_anio_pc          =   array('' => $titulo , $todo => $todo) + $array;
		}else{
			$combo_anio_pc          =   array('' => $titulo) + $array;
		}
		return $combo_anio_pc;
	}

	public function ge_existe_imagen($dni)
	{
		$foto_perfil        =       'foto_default1.jpg';

		$mi_imagen = public_path().'/img/fotografias/'.$dni.'.jpg';

		if (file_exists($mi_imagen)) {
			$foto_perfil        =   $dni.'.jpg';
		} 


		return $foto_perfil;

	}

	private function gn_generacion_combo($tabla,$atributo1,$atributo2,$titulo,$todo) {
		
		$array                      =   DB::table($tabla)
										->where('activo','=',1)
										->pluck($atributo2,$atributo1)
										->toArray();

		if($todo=='TODO'){
			$combo                  =   array('' => $titulo , $todo => $todo) + $array;
		}else{
			$combo                  =   array('' => $titulo) + $array;
		}

		return  $combo;                             
	}

	public function ge_obtener_fecha_letra($fecha){
		$dia= $this->conocerDiaSemanaFecha($fecha);
		$num = date("j", strtotime($fecha));
		$anno = date("Y", strtotime($fecha));
		$mes = array('enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre');
		$mes = $mes[(date('m', strtotime($fecha))*1)-1];
		return $dia.', '.$num.' de '.$mes.' del '.$anno;
	}

	public function conocerDiaSemanaFecha($fecha) {
		$dias = array('Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado');
		$dia = $dias[date('w', strtotime($fecha))];
		return $dia;
	}
	
	public function ge_crearCarpetaSiNoExiste($ruta){
		$valor = false;
		if (!file_exists($ruta)) {
			mkdir($ruta, 0777, true);
			$valor=true;
		}
		return $valor;
	}


	public function ge_getNuevoId($tabla){
		$idnuevo    =   0;
		$consulta   =   DB::table($tabla)->select(DB::raw('max(id) as id'))->get();
		$idnuevo    =   (int)$consulta[0]->id + 1;
		return $idnuevo;
	}
	
	
	public function ge_getMensajeError($error,$sw=true)
	{
		$mensaje = ($sw==true)?'Ocurrio un error Inesperado':'';
		if($this->ge_isUsuarioAdmin()){
			if(isset($error)){
				$mensaje=$mensaje.': '.$error;
			}
		}
		return $mensaje;
	}
	private function gn_generacion_combo_categoria($txt_grupo,$titulo,$todo) {
		
		$array                      =   DB::table('CMP.CATEGORIA')
										->where('COD_ESTADO','=',1)
										->where('TXT_GRUPO','=',$txt_grupo)
										->pluck('NOM_CATEGORIA','COD_CATEGORIA')
										->toArray();

		if($todo=='TODO'){
			$combo                  =   array('' => $titulo , $todo => $todo) + $array;
		}else{
			$combo                  =   array('' => $titulo) + $array;
		}

		return  $combo;                             
	}

		
	public function ge_isUsuarioAdmin()
	{
		$valor=false;
		if((Session::get('usuario')->id=='1CIX00000001') || (Session::get('usuario')->id=='1CIX00000021') || (Session::get('usuario')->id=='1CIX00000022')){
			$valor=true;
		}
		return $valor;
	}

	public function mostrarValor($dato)
	{
		if($this->ge_isUsuarioAdmin()){
			dd($dato);
		}
	}

	public function nombremes($mes){
		setlocale(LC_TIME, 'spanish');  
		$nombre=strftime("%B",mktime(0, 0, 0, $mes, 1, 2000)); 
		return $nombre;
	}

	public function gn_combo_tipo_cliente()
	{
		$combo      =   array('' => 'Seleccione tipo de cliente' , '0' => 'Tercero', '1' => 'Relacionada');
		return $combo;
	}

	public function rp_sexo_paciente($sexo_letra)
	{
		$sexo = 'Femenino';
		if($sexo_letra == 'M'){
			$sexo = 'Maculino';
		}
		return $sexo;
	}   

	public function getDatosTablas($nombretabla,$campo,$valor)
	{    
			$tabla  =   null;
			$tabla  =   DB::table($nombretabla)->where($campo,'=',$valor)->first();
			$rpta   =   !empty($tabla)?$tabla->id : 0;
			return $rpta;   
	}

	private function gn_generacion_combo_unidad()
	{
		$combo      =   array('' => 'Seleccione Unidad' , 
								'0' => 'AM', 
								'1' => 'TB',
								'2' => 'FR'
								);
		return  $combo; 
	}

	
	public function is_connected($url='www.google.com',$port=80)
	{
		$connected = @fsockopen($url, $port); 
		//website, port  (try 80 or 443)
		if ($connected){
			$is_conn = true; //action when connected
			fclose($connected);
		}else{
			$is_conn = false; //action in connection failure
		}
		return $is_conn;
	}

	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	public function ge_getComboDepartamentos($iddepartamento=NULL){
		$datos  =   [];
		$cadena =   [''=>'Ubicacion'];
		if(!is_null($iddepartamento)){
			$datos  =   Departamento::where('id','=',$iddepartamento)->pluck('descripcion','id')->toArray() + $cadena + Departamento::where('id','<>',$iddepartamento)->pluck('descripcion','id')->toArray();   
		}
		else{
			$datos  =   $cadena + Departamento::pluck('descripcion','id')->toArray();   
		}
		return $datos;
	}

	public function ge_getComboProvincias($idprovincia=NULL){
		$datos  =   [];
		$cadena =   [''=>'Ubicacion'];
		if(!is_null($idprovincia)){
			$registro   =   Provincia::find($idprovincia);
			$datos      =   Provincia::where('id','=',$idprovincia)->pluck('descripcion','id')->toArray() + $cadena + Provincia::where('departamento_id','=',$registro->departamento_id)->where('id','<>',$idprovincia)->pluck('descripcion','id')->toArray();  
		}
		else{
			$datos  =   $cadena + $datos;   
		}
		return $datos;
	}

	public function ge_getComboProvinciasDepartamento($iddepartamento=NULL){
		$datos  =   [];
		$cadena =   [''=>'Ubicacion'];
		if(!is_null($iddepartamento)){
			$datos      =   $cadena + Provincia::where('departamento_id','=',$iddepartamento)->pluck('descripcion','id')->toArray();    
		}
		else{
			$datos  =   $cadena + $datos;   
		}
		return $datos;
	}

	public function ge_getComboDistritos($iddistrito=NULL){
		$datos  =   [];
		$cadena =   [''=>'Ubicacion'];
		if(!is_null($iddistrito)){
			$registro   =   Distrito::find($iddistrito);
			$datos      =   Distrito::where('id','=',$iddistrito)->pluck('descripcion','id')->toArray() + $cadena + Distrito::where('provincia_id','=',$registro->provincia_id)->where('id','<>',$iddistrito)->pluck('descripcion','id')->toArray();    
		}
		else{
			$datos  =   $cadena + $datos;   
		}
		return $datos;
	}

	public function ge_getCodigoTabla($tabla){
		// $consulta = DB::table($tabla)->selectRaw("ISNULL(max(CONVERT(float,codigo)),0) as codigo")->where('activo','=',1)->first();
		$consulta = DB::table($tabla)->select(DB::raw('max(codigo) as codigo'))->first();
		$numero = !empty($consulta)? (float) $consulta->codigo + 1: 1;
		$codigo = str_pad($numero, 12, "0", STR_PAD_LEFT);
		return $codigo;
	}

	public function isFechaIgual($fechainicio,$fechafin){
		$rpta= false;
		if(strtotime($fechainicio)==strtotime($fechafin)){
			$rpta   =true;
		}
		return $rpta;
	}

	public function isFechaMayor($fechainicio,$fechafin){
		$rpta =false;
		if(!($this->isFechaIgual($fechainicio,$fechafin))){
			if(strtotime($fechainicio)>strtotime($fechafin)){
				$rpta =true;
			}
		}
		return $rpta;
	}
   
	public function isFechaMenor($fechainicio,$fechafin){
		$rpta =false;
		if(!($this->isFechaIgual($fechainicio,$fechafin))){
			if(strtotime($fechainicio)<strtotime($fechafin)){
				$rpta =true;
			}
		}
		return $rpta;
	}
   
	public function isFechaMenorIgual($fechainicio,$fechafin){
		$rpta =false;
		if(strtotime($fechainicio)<=strtotime($fechafin)){
			$rpta =true;
		}
		return $rpta;
	}

	public function isFechaMayorIgual($fechainicio,$fechafin){
		$rpta =false;
		if(strtotime($fechainicio)>=strtotime($fechafin)){
			$rpta =true;
		}
		return $rpta;
	}



	public function ge_getClassColorEstado($estado_id){
		$clase = 'general';
		switch($estado_id){
			case 1: $clase='general';break;
			case 2: $clase='primary';break;
			case 5: $clase='primary';break;
			case 6: $clase='success';break;
			case 3: $clase='danger';break;
			// case 1: $clase='general';break;
		}
		return $clase;
	}





	public function ge_sumaPeriodoFechas($fecha,$suma)
	{
			$nuevafecha = strtotime ( $suma , strtotime ( $fecha ) ) ;
			$nuevafecha = date ( 'Y-m-j' , $nuevafecha );
			return $nuevafecha;
	}


	public function ge_sumaPeriodoFechasTT($fecha,$suma)
	{
			$nuevafecha = strtotime ( $suma , strtotime ( $fecha ) ) ;
			$nuevafecha = date ( 'Y-m-j H:i:s' , $nuevafecha );
			return $nuevafecha;
	}

	public function ge_getUltimosIdBeneficiarios()
	{
		$ids = [];
		$ids = Beneficiario::select('beneficiarios.*')
							->join(
								DB::raw('(SELECT dni, MAX(fechacrea) AS ultimoreg FROM beneficiarios GROUP BY dni) ultimas_fichas'),
								function ($join) {
									$join->on('beneficiarios.dni', '=', 'ultimas_fichas.dni')
										->on('beneficiarios.fechacrea', '=', 'ultimas_fichas.ultimoreg');
								}
							)
							->pluck('id')->toArray();
		return $ids;
	}

	public  function ge_calcularEdad($fechanacimiento,$fechareg=NULL)
	{

		$horas  = date('H:i:s');
		$ffin   = date('Y-m-d H:i:s',strtotime($fechanacimiento.' '.$horas));
		if(is_null($fechareg)){
			$hoy = $this->fechaactual;
			$hoy = new Datetime($this->fechaactual);
		}
		else{
			$hoy    = date('Y-m-d H:i:s',strtotime($fechareg.' '.$horas));
			$hoy    = new Datetime($hoy);
		}

		$fechainicio  = new Datetime($ffin);
		$diferencia = $fechainicio->diff($hoy);
		$anios  = (int)$diferencia->y;
		return $anios;

	}
	
	public function getCreateIdISL($tabla,$prefijo,$ceros=17) {
		$cantidad = strlen($prefijo);
		$id="";
		$id = DB::table($tabla)
				->select(DB::raw('max(SUBSTRING(id,'.($cantidad+1).','.$ceros.')) as id'))
				->whereRaw("LEFT(Id,".$cantidad.")='".$prefijo."'")
				->get();
		// dd($id);
		$idsuma = (int)$id[0]->id + 1;
		$idopcioncompleta = str_pad($idsuma, $ceros, "0", STR_PAD_LEFT);
		$idopcioncompleta = $prefijo.$idopcioncompleta;
		// dd($idopcioncompleta);
		return $idopcioncompleta;   
	}

	public function getCreateIdISL_enMemoria($prefijo,$idsuma,$ceros=17) {      
		$idopcioncompleta = str_pad($idsuma, $ceros, "0", STR_PAD_LEFT);
		$idopcioncompleta = $prefijo.$idopcioncompleta;
		
		return $idopcioncompleta;   
	}
	
	public function codificarIdISL($idregistro,$ceros=17) {
		$id="";
		$cadceros = strval('-'.$ceros);
		$id = substr($idregistro,$cadceros);
		return Hashids::encode($id);
	}
	

	public function decodificarIdISL($id,$prefijo,$ceros=17) {

		$iddeco = Hashids::decode($id);
		//ver si viene con letras la cadena codificada
		if(count($iddeco)==0){ 
			return ''; 
		}
		$idopcioncompleta = str_pad($iddeco[0], $ceros, "0", STR_PAD_LEFT); 
		$idopcioncompleta = $prefijo.$idopcioncompleta;
		return $idopcioncompleta;
	}
	

	public function gn_GuardarEdicionInventario($tabla,$detalles){
		$rpta = false;
		$mensaje = 'TODO OK';
		try {
			// $datos=[];
			$usuariomod = Session::get('usuario')->id;
			DB::beginTransaction();
			foreach ($detalles as $index => $concepto) 
			{
				$dfila =    explode('-', $index);
				if(count($dfila)>1 && strlen($dfila[0])==8 && !is_null($concepto)){
					DB::table($tabla)
						->where('Id','=',$dfila[1])
						->update(
							[
								$this->eqdetconcp[$dfila[0]] => $concepto,
								'UsuarioModificacion'   =>  $usuariomod,
								'FechaModificacion'     =>  $this->fechaactual,
							]
						);
				}
			}
			$rpta=true;
			// dd($datos);
			DB::commit();
		} catch (Exception $e) {
			$rpta=false;
			$mensaje = $e;
		}
		return compact('rpta','mensaje');
	}

	public function getComboTipoConceptosInventario($registro_id,$tabla,$detalle)
	{
		// code...
		$datos = [];
		$datos = [''=>'SELECCIONE OPCION']+ 
						DB::table($tabla.' as T')
						->join($detalle. ' as D','T.Id','=','D.IdInventario')
						->join('WEB.TipoConceptoInventarios as TC','TC.Id','=','D.IdTipoConceptoInventario')
						->where('T.Id','=',$registro_id)
						->where('D.Activo','=',1)
						->selectRaw("DISTINCT TC.Id,TC.Descripcion")
						->pluck('TC.Descripcion','TC.Id')
						->toArray();
		return $datos;
	}

	
	public function getComboConceptosInventario($idregistro,$prefijo,$tipoconcepto,$tabla,$detalle)
	{
		// code...
		$registro_id = $this->decodificarIdISL($idregistro,$prefijo);
		$datos = [];
		$datos = [''=>'SELECCIONE CONCEPTO']+ 
						DB::table($tabla.' as T')
						->join($detalle. ' as D','T.Id','=','D.IdInventario')
						->join('WEB.TipoConceptoInventarios as TC','TC.Id','=','D.IdTipoConceptoInventario')
						->join('WEB.ConceptoInventarios as C','C.Id','=','D.IdConceptoInventario')
						->where('T.Id','=',$registro_id)
						->where('D.IdTipoConceptoInventario','=',$tipoconcepto)
						->where('D.Activo','=',1)
						->selectRaw("DISTINCT C.Id,C.Descripcion")
						->pluck('TC.Descripcion','TC.Id')
						->toArray();
		return $datos;
	}

	public function ge_validarSizeArchivos($files,$arr_archivos,$lote,$limite,$unidad)
	{
		$sw             =   true;
		$sizerestante   =   0;
		$sizefileslote  =   (float)DB::table('archivos')
								->where('activo','=',1)
								->where('lote','=',$lote)
								->sum('size'); ///en bytes  //1024^2 para ser megas
		$sizefiles = 0;
		foreach($files as $file){
			$nombreoriginal             = $file->getClientOriginalName();
			if(in_array($nombreoriginal,$arr_archivos)){
				$sizefiles = $sizefiles + filesize($file);
			}
		}

		if($limite>=($sizefileslote + $sizefiles))
		{
			//no supera el limite
			$sw=false;
			$sizerestante = $limite -  ($sizefileslote + $sizefiles);
		}
		// $sizeusado = $sizefiles + $sizefileslote;
		$sizeusado = $sizefileslote;

		$sizefiles      = round(($sizefiles/pow(1024,$unidad)),2);
		$sizeusado      = round(($sizeusado/pow(1024,$unidad)),2);
		$sizerestante   = round(($sizerestante/pow(1024,$unidad)),2);
		$sizefileslote  = round(($sizefileslote/pow(1024,$unidad)),2);
		$limitesize     = round(($limite/pow(1024,$unidad)),2);
		 
		// dd(compact('sw','sizefiles','sizefileslote','sizeusado','sizerestante','limitesize'));
		return compact('sw','sizefiles','sizefileslote','sizeusado','sizerestante','limitesize');
	}   


	public function getFechaVencimientoDocumento($IdTipoDocumento,$IdVehiculo,$IdTrabajador,$OrdenDocumento)
	{
		$fecha = date('Ymd');
		$consulta = NULL;
		if(!is_null($IdTipoDocumento)){
			if($IdTipoDocumento==$this->IdLicenciaConducir)
			{
				$consulta = PERPersonaDocumento::from('PER.PersonaDocumento as PD')
									->join('PER.Trabajador AS TR','PD.IdPersona','=','TR.IdPersona')
									->where('PD.IdTipoDocumento', $IdTipoDocumento)
									->where('TR.Id', $IdTrabajador)
									->where('PD.Activo', 1)
									->where('PD.Vigente', 1)
									->select('PD.FechaVencimiento')
									->first();
			}
			else{
				$consulta = SPCImagenesDocumento::where('IdVehiculo','=',$IdVehiculo)
						->where('IdTipoDocumento','=',$IdTipoDocumento)
						->where('IndVigente','=',1)
						->where('Activo','=',1)
						->select('FechaVencimiento')
						->skip($OrdenDocumento)
						->first();
			}
			if($consulta){
				$fecha = date('Ymd',strtotime($consulta->FechaVencimiento));
			}
		}
		return $fecha;
	}
	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	public function InsertarMensajeWhatsappProyecto($mensajetexto,$archivo,$modulo,$sizefile=0)
	{
		$valor = false;
		if(!$this->ge_isUsuarioAdmin()){
			try {
				$numeros = NumerosWhatsapp::where('Proyecto',$modulo)->where('Activo',1)->select('Numero','Nombre')->get();
				// $numeros = explode(',',$numerosdestino);
				foreach ($numeros as $index => $numero) {
					$mensaje                    =   new Whatsapp;
					$mensaje->NombreContacto    =   $numero->Nombre;
					$mensaje->NumeroContacto    =   $numero->Numero;
					$mensaje->Mensaje           =   $mensajetexto;
					$mensaje->NombreProyecto    =   $modulo;
					if($archivo!=='')
					{
						$mensaje->IndArchivo    =   1;
						$mensaje->RutaArchivo   =   $archivo;
					}
					if($sizefile!==0){
						$mensaje->SizeArchivo = $sizefile;
					}
					$mensaje->save();
				}

			} catch (Exception $e) {
				return false;
			}
		}
		else{
			try {
				$numero = env('NUMEROADMIN');
				$nombre = env('NOMBREADMIN');
				$mensaje                    =   new Whatsapp;
				$mensaje->NombreContacto    =   $nombre;
				$mensaje->NumeroContacto    =   $numero;
				$mensaje->Mensaje           =   $mensajetexto;
				$mensaje->NombreProyecto    =   $modulo;
				if($archivo!=='')
				{
					$mensaje->IndArchivo    =   1;
					$mensaje->RutaArchivo   =   $archivo;
				}
				if($sizefile!==0){
					$mensaje->SizeArchivo = $sizefile;
				}
				$mensaje->save();

			} catch (Exception $e) {
				return false;
			}
		}
		return $valor;
	}

	public function InsertarMensajeWhatsapp($numerosdestino,$mensajetexto,$archivo,$modulo,$sizefile=0)
	{
		$valor = false;
		if(!$this->ge_isUsuarioAdmin()){

			try {
				$numeros = explode(',',$numerosdestino);
				foreach ($numeros as $index => $numero) {
					$mensaje                    =   new Whatsapp;
					$mensaje->NumeroContacto    =   $numero;
					$mensaje->Mensaje           =   $mensajetexto;
					$mensaje->NombreProyecto    =   $modulo;
					if($archivo!=='')
					{
						$mensaje->IndArchivo    =   1;
						$mensaje->RutaArchivo   =   $archivo;
					}
					if($sizefile!==0){
						$mensaje->SizeArchivo = $sizefile;
					}
					$mensaje->save();
				}
			} catch (Exception $e) {
				return false;
			}
		}
		return $valor;
	}
	public function InsertarMensajeWhatsappHUB($numerosdestino,$nombrecontacto,$mensajetexto,$modulo)
	{
		$valor = false;
		if(!$this->ge_isUsuarioAdmin()){

			try {
				$numeros = explode(',',$numerosdestino);
				foreach ($numeros as $index => $numero) {
					$mensaje                    =   new Whatsapp;
					$mensaje->NumeroContacto    =   $numero;
					$mensaje->NombreContacto    =   $nombrecontacto;
					$mensaje->Mensaje           =   $mensajetexto;
					$mensaje->NombreProyecto    =   $modulo;
					
					
					$mensaje->save();
				}
			} catch (Exception $e) {
				return false;
			}
		}
		return $valor;
	}
	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// LOG REGISTRO DE SESSION
	public function getBrowser()
	{
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		$navegador = '';
		if(strpos($user_agent, 'MSIE') !== FALSE){
			$navegador   =   'Internet explorer';
		}elseif(strpos($user_agent, 'Edge') !== FALSE){//Microsoft Edge
			$navegador   =   'Microsoft Edge';
		}elseif(strpos($user_agent, 'Trident') !== FALSE){//IE 11
			$navegador   =   'Internet explorer';
		} 
		elseif(strpos($user_agent, 'Opera Mini') !== FALSE){
			$navegador   =  'Opera Mini';
		}elseif(strpos($user_agent, 'Opera') || strpos($user_agent, 'OPR') !== FALSE){
			$navegador   =  'Opera';
		}elseif(strpos($user_agent, 'Firefox') !== FALSE){
			$navegador   =   'Mozilla Firefox';
		}elseif(strpos($user_agent, 'Chrome') !== FALSE){
			$navegador   =   'Google Chrome';
		}elseif(strpos($user_agent, 'Safari') !== FALSE){
			$navegador   =  'Safari';
		}else{
			$navegador   =   'No hemos podido detectar su navegador';
		}
		return $navegador;
	}

	

	public function getIpUser()
	{
		$ip = '0.0.0.0';
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		return $ip;
	}
 
	public function getRegistraLogSesion($IndAuditado,$fechaActual, $parm_modulo,$accion,$descripcion='',$empresa='1CH000006026')
	{
		if($IndAuditado==1){
			$prefijo                =   Session::get('usuario')->prefijo;
			$IdNuevo                =   $this->getCreateIdISL('logsesion',$prefijo,17);
			$tabla                  =   new LogSession();
			$tabla->Id              =   $IdNuevo;
			$tabla->Fecha           =   $fechaActual;
			$tabla->Descripcion     =   $descripcion;
			$tabla->Accion          =   $accion;
			$tabla->Ip              =   $this->getIpUser();
			// $tabla->Ip               =   $request->ip();
			$tabla->Navegador       =   $this->getBrowser();
			$tabla->UserId          =   Session::get('usuario')->usuarioisl_id;
			$tabla->Modulo          =   $parm_modulo;
			$tabla->Nombre          =   Session::get('usuario')->NombrePersona. ' '.Session::get('usuario')->ApellidoPaterno.' '.Session::get('usuario')->ApellidoMaterno;
			$tabla->IdEmpresa       =   $empresa;
			
			$tabla->FechaCreacion  =   $fechaActual;
			$tabla->save();
		}
	}


	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	
	public function getComboEstadosIncidenciasVehiculos()
	{
		$consulta = [''=>'SELECCIONE']+DB::table('STD.Estado')->whereIn('Nombre',['GENERADA','EN PROCESO','TERMINADA'])->pluck('Nombre','Id')->toArray();
		return $consulta;
	}

	public function getComboTiposIncidenciasVehiculos()
	{
		$consulta = [''=>'SELECCIONE']+DB::table('OPE.TipoIncidencia')->pluck('Nombre','Id')->toArray();
		return $consulta;
	}
	
	public function gn_getComboSedeWeb()
	{
		$consulta = [''=>'SELECCIONE']+DB::table('WEB.SedeWeb')->pluck('nombreabreviado','id')->toArray();
		return $consulta;
	}

	public function validarViajeFinalizado($IdViaje)
	{
		$valor = false;
		if($IdViaje){
			$consulta = OPEViaje::find($IdViaje);
			$valor=($consulta->IdEstado==$this->estviajefin->Id)?true:false;
		}
		return $valor;
	}

	public function getComboTipoEstadosViajeVehiculo($IdViaje,$IdEstadoViajeVehiculo=NULL)
	{
		$combo = [];
		// $IdEstadoViaje       =   OPEViaje::find($IdViaje)->IdEstado;
		// $olEstadosViajes =   STDEstado::where('Activo', '=', 1)
		//                          ->whereRaw("Codigo LIKE '%EV%'")
		//                          ->select('Id', 'Nombre', 'Codigo')
		//                          ->selectRaw('RIGHT(Codigo, 8) as IdEstadoViaje')
		//                          // ->havingRaw('IdEstadoViaje = ?', ['1CH00004'])
		//                          ->orderBy('Codigo', 'asc')
		//                          ->get();
		// if($IdEstadoViaje!==$this->estvprogram->Id){
		//  // SI NO ESTA PROGRAMADO
		//  $oeEstadoViaje      =   $olEstadosViajes->where('IdEstadoViaje','=',$IdEstadoViaje)->first();
		//  dd($oeEstadosViaje);
		//  // $combo = [];
		//  // foreach ($olEstadosViajes as $index => $estado) {
		//  //  $lineacombo = [];

		//  //  $combo[] = $lineacombo; 
		//  // }
		// }
		// else{
			// ESTADO PROGRAMADO
			$consulta = ($IdEstadoViajeVehiculo)? STDEstado::where('Activo','=',1)->whereRaw("Codigo LIKE '%"."EV"."%'")->where('Id','>',$IdEstadoViajeVehiculo)->pluck('Nombre','Id')->toArray() : STDEstado::where('Activo','=',1)->whereRaw("Codigo LIKE '%"."EV"."%'")->pluck('Nombre','Id')->toArray();
			$combo = $consulta;
			return $combo;
		// }

	}
	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	public function getNombreTrabajador($dni)
	{
		$nombre = '';
		if(($dni!=='') || (!is_null($dni))){
			$consulta = WEBTrabajadorIsl::where('Dni','=',$dni)->first();
			$nombre =($consulta)?$consulta->NombreCompleto:'';
		}
		return $nombre; 
	}

	public function getClaveEncriptadaSGI($clave)
	{
		// $encriptada = '';
		$urlApiCSgi = env('URLAPIENCCLAVE');
		$urlApi =   $urlApiCSgi.'encriptar';
		$curl = curl_init();
		$datosjson = json_encode(['texto'=>$clave]);
		curl_setopt_array($curl, array(
		  CURLOPT_URL => $urlApi,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'POST',
		  CURLOPT_POSTFIELDS =>$datosjson,
		  CURLOPT_HTTPHEADER => array(
			'Content-Type: application/json'
		  ),
		));

		$response = curl_exec($curl);
		curl_close($curl);
		$rpta = json_decode($response,true);
		// echo $response;

		return $rpta['TextoEncriptado'];
	}

	// -----------------------------------------------------------------------------------------------------------------------------------
	//BASE // TRAB
	public function GetUrlApiBuk($Tipo='BASE')
	{
		return env('URL_BUK_'.$Tipo);
	}

	public function GetTokenApiBuk()
	{
		return env('TOKEN_BUK');
	}
	// -----------------------------------------------------------------------------------------------------------------------------------
	public static function ejecutarSP($sql, $params = [])
	{
		$pdo = DB::connection()->getPdo();

		$stmt = $pdo->prepare($sql);

		// Vincular parámetros dinámicamente
		foreach ($params as $key => $value) {
			$stmt->bindValue($key, $value);
		}

		$stmt->execute();

		$resultados = [];
		do {
			//$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
			// if ($rows) {
			//  $resultados[] = $rows;
			// }
			// consumir aunque no tenga columnas
			if ($stmt->columnCount() > 0) {
				$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
				if ($rows) {
					$resultados[] = $rows;
				}
			}
		} while ($stmt->nextRowset());

		return $resultados; // Array de tablas
	}

	public function gn_getComboEstadosCelular($titulo = 'SELECCIONE',$IdEstado = '')
	{
		if($IdEstado!==''){
			$consulta = DB::table('STD.Estado')->where('Id',$IdEstado)->pluck('Nombre','Id')->toArray()
						+ [''=>$titulo]
						+ DB::table('STD.Estado')->whereIn('Id',$this->IdEstadosBien)->where('Id','<>',$IdEstado)->orderBy('Codigo','asc')->pluck('Nombre','Id')->toArray();
		}
		else{
			$consulta = [''=>$titulo]+ DB::table('STD.Estado')->whereIn('Id',$this->IdEstadosBien)->orderBy('Codigo','asc')->pluck('Nombre','Id')->toArray();
		}
		return $consulta;
	}


	public function gn_getComboSedesTrabWeb($titulo = 'SELECCIONE',$IdRegistro = '')
	{
		if($IdRegistro!==''){
			$consulta = DB::table('WEB.SedeWeb')->where('Id',$IdRegistro)->pluck('Sector','Id')->toArray()
						+ [''=>$titulo]
						+ DB::table('WEB.SedeWeb')->where('Id','<>',$IdRegistro)->where('activo',1)->pluck('Sector','Id')->toArray();
		}
		else{
			$consulta = [''=>$titulo]+ DB::table('WEB.SedeWeb')->where('activo',1)->pluck('Sector','Id')->toArray();
		}
		return $consulta;
	}

	public function gn_getComboMarcaCelular($titulo = 'SELECCIONE',$IdRegistro = '')
	{
		if($IdRegistro!==''){
			$consulta = DB::table('CEL.Marca')->where('IdMarca',$IdRegistro)->pluck('NombreMarca','IdMarca')->toArray()
						+ [''=>$titulo]
						+ DB::table('CEL.Marca')->where('IdMarca','<>',$IdRegistro)->pluck('NombreMarca','IdMarca')->toArray();
		}
		else{
			$consulta = [''=>$titulo]+ DB::table('CEL.Marca')->pluck('NombreMarca','IdMarca')->toArray();
		}
		return $consulta;
	}

	public function gn_GetFechaSpanish($fecha)
	{
		// Separar la fecha recibida (formato dd/mm/yyyy)
		$auxFecha = (string)date('d-m-Y',strtotime($fecha));
		// dd($auxFecha);
		list($dia, $mes, $anio) = explode('-', $auxFecha);

		// Arreglo con los nombres de los meses en español
		$meses = [
			"01" => "Enero", "02" => "Febrero", "03" => "Marzo",
			"04" => "Abril", "05" => "Mayo", "06" => "Junio",
			"07" => "Julio", "08" => "Agosto", "09" => "Septiembre",
			"10" => "Octubre", "11" => "Noviembre", "12" => "Diciembre"
		];

		// Retornar la fecha formateada
		return intval($dia) . ' de ' . $meses[$mes] . ' del ' . $anio;

	}

	// public function gn_EnviarDocumentoHaciaBuk($oeRegistro,$datosPdf,$tipoArchivoBuk)
	// {
	//  $oedatosPdf     =   json_decode($datosPdf,true);
	//  $idTrabBuk      =   $oeRegistro->IdBukTrabajador;
	//  $CarpetaBuk     =   $oedatosPdf['carpetaBuk'];
	//  $token_buk      =   env('TOKEN_BUK');
	//  $fileDoc        =   $oedatosPdf['archivo'];
	//  $fileOnRed      =   $oedatosPdf['file_red'];
	//  $ruoteFileOnNet =   $oedatosPdf['ruta_red'];
	//  $extecionFile   =   $oedatosPdf['extension'];
	//  $oeFile         =   file_get_contents($fileOnRed);
	//  $pdfBase64      =   $oedatosPdf['base64'];
	//  // dd($fileDoc);
	//  $file_base64 = json_encode(array(
	//                  "content" => $pdfBase64,
	//                  "filename" => $fileDoc
	//              ));
	//  // dd($file_base64);
		
	//  // Asignar los datos a un array
	//  $datosJson = array(
	//      'overwrite'=>true,
	//      'file_base64' => $file_base64
	//  );

	//  // dd(compact('datosPdf','datosJson'));
	//  $curl       =   curl_init();
	//  $urlApiBuk  =   env('URL_BUK_TRAB').$idTrabBuk.'/docs?visible=true&signable_by_employee=true&start_signature_workflow=false&path='.$CarpetaBuk;
	//  // dd($urlApiBuk);
	//  // dd($datosJson);
	//  curl_setopt_array($curl, array(
	//    CURLOPT_URL => $urlApiBuk,
	//    CURLOPT_RETURNTRANSFER => true,
	//    CURLOPT_ENCODING => '',
	//    CURLOPT_MAXREDIRS => 10,
	//    CURLOPT_TIMEOUT => 0,
	//    CURLOPT_FOLLOWLOCATION => true,
	//    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	//    CURLOPT_CUSTOMREQUEST => 'POST',
	//    CURLOPT_POSTFIELDS => $datosJson,
	//    CURLOPT_HTTPHEADER => array(
	//      'Accept: application/json',
	//      'auth_token: '.$token_buk
	//    ),
	//  ));

	//  $response = curl_exec($curl);

	//  curl_close($curl);
	//  if(!is_null($response)){ //SI NO ES NULL
	//      $rptajson               =   json_decode($response,true);
	//      $datosBukFile           =   $rptajson['employee_file'];
	//      $oeRegistro->IdDocBuk   =   ($this->gnRegistrarArchivosBuk($oeRegistro,$oedatosPdf,$datosBukFile,$tipoArchivoBuk))?$datosBukFile['id']:NULL;
	//      $this->IniciarProcesoFirmaDocumento($datosBukFile['id']);
	//      $oeRegistro->save();
	//  }
	//  else{
	//      return false;
	//  }
	//  return true;

	// }

	public function generarActaPDFDocBukCelular($oeRegistro, $empresa,$uReg,$prefijoArch,$rutaFile)
	{
		
		$mensaje    =   'TODO OK';
		$error      =   false;
		try {
				// 1. Generar PDF desde blade
				$pdf = PDF::loadView($this->view.'.pdf.acta', [
					'oeRegistro' => $oeRegistro,
					'empresa' => $empresa,
					'uReg'  =>  $uReg
				])->setPaper('A4', 'portrait');
				// 2. Crear archivo temporal
				$filename = $prefijoArch.'-' .date('YmdHi') .'-'. substr($oeRegistro->Codigo,-5) .'-'.$oeRegistro->Dni.'-'.$oeRegistro->Trabajador.'.pdf';
				$tempDir = storage_path('temporal');
				if (!file_exists($tempDir)) {
					mkdir($tempDir, 0777, true);
				}
				$tempPath = $tempDir . '/' . $filename;
				file_put_contents($tempPath, $pdf->output());
				// 3. Crear carpeta destino en red
				$file_size = filesize($tempPath); // tamaño en bytes
				// $rutaRed = $this->rutafiles . '\\' . $oeRegistro->Codigo . '\\';
				$rutaRed = $rutaFile. '\\' ;
				if (!file_exists($rutaRed)) {
					mkdir($rutaRed, 0777, true);
				}
				// // 4. Copiar archivo a red
				copy($tempPath, $rutaRed . $filename);
				// 5. Convertir a Base64 desde archivo guardado
				$base64PDF = base64_encode(file_get_contents($tempPath));
				$file_red = $rutaRed.$filename;
				// 6. Eliminar archivo temporal
				// unlink($tempPath); //MIENTRAS NO LO DESCARGUE
		} catch (\Exception $ex) {
			$error= true;
			// $mensaje=$ex;
			$mensaje = $ex->getMessage(); // mensaje de error legible
			return json_encode([
				'error' => $error,
				'mensaje'=>$mensaje]
			);
		}

		return json_encode([
			'error' => $error,
			'mensaje'=>$mensaje,
			'archivo' => $filename,
			'extension'=>'pdf',
			'ruta_red' => $rutaRed,
			'carpetaBuk'=>$this->rutaCarpetaBuk,
			'file_red'=>$file_red,
			'base64' => $base64PDF,
			'size'  =>  $file_size
		]);
	}

	public function ResubirPdfABukRecepcion($oeRegistro,$tipoArchivoBuk,$prefijoArch,$rutaFile)
	{
		$usuario = Session::get('usuario');
		$uReg = DB::table('usuariosisls')->where('IdTrabajador',$usuario->IdTrabajador)->first();

		$datosPdf = $this->generarActaPDFDocBukCelular($oeRegistro,$this->empresa,$uReg,$prefijoArch,$rutaFile);


		$mensaje    =   'TODO OK';
		$error      =   false;
		try {
			
				// $respuesta   =   $this->gn_EnviarDocumentoHaciaBuk($oeRegistro,$datosPdf,$tipoArchivoBuk);
				$oedatosPdf     =   json_decode($datosPdf,true);
				$idTrabBuk      =   $oeRegistro->IdBukTrabajador;
				$CarpetaBuk     =   $oedatosPdf['carpetaBuk'];
				$token_buk      =   env('TOKEN_BUK');
				$fileDoc        =   $oedatosPdf['archivo'];
				$fileOnRed      =   $oedatosPdf['file_red'];
				$ruoteFileOnNet =   $oedatosPdf['ruta_red'];
				$extecionFile   =   $oedatosPdf['extension'];
				$oeFile         =   file_get_contents($fileOnRed);
				$pdfBase64      =   $oedatosPdf['base64'];
				$file_base64    =   json_encode(array(
										"content" => $pdfBase64,
										"filename" => $fileDoc
									));
				// Asignar los datos a un array
				$datosJson = array(
					'overwrite'=>true,
					'file_base64' => $file_base64
				);
				$curl       =   curl_init();
				$urlApiBuk  =   env('URL_BUK_TRAB').$idTrabBuk.'/docs?visible=true&signable_by_employee=true&start_signature_workflow=false&path='.$CarpetaBuk;
				curl_setopt_array($curl, array(
				  CURLOPT_URL => $urlApiBuk,
				  CURLOPT_RETURNTRANSFER => true,
				  CURLOPT_ENCODING => '',
				  CURLOPT_MAXREDIRS => 10,
				  CURLOPT_TIMEOUT => 0,
				  CURLOPT_FOLLOWLOCATION => true,
				  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				  CURLOPT_CUSTOMREQUEST => 'POST',
				  CURLOPT_POSTFIELDS => $datosJson,
				  CURLOPT_HTTPHEADER => array(
					'Accept: application/json',
					'auth_token: '.$token_buk
				  ),
				));

				$response = curl_exec($curl);

				curl_close($curl);
				if(!is_null($response)){ //SI NO ES NULL
					$rptajson               =   json_decode($response,true);
					$datosBukFile           =   $rptajson['employee_file'];
					$consRegArchBuk         =   $this->gnRegistrarArchivosBuk($oeRegistro,$oedatosPdf,$datosBukFile,$tipoArchivoBuk);

					if(!$consRegArchBuk['error']){
						$oeRegistro->IdDocBuk   =   $datosBukFile['id'];
						$this->IniciarProcesoFirmaDocumento($datosBukFile['id']);
						$oeRegistro->save();
					}
					else{
						$mensaje= 'Error No se Registro en BD el Archivo';
						goto Error_Generando_Acta_Documento_EnBuk;
					}
				}
				else{
					// return false;
					$mensaje= 'Error No se Subio a Buk el Archivo';
					goto Error_Generando_Acta_Documento_EnBuk;
				}
				// return true;
		} catch (\Exception $ex) {
			$mensaje = $ex->getMessage();
			Error_Generando_Acta_Documento_EnBuk:
			$error = true;
		}

		return response()->json([
			'error' => $error,
			'mensaje'=>$mensaje
		]);

	}


	public function gn_ValidarDocumentoFirmado($oeRegistro,$tipoArchivoBuk,$prefijoArch,$rutaFile)
	{
		$idDocBuk   =   $oeRegistro->IdDocBuk;

		$urlApiBuk  =   env('URL_BUK_BASE').'docs/'.$idDocBuk;
		$updated = date('d-m-Y H:i:s');
		$opcFirma = false;
		$curl = curl_init();
		curl_setopt_array($curl, array(
		  CURLOPT_URL => $urlApiBuk,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'GET',
		  CURLOPT_HTTPHEADER => array(
			'Accept: application/json',
			'auth_token: '.env('TOKEN_BUK')
		  ),
		));

		$response = curl_exec($curl);

		curl_close($curl);
		// echo $response;
		$respuesta = json_decode($response,true);
		$data = json_decode($response, true);

		// Validar si existe employee_file en la respuesta
		if (isset($respuesta['employee_file'])) {
			$created = $respuesta['employee_file']['created_at'];
			$updated = $respuesta['employee_file']['updated_at'];
			if ($created !== $updated) {
				$opcFirma = true;
			} else {
				$opcFirma = false;
			}

		} else {
			// $this->mostrarValor($oeRegistro);
			if (isset($respuesta['errors'])) {
				// if($respuesta['errors'][0]=='Not found: Api::V1::Models::EmployeeFileModel'){
				if($respuesta['errors'][0]=='No se encontró el empleado'){
					// $this->mostrarValor('REENNVIAR A BUK DOC');
					$this->ResubirPdfABukRecepcion($oeRegistro,$tipoArchivoBuk,$prefijoArch,$rutaFile);
				}
			}
			$opcFirma = false;
		}
		$FechaFirma = date('d-m-Y H:i:s',strtotime($updated));
		return [
				'Firma'=>$opcFirma,
				'FechaFirma'=>$FechaFirma
			];
	}

	public function gnRegistrarArchivosBuk($oeRegistro,$oedatosPdf,$datosFile,$tipoArchivoBuk)
	{
		$error = false;
		$mensaje='todo ok';
		try {
				$datosTrab                          =   DB::table('usuariosisls')
															->where('IdTrabajador',$oeRegistro->IdTrabajador)
															->select('Dni','DniRRHH','IdTrabajador','IdTrabWeb')
															->first();

				$prefijo                            =   Session::get('usuario')->prefijo;
				$registroFile                       =   new WEBArchivosBuk();

				$idregistro                         =   $this->getCreateIdISL('WEB.ArchivosBuk', $prefijo);
				$registroFile->Id                   =   $idregistro;    
				$registroFile->Prefijo              =   $prefijo;   

				$registroFile->IdTrabajador         =   $datosTrab->IdTrabajador;   
				$registroFile->Dni                  =   $datosTrab->Dni;    
				
				$registroFile->IdTrabajadorWeb      =   $datosTrab->IdTrabWeb;  
				$registroFile->DniWeb               =   $datosTrab->DniRRHH;    

				$registroFile->IdBukDoc             =   $datosFile['id'];   
				$registroFile->Employee_Folder_Id   =   $datosFile['employee_folder_id'];   
				$registroFile->IdRegistro           =   $oeRegistro->Id;    
				$registroFile->IdBukTrabajador      =   $oeRegistro->IdBukTrabajador;   


				$registroFile->Tipo                 =   $tipoArchivoBuk;    
				$registroFile->NombreOriginal       =   $datosFile['original_filename'];    
				$registroFile->Nombre               =   $oedatosPdf['archivo']; 
				$registroFile->Url                  =   $oedatosPdf['file_red'];    
				$registroFile->UrlBuk               =   env('URL_BUK_TRAB').$oeRegistro->IdBukTrabajador.'/docs/'.$datosFile['id']; 
				
				$registroFile->Folder               =   $oedatosPdf['ruta_red'];    
				$registroFile->Name                 =   $oedatosPdf['archivo'];
				$registroFile->Extension            =   $oedatosPdf['extension'];   
				$registroFile->Size                 =   $oedatosPdf['size'];    

				$registroFile->FechaRegistro        =   date('d-m-Y H:i:s');
				
				$registroFile->FechaCreacion        =   date('d-m-Y H:i:s');
				$registroFile->UsuarioCreacion      =   Session::get('usuario')->usuarioisl_id; 
				$registroFile->save();
		} catch (\Exception $ex) {
			$error = true;
			$mensaje = $ex->getMessage(); // mensaje de error legible
			// dd($mensaje);
		}
		return [
				'error'=>$error,
				'mensaje'=>$mensaje
				];
	}

	public function IniciarProcesoFirmaDocumento($IdDocBuk)
	{
		// dd('ntro a la firma: '.$IdDocBuk);
		$error = false;
		$mensaje = 'todo ok';
		try{
			$curl = curl_init();
			$urlApiBuk = env('URL_BUK_BASE').'docs/'.$IdDocBuk.'/signatures/process';
			$tokenBuk  = env('TOKEN_BUK');
			curl_setopt_array($curl, array(
			  CURLOPT_URL => $urlApiBuk,
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => '',
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 0,
			  CURLOPT_FOLLOWLOCATION => true,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => 'POST',
			  CURLOPT_HTTPHEADER => array(
				'Content-Type: application/json',
				'Accept: application/json',
				'auth_token: '.$tokenBuk
			  ),
			));

			$response = curl_exec($curl);
			curl_close($curl);

			$rptajson = json_decode($response,true);
			if(array_key_exists('error', $rptajson)){
				$error = true;
				$mensaje = $rptajson['error'];
			}
			if(array_key_exists('ok', $rptajson)){
				$error = false;
				$mensaje = $rptajson['ok'];
				$cabecera                 = new WEBIlog;
				$cabecera->Sistema        = 'ILSMOVIL';
				$cabecera->Modulo         = 'ENTREGA/RECEPCION DE EQUIPO CELULAR';
				$cabecera->Accion         = 'INICIAR PROCESO FIRMA DOCUMENTO: '.$IdDocBuk;
				$cabecera->Descripcion    = 'Inicio de proceso de firma en buk del documento de Entrega/Recepcion de equipo celular con id : '. $IdDocBuk.' ';
				$cabecera->Fecha          = date('d-m-Y');
				$cabecera->FechaTime      = date('d-m-Y H:i:s');
				$cabecera->save();
			}
		} catch (\Exception $ex) {
			$mensaje = $ex->getMessage(); // mensaje de error legible
			$error = true;
		}

		return [
				'error'=>$error,
				'mensaje'=>$mensaje
		];

	}

	public function getCategoriaInspeccionFoto()
	{   
	   $categoriasFotos = NEUCategoriaInspeccionFoto::select(
			'Id',
			'Nombre',
			'Clase'
		)
		->orderBy('Nombre')
		->get()
		->mapWithKeys(function ($item) {
			return [
				$item->Id => [
					'nombre' => $item->Nombre,
					'clase'  => $item->Clase,
				]
			];
		});
		return $categoriasFotos;
	}


	public function getCodigo($tabla,$longitud=12){
		// maximo valor de la tabla referente
		$consulta = DB::Table($tabla)->select(DB::raw('max(convert(bigint,Codigo)) as id'))->first();
		$idsuma=1;
		if($consulta){
			$idsuma = (float)$consulta->id + 1;
		}
		//concatenar con ceros
		return	str_pad($idsuma, $longitud, "0", STR_PAD_LEFT);
		
	}

}