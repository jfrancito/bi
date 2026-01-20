<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use App\Traits\UserTraits;

class AcopioTendenciaController extends Controller
{
    use UserTraits;

    public function actionListarTendencia($idopcion)
    {
        /******************* validar url **********************/
        $validarurl = $this->user_url($idopcion, 'ver');
        if ($validarurl != 'true') {
            return $validarurl;
        }
        /******************************************************/
        View::share('titulo', 'Tendencia');

        // Fetch filter data
        $anios = DB::table('acopio_arroz_raw')
            ->select('anio_compra')
            ->whereNotNull('anio_compra')
            ->distinct()
            ->orderBy('anio_compra', 'desc')
            ->get();

        $centros = DB::table('acopio_arroz_raw')
            ->select('centro_acopio_nombre')
            ->whereNotNull('centro_acopio_nombre')
            ->distinct()
            ->orderBy('centro_acopio_nombre', 'asc')
            ->get();

        $meses = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];

        $ultima_actualizacion = DB::table('acopio_arroz_raw')->max('fecha_entrada');

        return View::make(
            'pages/acopio/tendencia',
            [
                'idopcion' => $idopcion,
                'anios' => $anios,
                'centros' => $centros,
                'meses' => $meses,
                'ultima_actualizacion' => $ultima_actualizacion ? date('d/m/Y H:i', strtotime($ultima_actualizacion)) : 'N/A'
            ]
        );
    }

    public function actionAjaxListarTendencia(Request $request)
    {
        try {
            $anio = $request->input('anio');
            $mes = $request->input('mes');
            $centro = $request->input('centro');

            // Daily data for Chart 1
            $dailyQuery = DB::table('acopio_arroz_raw')
                ->select(
                    'dia_numero_compra',
                    DB::raw('SUM(importe_total_compra) as total_importe'),
                    DB::raw('SUM(peso_humedo_kg) as total_peso_humedo'),
                    DB::raw('SUM(peso_seco_kg) as total_peso_seco'),
                    DB::raw('SUM(precio_compra_inicial_kg * peso_humedo_kg) as total_compra_ponderada')
                )
                ->when($anio, function ($q) use ($anio) {
                    $values = array_filter(explode(',', $anio));
                    return empty($values) ? $q : $q->whereIn('anio_compra', $values);
                })
                ->when($mes, function ($q) use ($mes) {
                    $values = array_filter(explode(',', $mes));
                    return empty($values) ? $q : $q->whereIn('mes_numero_compra', $values);
                })
                ->when($centro, function ($q) use ($centro) {
                    $values = array_filter(explode(',', $centro));
                    return empty($values) ? $q : $q->whereIn('centro_acopio_nombre', $values);
                })
                ->groupBy('dia_numero_compra')
                ->orderBy('dia_numero_compra', 'asc');

            // Monthly data for Chart 2
            $monthlyQuery = DB::table('acopio_arroz_raw')
                ->select(
                    'mes_numero_compra',
                    DB::raw('SUM(importe_total_compra) as total_importe'),
                    DB::raw('SUM(peso_humedo_kg) as total_peso_humedo'),
                    DB::raw('SUM(peso_seco_kg) as total_peso_seco')
                )
                ->when($anio, function ($q) use ($anio) {
                    $values = array_filter(explode(',', $anio));
                    return empty($values) ? $q : $q->whereIn('anio_compra', $values);
                })
                ->when($centro, function ($q) use ($centro) {
                    $values = array_filter(explode(',', $centro));
                    return empty($values) ? $q : $q->whereIn('centro_acopio_nombre', $values);
                })
                ->groupBy('mes_numero_compra')
                ->orderBy('mes_numero_compra', 'asc');

            return response()->json([
                'daily' => $dailyQuery->get(),
                'monthly' => $monthlyQuery->get()
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}