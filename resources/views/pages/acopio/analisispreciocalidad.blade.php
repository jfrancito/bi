@extends('layouts.app')
@section('title', 'Analisis Precio Calidad')
@section('page-title', 'Analisis Precio Calidad')
@section('breadcrumb')
    <div class="breadcrumb-card mb-25 d-md-flex align-items-center justify-content-between">
        <h5 class="mb-0">Analisis Precio Calidad</h5>
        <ol class="breadcrumb list-unstyled mt-0 mb-0 p-0">
            <li class="breadcrumb-item d-inline-block position-relative">
                <a href="{{ url('/') }}" class="d-inline-block position-relative">
                    <i class="material-symbols-outlined">home</i>
                    Inicio
                </a>
            </li>
            <li class="breadcrumb-item d-inline-block position-relative">
                Analisis Precio Calidad
            </li>
        </ol>
    </div>
@endsection
@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/reporte.css?v=' . $version) }}">
@endpush
@section('content')
    <div class="card bg-white border-0 rounded-3 mb-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2">
                <h4 class="text-primary text-uppercase fw-bold mb-0">
                    <i class="material-symbols-outlined align-middle me-1">analytics</i>
                    Análisis de Acopio por Calidad y Mes
                </h4>
                <span class="badge bg-primary-transparent text-primary fw-medium px-3 py-2 rounded-pill">
                    <i class="ri-checkbox-circle-line align-middle me-1"></i> Actualizado al: {{ $ultima_actualizacion }}
                </span>
            </div>

            <!-- Filtros Generales -->
            <div class="row mb-4 align-items-center">
                <div class="col-lg-12">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="filter-box p-3 border rounded shadow-sm">
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
                        <div class="col-md-6">
                            <div class="filter-box p-3 border rounded shadow-sm">
                                <h6 class="filter-title mb-2">Calidad <button
                                        class="btn btn-sm btn-link p-0 float-end clear-filter" data-filter="calidad"><i
                                            class="material-symbols-outlined fs-6">filter_alt_off</i></button></h6>
                                <div class="d-flex flex-wrap gap-1 filter-buttons" id="filter-calidad">
                                    @foreach($calidades as $c)
                                        <button class="btn btn-outline-primary btn-sm btn-filter"
                                            data-value="{{ $c->calidad }}">{{ $c->calidad }}</button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <hr class="my-4">

            <!-- Navegación por Pestañas -->
            <ul class="nav nav-tabs border-0 mb-4 gap-2" id="analisisTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active rounded-pill px-4 fw-bold shadow-sm" id="pesos-tab" data-bs-toggle="tab"
                        data-bs-target="#pesos-content" type="button" role="tab">
                        <i class="material-symbols-outlined align-middle me-1">scale</i> Resumen de Pesos
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill px-4 fw-bold shadow-sm" id="precios-tab" data-bs-toggle="tab"
                        data-bs-target="#precios-content" type="button" role="tab">
                        <i class="material-symbols-outlined align-middle me-1">payments</i> Análisis de Precios (P.U.)
                    </button>
                </li>
            </ul>

            <div class="row mb-3">
                <div class="col-12 text-end">
                    <div class="export-hint">
                        <i class="material-symbols-outlined fs-6 me-1">info</i>
                        💡 Haga doble clic en los años o meses para descargar el detalle granular.
                    </div>
                </div>
            </div>

            <div class="tab-content" id="analisisTabsContent">
                <!-- Tab 1: Pesos -->
                <div class="tab-pane fade show active" id="pesos-content" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="text-secondary fw-bold mb-0">Total Peso Húmedo y Seco por Calidad</h5>
                        <button id="btn-export-pesos" class="btn btn-success btn-sm shadow-sm">
                            <i class="material-symbols-outlined align-middle fs-6">download</i> EXPORTAR
                        </button>
                    </div>

                    <div class="table-responsive table-container-horizontal">
                        <table id="tbl-analisis-pesos" class="table table-bordered align-middle">
                            <thead>
                                <tr class="header-calidades">
                                    <th rowspan="2" class="sticky-col-1 border-end-dark align-middle" style="width: 80px;">
                                        AÑO</th>
                                    <th rowspan="2" class="sticky-col-2 border-end-dark align-middle" style="width: 100px;">
                                        MES</th>
                                </tr>
                                <tr class="header-metrics"></tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot class="footer-analisis"></tfoot>
                        </table>
                    </div>
                </div>

                <!-- Tab 2: Precios -->
                <div class="tab-pane fade" id="precios-content" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="text-secondary fw-bold mb-0">Precio Unitario Húmedo y Seco por Calidad</h5>
                        <button id="btn-export-precios" class="btn btn-success btn-sm shadow-sm">
                            <i class="material-symbols-outlined align-middle fs-6">download</i> EXPORTAR
                        </button>
                    </div>

                    <div class="table-responsive table-container-horizontal">
                        <table id="tbl-analisis-precios" class="table table-bordered align-middle">
                            <thead>
                                <tr class="header-calidades">
                                    <th rowspan="2" class="sticky-col-1 border-end-dark align-middle" style="width: 80px;">
                                        AÑO</th>
                                    <th rowspan="2" class="sticky-col-2 border-end-dark align-middle" style="width: 100px;">
                                        MES</th>
                                </tr>
                                <tr class="header-metrics"></tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot class="footer-analisis"></tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <input type="hidden" id="token" value="{{ csrf_token() }}">
@endsection

        @push('scripts')
            <script src="{{ asset('assets/js/reporte/analisispreciocalidad.js?v=' . $version) }}"
                type="text/javascript"></script>
        @endpush