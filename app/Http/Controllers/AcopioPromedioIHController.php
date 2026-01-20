<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use App\Traits\UserTraits;

class AcopioPromedioIHController extends Controller
{
    use UserTraits;

    public function actionListarPromedioIH($idopcion)
    {
        /******************* validar url **********************/
        $validarurl = $this->user_url($idopcion, 'ver');
        if ($validarurl != 'true') {
            return $validarurl;
        }
        /******************************************************/
        View::share('titulo', 'Promedio I-H');

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
            'pages/acopio/promedioih',
            [
                'idopcion' => $idopcion,
                'anios' => $anios,
                'meses' => $meses,
                'centros' => $centros,
                'ultima_actualizacion' => $ultima_actualizacion ? date('d/m/Y H:i', strtotime($ultima_actualizacion)) : 'N/A'
            ]
        );
    }

    public function actionAjaxListarPromedioIH(Request $request)
    {
        $anio = $request->input('anio');
        $centro = $request->input('centro');

        $query = DB::table('acopio_arroz_raw')
            ->select(
                'centro_acopio_nombre',
                'anio_compra',
                'mes_numero_compra',
                DB::raw('SUM(peso_humedo_kg) as total_peso_humedo'),
                DB::raw('SUM(peso_seco_kg) as total_peso_seco'),
                DB::raw('SUM(importe_total_compra) as total_importe'),
                DB::raw('SUM(peso_humedo_kg * impurezas_porcentaje) as total_impurezas_w'),
                DB::raw('SUM(peso_humedo_kg * humedad_porcentaje) as total_humedad_w')
            )
            ->when($anio, function ($q) use ($anio) {
                $values = is_array($anio) ? $anio : array_filter(explode(',', $anio));
                return empty($values) ? $q : $q->whereIn('anio_compra', $values);
            })
            ->when($centro, function ($q) use ($centro) {
                $values = is_array($centro) ? $centro : array_filter(explode(',', $centro));
                return empty($values) ? $q : $q->whereIn('centro_acopio_nombre', $values);
            })
            ->groupBy('centro_acopio_nombre', 'anio_compra', 'mes_numero_compra')
            ->orderBy('centro_acopio_nombre')
            ->orderBy('anio_compra', 'desc')
            ->orderBy('mes_numero_compra', 'asc');

        $data = $query->get();

        return response()->json($data);
    }

    public function actionExportarDetallePromedioIH(Request $request)
    {
        $anio_filtro = $request->input('anio_filtro');
        $centro_filtro = $request->input('centro_filtro');
        $anio_clic = $request->input('anio_clic');
        $centro_clic = $request->input('centro_clic');
        $mes_clic = $request->input('mes_clic');

        $query = DB::table('acopio_arroz_raw')
            ->when($anio_filtro, function ($q) use ($anio_filtro) {
                $values = array_filter(explode(',', $anio_filtro));
                return empty($values) ? $q : $q->whereIn('anio_compra', $values);
            })
            ->when($centro_filtro, function ($q) use ($centro_filtro) {
                $values = array_filter(explode(',', $centro_filtro));
                return empty($values) ? $q : $q->whereIn('centro_acopio_nombre', $values);
            })
            ->when($anio_clic, function ($q) use ($anio_clic) {
                return $q->where('anio_compra', $anio_clic);
            })
            ->when($centro_clic, function ($q) use ($centro_clic) {
                return $q->where('centro_acopio_nombre', $centro_clic);
            })
            ->when($mes_clic, function ($q) use ($mes_clic) {
                return $q->where('mes_numero_compra', $mes_clic);
            });

        $data = $query->get();

        if ($data->isEmpty()) {
            return "No hay datos para exportar";
        }

        $filename = "detalle_promedio_ih_" . date('Ymd_His') . ".csv";
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

            // Convert first row to array to get keys correctly
            $firstRow = (array) $data[0];
            $columns = array_keys($firstRow);
            fputcsv($file, $columns, ';');

            foreach ($data as $row) {
                fputcsv($file, (array) $row, ';');
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}