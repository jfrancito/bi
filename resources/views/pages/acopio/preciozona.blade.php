@extends('layouts.app')
@section('title', 'Precio Zona')
@section('page-title', 'Precio Zona')
@section('breadcrumb')
    <div class="breadcrumb-card mb-25 d-md-flex align-items-center justify-content-between">
        <h5 class="mb-0">Precio Zona</h5>
        <ol class="breadcrumb list-unstyled mt-0 mb-0 p-0">
            <li class="breadcrumb-item d-inline-block position-relative">
                <a href="{{ url('/') }}" class="d-inline-block position-relative">
                    <i class="material-symbols-outlined">home</i>
                    Inicio
                </a>
            </li>
            <li class="breadcrumb-item d-inline-block position-relative">
                Precio Zona
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
                    <i class="material-symbols-outlined align-middle me-1">map</i>
                    Acopio por Zona y Centro
                </h4>
                <span class="badge bg-primary-transparent text-primary fw-medium px-3 py-2 rounded-pill">
                    <i class="ri-checkbox-circle-line align-middle me-1"></i> Actualizado al: {{ $ultima_actualizacion }}
                </span>
            </div>

            <!-- Filtros -->
            <div class="row mb-4 align-items-center">
                <div class="col-lg-12">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="filter-box p-2 border rounded">
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
                        <div class="col-md-4">
                            <div class="filter-box p-2 border rounded">
                                <h6 class="filter-title mb-2">Mes <button
                                        class="btn btn-sm btn-link p-0 float-end clear-filter" data-filter="mes"><i
                                            class="material-symbols-outlined fs-6">filter_alt_off</i></button></h6>
                                <div class="d-flex flex-wrap gap-1 filter-buttons" id="filter-mes">
                                    @foreach($meses as $m)
                                        <button class="btn btn-outline-primary btn-sm btn-filter"
                                            data-value="{{ $m->mes_numero_compra }}"
                                            title="{{ $m->mes_compra }}">{{ $m->mes_numero_compra }}</button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="filter-box p-2 border rounded">
                                <h6 class="filter-title mb-2">Centro <button
                                        class="btn btn-sm btn-link p-0 float-end clear-filter" data-filter="centro"><i
                                            class="material-symbols-outlined fs-6">filter_alt_off</i></button></h6>
                                <div class="d-flex flex-wrap gap-1 filter-buttons" id="filter-centro">
                                    @foreach($centros as $c)
                                        <button class="btn btn-outline-primary btn-sm btn-filter"
                                            data-value="{{ $c->centro_acopio_nombre }}">{{ substr($c->centro_acopio_nombre, 0, 2) }}</button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navegación por Pestañas Estilizada -->
            <ul class="nav nav-tabs border-0 mb-4 gap-2" id="reportTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active rounded-pill px-4 fw-bold shadow-sm" id="zone-tab" data-bs-toggle="tab"
                        data-bs-target="#zone-content" type="button" role="tab">
                        <i class="material-symbols-outlined align-middle me-1">map</i> Resumen por Zona
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill px-4 fw-bold shadow-sm" id="comparison-tab" data-bs-toggle="tab"
                        data-bs-target="#comparison-content" type="button" role="tab">
                        <i class="material-symbols-outlined align-middle me-1">equalizer</i> Comparativo Mensual de Precios
                    </button>
                </li>
            </ul>

            <div class="row mb-3">
                <div class="col-12 text-end">
                    <div class="export-hint">
                        <i class="material-symbols-outlined fs-6 me-1">info</i>
                        💡 Haga doble clic en las filas de zona o celdas de mes para descargar el detalle.
                    </div>
                </div>
            </div>

            <div class="tab-content" id="reportTabsContent">
                <!-- Tab 1: Resumen por Zona -->
                <div class="tab-pane fade show active" id="zone-content" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3 mt-4">
                        <h5 class="text-secondary fw-bold mb-0">
                            <i class="material-symbols-outlined align-middle me-1">table_chart</i>
                            Cuadro Resumen: Acopio por Zona Comercial y Centro
                        </h5>
                        <button id="btn-export-price-zona" class="btn btn-success btn-sm shadow-sm">
                            <i class="material-symbols-outlined align-middle fs-6">download</i> EXPORTAR
                        </button>
                    </div>

                    <div class="table-responsive table-container-horizontal">
                        <table id="tbl-precio-zona" class="table table-bordered table-hover align-middle">
                            <thead>
                                <tr>
                                    <th class="sticky-col-1 border-end-dark" style="width: 80px;">AÑO</th>
                                    <th class="sticky-col-2 border-end-dark" style="width: 100px;">CENTRO</th>
                                    <th class="sticky-col-3 border-end-dark" style="width: 120px;">ZONA</th>
                                    <th class="text-end">P. Unit. Húmedo</th>
                                    <th class="text-end">T. Peso Húmedo</th>
                                    <th class="text-end">Total Compra</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot>
                                <tr>
                                    <td class="sticky-col-1 border-end-dark" colspan="3">Total General</td>
                                    <td class="text-end" id="total-p-unt-h">0.00</td>
                                    <td class="text-end" id="total-peso-h">0.00</td>
                                    <td class="text-end" id="total-compra">0.00</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Tab 2: Comparativo Mensual de Precios -->
                <div class="tab-pane fade" id="comparison-content" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3 mt-4">
                        <h5 class="text-secondary fw-bold mb-0">
                            <i class="material-symbols-outlined align-middle me-1">query_stats</i>
                            Análisis Mensual: Precio Húmedo y Seco por Centro
                        </h5>
                        <button id="btn-export-comparison" class="btn btn-success btn-sm shadow-sm">
                            <i class="material-symbols-outlined align-middle fs-6">download</i> EXPORTAR
                        </button>
                    </div>

                    <div class="table-responsive table-container-horizontal">
                        <table id="tbl-comparativo-precios" class="table table-bordered align-middle">
                            <thead>
                                <tr id="comp-header-centers">
                                    <th rowspan="2" class="sticky-col-1 border-end-dark align-middle"
                                        style="min-width: 140px;">MES</th>
                                </tr>
                                <tr id="comp-header-metrics"></tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot id="comp-footer"></tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <input type="hidden" id="token" value="{{ csrf_token() }}">
@endsection

        @push('scripts')
            <script src="{{ asset('assets/js/reporte/preciozona.js?v=' . $version) }}" type="text/javascript"></script>
        @endpush