@extends('layouts.app')
@section('title', 'Promedio I-H')
@section('page-title', 'Promedio I-H')
@section('breadcrumb')
    <div class="breadcrumb-card mb-25 d-md-flex align-items-center justify-content-between">
        <h5 class="mb-0">Promedio I-H</h5>
        <ol class="breadcrumb list-unstyled mt-0 mb-0 p-0">
            <li class="breadcrumb-item d-inline-block position-relative">
                <a href="{{ url('/') }}" class="d-inline-block position-relative">
                    <i class="material-symbols-outlined">home</i>
                    Inicio
                </a>
            </li>
            <li class="breadcrumb-item d-inline-block position-relative">
                Promedio I-H
            </li>
        </ol>
    </div>
@endsection
@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/reporte.css?v=' . $version) }}">
    <style>
        #tbl-promedio-af thead th {
            vertical-align: middle;
            text-align: center;
        }

        .header-imp {
            background-color: rgba(59, 130, 246, 0.05) !important;
            color: var(--executive-blue) !important;
        }

        .header-hum {
            background-color: rgba(16, 185, 129, 0.05) !important;
            color: #059669 !important;
        }

        .cell-imp {
            background-color: rgba(59, 130, 246, 0.01);
        }

        .cell-hum {
            background-color: rgba(16, 185, 129, 0.01);
        }

        .border-left-dark {
            border-left: 2px solid #94a3b8 !important;
        }
    </style>
