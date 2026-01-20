<!DOCTYPE html>
<html lang="es">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<title>BI - Executive Portal</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    @include('partials.styles')
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            --accent-gold: #d4af37;
            --glass-bg: rgba(255, 255, 255, 0.95);
            --glass-border: rgba(255, 255, 255, 0.2);
        }

        body {
            font-family: 'Outfit', sans-serif;
            margin: 0;
            padding: 0;
            overflow: hidden;
            background: #f8fafc;
        }

        .login-container {
            display: flex;
            height: 100vh;
            width: 100vw;
        }

        /* Video/Image Hero Section */
        .hero-section {
            flex: 1.2;
            position: relative;
            background: url('{{ asset('assets/images/bi/login_bg_premium.png') }}') no-repeat center center;
            background-size: cover;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(225deg, rgba(15, 23, 42, 0.4) 0%, rgba(15, 23, 42, 0.1) 100%);
        }

        .hero-overlay {
            position: relative;
            z-index: 2;
            text-align: center;
            color: white;
            padding: 2rem;
            max-width: 600px;
        }

        .hero-overlay h1 {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            letter-spacing: -1px;
            text-shadow: 0 10px 30px rgba(0,0,0,0.3);
            animation: slideUp 1s ease-out;
        }

        .hero-overlay p {
            font-size: 1.25rem;
            opacity: 0.9;
            font-weight: 300;
            animation: fadeIn 1.5s ease-out;
        }

        /* Form Section */
        .form-section {
            flex: 0.8;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
            box-shadow: -20px 0 50px rgba(0,0,0,0.1);
        }

        .form-wrapper {
            width: 100%;
            max-width: 400px;
            padding: 3rem;
            animation: fadeInRight 0.8s ease-out;
        }

        .logo-box {
            margin-bottom: 2.5rem;
            text-align: center;
        }

        .logo-box img {
            max-height: 50px;
            margin-bottom: 1rem;
        }

        .welcome-text {
            margin-bottom: 2.5rem;
        }

        .welcome-text h2 {
            font-size: 2rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 0.5rem;
        }

        .welcome-text p {
            color: #64748b;
            font-size: 1rem;
        }

        .form-label {
            font-weight: 600;
            color: #475569;
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
        }

        .form-control {
            border: 2px solid #f1f5f9;
            border-radius: 12px;
            padding: 0.75rem 1rem;
            height: auto;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8fafc;
        }

        .form-control:focus {
            background: white;
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        .btn-login {
            background: #0f172a;
            color: white;
            border: none;
            padding: 1rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1.1rem;
            width: 100%;
            margin-top: 1rem;
            transition: all 0.3s ease;
            cursor: pointer;
            box-shadow: 0 10px 20px rgba(15, 23, 42, 0.2);
        }

        .btn-login:hover {
            background: #1e293b;
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(15, 23, 42, 0.3);
        }

        .footer-note {
            margin-top: 3rem;
            text-align: center;
            font-size: 0.875rem;
            color: #94a3b8;
        }

        /* Animations */
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes fadeInRight {
            from { opacity: 0; transform: translateX(50px); }
            to { opacity: 1; transform: translateX(0); }
        }

        /* Responsive */
        @media (max-width: 992px) {
            .hero-section { display: none; }
            .form-section { flex: 1; box-shadow: none; }
        }
    </style>
</head>
<body>
    @include('partials.preloader')
    @include('partials.error')

    <div class="login-container">
        <!-- Parte Izquierda (Hero) -->
        <div class="hero-section">
            <div class="hero-overlay">
                <h1>Toma de decisiones <br><span style="color: var(--accent-gold)">Redefinida.</span></h1>
                <p>Bienvenido al Portal Elite de Inteligencia de Negocios. Sus datos, visualizados para el crecimiento estratégico.</p>
            </div>
        </div>

        <!-- Parte Derecha (Login) -->
        <div class="form-section">
            <div class="form-wrapper">
                <div class="logo-box">
                    <img src="{{ asset('assets/images/bi/logo.png') }}" alt="Elite BI Logo">
                </div>

                <div class="welcome-text">
                    <h2>Bienvenido</h2>
                    <p>Inicie sesión en su panel ejecutivo</p>
                </div>

                <form method="POST" action="{{ url('login') }}" id="frmLoginMain" class="needs-validation" novalidate>
                    {{ csrf_field() }}

                    <div class="mb-4">
                        <label class="form-label" for="usuario">ID de Usuario</label>
                        <input type="text" name="name" id="usuario" class="form-control" placeholder="Ingrese su usuario" required>
                        <div class="invalid-feedback">El usuario es requerido.</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="password">Contraseña</label>
                        <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required minlength="4">
                        <div class="invalid-feedback">Contraseña inválida.</div>
                    </div>

                    <button type="submit" class="btn-login d-flex align-items-center justify-content-center">
                        <i class="material-symbols-outlined me-2">verified_user</i>
                        Ingresar al Sistema
                    </button>
                </form>

                <div class="footer-note">
                    &copy; {{ date('Y') }} Elite Business Intelligence. Todos los derechos reservados.
                </div>
            </div>
        </div>
    </div>

    @include('partials.scripts')
    <script src="{{ url('/assets/js/custom/custom.js') }}"></script>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            // Animación adicional para inputs
            const inputs = document.querySelectorAll('.form-control');
            inputs.forEach(input => {
                input.addEventListener('focus', () => {
                    input.parentElement.querySelector('.form-label').style.color = '#3b82f6';
                });
                input.addEventListener('blur', () => {
                    input.parentElement.querySelector('.form-label').style.color = '#475569';
                });
            });
        });
    </script>
</body>
</html>
