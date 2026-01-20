<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use App\Traits\UserTraits;

class AcopioDashboardController extends Controller
{
    use UserTraits;

    public function actionAjaxGetDashboardMetrics(Request $request)
    {
        try {
            $anio = $request->input('anio');
            $centro = $request->input('centro');

            // Default year logic for initial load
            if (empty($anio)) {
                $currentYear = date('Y');
                $exists = DB::table('acopio_arroz_raw')->where('anio_compra', $currentYear)->exists();
                $anio = $exists ? $currentYear : DB::table('acopio_arroz_raw')->max('anio_compra');
            }

            $queryBase = DB::table('acopio_arroz_raw')
                ->when($anio, function ($q) use ($anio) {
                    $values = array_filter(explode(',', $anio));
                    return empty($values) ? $q : $q->whereIn('anio_compra', $values);
                })
                ->when($centro, function ($q) use ($centro) {
                    $values = array_filter(explode(',', $centro));
                    return empty($values) ? $q : $q->whereIn('centro_acopio_nombre', $values);
                });

            // Overall KPIs
            $kpis = (clone $queryBase)
                ->select(
                    DB::raw('SUM(peso_humedo_kg) as total_peso_humedo'),
                    DB::raw('SUM(peso_seco_kg) as total_peso_seco'),
                    DB::raw('SUM(importe_total_compra) as total_importe'),
                    DB::raw('AVG(humedad_porcentaje) as avg_humedad'),
                    DB::raw('AVG(impurezas_porcentaje) as avg_impurezas'),
                    DB::raw('COUNT(*) as total_registros')
                )
                ->first();

            // Weight by Variety
            $byVariety = (clone $queryBase)
                ->select('variedad_arroz', DB::raw('SUM(peso_humedo_kg) as peso'))
                ->groupBy('variedad_arroz')
                ->orderBy('peso', 'desc')
                ->limit(5)
                ->get();

            // Investment by Center
            $byCenter = (clone $queryBase)
                ->select(
                    'centro_acopio_nombre',
                    DB::raw('SUM(peso_humedo_kg) as peso'),
                    DB::raw('SUM(importe_total_compra) as inversion')
                )
                ->groupBy('centro_acopio_nombre')
                ->orderBy('inversion', 'desc')
                ->get();

            // Monthly Trend
            $monthlyTrend = (clone $queryBase)
                ->select(
                    'anio_compra',
                    'mes_numero_compra',
                    DB::raw('SUM(peso_humedo_kg) as peso'),
                    DB::raw('SUM(importe_total_compra) / NULLIF(SUM(peso_humedo_kg), 0) as precio_promedio')
                )
                ->groupBy('anio_compra', 'mes_numero_compra')
                ->orderBy('anio_compra', 'asc')
                ->orderBy('mes_numero_compra', 'asc')
                ->get();

            // Filters Data
            $anios = DB::table('acopio_arroz_raw')->select('anio_compra')->distinct()->orderBy('anio_compra', 'desc')->pluck('anio_compra');
            $centros = DB::table('acopio_arroz_raw')->select('centro_acopio_nombre')->distinct()->orderBy('centro_acopio_nombre', 'asc')->pluck('centro_acopio_nombre');

            return response()->json([
                'kpis' => $kpis,
                'byVariety' => $byVariety,
                'byCenter' => $byCenter,
                'monthlyTrend' => $monthlyTrend,
                'filters' => [
                    'anios' => $anios,
                    'centros' => $centros
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
