@extends('layouts.app')
@section('title', 'Resumen Calidad')
@section('page-title', 'Resumen Calidad')
@section('breadcrumb')
    <div class="breadcrumb-card mb-25 d-md-flex align-items-center justify-content-between">
        <h5 class="mb-0">Resumen Calidad</h5>
        <ol class="breadcrumb list-unstyled mt-0 mb-0 p-0">
            <li class="breadcrumb-item d-inline-block position-relative">
                <a href="{{ url('/') }}" class="d-inline-block position-relative">
                    <i class="material-symbols-outlined">home</i>
                    Inicio
                </a>
            </li>
            <li class="breadcrumb-item d-inline-block position-relative">
                Resumen Calidad
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
                    Volumen de Compras por Calidad y Variedad
                </h4>
                <span class="badge bg-primary-transparent text-primary fw-medium px-3 py-2 rounded-pill">
                    <i class="ri-checkbox-circle-line align-middle me-1"></i> Actualizado al: {{ $ultima_actualizacion }}
                </span>
            </div>
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
                    <button class="nav-link active rounded-pill px-4 fw-bold shadow-sm" id="general-tab"
                        data-bs-toggle="tab" data-bs-target="#general-content" type="button" role="tab">
                        <i class="material-symbols-outlined align-middle me-1">dashboard</i> Resumen General
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill px-4 fw-bold shadow-sm" id="monthly-tab" data-bs-toggle="tab"
                        data-bs-target="#monthly-content" type="button" role="tab">
                        <i class="material-symbols-outlined align-middle me-1">calendar_month</i> Volumen Mensual
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill px-4 fw-bold shadow-sm" id="price-tab" data-bs-toggle="tab"
                        data-bs-target="#price-content" type="button" role="tab">
                        <i class="material-symbols-outlined align-middle me-1">payments</i> Tendencia de Precios
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="reportTabsContent">
                <!-- Tab 1: Resumen General -->
                <div class="tab-pane fade show active" id="general-content" role="tabpanel">
                    <div class="row">
                        <!-- Tabla 1: Detalle -->
                        <div class="col-lg-12 mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="text-secondary fw-bold mb-0">Acopio por Centro, Variedad y Calidad</h5>
                                <button id="btn-export-summary" class="btn btn-success btn-sm shadow-sm">
                                    <i class="material-symbols-outlined align-middle fs-6">download</i> Exportar
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table id="tbl-resumen-calidad" class="table table-bordered table-hover align-middle">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>CENTRO</th>
                                            <th>VARIEDAD</th>
                                            <th>CALIDAD</th>
                                            <th class="text-end">T Peso H</th>
                                            <th class="text-end">P Unt H</th>
                                            <th class="text-end">T Peso S</th>
                                            <th class="text-end">P Unt S</th>
                                            <th class="text-end">Var H/S</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                    <tfoot class="bg-light fw-bold">
                                        <tr>
                                            <td colspan="3">Total general</td>
                                            <td class="text-end" id="total-peso-h">0.00</td>
                                            <td class="text-end" id="total-p-unt-h">0.00</td>
                                            <td class="text-end" id="total-peso-s">0.00</td>
                                            <td class="text-end" id="total-p-unt-s">0.00</td>
                                            <td class="text-end" id="total-var-hs">0.00%</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <!-- Tabla 2: Resumen Calidad -->
                        <div class="col-lg-12">
                            <div class="d-flex justify-content-between align-items-center mb-3 mt-2">
                                <h5 class="text-secondary fw-bold mb-0">Consolidado por Calidad</h5>
                                <button id="btn-export-summary-calidad" class="btn btn-success btn-sm shadow-sm">
                                    <i class="material-symbols-outlined align-middle fs-6">download</i> Exportar
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table id="tbl-resumen-solo-calidad" class="table table-bordered table-hover align-middle">
                                    <thead class="bg-light">
                                        <tr>
                                            <th colspan="3">CALIDAD</th>
                                            <th class="text-end">T Peso H</th>
                                            <th class="text-end">P Unt H</th>
                                            <th class="text-end">T Peso S</th>
                                            <th class="text-end">P Unt S</th>
                                            <th class="text-end">Var H/S</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                    <tfoot class="bg-light fw-bold">
                                        <tr>
                                            <td colspan="3">Total general</td>
                                            <td class="text-end" id="total-q-peso-h">0.00</td>
                                            <td class="text-end" id="total-q-p-unt-h">0.00</td>
                                            <td class="text-end" id="total-q-peso-s">0.00</td>
                                            <td class="text-end" id="total-q-p-unt-s">0.00</td>
                                            <td class="text-end" id="total-q-var-hs">0.00%</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab 2: Volumen Mensual -->
                <div class="tab-pane fade" id="monthly-content" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="text-secondary fw-bold mb-0">Comparativo Mensual: Peso y Precio por Centro</h5>
                        <button id="btn-export-monthly" class="btn btn-success btn-sm shadow-sm">
                            <i class="material-symbols-outlined align-middle fs-6">download</i> Exportar
                        </button>
                    </div>

                    <div class="filter-box p-3 border rounded mb-3 bg-light-subtle shadow-sm">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fs-13 fw-semibold mb-0">Visibilidad de Meses:</h6>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-secondary py-0 px-2 fs-11"
                                    id="btn-month-all">Ver Todos</button>
                                <button type="button" class="btn btn-outline-secondary py-0 px-2 fs-11"
                                    id="btn-month-none">Ocultar Todos</button>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap gap-1" id="monthly-month-filter">
                            @foreach($meses as $m)
                                <div class="form-check form-check-inline mx-0">
                                    <input class="form-check-input chk-month-toggle" type="checkbox"
                                        id="chk-m-{{ $m->mes_numero_compra }}" value="{{ $m->mes_numero_compra }}" checked>
                                    <label class="form-check-label fs-12"
                                        for="chk-m-{{ $m->mes_numero_compra }}">{{ $m->mes_compra }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="tbl-resumen-mensual" class="table table-bordered table-sm align-middle mb-0">
                            <thead class="bg-light text-center">
                                <tr id="monthly-header-months">
                                    <th rowspan="2" class="align-middle">CENTRO</th>
                                    @foreach($meses as $m)
                                        <th colspan="2" class="col-month-{{ $m->mes_numero_compra }}">{{ $m->mes_compra }}</th>
                                    @endforeach
                                    <th colspan="2">TOTAL ANUAL</th>
                                </tr>
                                <tr id="monthly-header-metrics">
                                    @foreach($meses as $m)
                                        <th class="col-month-{{ $m->mes_numero_compra }} fs-11" style="min-width: 90px;">T Peso
                                            H</th>
                                        <th class="col-month-{{ $m->mes_numero_compra }} fs-11" style="min-width: 80px;">P Unt H
                                        </th>
                                    @endforeach
                                    <th class="fs-11" style="min-width: 100px;">T Peso H</th>
                                    <th class="fs-11" style="min-width: 80px;">P Unt H</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot class="bg-light fw-bold text-end">
                                <tr id="monthly-footer"></tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Tab 3: Tendencia Precios -->
                <div class="tab-pane fade" id="price-content" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="text-secondary fw-bold mb-0">Precio Unitario Húmedo Mensual por Calidad</h5>
                        <button id="btn-export-price-quality" class="btn btn-success btn-sm shadow-sm">
                            <i class="material-symbols-outlined align-middle fs-6">download</i> Exportar
                        </button>
                    </div>

                    <div class="filter-box p-3 border rounded mb-3 bg-light-subtle shadow-sm">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fs-13 fw-semibold mb-0">Visibilidad de Meses:</h6>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-secondary py-0 px-2 fs-11"
                                    id="btn-pq-month-all">Ver Todos</button>
                                <button type="button" class="btn btn-outline-secondary py-0 px-2 fs-11"
                                    id="btn-pq-month-none">Ocultar Todos</button>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap gap-1" id="price-month-filter">
                            @foreach($meses as $m)
                                <div class="form-check form-check-inline mx-0">
                                    <input class="form-check-input chk-pq-month-toggle" type="checkbox"
                                        id="chk-pq-m-{{ $m->mes_numero_compra }}" value="{{ $m->mes_numero_compra }}" checked>
                                    <label class="form-check-label fs-12"
                                        for="chk-pq-m-{{ $m->mes_numero_compra }}">{{ $m->mes_compra }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="tbl-resumen-precios-calidad" class="table table-bordered table-sm align-middle mb-0">
                            <thead class="bg-light text-center">
                                <tr>
                                    <th class="align-middle">CALIDAD</th>
                                    @foreach($meses as $m)
                                        <th class="col-pq-month-{{ $m->mes_numero_compra }} fs-11" style="min-width: 80px;">
                                            {{ $m->mes_compra }}</th>
                                    @endforeach
                                    <th class="fs-11" style="min-width: 80px;">PROM. ANUAL</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot class="bg-light fw-bold text-end">
                                <tr id="price-quality-footer"></tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/reporte/resumencalidad.js?v=' . $version) }}" type="text/javascript"></script>
@endpush