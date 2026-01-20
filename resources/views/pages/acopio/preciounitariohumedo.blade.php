@extends('layouts.app')
@section('title', 'Precio Unitario Humedo')
@section('page-title', 'Precio Unitario Humedo')
@section('breadcrumb')
    <div class="breadcrumb-card mb-25 d-md-flex align-items-center justify-content-between">
        <h5 class="mb-0">Precio Unitario Humedo</h5>
        <ol class="breadcrumb list-unstyled mt-0 mb-0 p-0">
            <li class="breadcrumb-item d-inline-block position-relative">
                <a href="{{ url('/') }}" class="d-inline-block position-relative">
                    <i class="material-symbols-outlined">home</i>
                    Inicio
                </a>
            </li>
            <li class="breadcrumb-item d-inline-block position-relative">
                Precio Unitario Humedo
            </li>
        </ol>
    </div>
@endsection
@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/reporte.css?v=' . ($version ?? '1')) }}">
@endpush
@section('content')
    <div class="card bg-white border-0 rounded-3 mb-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2">
                <h4 class="text-primary text-uppercase fw-bold mb-0">
                    <i class="material-symbols-outlined align-middle me-1">calendar_view_day</i>
                    Precio Unitario Húmedo por Día
                </h4>
                <span class="badge bg-primary-transparent text-primary fw-medium px-3 py-2 rounded-pill">
                    <i class="ri-checkbox-circle-line align-middle me-1"></i> Actualizado al: {{ $ultima_actualizacion }}
                </span>
            </div>

            <!-- Filtros -->
            <div class="row mb-4">
                <div class="col-lg-12">
                    <div class="row g-3">
                        <div class="col-md-4">
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
                        <div class="col-md-8">
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
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3 mt-4">
                <h5 class="text-secondary fw-bold mb-0">
                    <i class="material-symbols-outlined align-middle me-1">list_alt</i>
                    Resumen Diario por Variedad
                </h5>
                <button id="btn-export-pu-humedo" class="btn btn-success btn-sm shadow-sm">
                    <i class="material-symbols-outlined align-middle fs-6">download</i> EXPORTAR
                </button>
            </div>

            <div class="row mb-2">
                <div class="col-12">
                    <div class="export-hint">
                        <i class="material-symbols-outlined fs-6 me-1">info</i>
                        💡 Haga doble clic en las celdas de los días para descargar el detalle diario.
                    </div>
                </div>
            </div>

            <div class="table-responsive table-container-horizontal">
                <table id="tbl-pu-humedo" class="table table-bordered align-middle">
                    <thead>
                        <tr id="header-days">
                            <!-- Injected by JS -->
                        </tr>
                        <tr id="header-metrics">
                            <!-- Injected by JS -->
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data will be loaded via AJAX -->
                    </tbody>
                    <tfoot>
                        <tr id="footer-pu-humedo">
                            <!-- Totals will be injected here -->
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="mt-3">
                <small class="text-muted">
                    <span class="badge bg-info-transparent text-dark border me-2">P. Unit. H</span> Precio Unitario Húmedo
                    <span class="badge bg-secondary-transparent text-dark border me-2">Min</span> Precio Mínimo Recepción
                    <span class="badge bg-secondary-transparent text-dark border">Max</span> Precio Máximo Recepción
                </small>
            </div>

            <input type="hidden" id="token" value="{{ csrf_token() }}">
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/reporte/preciounitariohumedo.js?v=' . ($version ?? '1')) }}"
        type="text/javascript"></script>
@endpush