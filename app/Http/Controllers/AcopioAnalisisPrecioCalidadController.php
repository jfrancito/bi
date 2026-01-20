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

class AcopioAnalisisPrecioCalidadController extends Controller
{
    use UserTraits;
    public function actionListarAnalisisPrecioCalidad($idopcion)
    {
        /******************* validar url **********************/
        $validarurl = $this->user_url($idopcion, 'ver');
        if ($validarurl != 'true') {
            return $validarurl;
        }
        /******************************************************/
        View::share('titulo', 'Análisis Precio Calidad');

        // Fetch years for general filter
        $anios = DB::table('acopio_arroz_raw')
            ->select('anio_compra')
            ->distinct()
            ->orderBy('anio_compra', 'desc')
            ->get();

        // Fetch available qualities for general filter
        $calidades = DB::table('acopio_arroz_raw')
            ->select('calidad')
            ->whereNotNull('calidad')
            ->where('calidad', '<>', '')
            ->distinct()
            ->orderBy('calidad', 'asc')
            ->get();

        $ultima_actualizacion = DB::table('acopio_arroz_raw')->max('fecha_entrada');

        return View::make(
            'pages/acopio/analisispreciocalidad',
            [
                'idopcion' => $idopcion,
                'anios' => $anios,
                'calidades' => $calidades,
                'ultima_actualizacion' => $ultima_actualizacion ? date('d/m/Y H:i', strtotime($ultima_actualizacion)) : 'N/A'
            ]
        );
    }

    public function actionAjaxListarAnalisisPrecioCalidad(Request $request)
    {
        $anios_filtro = $request->input('anio');
        $calidades_filtro = $request->input('calidades');

        $query = DB::table('acopio_arroz_raw')
            ->select(
                'anio_compra',
                'mes_numero_compra',
                'mes_compra',
                'calidad',
                DB::raw('SUM(peso_humedo_kg) as total_peso_humedo'),
                DB::raw('SUM(peso_seco_kg) as total_peso_seco'),
                DB::raw('SUM(importe_total_compra) as total_importe')
            )
            ->when($anios_filtro, function ($q) use ($anios_filtro) {
                $values = is_array($anios_filtro) ? $anios_filtro : array_filter(explode(',', $anios_filtro));
                return empty($values) ? $q : $q->whereIn('anio_compra', $values);
            })
            ->when($calidades_filtro, function ($q) use ($calidades_filtro) {
                $values = is_array($calidades_filtro) ? $calidades_filtro : array_filter(explode(',', $calidades_filtro));
                return empty($values) ? $q : $q->whereIn('calidad', $values);
            })
            ->groupBy('anio_compra', 'mes_numero_compra', 'mes_compra', 'calidad')
            ->orderBy('anio_compra', 'desc')
            ->orderBy('mes_numero_compra', 'asc')
            ->orderBy('calidad', 'asc');

        $data = $query->get();
        return response()->json($data);
    }

    public function actionExportarDetalleAnalisis(Request $request)
    {
        $anio = $request->input('anio');
        $mes = $request->input('mes');
        $calidad = $request->input('calidad');

        $query = DB::table('acopio_arroz_raw')
            ->when($anio, function ($q) use ($anio) {
                return $q->where('anio_compra', $anio);
            })
            ->when($mes, function ($q) use ($mes) {
                return $q->where('mes_numero_compra', $mes);
            })
            ->when($calidad, function ($q) use ($calidad) {
                return $q->where('calidad', $calidad);
            });

        $data = $query->get();

        if ($data->isEmpty()) {
            return "No hay datos para exportar";
        }

        $filename = "detalle_analisis_calidad_" . date('Ymd_His') . ".csv";
        $headers = array(
            "Content-type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            $columns = array_keys((array) $data[0]);
            fputcsv($file, $columns, ';');
            foreach ($data as $row) {
                fputcsv($file, (array) $row, ';');
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }


}