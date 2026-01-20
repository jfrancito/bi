<div class="sidebar-area" id="sidebar-area">
	<div class="logo position-relative">
		<a href="{{ url('/') }}" class="d-block text-decoration-none position-relative">
			<img src="{{ asset('assets/images/bi/logo.png') }}" alt="logo-icon" style="width:24px;">
			<span class="logo-text fw-bold text-dark">Sistema BI</span>
		</a>
		<button
			class="sidebar-burger-menu bg-transparent p-0 border-0 opacity-0 z-n1 position-absolute top-50 end-0 translate-middle-y"
			id="sidebar-burger-menu">
			<i data-feather="x"></i>
		</button>
	</div>


	<aside id="layout-menu" class="layout-menu menu-vertical menu active" data-simplebar>
		<ul class="menu-inner">
			<li class="menu-title small text-uppercase">
				<span class="menu-title-text">MAIN</span>
			</li>


			<li class="menu-item">
				<a href="{{ route('login') }}" class="menu-link active">
					<span class="material-symbols-outlined menu-icon">dashboard</span>
					<span class="title">Inicio</span>
				</a>
			</li>

			<li class="menu-title small text-uppercase">
				<span class="menu-title-text">OPCIONES</span>
			</li>

			@foreach(Session::get('listamenu') as $grupo)
				<li class="menu-item">
					<a href="javascript:void(0);" class="menu-link menu-toggle">
						<span class="material-symbols-outlined menu-icon">{{ $grupo->icono }}</span>
						<span class="title">{{$grupo->nombre}}</span>
					</a>
					@foreach($grupo->opcion as $opcion)
						@if(in_array($opcion->id, Session::get('listaopciones')))
							<ul class="menu-sub">
								<li class="menu-item opcmenu">
									<a href="{{ url('/' . $opcion->pagina . '/' . Hashids::encode(substr($opcion->id, -8))) }}"
										class="menu-link">
										{{$opcion->nombre}}
									</a>
								</li>
							</ul>
						@endif
					@endforeach
				</li>
			@endforeach
			<li class="menu-item">
				<a href="{{ url('cerrarsession') }}" class="menu-link logout">
					<span class="material-symbols-outlined menu-icon">logout</span>
					<span class="title">Salir</span>
				</a>
			</li>
		</ul>
	</aside>
</div>