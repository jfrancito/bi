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

class ResumenCalidadController extends Controller
{
    use UserTraits;
    public function actionListarResumenCalidad($idopcion)
    {
        /******************* validar url **********************/
        $validarurl = $this->user_url($idopcion, 'ver');
        if ($validarurl != 'true') {
            return $validarurl;
        }
        /******************************************************/
        View::share('titulo', 'Resumen de Calidad');

        $anios = DB::table('acopio_arroz_raw')
            ->select('anio_compra')
            ->distinct()
            ->orderBy('anio_compra', 'desc')
            ->get();

        $meses = DB::table('acopio_arroz_raw')
            ->select('mes_numero_compra', 'mes_compra')
            ->distinct()
            ->orderBy('mes_numero_compra', 'asc')
            ->get();

        $centros = DB::table('acopio_arroz_raw')
            ->select('centro_acopio_nombre')
            ->distinct()
            ->orderBy('centro_acopio_nombre', 'asc')
            ->get();

        $ultima_actualizacion = DB::table('acopio_arroz_raw')->max('fecha_entrada');

        return View::make(
            'pages/acopio/resumencalidad',
            [
                'idopcion' => $idopcion,
                'anios' => $anios,
                'meses' => $meses,
                'centros' => $centros,
                'ultima_actualizacion' => $ultima_actualizacion ? date('d/m/Y H:i', strtotime($ultima_actualizacion)) : 'N/A'
            ]
        );

    }

    public function actionAjaxListarResumenCalidad(Request $request)
    {
        $anio = $request->input('anio');
        $mes = $request->input('mes');
        $centro = $request->input('centro');

        $query = DB::table('acopio_arroz_raw')
            ->select(
                'centro_acopio_nombre',
                'variedad_arroz',
                'calidad',
                DB::raw('SUM(peso_humedo_kg) as total_peso_humedo'),
                DB::raw('SUM(peso_seco_kg) as total_peso_seco'),
                DB::raw('SUM(importe_total_compra) as total_importe')
            )
            ->when($anio, function ($q) use ($anio) {
                $values = is_array($anio) ? $anio : array_filter(explode(',', $anio));
                return empty($values) ? $q : $q->whereIn('anio_compra', $values);
            })
            ->when($mes, function ($q) use ($mes) {
                $values = is_array($mes) ? $mes : array_filter(explode(',', $mes));
                return empty($values) ? $q : $q->whereIn('mes_numero_compra', $values);
            })
            ->when($centro, function ($q) use ($centro) {
                $values = is_array($centro) ? $centro : array_filter(explode(',', $centro));
                return empty($values) ? $q : $q->whereIn('centro_acopio_nombre', $values);
            })
            ->groupBy('centro_acopio_nombre', 'variedad_arroz', 'calidad')
            ->orderBy('centro_acopio_nombre')
            ->orderBy('variedad_arroz')
            ->orderBy('calidad');

        $data = $query->get();

        return response()->json($data);
    }

    public function actionAjaxResumenMensual(Request $request)
    {
        $anio = $request->input('anio');
        // Solo regido por año según solicitud

        $query = DB::table('acopio_arroz_raw')
            ->select(
                'centro_acopio_nombre',
                'mes_numero_compra',
                'mes_compra',
                DB::raw('SUM(peso_humedo_kg) as total_peso_humedo'),
                DB::raw('SUM(importe_total_compra) as total_importe')
            )
            ->when($anio, function ($q) use ($anio) {
                $values = is_array($anio) ? $anio : array_filter(explode(',', $anio));
                return empty($values) ? $q : $q->whereIn('anio_compra', $values);
            })
            ->groupBy('centro_acopio_nombre', 'mes_numero_compra', 'mes_compra')
            ->orderBy('centro_acopio_nombre')
            ->orderBy('mes_numero_compra');

        $data = $query->get();
        return response()->json($data);
    }

    public function actionAjaxResumenCalidadMensual(Request $request)
    {
        $anio = $request->input('anio');

        $query = DB::table('acopio_arroz_raw')
            ->select(
                'calidad',
                'mes_numero_compra',
                'mes_compra',
                DB::raw('SUM(peso_humedo_kg) as total_peso_humedo'),
                DB::raw('SUM(importe_total_compra) as total_importe')
            )
            ->when($anio, function ($q) use ($anio) {
                $values = is_array($anio) ? $anio : array_filter(explode(',', $anio));
                return empty($values) ? $q : $q->whereIn('anio_compra', $values);
            })
            ->groupBy('calidad', 'mes_numero_compra', 'mes_compra')
            ->orderBy('calidad')
            ->orderBy('mes_numero_compra');

        $data = $query->get();
        return response()->json($data);
    }

    public function actionExportarDetalleResumenCalidad(Request $request)
    {
        $anio = $request->input('anio');
        $mes = $request->input('mes');
        $centro_filtro = $request->input('centro_filtro'); // Filtros de la cabecera

        // Especificos del clic
        $centro = $request->input('centro');
        $variedad = $request->input('variedad');
        $calidad = $request->input('calidad');

        $query = DB::table('acopio_arroz_raw')
            ->when($anio, function ($q) use ($anio) {
                $values = is_array($anio) ? $anio : array_filter(explode(',', $anio));
                return empty($values) ? $q : $q->whereIn('anio_compra', $values);
            })
            ->when($mes, function ($q) use ($mes) {
                $values = is_array($mes) ? $mes : array_filter(explode(',', $mes));
                return empty($values) ? $q : $q->whereIn('mes_numero_compra', $values);
            })
            ->when($centro_filtro, function ($q) use ($centro_filtro) {
                $values = is_array($centro_filtro) ? $centro_filtro : array_filter(explode(',', $centro_filtro));
                return empty($values) ? $q : $q->whereIn('centro_acopio_nombre', $values);
            })
            ->when($centro, function ($q) use ($centro) {
                return $q->where('centro_acopio_nombre', $centro);
            })
            ->when($variedad, function ($q) use ($variedad) {
                return $q->where('variedad_arroz', $variedad);
            })
            ->when($calidad, function ($q) use ($calidad) {
                return $q->where('calidad', $calidad);
            });

        $data = $query->get();

        if ($data->isEmpty()) {
            return "No hay datos para exportar";
        }

        $filename = "detalle_acopio_" . date('Ymd_His') . ".csv";
        $headers = array(
            "Content-type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');

            // BOM for Excel compatibility with UTF-8
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Get columns from first row
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