@extends('layouts.app')
@section('title', 'Modificar Rol')
@section('page-title', 'Modificar de Rol')
@section('breadcrumb')
	<div class="breadcrumb-card mb-25 d-md-flex align-items-center justify-content-between">
		<h5 class="mb-0">Roles</h5>

		<ol class="breadcrumb list-unstyled mt-0 mb-0 p-0">
			<li class="breadcrumb-item d-inline-block position-relative">
				<a href="{{ url('/') }}" class="d-inline-block position-relative">
					<i class="material-symbols-outlined">home</i>
					Inicio
				</a>
			</li>
			<li class="breadcrumb-item d-inline-block position-relative">
				Roles
			</li>
			<li class="breadcrumb-item d-inline-block position-relative">
				Modificar Rol
			</li>

		</ol>
	</div>
@endsection
@push('styles')
	<link rel="stylesheet" href="{{ asset('assets/plugins/datatables/datatables.min.css') }}">


@endpush

@section('content')


	<div class="card bg-white border-0 rounded-3 mb-4">
		<div class="card-body p-4">
			<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
				<h3 class="mb-0">Modificar Rol</h3>
			</div>
			<form action="{{ url('/modificar-rol/'.$idopcion.'/'.Hashids::encode(substr($rol->id, -8))) }}" id='frmregistrarroles' method="POST" class="needs-validation frmcargando" novalidate>
				@csrf
				<div class="row">

					<div class="col-md-4">
						<label class="label text-secondary">Rol</label>
						<input name="nombre" type="text" id='nombre' value="{{ old('nombre', $rol->nombre)}}" class="form-control h-55" 
								placeholder="Rol" required>
						<div class="invalid-feedback">
							Por favor ingrese Rol.
						</div>
					</div>

					<div class="col-md-4">
						<label for="activo" class="form-label label text-secondary">Estado</label>
						<div class="position-relative">
							<select class="form-select form-control h-55" id="activo" name="activo" required>
								<option  disabled value="">Seleccione</option>
								<option value="1" @if($rol->activo == 1) selected @endif>Activado</option>
								<option value="0" @if($rol->activo == 0) selected @endif>Desactivado</option>
							</select>
							<div class="invalid-feedback">
								Por favor seleccione Estado.
							</div>
						</div>
					</div>


					<div class="form-group mb-4"></div>
					<div class="d-flex gap-2">
						<button type="submit" id="btnGuardar"
							class="btn btn-primary bg-primary bg-opacity-10 text-primary border-0 fw-semibold py-2 px-4">
							<i class="ri-save-3-line me-2"></i>
							Guardar
						</button>
						<a href="{{url('/gestion-de-roles/'.$idopcion) }}"
							class="btn btn-danger bg-danger bg-opacity-10 text-danger border-0 fw-semibold py-2 px-4">
							<i class="ri-close-line me-2"></i>
							Cancelar
						</a>
					</div>

				</div>
			</form>

		</div>
	</div>

@endsection

@push('scripts')
	<script>
		(function ($) {
			'use strict';

			$(document).ready(function () {


				let formname = $('#nameform').val();
				// Inicializar Select2
				$('select[name="rol_id"], select[name="activo"]').select2({
					width: '100%',
					dropdownParent: $('body'),
					placeholder: 'Seleccione una opción',
					allowClear: true,
					minimumResultsForSearch: 5
				});

				// ✅ EVENTO SUBMIT DEL FORMULARIO
				// Este evento solo se dispara si el formulario es válido
				$('#frmmodificarroles').on('submit', function (e) {
					// Si llega aquí, es que pasó el 'required'
					abrircargando('Modificando Registro ... ');
				});


			});

		})(jQuery);
	</script>
@endpush