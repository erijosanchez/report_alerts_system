<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema Trimax</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/login.css') }}">
</head>

<body>
    <div class="login-container">
        <!-- Área del logo -->
        <div class="brand-area">
            <h1 class="brand-title">TRIMAX</h1>
            <p class="brand-subtitle">Sistema de Tickets y Alarmas</p>
        </div>

        <!-- Card de login -->
        <div class="login-card">
            <h2 class="login-title">Iniciar Sesión</h2>

            <!-- Alerta de error (oculta por defecto) -->
            <div class="alert-error" id="alertError">
                <i class="bi bi-exclamation-circle-fill"></i>
                <span>Credenciales incorrectas. Por favor, intenta nuevamente.</span>
            </div>

            <form method="POST" action="{{ route('login') }}" id="loginForm">
                @csrf
                <!-- Campo de email -->
                <div class="form-group-custom">
                    <label class="custom-label">
                        <i class="bi bi-envelope-fill"></i>
                        Correo Electrónico
                    </label>
                    <div class="input-wrapper">
                        <i class="bi bi-envelope-fill input-icon"></i>
                        <input 
                            type="email" 
                            name="email"
                            class="custom-input" 
                            placeholder="usuario@trimax.com" 
                            required
                            value="{{ old('email') }}"
                            autocomplete="email"
                        >
                    </div>
                </div>

                <!-- Campo de contraseña -->
                <div class="form-group-custom">
                    <label class="custom-label">
                        <i class="bi bi-lock-fill"></i>
                        Contraseña
                    </label>
                    <div class="input-wrapper">
                        <i class="bi bi-lock-fill input-icon"></i>
                        <input 
                            type="password" 
                            name="password"
                            class="custom-input" 
                            placeholder="••••••••" 
                            required
                            autocomplete="current-password"
                        >
                    </div>
                </div>

                <!-- Opciones adicionales -->
                <div class="d-flex justify-content-between align-items-center small-options">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember">
                        <label class="form-check-label" for="remember">
                            Recordarme
                        </label>
                    </div>
                    <a href="#" class="forgot-link">¿Olvidaste tu contraseña?</a>
                </div>

                <!-- Botón de login -->
                <button type="submit" class="btn btn-login w-100">
                    <div class="spinner"></div>
                    <i class="bi bi-box-arrow-in-right"></i>
                    <span>Ingresar al Sistema</span>
                </button>
            </form>

            <!-- Divisor (opcional) -->
            <!-- <div class="divider">
                <span>O CONTINUAR CON</span>
            </div> -->

            <!-- Botones sociales (opcional) -->
            <!-- <div class="social-login">
                <button class="btn-social">
                    <i class="bi bi-google"></i>
                    Google
                </button>
                <button class="btn-social">
                    <i class="bi bi-microsoft"></i>
                    Microsoft
                </button>
            </div> -->
        </div>

        <!-- Footer -->
        <p class="footer-text">
            © 2024 <strong>Laboratorio Óptico Trimax</strong><br>
            Desarrollado por A. Ruiz y E. Sánchez
        </p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>