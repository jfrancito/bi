@extends('layouts.app')

@section('title', 'BI - Bienvenido')

@section('page-title', 'Bienvenido Usuario')

@section('breadcrumb')
    <div class="breadcrumb-card mb-25 d-md-flex align-items-center justify-content-between">
        <h5 class="mb-0">Bienvenido</h5>

        <ol class="breadcrumb list-unstyled mt-0 mb-0 p-0">
            <li class="breadcrumb-item d-inline-block position-relative">
                <a href="{{ url('/') }}" class="d-inline-block position-relative">
                    <i class="material-symbols-outlined">home</i>
                    Inicio
                </a>
            </li>

            <li class="breadcrumb-item d-inline-block position-relative">
                Bienvenido
            </li>
        </ol>
    </div>
@endsection

@push('styles')
    <style>
        .welcome-animation {
            animation: fadeInUp 0.8s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .icon-bounce {
            animation: bounce 2s infinite;
        }

        @keyframes bounce {

            0%,
            20%,
            50%,
            80%,
            100% {
                transform: translateY(0);
            }

            40% {
                transform: translateY(-10px);
            }

            60% {
                transform: translateY(-5px);
            }
        }
    </style>
@endpush

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-10">

        </div>
    </div>
@endsection

            @push('scripts')
                {{-- @vite(['resources/js/app.js']) --}}


                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        console.log('Página de bienvenida cargada correctamente');

                        // Animación suave al hacer scroll a la guía rápida
                        const guiaLink = document.querySelector('a[href="#guia-rapida"]');
                        if (guiaLink) {
                            guiaLink.addEventListener('click', function (e) {
                                e.preventDefault();
                                const target = document.querySelector('#guia-rapida');
                                if (target) {
                                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                                }
                            });
                        }


                        // Reinicializar dropdowns de Bootstrap
                        const dropdowns = document.querySelectorAll('[data-bs-toggle="dropdown"]');
                        dropdowns.forEach(dropdown => {
                            new bootstrap.Dropdown(dropdown);
                        });
                        console.log('Página de bdata-bs-toggle="dropdown"');

                        console.log("DOM listo");

                        $(function () {
                            console.log("jQuery funcionando correctamente con Vite");
                        });

                    });
                </script>
            @endpush