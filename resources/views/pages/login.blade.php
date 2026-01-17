<!DOCTYPE html>
<html lang="es">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="csrf-token" content="{{ csrf_token() }}">
		<title>BI - Inicio de Sessión</title>
		<!-- Styles -->
		@include('partials.styles')
	</head>
	<body class="boxed-size bg-white">
		@include('partials.preloader')
		@include('partials.error')

		<form method="POST" action="{{ url('login') }}"  class="needs-validation" novalidate>
			{{ csrf_field() }}
		

		<div class="container">
			<div class="main-content d-flex flex-column p-0">
				<div class="m-auto m-1230">
					<div class="row align-items-center">

						<div class="col-lg-6 d-none d-lg-block">
							<img src="{{ asset('assets/images/bi/login2.png') }}" class="rounded-3" alt="login" style="box-shadow: 2px 1px 13px;">
						</div>

						<div class="col-lg-6">
							<div class="mw-480 ms-lg-auto">
								<div class="center">
									<a href="#" class="d-inline-block mb-4 center">
										<img src="{{ asset('assets/images/bi/logo.png') }}" class="rounded-3 for-light-logo center" alt="login">
										<img src="{{ asset('assets/images/white-logo.svg') }}" class="rounded-3 for-dark-logo center" alt="login">
									</a>
								</div>


								<h3 class="fs-28 mb-2 center">Ingrese sus Datos</h3>

								<!-- Usuario -->
								<div class="form-group mb-4">
									<label class="label text-secondary">Usuario</label>
									<input name="name" type="text" id='usuario' class="form-control h-55" 
										   placeholder="Usuario" required>
									<div class="invalid-feedback">
										Por favor ingresa tu Usuario.
									</div>
								</div>

								<!-- PASSWORD -->
								<div class="form-group mb-4">
									<label class="label text-secondary">Password</label>
									<input name="password" type="password" id='password' class="form-control h-55"
										   placeholder="password ***" required minlength="4">
									<div class="invalid-feedback">
										La contraseña es obligatoria.
									</div>
								</div>

								<!-- SUBMIT -->
								<div class="form-group mb-4">
									<button type="submit" class="btn btn-primary fw-medium py-2 px-3 w-100">
										<div class="d-flex align-items-center justify-content-center py-1">
											<i class="material-symbols-outlined text-white fs-20 me-2">login</i>
											<span>Ingresar</span>
										</div>
									</button>
								</div>


							</div>
						</div>

					</div>
				</div>
			</div>
		</div>

	</form>

		<button class="theme-settings-btn p-0 border-0 bg-transparent position-absolute" style="right: 30px; bottom: 30px;" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasScrolling" aria-controls="offcanvasScrolling">
			<i class="material-symbols-outlined bg-primary wh-35 lh-35 text-white rounded-1" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-title="Click On Theme Settings">settings</i>
		</button>
		
		{{-- <script>console.log("Existe $:", typeof $);</script> --}}

		
		@include('partials.theme_settings')

	
		@vite(['resources/css/app.css', 'resources/js/app.js'])
		{{-- @include('partials.theme_settings') --}}
		{{-- @include('partials.theme_settings') --}}
		@include('partials.scripts')
		<script src="{{ url('/assets/js/custom/custom.js') }}"></script>	

		<script>
			document.addEventListener("DOMContentLoaded", () => {
				console.log("DOM listo");
				$('#frmLoginMain').on('submit', function (e) {
					{{-- debugger; --}}
					let usuario = $('#usuario').val().trim();
					{{-- alert(usuario); --}}
					let pass  = $('#password').val().trim();
					let valido = true;
					{{-- alert(pass); --}}
					{{-- debugger; --}}

					// Eliminar mensajes previos
					$('.is-invalid').removeClass('is-invalid');
					{{-- debugger; --}}

					// Validar usuario
					if (usuario === '') {
						$('input[name="usuario"]').addClass('is-invalid');
						valido = false;
					}

					// Validar password
					if (pass === '') {
						$('input[name="password"]').addClass('is-invalid');
						valido = false;
					}

					// Si no es válido, detener envío del form
					if (!valido) {
						e.preventDefault();
						return false;
					}

					return true;
				});

			});
		</script>

			


	</body>
</html>
