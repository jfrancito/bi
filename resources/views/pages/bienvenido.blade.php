@extends('layouts.app')

@section('title', 'BI - Dashboard')

@section('page-title', 'Dashboard Principal')

@section('breadcrumb')
    <div class="breadcrumb-card mb-25 d-md-flex align-items-center justify-content-between">
        <h5 class="mb-0">Panel de Control Estratégico</h5>
        <ol class="breadcrumb list-unstyled mt-0 mb-0 p-0">
            <li class="breadcrumb-item d-inline-block position-relative">
                <a href="{{ url('/') }}" class="d-inline-block position-relative">
                    <i class="material-symbols-outlined">home</i>
                    Inicio
                </a>
            </li>
            <li class="breadcrumb-item d-inline-block position-relative">Dashboard</li>
        </ol>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/reporte.css?v=' . $version) }}">
    <style>
        .kpi-card {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
            position: relative;
            overflow: hidden;
            border-left: 5px solid #3e64ff;
            height: 100%;
        }

        .kpi-card:hover {
            transform: translateY(-5px);
        }

        .kpi-icon {
            position: absolute;
            right: 15px;
            bottom: 10px;
            font-size: 3rem;
            opacity: 0.1;
            color: #3e64ff;
        }

        .kpi-value {
            font-size: 1.6rem;
            font-weight: 800;
            color: #333;
            margin: 5px 0;
        }

        .kpi-label {
            font-size: 0.8rem;
            color: #888;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .dashboard-tab {
            border: none !important;
            color: #777 !important;
            font-weight: 600 !important;
            padding: 12px 25px !important;
            border-radius: 12px 12px 0 0 !important;
            transition: all 0.3s ease;
            background: #e2e8f0 !important;
            margin-right: 5px;
        }

        .dashboard-tab.active {
            background: #fff !important;
            color: #3e64ff !important;
            box-shadow: 0 -4px 15px rgba(0, 0, 0, 0.05);
        }

        .tab-content-container {
            background: #fff;
            border-radius: 0 12px 12px 12px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }

        .chart-container {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            border: 1px solid #f0f0f0;
            height: 100%;
            position: relative;
        }

        .chart-title {
            font-size: 0.95rem;
            font-weight: 700;
            margin-bottom: 15px;
            color: #2c3e50;
            display: flex;
            align-items: center;
        }

        .chart-explanation {
            font-size: 0.8rem;
            color: #7f8c8d;
            background: #fdfefe;
            padding: 10px;
            border-radius: 8px;
            border-top: 1px dashed #eee;
            margin-top: 15px;
            font-style: italic;
        }
    </style>
@endpush

