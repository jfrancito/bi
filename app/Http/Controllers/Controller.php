<?php

namespace App\Http\Controllers;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use stdClass;

use App\Biblioteca\Funcion;
use Illuminate\Support\Facades\DB;
use DateTime;
use Session;

use Hashids;

abstract class Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    public $IndProyecto;
    public $funciones;
    public $inicio;
    public $fin;
    public $hoy;
    public $prefijomaestro;
    public $fechaactual;

    public $ruc;
    public $usuario;
    public $password;
    public $fechacrea;
    public $pathImg='img/app';
    // public $rutaurl='http://216.244.171.14:8080/islmovil';
    public $rutaurl='merge.grupoinduamerica.com.pe/islmovil';
    public $emailde='';

    public $estgenerado;
    public $estproceso;
    public $estterminado;
    public $estanulado;

    public $estcarga;
    public $esttransito;
    public $estdescarga;
    public $estescaneando;

    public $confotoacta;
    public $confotoetiqueta;
    public $convideocaja;
    public $confotocajaabierta;
    public $confotolado1;
    public $confotolado2;
    public $confotolado3;
    public $confotolado4;
    public $confotoproducto;

    public $devo_estgenerado;
    public $devo_estfacturado;
    public $devo_estsustentado;


    // public $numerowhatsappHUB;

    // public $estevaluado;
    // public $estatendido;
    // public $estviajefin;
    // public $estvprogram;

    public $eqdetconcp;
    public $estados;
    public $IdLicenciaConducir;

    public $RolConductor;
    public $RolConductorHub;
    public $ModoPrueba;



    // public $ar_estadosviaje;
    public $dias_rango_viajes;
    public $dias_rango_cargas;
    public $cant_ultimos_viajes;
    public $AClienteConformidad;
    public $modDev;
    public $FechaInicioSeguimiento;
    public $IdEstadosBien;

    public $TipoDevolucion;
    public $TipoPagoDevolucionCel;
    private $permisosopciones = array('ver'=>0,'anadir'=>0,'modificar'=>0,'eliminar'=>0,'todas'=>0);
    public $combo_iconos_gp = [''=>'SELECCIONE','dashboard'=>'dashboard','note_stack'=>'note_stack','format_list_bulleted'=>'format_list_bulleted','date_range'=>'date_range','contact_page'=>'contact_page','chat'=>'chat','mail'=>'mail','team_dashboard'=>'team_dashboard','folder_open'=>'folder_open','shopping_cart'=>'shopping_cart','handshake'=>'handshake','description'=>'description','auto_stories'=>'auto_stories','support'=>'support','store'=>'store','real_estate_agent'=>'real_estate_agent','calculate'=>'calculate','stethoscope'=>'stethoscope','lunch_dining'=>'lunch_dining','hotel'=>'hotel','location_away'=>'location_away','paid'=>'paid','local_activity'=>'local_activity','share'=>'share','content_paste'=>'content_paste','person'=>'person','account_box'=>'account_box','star'=>'star','add_reaction'=>'add_reaction','qr_code_scanner'=>'qr_code_scanner','table_chart'=>'table_chart','forum'=>'forum','pie_chart'=>'pie_chart','lock_open'=>'lock_open','content_copy'=>'content_copy','error'=>'error','widgets'=>'widgets','map'=>'map','notifications'=>'notifications','group'=>'group','account_circle'=>'account_circle','settings'=>'settings','unfold_more'=>'unfold_more','logout'=>'logout'];
    public $empresa;
    public function __construct()
    {
        $this->funciones = new Funcion();
        $fecha = new DateTime();
        $fecha->modify('first day of this month');
        $this->AClienteConformidad=['1CH000003674'];
        $this->modDev                   =   env('APP_DEV');
        //fecha actual -7 dias
        $fechasiete = date('Y-m-j');
        $nuevafecha = strtotime ( '-7 day' , strtotime($fechasiete));
        $nuevafecha = date ('Y-m-j' , $nuevafecha);
        $this->IndProyecto=2;
        //fecha actual -1 dias
        $fechasuno      = date('Y-m-j');
        $nuevafechauno  = strtotime ( '-1 day' , strtotime($fechasuno));
        $nuevafechauno  = date ('Y-m-j' , $nuevafechauno);

        $this->ModoPrueba           =   true;
        $this->dias_rango_viajes = 30; //DIAS DE RANGO DE VIAJES

        $diacarga = date('l'); // Guarda el día de la semana, en inglés (ejemplo: "Monday", "Tuesday", etc.)
        if ($diacarga == 'Monday') {
            $this->dias_rango_cargas = 2; // Si es lunes
        } else {
            $this->dias_rango_cargas = 1; // Si no es lunes
        }

        $this->cant_ultimos_viajes  =   3; //DIAS DE RANGO DE VIAJES
        // $this->ar_estadosviaje      =   DB::table('STD.Estado')->whereRaw("Codigo LIKE '%"."EV"."%'")->where('Activo','=',1)->pluck('Nombre','Id')->toArray();

        // $this->fecha_menos_un_dia       = date_format(date_create($nuevafechauno), 'Y-m-d');
        // $this->fecha_menos_siete_dias   = date_format(date_create($nuevafecha), 'Y-m-d');
        $this->inicio                   = date_format(date_create($fecha->format('Y-m-d')), 'Y-m-d');

        $this->fechaactual              = date('d-m-Y H:i:s');
        $this->fin                      = date_format(date_create(date('Y-m-d')), 'Y-m-d');
        // $this->fecha_hora               = date_format(date_create(date('Y-m-d')), 'Y-m-d H:m:s');
        // $this->fecha_sin_hora           = date('d-m-Y');

        // $this->fecha_menos_uno          = date_format(date_create(date('Y-m-d')), 'Y-m-d');

        // $this->estgenerado              =   DB::table('STD.Estado')->where('Nombre','=','GENERADA')->first();
        // $this->estproceso               =   DB::table('STD.Estado')->where('Nombre','=','EN PROCESO')->first();
        // $this->estterminado             =   DB::table('STD.Estado')->where('Nombre','=','TERMINADA')->first();
        // $this->estanulado               =   DB::table('STD.Estado')->where('Nombre','=','ANULADO')->first();

        // $this->estcarga                 =   DB::table('STD.Estado')->where('Nombre','=','CARGA')->first();
        // $this->esttransito              =   DB::table('STD.Estado')->where('Nombre','=','TRANSITO')->first();
        // $this->estdescarga              =   DB::table('STD.Estado')->where('Nombre','=','DESCARGA')->first();
        // $this->estescaneando            =   DB::table('STD.Estado')->where('Nombre','=','ESCANEANDO')->first();

        // $this->estevaluado              =   DB::table('STD.Estado')->where('Nombre','=','EVALUADO')->first();
        // $this->estatendido              =   DB::table('STD.Estado')->where('Nombre','=','ATENDIDO')->first();
        // $this->estviajefin              =   DB::table('STD.Estado')->where('Nombre','=','FIN')->first();
        // $this->estvprogram              =   DB::table('STD.Estado')->where('Nombre','=','PROGRAMADO')->first();

        $this->IdEstadosBien            =   ['1CH00052','1CH00022','1CH00023','1CH00024','1CH00025'];

        // $this->confotoacta              =   DB::table('SPC.Concepto')->where('Nombre','=','FOTO ACTA')->where('Prefijo','=','185')->first();
        // $this->confotoetiqueta          =   DB::table('SPC.Concepto')->where('Nombre','=','FOTO ETIQUETA')->where('Prefijo','=','185')->first();
        // $this->convideocaja             =   DB::table('SPC.Concepto')->where('Nombre','=','VIDEO CAJA')->where('Prefijo','=','185')->first();
        // $this->confotocajaabierta       =   DB::table('SPC.Concepto')->where('Nombre','=','FOTO CAJA ABIERTA')->where('Prefijo','=','185')->first();
        // $this->confotolado1             =   DB::table('SPC.Concepto')->where('Nombre','=','FOTO LADO 1')->where('Prefijo','=','185')->first();
        // $this->confotolado2             =   DB::table('SPC.Concepto')->where('Nombre','=','FOTO LADO 2')->where('Prefijo','=','185')->first();
        // $this->confotolado3             =   DB::table('SPC.Concepto')->where('Nombre','=','FOTO LADO 3')->where('Prefijo','=','185')->first();
        // $this->confotolado4             =   DB::table('SPC.Concepto')->where('Nombre','=','FOTO LADO 4')->where('Prefijo','=','185')->first();
        // $this->confotoproducto          =   DB::table('SPC.Concepto')->where('Nombre','=','FOTO PRODUCTO')->where('Prefijo','=','185')->first();

        // $this->devo_estgenerado         =   DB::table('SPC.Concepto')->where('Nombre','=','GENERADO')->where('Prefijo','=','184')->first();
        // $this->devo_estfacturado        =   DB::table('SPC.Concepto')->where('Nombre','=','FACTURADO')->where('Prefijo','=','184')->first();
        // $this->devo_estsustentado       =   DB::table('SPC.Concepto')->where('Nombre','=','SUSTENTADO')->where('Prefijo','=','184')->first();
        // $this->numerowhatsappHUB        =   DB::table('numeroswhatsapp')
        //                                         ->where('Proyecto','=','HUB')
        //                                         ->where('Nombre','=','Sistemas')
        //                                         ->where('activo','=',1)
        //                                         ->where('indgrupo','=',1)
        //                                         ->first();

        $this->IdLicenciaConducir       =   '1CH000000060';
        $this->FechaInicioSeguimiento   =   date('Y-m-d',strtotime('2025-07-01'));

        $this->ruc = '20479729141';
        $this->usuario = 'SYST1NDU';
        $this->password = '1ndu4m3r1c@';
        $this->fechacrea = date('Ymd');
        $this->emailde = 'neil.vigil@induamerica.com.pe';
        $this->estados = [
            '1'=>'NUEVO',
            '2'=>'BUENO',
            '3'=>'REGULAR',
            '4'=>'MALO'
        ];
        $this->eqdetconcp = [
            'dconcexi'      =>  'Existencia',
            'dconcobs'      =>  'Observacion',
            'dconcfvc'      =>  'FechaVencimiento',
            'dconcstd'      =>  'Estado',
            'dconccnt'      =>  'Cantidad',
            'dconccob'      =>  'CantidadObligatoria',
        ];

        $this->TipoDevolucion = [
                                    '0'=>'DEVOLUCION',
                                    '1'=>'COMPRA'
                                ];
        $this->TipoPagoDevolucionCel = [
                                    '0'=>'',
                                    '1'=>'EFECTIVO',
                                    '2'=>'DESCUENTO POR PLANILLA'
                                ];
        // $this->RolConductor     =   DB::table('WEB.rols')->where('nombre','=','CONDUCTOR')->first();
        // $this->RolConductorHub  =   DB::table('WEB.rols')->where('nombre','=','CONDUCTOR HUB')->first();
        $this->empresa = new stdClass();
        $this->empresa->Ruc           = '20479729141';
        $this->empresa->RazonSocial   = 'Induamerica Servicios Logisticos S.A.C.';
    }

    public function getPermisosOpciones($idopcion,$idusuario)
    {

        //decodificar variable
        $decidopcion = Hashids::decode($idopcion);


        //concatenar con ceros
        $idopcioncompleta = str_pad($decidopcion[0], 12, "0", STR_PAD_LEFT);
        //concatenar prefijo

        $idopcioncompleta = 'PRMAECEN'.$idopcioncompleta;

        // ver si la opcion existe
        $opcion =  DB::table('WEB.rolopciones as RO')
                        ->join('WEB.rols as R','RO.rol_id','=','R.id')
                        ->join('DBBO.users as U','U.rol_id','=','R.id')
                        ->where('U.id','=',$idusuario)
                        ->where('RO.opcion_id','=',$idopcioncompleta)
                        ->select(
                            'RO.ver',
                            'RO.anadir',
                            'RO.modificar',
                            'RO.eliminar',
                            'RO.todas',
                            'RO.*'
                        )
                        ->first();
        // dd($opcion);
        if((count($opcion)>0) && !empty($opcion))
        {
            $permisosopciones['ver']        = $opcion->ver;
            $permisosopciones['anadir']     = $opcion->anadir;
            $permisosopciones['modificar']  = $opcion->modificar;
            $permisosopciones['eliminar']   = $opcion->eliminar;
            $permisosopciones['todas']      = $opcion->todas;
        }
        // $opciones= P
        return $permisosopciones;
    }

    public function fn_crearCarpetaSiNoExiste($ruta){
        $valor = false;

        if (!file_exists($ruta)) {
            mkdir($ruta, 0777, true);
            $valor=true;
        }
        return $valor;
    }

    public function fn_borrarCarpeta($carpeta)
    {
        // Obtener el contenido del directorio
        $items = scandir($carpeta);

        foreach ($items as $item) {

            // Ignorar . y ..
            if ($item === "." || $item === "..") {
                continue;
            }

            $ruta = $carpeta . DIRECTORY_SEPARATOR . $item;

            // Si es carpeta, llamar recursivamente
            if (is_dir($ruta)) {
                $this->fn_borrarCarpeta($ruta);
            } else {
                unlink($ruta);
            }
        }

        // Finalmente eliminamos la carpeta
        rmdir($carpeta);
    }

    // public function fn_borrarCarpeta($carpeta)
    // {
    //     $folderCont = scandir($carpeta);
    //     foreach ($folderCont as $clave => $valor)
    //     {
    //         if(is_dir($folderCont[$clave].'/'.$valor) && $valor!='.' && $valor!='..'){
    //             rmDir_rf($carpeta.'/'.$folderCont[$clave]);
    //         }
    //         else
    //         {
    //             unlink($carpeta.'/'.$folderCont[$clave]);
    //         }
    //     }
    //     rmdir($carpeta);
    //  }

}
