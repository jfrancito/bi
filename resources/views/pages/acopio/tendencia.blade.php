@extends('layouts.app')
@section('title', 'Tendencia')
@section('page-title', 'Tendencia')
@section('breadcrumb')
    <div class="breadcrumb-card mb-25 d-md-flex align-items-center justify-content-between">
        <h5 class="mb-0">Tendencia</h5>
        <ol class="breadcrumb list-unstyled mt-0 mb-0 p-0">
            <li class="breadcrumb-item d-inline-block position-relative">
                <a href="{{ url('/') }}" class="d-inline-block position-relative">
                    <i class="material-symbols-outlined">home</i>
                    Inicio
                </a>
            </li>
            <li class="breadcrumb-item d-inline-block position-relative">
                Tendencia
            </li>
        </ol>
    </div>
@endsection
@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/reporte.css?v=' . $version) }}">
    <style>
        .chart-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            padding: 20px;
            margin-bottom: 25px;
        }
    </style>
@endpush
@section('content')
    <div class="card bg-white border-0 rounded-3 mb-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2">
                <h4 class="text-primary text-uppercase fw-bold mb-0">
                    <i class="material-symbols-outlined align-middle me-1">trending_up</i>
                    Comportamiento de Precios (Tendencia)
                </h4>
                <span class="badge bg-primary-transparent text-primary fw-medium px-3 py-2 rounded-pill">
                    <i class="ri-checkbox-circle-line align-middle me-1"></i> Actualizado al: {{ $ultima_actualizacion }}
                </span>
            </div>

            <!-- Filtros Estandarizados -->
            <div class="row mb-4">
                <div class="col-lg-12">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="filter-box p-3 border rounded shadow-sm h-100">
                                <h6 class="filter-title mb-2">Año <button
                                        class="btn btn-sm btn-link p-0 float-end clear-filter" data-filter="anio"><i
                                            class="material-symbols-outlined fs-6">filter_alt_off</i></button></h6>
                                <div class="d-flex flex-wrap gap-1 filter-buttons" id="filter-anio">
                                    @foreach($anios as $a)
                                        <button class="btn btn-outline-primary btn-sm btn-filter"
                                            data-value="{{ $a->anio_compra }}">{{ $a->anio_compra }}</button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="filter-box p-3 border rounded shadow-sm h-100">
                                <h6 class="filter-title mb-2">Mes <button
                                        class="btn btn-sm btn-link p-0 float-end clear-filter" data-filter="mes"><i
                                            class="material-symbols-outlined fs-6">filter_alt_off</i></button></h6>
                                <div class="d-flex flex-wrap gap-1 filter-buttons" id="filter-mes">
                                    @foreach($meses as $num)
                                        <button class="btn btn-outline-primary btn-sm btn-filter"
                                            data-value="{{ $num }}">{{ $num }}</button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="filter-box p-3 border rounded shadow-sm h-100">
                                <h6 class="filter-title mb-2">Centro <button
                                        class="btn btn-sm btn-link p-0 float-end clear-filter" data-filter="centro"><i
                                            class="material-symbols-outlined fs-6">filter_alt_off</i></button></h6>
                                <div class="d-flex flex-wrap gap-1 filter-buttons" id="filter-centro">
                                    @foreach($centros as $c)
                                        <button class="btn btn-outline-primary btn-sm btn-filter"
                                            data-value="{{ $c->centro_acopio_nombre }}">{{ $c->centro_acopio_nombre }}</button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="chart-card">
                        <h5 class="text-secondary fw-bold mb-3 text-uppercase">Comportamiento del Precio Húmedo y Seco
                            (Diario)</h5>
                        <div id="chart-precios" style="min-height: 400px;">
                            <div class="text-center py-5 loading-spinner">
                                <div class="spinner-border text-primary" role="status"></div>
                                <p class="mt-2">Cargando gráfico...</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="chart-card">
                        <h5 class="text-secondary fw-bold mb-3 text-uppercase">Variación Precio Húmedo y Seco Unitario
                            (Mensual)</h5>
                        <div id="chart-variacion-mensual" style="min-height: 400px;">
                            <div class="text-center py-5 loading-spinner">
                                <div class="spinner-border text-primary" role="status"></div>
                                <p class="mt-2">Cargando gráfico...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <input type="hidden" id="token" value="{{ csrf_token() }}">
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/reporte/tendencia.js?v=' . $version) }}" type="text/javascript"></script>
@endpush