@endpush
@section('content')
    <div class="card bg-white border-0 rounded-3 mb-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2">
                <h4 class="text-primary text-uppercase fw-bold mb-0">
                    <i class="material-symbols-outlined align-middle me-1">analytics</i>
                    Reporte Promedio I-H (Precio Unitario Húmedo)
                </h4>
                <span class="badge bg-primary-transparent text-primary fw-medium px-3 py-2 rounded-pill">
                    <i class="ri-checkbox-circle-line align-middle me-1"></i> Actualizado al: {{ $ultima_actualizacion }}
                </span>
            </div>

            <!-- Filtros -->
            <div class="row mb-4">
                <div class="col-lg-6">
                    <div class="filter-box p-3 border rounded shadow-sm h-100">
                        <h6 class="filter-title mb-2">Centro <button class="btn btn-sm btn-link p-0 float-end clear-filter"
                                data-filter="centro"><i class="material-symbols-outlined fs-6">filter_alt_off</i></button>
                        </h6>
                        <div class="d-flex flex-wrap gap-1 filter-buttons" id="filter-centro">
                            @foreach($centros as $c)
                                <button class="btn btn-outline-primary btn-sm btn-filter"
                                    data-value="{{ $c->centro_acopio_nombre }}">{{ $c->centro_acopio_nombre }}</button>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="filter-box p-3 border rounded shadow-sm h-100">
                        <h6 class="filter-title mb-2">Año <button class="btn btn-sm btn-link p-0 float-end clear-filter"
                                data-filter="anio"><i class="material-symbols-outlined fs-6">filter_alt_off</i></button>
                        </h6>
                        <div class="d-flex flex-wrap gap-1 filter-buttons" id="filter-anio">
                            @foreach($anios as $a)
                                <button class="btn btn-outline-primary btn-sm btn-filter"
                                    data-value="{{ $a->anio_compra }}">{{ $a->anio_compra }}</button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-12 text-end">
                    <div class="export-hint">
                        <i class="material-symbols-outlined fs-6 me-1">info</i>
                        💡 Haga doble clic en las celdas de las tablas para descargar el detalle granular en Excel.
                    </div>
                </div>
            </div>

            <!-- Tabs Navigation -->
            <ul class="nav nav-tabs border-0 mb-4 gap-2" id="ihTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active rounded-pill px-4 fw-bold shadow-sm" id="humedo-tab" data-bs-toggle="tab"
                        data-bs-target="#humedo-content" type="button" role="tab">
                        <i class="material-symbols-outlined align-middle me-1">water_drop</i> Precio Unitario Húmedo
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill px-4 fw-bold shadow-sm" id="seco-tab" data-bs-toggle="tab"
                        data-bs-target="#seco-content" type="button" role="tab">
                        <i class="material-symbols-outlined align-middle me-1">wb_sunny</i> Precio Unitario Seco
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill px-4 fw-bold shadow-sm" id="var-tab" data-bs-toggle="tab"
                        data-bs-target="#var-content" type="button" role="tab">
                        <i class="material-symbols-outlined align-middle me-1">unfold_more</i> Variación H/S
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill px-4 fw-bold shadow-sm" id="fisico-tab" data-bs-toggle="tab"
                        data-bs-target="#fisico-content" type="button" role="tab">
                        <i class="material-symbols-outlined align-middle me-1">biotech</i> Análisis Físico (Imp/Hum)
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="ihTabsContent">
                <!-- Tab 1: Precio Húmedo -->
                <div class="tab-pane fade show active" id="humedo-content" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="text-secondary fw-bold mb-0">
                            <i class="material-symbols-outlined align-middle me-1">table_chart</i>
                            Promedio Mensual de Precio Unitario Húmedo
                        </h5>
                        <button id="btn-export-promedio-ih" class="btn btn-success btn-sm shadow-sm">
                            <i class="material-symbols-outlined align-middle fs-6">download</i> EXPORTAR
                        </button>
                    </div>

                    <div class="table-responsive table-container-horizontal mb-5">
                        <table id="tbl-promedio-ih" class="table table-bordered table-hover align-middle">
                            <thead>
                                <tr>
                                    <th class="sticky-col-1 border-end-dark" style="width: 140px;">CENTRO</th>
                                    <th class="sticky-col-2 border-end-dark" style="width: 100px;">AÑO</th>
                                    @foreach($meses as $m)
                                        <th class="text-center" style="min-width: 80px;">{{ substr($m->mes_compra, 0, 3) }}</th>
                                    @endforeach
                                    <th class="text-center fw-bold"
                                        style="min-width: 90px; background-color: rgba(59, 130, 246, 0.05);">PROM. GRAL</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot>
                                <tr>
                                    <td class="sticky-col-1 border-end-dark" colspan="2">PROMEDIO GENERAL</td>
                                    @foreach($meses as $m)
                                        <td class="text-end fw-bold" id="total-m-{{ $m->mes_numero_compra }}">0.00</td>
                                    @endforeach
                                    <td class="text-end fw-bold text-primary-value" id="total-g-total"
                                        style="background-color: rgba(59, 130, 246, 0.05);">0.00</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Annual Humedo part removed for brevity, it's already there in my mental model, I will keep it in the real file -->
                    <div class="d-flex justify-content-between align-items-center mb-3 mt-4 pb-2 border-bottom">
                        <h5 class="text-secondary fw-bold mb-0">
                            <i class="material-symbols-outlined align-middle me-1">summarize</i>
                            Consolidado Anual P.U. Húmedo (General)
                        </h5>
                        <button id="btn-export-promedio-ih-anual" class="btn btn-success btn-sm shadow-sm">
                            <i class="material-symbols-outlined align-middle fs-6">download</i> EXPORTAR
                        </button>
                    </div>
                    <div class="table-responsive table-container-horizontal">
                        <table id="tbl-promedio-ih-anual" class="table table-bordered table-hover align-middle">
                            <thead>
                                <tr>
                                    <th class="sticky-col-1 border-end-dark" style="width: 140px;">AÑO</th>
                                    @foreach($meses as $m)
                                        <th class="text-center" style="min-width: 80px;">{{ substr($m->mes_compra, 0, 3) }}</th>
                                    @endforeach
                                    <th class="text-center fw-bold"
                                        style="min-width: 90px; background-color: rgba(59, 130, 246, 0.05);">PROM. GRAL</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot>
                                <tr>
                                    <td class="sticky-col-1 border-end-dark">TOTAL GENERAL</td>
                                    @foreach($meses as $m)
                                        <td class="text-end fw-bold" id="total-anual-m-{{ $m->mes_numero_compra }}">0.00</td>
                                    @endforeach
                                    <td class="text-end fw-bold text-primary-value" id="total-anual-g-total"
                                        style="background-color: rgba(59, 130, 246, 0.05);">0.00</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Tab 2: Precio Seco (Keeping same as before) -->
                <div class="tab-pane fade" id="seco-content" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="text-secondary fw-bold mb-0"><i
                                class="material-symbols-outlined align-middle me-1">grid_view</i> Promedio Mensual de Precio
                            Unitario Seco</h5>
                        <button id="btn-export-promedio-is" class="btn btn-success btn-sm shadow-sm"><i
                                class="material-symbols-outlined align-middle fs-6">download</i> EXPORTAR</button>
                    </div>
                    <div class="table-responsive table-container-horizontal mb-5">
                        <table id="tbl-promedio-is" class="table table-bordered table-hover align-middle">
                            <thead>
                                <tr>
                                    <th class="sticky-col-1 border-end-dark" style="width: 140px;">CENTRO</th>
                                    <th class="sticky-col-2 border-end-dark" style="width: 100px;">AÑO</th>
                                    @foreach($meses as $m) <th class="text-center" style="min-width: 80px;">
                                    {{ substr($m->mes_compra, 0, 3) }}</th> @endforeach
                                    <th class="text-center fw-bold"
                                        style="min-width: 90px; background-color: rgba(22, 163, 74, 0.05);">PROM. GRAL</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot>
                                <tr>
                                    <td class="sticky-col-1 border-end-dark" colspan="2">PROMEDIO GENERAL</td>
                                    @foreach($meses as $m) <td class="text-end fw-bold"
                                    id="total-is-m-{{ $m->mes_numero_compra }}">0.00</td> @endforeach
                                    <td class="text-end fw-bold text-success" id="total-is-g-total"
                                        style="background-color: rgba(22, 163, 74, 0.05);">0.00</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <!-- Annual Seco -->
                    <div class="d-flex justify-content-between align-items-center mb-3 mt-4 pb-2 border-bottom">
                        <h5 class="text-secondary fw-bold mb-0"><i
                                class="material-symbols-outlined align-middle me-1">history_edu</i> Consolidado Anual P.U.
                            Seco (General)</h5>
                        <button id="btn-export-promedio-is-anual" class="btn btn-success btn-sm shadow-sm"><i
                                class="material-symbols-outlined align-middle fs-6">download</i> EXPORTAR</button>
                    </div>
                    <div class="table-responsive table-container-horizontal">
                        <table id="tbl-promedio-is-anual" class="table table-bordered table-hover align-middle">
                            <thead>
                                <tr>
                                    <th class="sticky-col-1 border-end-dark" style="width: 140px;">AÑO</th>
                                    @foreach($meses as $m) <th class="text-center" style="min-width: 80px;">
                                    {{ substr($m->mes_compra, 0, 3) }}</th> @endforeach
                                    <th class="text-center fw-bold"
                                        style="min-width: 90px; background-color: rgba(22, 163, 74, 0.05);">PROM. GRAL</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot>
                                <tr>
                                    <td class="sticky-col-1 border-end-dark">TOTAL GENERAL</td>
                                    @foreach($meses as $m) <td class="text-end fw-bold"
                                    id="total-is-anual-m-{{ $m->mes_numero_compra }}">0.00</td> @endforeach
                                    <td class="text-end fw-bold text-success" id="total-is-anual-g-total"
                                        style="background-color: rgba(22, 163, 74, 0.05);">0.00</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Tab 3: Variación H/S (Keeping same) -->
                <div class="tab-pane fade" id="var-content" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="text-secondary fw-bold mb-0"><i
                                class="material-symbols-outlined align-middle me-1">difference</i> Variación Mensual H/S
                            (Porcentaje)</h5>
                        <button id="btn-export-promedio-iv" class="btn btn-success btn-sm shadow-sm"><i
                                class="material-symbols-outlined align-middle fs-6">download</i> EXPORTAR</button>
                    </div>
                    <div class="table-responsive table-container-horizontal mb-5">
                        <table id="tbl-promedio-iv" class="table table-bordered table-hover align-middle">
                            <thead>
                                <tr>
                                    <th class="sticky-col-1 border-end-dark" style="width: 140px;">CENTRO</th>
                                    <th class="sticky-col-2 border-end-dark" style="width: 100px;">AÑO</th>
                                    @foreach($meses as $m) <th class="text-center" style="min-width: 80px;">
                                    {{ substr($m->mes_compra, 0, 3) }}</th> @endforeach
                                    <th class="text-center fw-bold"
                                        style="min-width: 90px; background-color: rgba(245, 158, 11, 0.05);">VAR. PROM</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot>
                                <tr>
                                    <td class="sticky-col-1 border-end-dark" colspan="2">VAR. PROMEDIO GENERAL</td>
                                    @foreach($meses as $m) <td class="text-end fw-bold"
                                    id="total-iv-m-{{ $m->mes_numero_compra }}">0.00%</td> @endforeach
                                    <td class="text-end fw-bold text-warning" id="total-iv-g-total"
                                        style="background-color: rgba(245, 158, 11, 0.05);">0.00%</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <!-- Annual Var -->
                    <div class="d-flex justify-content-between align-items-center mb-3 mt-4 pb-2 border-bottom">
                        <h5 class="text-secondary fw-bold mb-0"><i
                                class="material-symbols-outlined align-middle me-1">analytics</i> Consolidado Anual
                            Variación H/S (General)</h5>
                        <button id="btn-export-promedio-iv-anual" class="btn btn-success btn-sm shadow-sm"><i
                                class="material-symbols-outlined align-middle fs-6">download</i> EXPORTAR</button>
                    </div>
                    <div class="table-responsive table-container-horizontal">
                        <table id="tbl-promedio-iv-anual" class="table table-bordered table-hover align-middle">
                            <thead>
                                <tr>
                                    <th class="sticky-col-1 border-end-dark" style="width: 140px;">AÑO</th>
                                    @foreach($meses as $m) <th class="text-center" style="min-width: 80px;">
                                    {{ substr($m->mes_compra, 0, 3) }}</th> @endforeach
                                    <th class="text-center fw-bold"
                                        style="min-width: 90px; background-color: rgba(245, 158, 11, 0.05);">VAR. PROM</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot>
                                <tr>
                                    <td class="sticky-col-1 border-end-dark">VAR. TOTAL GENERAL</td>
                                    @foreach($meses as $m) <td class="text-end fw-bold"
                                    id="total-iv-anual-m-{{ $m->mes_numero_compra }}">0.00%</td> @endforeach
                                    <td class="text-end fw-bold text-warning" id="total-iv-anual-g-total"
                                        style="background-color: rgba(245, 158, 11, 0.05);">0.00%</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Tab 4: Análisis Físico (The focus of this swap) -->
                <div class="tab-pane fade" id="fisico-content" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="text-secondary fw-bold mb-0">
                            <i class="material-symbols-outlined align-middle me-1">biotech</i>
                            Análisis Físico: (X̄ I) % y (X̄ H) % por Mes
                        </h5>
                        <button id="btn-export-promedio-af" class="btn btn-success btn-sm shadow-sm">
                            <i class="material-symbols-outlined align-middle fs-6">download</i> EXPORTAR
                        </button>
                    </div>

                    <div class="table-responsive table-container-horizontal mb-5 shadow-sm rounded">
                        <table id="tbl-promedio-af" class="table table-bordered table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th rowspan="2" class="sticky-col-1 border-end-dark" style="width: 140px;">CENTRO</th>
                                    <th rowspan="2" class="sticky-col-2 border-end-dark" style="width: 100px;">AÑO</th>
                                    <th colspan="13" class="header-imp border-left-dark">PROMEDIO IMPUREZAS (X̄ I) %</th>
                                    <th colspan="13" class="header-hum border-left-dark">PROMEDIO HUMEDAD (X̄ H) %</th>
                                </tr>
                                <tr id="header-fisico-meses">
                                    <!-- Dynamic month headers from JS for both blocks -->
                                </tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot>
                                <tr class="fw-bold bg-dark text-white">
                                    <td class="sticky-col-1 border-end-dark" colspan="2">PROMEDIO GENERAL</td>
                                    <!-- Dynamic footers from JS -->
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" id="token" value="{{ csrf_token() }}">
    <input type="hidden" id="json-meses" value="{{ json_encode($meses) }}">
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/reporte/promedioih.js?v=' . $version) }}" type="text/javascript"></script>
@endpush