(function () {
	"use strict";

	window.onload = function () {
	};

	/************************ modal cargando ************************/
	$('.opcmenu').on('click', function (e) {
		abrircargando('Cargando Opcion...');
	});

	function setMensajeCargando(mensaje) {
		var cadmensaje = '';
		if ((mensaje === undefined) || (mensaje == '')) {
			cadmensaje = "<div class='texto'>Procesando la información<br>espere por favor</div>";
		}
		else {
			cadmensaje = "<div class='texto'>" + mensaje + "</div>";
		}
		return cadmensaje;
	}

	// ✅ EXPONER cerrarcargando al scope global
	window.cerrarcargando = function () {
		$("#WindowLoad").remove();
	}






	// ✅ EXPONER abrircargando al scope global - VERSIÓN MEJORADA
	window.abrircargando = function (mensaje, nivel = 3, efecto = 3) {
		// Eliminar si existe
		// debugger;
		cerrarcargando();

		var cadnivel = '';
		for (var i = 0; i < nivel; i++) {
			cadnivel = cadnivel + "../";
		}

		mensaje = setMensajeCargando(mensaje);
		// debugger;

		// Crear el contenedor principal
		var $windowLoad = $('<div>', {
			id: 'WindowLoad',
			css: {
				position: 'fixed',
				top: 0,
				left: 0,
				width: '100%',
				height: '100%',
				backgroundColor: 'rgba(0, 0, 0, 0.8)',
				zIndex: 99999,
				display: 'flex',
				justifyContent: 'center',
				alignItems: 'center',
				overflow: 'hidden'
			}
		});

		// Crear el contenedor del contenido
		var $content = $('<div>', {
			css: {
				textAlign: 'center',
				color: '#ffffff',
				marginTop: '-250px'  // Esto lo sube 80px hacia arriba
			}
		});

		// Crear la imagen
		var imgSrc = (window.baseUrl ? window.baseUrl : '') + '/img/gif/mnto.gif';
		var $img = $('<img>', {
			src: imgSrc,
			css: {
				width: '200px',
				display: 'block',
				margin: '0 auto 20px'
			},
			// Fallback si no encuentra la imagen
			error: function () {
				$(this).attr('src', imgSrc);
			}
		});


		if (efecto == 1) {
			// Crear el mensaje
			var $mensaje = $('<div>', {
				class: 'msjcargando',
				html: mensaje,
				css: {
					fontSize: '18px',
					fontWeight: '600',
					marginTop: '20px',
					border: '2px solid white',
					borderRadius: '13px',
					padding: '15px 30px',
					backgroundColor: 'rgba(255, 255, 255, 0.15)',  // Fondo semi-transparente
					boxShadow: '0 8px 32px rgba(0, 0, 0, 0.4), inset 0 0 20px rgba(255, 255, 255, 0.1)',  // Sombra exterior e interior
					textShadow: '2px 2px 8px rgba(0, 0, 0, 0.8), 0 0 10px rgba(255, 255, 255, 0.3)',  // Sombra del texto más fuerte
					backdropFilter: 'blur(10px)',  // Efecto blur de fondo
					letterSpacing: '0.5px'  // Espaciado de letras para mejor legibilidad
				}
			});
		}
		if (efecto == 2) {
			// Crear el mensaje
			var $mensaje = $('<div>', {
				class: 'msjcargando',
				html: mensaje,
				css: {
					fontSize: '18px',
					fontWeight: '600',
					marginTop: '20px',
					border: '2px solid rgba(255, 255, 255, 0.8)',
					borderRadius: '13px',
					padding: '15px 30px',
					backgroundColor: 'rgba(0, 0, 0, 0.6)',
					boxShadow: '0 0 20px rgba(255, 255, 255, 0.3), 0 0 40px rgba(255, 255, 255, 0.1), inset 0 0 10px rgba(255, 255, 255, 0.05)',
					textShadow: '0 0 10px rgba(255, 255, 255, 0.8), 0 0 20px rgba(255, 255, 255, 0.5), 2px 2px 4px rgba(0, 0, 0, 0.8)'
				}
			});
		}
		if (efecto == 3) {
			// Crear el mensaje
			var $mensaje = $('<div>', {
				class: 'msjcargando',
				html: mensaje,
				css: {
					fontSize: '18px',
					fontWeight: '700',
					marginTop: '20px',
					border: '2px solid rgba(255, 255, 255, 0.9)',
					borderRadius: '13px',
					padding: '15px 30px',
					background: 'linear-gradient(135deg, rgba(255, 255, 255, 0.2), rgba(255, 255, 255, 0.05))',
					boxShadow: '0 10px 40px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.1) inset',
					textShadow: '0 2px 4px rgba(0, 0, 0, 0.9), 0 0 15px rgba(255, 255, 255, 0.4)',
					backdropFilter: 'blur(15px)',
					WebkitBackdropFilter: 'blur(15px)',  // Para Safari
					color: '#ffffff'
				}
			});
		}

		// Input oculto para foco
		var $input = $('<input>', {
			id: 'focusInput',
			type: 'text',
			css: {
				position: 'absolute',
				opacity: 0,
				pointerEvents: 'none'
			}
		});

		// Ensamblar todo
		$content.append($img).append($mensaje);
		$windowLoad.append($input).append($content);

		// Agregar al body
		$('body').append($windowLoad);

		// Focus en el input oculto
		$('#focusInput').focus();

		console.log('✅ Modal de carga mostrado');
	}

	// Versión alternativa con spinner CSS (por si no hay gif)
	window.abrircargandoSimple = function (mensaje) {
		cerrarcargando();

		mensaje = setMensajeCargando(mensaje);

		var html = `
			<div id="WindowLoad" style="
				position: fixed;
				top: 0;
				left: 0;
				width: 100%;
				height: 100%;
				background: rgba(0, 0, 0, 0.8);
				z-index: 99999;
				display: flex;
				justify-content: center;
				align-items: center;
			">
				<div style="text-align: center; color: white;">
					<div class="spinner-border text-light" role="status" style="width: 4rem; height: 4rem; margin-bottom: 20px;">
						<span class="visually-hidden">Cargando...</span>
					</div>
					<div class="msjcargando" style="font-size: 18px; font-weight: 600;">
						${mensaje}
					</div>
				</div>
			</div>
		`;

		$('body').append(html);
	}

	window.showAlertBS5 = function (texto, tipo, contenedor, velocidad = 100) {
		const id = "rd" + Math.floor((Math.random() * 500) + 1);

		const html = `
	        <div id="${id}" class="alert alert-${tipo} alert-dismissible fade show alertawrelative" role="alert"
	            style="z-index:999999;">
	            <span class="icon mdi mdi-check"></span>
	            <strong>${tipo === "success" ? "Bien Hecho!" : "Error!"}</strong> ${texto}
	            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
	        </div>
	    `;

		$(contenedor).append(html);
		setTimeout(() => {
			const $alert = $("#" + id);

			$alert.fadeOut(velocidad, function () {
				// Bootstrap 5 destruye la alerta completamente
				const alertInstance = bootstrap.Alert.getOrCreateInstance($alert[0]);
				alertInstance.close();
			});

		}, velocidad);

	};


	window.alertmobil = function (texto) {
		showAlertBS5(texto, "success", ".panel-ajax-alert-mobil", 100);
	};

	window.alertdangermobil = function (texto) {
		showAlertBS5(texto, "danger", ".panel-ajax-alert-mobil", 100);
	};

	window.alertdangermobil_lenta = function (texto) {
		showAlertBS5(texto, "danger", ".panel-ajax-alert-mobil", 1000);
	};

	window.alertajax = function (texto) {
		// debugger;
		showAlertBS5(texto, "success", ".panel-ajax-alert", 1200);
	};

	window.alerterrorajax = function (texto) {
		showAlertBS5(texto, "danger", ".panel-ajax-alert", 2500);
	};

	window.alerterrorajaxflotante = function (texto) {
		showAlertBS5(texto, "danger", ".panel-ajax-alert-flotante", 2500);
	};

	window.alerterror505ajax = function (texto) {
		showAlertBS5(texto, "danger", ".panel-ajax-alert", 3000);
	};


})();

document.addEventListener("DOMContentLoaded", function () {
	const forms = document.querySelectorAll('.frmcargando');

	forms.forEach(function (form) {
		form.addEventListener('submit', function (event) {
			if (!form.checkValidity()) {
				event.preventDefault();
				event.stopPropagation();
			} else {
				// Si la validación pasa, mostramos el loading
				abrircargando("Procesando la información");
			}

			form.classList.add('was-validated');
		}, false);
	});
});