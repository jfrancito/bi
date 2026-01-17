<!DOCTYPE html>
<html lang="es">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<title>@yield('title', 'AppMovil')</title>

	{{--
	<link rel="icon" href="public/images/icono/iconoapp.ico" type="image/x-icon"> --}}
	{{--
	<link rel="manifest" href="{{ url('/manifest.json') }}"> --}}

	<link rel="manifest" href="{{ asset('manifest.json') }}">
	<link rel="apple-touch-icon" href="{{ asset('assets/images/icono/iconoappmto192.png') }}">
	<link rel="icon" href="{{ asset('assets/images/icono/iconoapp.ico') }}" type="image/x-icon">

	<script>
		window.baseUrl = "{{ url('/') }}";
		if ('serviceWorker' in navigator) {
			navigator.serviceWorker.register("{{ asset('sw.js') }}")
				.then(reg => console.log('Service Worker registrado', reg))
				.catch(err => console.error('SW error', err));
		}
	</script>


<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

	<link rel="stylesheet" href="{{ asset('assets/css/base/trezo-breadcrumb.css?v=' . $version) }}">

	<!-- Styles -->
	@include('partials.styles')

	@stack('styles')
</head>

<body class="boxed-size">
	{{-- @include('partials.preloader') --}}
	@include('partials.alerts')
	@include('partials.sidebar')
	<input type="hidden" id="token" name="_token" value="{{ csrf_token() }}">
	<input type='hidden' id='carpeta' value="{{$carpeta}}" />

	<div class="container-fluid">
		<div class="main-content d-flex flex-column">
			<!-- Start Header Area -->
			@include('partials.header')
			<!-- End Header Area -->

			<div class="main-content-container overflow-hidden">
				<!-- Breadcrumb Section (Optional) -->
				@hasSection('breadcrumb')
					@yield('breadcrumb')
				@endif


				<!-- Main Content -->
				@yield('content')
			</div>

			<div class="flex-grow-1"></div>

			<!-- Start Footer Area -->
			@include('partials.footer')
			<!-- End Footer Area -->
		</div>
	</div>

	@include('partials.theme_settings')

	<!-- 1. jQuery PRIMERO -->
	<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

	<!-- 2. Select2 JS DESPUÉS de jQuery -->
	<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

	<!-- 3. Bootstrap y otros scripts -->
	@include('partials.scripts')

	<!-- 4. Vite assets -->
	@vite(['resources/css/app.css', 'resources/js/app.js'])

	<!-- 5. Custom scripts -->
	<script src="{{ asset('/assets/js/custom/custom.js') }}"></script>
	<script src="{{ asset('/assets/js/custom/general.js?version=' . $version) }}"></script>

	<!-- 6. Scripts adicionales de las páginas AL FINAL -->
	@stack('scripts')

</body>

</html>