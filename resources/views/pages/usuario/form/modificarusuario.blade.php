@extends('layouts.app')
@section('title', 'Modificar Rol')
@section('page-title', 'Modificar Usuario')
@section('breadcrumb')
    <div class="breadcrumb-card mb-25 d-md-flex align-items-center justify-content-between">
        <h5 class="mb-0">Usuarios</h5>
        <ol class="breadcrumb list-unstyled mt-0 mb-0 p-0">
            <li class="breadcrumb-item d-inline-block position-relative">
                <a href="{{ url('/') }}" class="d-inline-block position-relative">
                    <i class="material-symbols-outlined">home</i>
                    Inicio
                </a>
            </li>
            <li class="breadcrumb-item d-inline-block position-relative">
                Usuarios
            </li>
            <li class="breadcrumb-item d-inline-block position-relative">
                Modificar Usuario
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
                <h3 class="mb-0">Modificar </h3>
            </div>

            <form action="{{ url('/modificar-usuario/' .$idopcion.'/'.Hashids::encode(substr($usuario->id, -8))) }}" id='frmregistrarusuarios' method="POST"
                class="needs-validation frmcargando" novalidate>
                @csrf
                <div class="row">

                    <div class="col-md-4">
                        <label class="label text-secondary">Nombres</label>
                        <input name="nombre" type="text" id='nombre' value="{{ old('nombre', $usuario->nombre) }}" class="form-control h-55" placeholder="Nombre"
                            required>
                        <div class="invalid-feedback">
                            Por favor ingrese Nombre.
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="label text-secondary">Usuario</label>
                        <input name="name" type="text" id='name' value="{{ old('name', $usuario->name) }}" class="form-control h-55" placeholder="Usuario"
                            required>
                        <div class="invalid-feedback">
                            Por favor ingrese Usuario.
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="label text-secondary">Clave 
                            @if(isset($usuario->password) && !empty($usuario->password))
                                <small class="text-muted">(Actual: {{ Crypt::decrypt($usuario->password) }})</small>
                            @endif
                        </label>
                        <input name="password" type="password" id='password' value="" class="form-control h-55"
                            placeholder="Clave" required>
                        <div class="invalid-feedback">
                            Por favor ingrese Clave.
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label for="rol_id" class="form-label label text-secondary">Rol</label>
                        <div class="position-relative">
                            <select class="form-select form-control h-55" id="rol_id" name="rol_id" required>
                                <option disabled value="">Seleccione</option>
                                @foreach($comborol as $key => $value)
                                    <option value="{{ $key }}" @if($usuario->rol_id == $key) selected @endif>{{ $value }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">
                                Por favor seleccione Rol.
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
						<label for="activo" class="form-label label text-secondary">Estado</label>
						<div class="position-relative">
							<select class="form-select form-control h-55" id="activo" name="activo" required>
								<option  disabled value="">Seleccione</option>
								<option value="1" @if($usuario->activo == 1) selected @endif>Activado</option>
								<option value="0" @if($usuario->activo == 0) selected @endif>Desactivado</option>
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

                        <a href="{{url('/gestion-de-usuarios/' . $idopcion) }}"
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

    </script>
@endpush