@section('content')
    <div class="row mb-4">
        <div class="col-12">
            <!-- Pestañas de Módulos -->
            <ul class="nav nav-tabs border-0" id="dashboardTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link dashboard-tab active" data-bs-toggle="tab" data-bs-target="#acopio"
                        type="button"><i class="ri-truck-line me-1"></i> ACOPIO</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link dashboard-tab disabled" type="button">VENTAS</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link dashboard-tab disabled" type="button">LOGÍSTICA</button>
                </li>
            </ul>

            <div class="tab-content tab-content-container">
                <!-- ACOPIO TAB -->
                <div class="tab-pane fade show active" id="acopio">

                    <!-- Filtros con estilo Estándar (exclusivos de Acopio) -->
                    <div class="row mb-4">
                        <div class="col-lg-12">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <div class="filter-box p-3 border rounded shadow-sm h-100">
                                        <h6 class="filter-title mb-2">Año <button
                                                class="btn btn-sm btn-link p-0 float-end clear-filter" data-filter="anio"><i
                                                    class="material-symbols-outlined fs-6">filter_alt_off</i></button></h6>
                                        <div class="d-flex flex-wrap gap-1 filter-buttons" id="dash-filter-anio">
                                            <!-- Cargado vía AJAX -->
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-9">
                                    <div class="filter-box p-3 border rounded shadow-sm h-100">
                                        <h6 class="filter-title mb-2">Centro de Acopio <button
                                                class="btn btn-sm btn-link p-0 float-end clear-filter"
                                                data-filter="centro"><i
                                                    class="material-symbols-outlined fs-6">filter_alt_off</i></button></h6>
                                        <div class="d-flex flex-wrap gap-1 filter-buttons" id="dash-filter-centro">
                                            <!-- Cargado vía AJAX -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- KPI Headline -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <div class="kpi-card">
                                <i class="material-symbols-outlined kpi-icon">scale</i>
                                <div class="kpi-label">Volumen Acopiado</div>
                                <div class="kpi-value" id="kpi-peso-h">...</div>
                                <div class="small text-muted">Peso húmedo total recibido a planta.</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="kpi-card" style="border-left-color: #28a745;">
                                <i class="material-symbols-outlined kpi-icon" style="color: #28a745;">payments</i>
                                <div class="kpi-label">Capital Invertido</div>
                                <div class="kpi-value" id="kpi-inversion">...</div>
                                <div class="small text-muted">Costo total de adquisición de materia prima.</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="kpi-card" style="border-left-color: #ffc107;">
                                <i class="material-symbols-outlined kpi-icon" style="color: #ffc107;">water_drop</i>
                                <div class="kpi-label">Calidad Promedio</div>
                                <div class="kpi-value" id="kpi-humedad">...</div>
                                <div class="small text-muted">Humedad % detectada en recepción.</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="kpi-card" style="border-left-color: #dc3545;">
                                <i class="material-symbols-outlined kpi-icon" style="color: #dc3545;">local_shipping</i>
                                <div class="kpi-label">Flujo de Recepción</div>
                                <div class="kpi-value" id="kpi-viajes">...</div>
                                <div class="small text-muted">Cantidad total de viajes/lotes procesados.</div>
                            </div>
                        </div>
                    </div>

                    <!-- Main Trends Row -->
                    <div class="row g-4 mb-4">
                        <div class="col-lg-8">
                            <div class="chart-container">
                                <div class="chart-title"><i class="ri-line-chart-line text-primary me-2"></i>TENDECIA
                                    MENSUAL: VOLUMEN VS PRECIO</div>
                                <div id="chart-main-trend" style="height: 350px;"></div>
                                <div class="chart-explanation">
                                    <strong>Para Gerencia:</strong> Este gráfico cruza el volumen de entrada con el precio
                                    promedio pagado. Útil para identificar si estamos comprando más caro en picos de cosecha
                                    o si el precio se mantiene estable ante aumentos de volumen.
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="chart-container">
                                <div class="chart-title"><i class="ri-compass-3-line text-primary me-2"></i>INVERSIÓN POR
                                    CENTRO (S/.)</div>
                                <div id="chart-center-dist" style="height: 350px;"></div>
                                <div class="chart-explanation">
                                    Identifica dónde se está invirtiendo más capital. Crucial para gerentes financieros que
                                    necesitan ver la distribución del presupuesto por zona de acopio.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Details Row -->
                    <div class="row g-4">
                        <div class="col-lg-6">
                            <div class="chart-container">
                                <div class="chart-title"><i class="ri-medal-line text-primary me-2"></i>PREFERENCIAS DE
                                    VARIEDAD</div>
                                <div id="chart-variety-dist" style="height: 300px;"></div>
                                <div class="chart-explanation">
                                    Distribución de las 5 variedades más compradas. Permite planificar la producción
                                    basándose en el tipo de grano mayoritario en almacén.
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="chart-container">
                                <div class="chart-title"><i class="ri-shield-check-line text-primary me-2"></i>RADAR DE
                                    CALIDAD ESTRATÉGICA</div>
                                <div id="chart-quality-radar" style="height: 300px;"></div>
                                <div class="chart-explanation">
                                    Compara visualmente los parámetros críticos de calidad. Un área más grande indica mayor
                                    desviación, lo que requiere atención técnica inmediata.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" id="token" value="{{ csrf_token() }}">
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/reporte/bienvenido.js?v=' . $version) }}" type="text/javascript"></script>
@endpush