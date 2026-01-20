<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use App\Traits\UserTraits;

class AcopioPrecioUnitarioSecoController extends Controller
{
    use UserTraits;

    public function actionListarPrecioUnitarioSeco($idopcion)
    {
        /******************* validar url **********************/
        $validarurl = $this->user_url($idopcion, 'ver');
        if ($validarurl != 'true') {
            return $validarurl;
        }
        /******************************************************/
        View::share('titulo', 'Precio Unitario Seco');

        // Fetch filter data
        $anios = DB::table('acopio_arroz_raw')
            ->select('anio_compra')
            ->whereNotNull('anio_compra')
            ->distinct()
            ->orderBy('anio_compra', 'desc')
            ->get();

        // Meses números
        $meses = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];

        $ultima_actualizacion = DB::table('acopio_arroz_raw')->max('fecha_entrada');

        return View::make(
            'pages/acopio/preciounitarioseco',
            [
                'idopcion' => $idopcion,
                'anios' => $anios,
                'meses' => $meses,
                'ultima_actualizacion' => $ultima_actualizacion ? date('d/m/Y H:i', strtotime($ultima_actualizacion)) : 'N/A'
            ]
        );
    }

    public function actionAjaxListarPrecioUnitarioSeco(Request $request)
    {
        try {
            $anio = $request->input('anio');
            $mes = $request->input('mes');

            $query = DB::table('acopio_arroz_raw')
                ->select(
                    'centro_acopio_nombre',
                    'variedad_arroz',
                    'dia_numero_compra',
                    DB::raw('SUM(peso_seco_kg) as total_peso_seco'),
                    DB::raw('SUM(importe_total_compra) as total_importe'),
                    DB::raw('MIN(cpus) as min_ps'),
                    DB::raw('MAX(cpus) as max_ps')
                )
                ->when($anio, function ($q) use ($anio) {
                    $values = array_filter(explode(',', $anio));
                    return empty($values) ? $q : $q->whereIn('anio_compra', $values);
                })
                ->when($mes, function ($q) use ($mes) {
                    $values = array_filter(explode(',', $mes));
                    return empty($values) ? $q : $q->whereIn('mes_numero_compra', $values);
                })
                ->groupBy('centro_acopio_nombre', 'variedad_arroz', 'dia_numero_compra')
                ->orderBy('centro_acopio_nombre')
                ->orderBy('variedad_arroz')
                ->orderBy('dia_numero_compra');

            $data = $query->get();
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function actionExportarDetallePrecioUnitarioSeco(Request $request)
    {
        $anio = $request->input('anio');
        $mes = $request->input('mes');
        $centro = $request->input('centro');
        $variedad = $request->input('variedad');
        $dia = $request->input('dia');

        $query = DB::table('acopio_arroz_raw')
            ->when($anio, function ($q) use ($anio) {
                return $q->where('anio_compra', $anio);
            })
            ->when($mes, function ($q) use ($mes) {
                return $q->where('mes_numero_compra', $mes);
            })
            ->when($centro, function ($q) use ($centro) {
                return $q->where('centro_acopio_nombre', $centro);
            })
            ->when($variedad, function ($q) use ($variedad) {
                return $q->where('variedad_arroz', $variedad);
            })
            ->when($dia, function ($q) use ($dia) {
                return $q->where('dia_numero_compra', $dia);
            });

        $data = $query->get();

        if ($data->isEmpty()) {
            return "No hay datos para exportar";
        }

        $filename = "detalle_pu_seco_" . date('Ymd_His') . ".csv";
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