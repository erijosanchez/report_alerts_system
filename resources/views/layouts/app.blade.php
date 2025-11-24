<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Sistema Tickets Trimax</title>
</head>

<body>
    @auth
        <header>
            <h1>🎯 Sistema Automatizado de Tickets y Alarmas - Trimax</h1>
            <nav>
                <a href="{{ route('dashboard') }}">Dashboard</a> |
                <a href="{{ route('tickets.index') }}">Tickets</a> |
                @can('ver-monitoreo')
                    <a href="{{ route('monitoreo.index') }}">🖥️ Monitoreo</a> |
                @endcan
                @can('ver-alarmas')
                    <a href="{{ route('alarmas.index') }}">🔔 Alarmas</a> |
                @endcan
                @can('gestionar-sedes')
                    <a href="{{ route('sedes.index') }}">Sedes</a> |
                @endcan
                @can('gestionar-usuarios')
                    <a href="{{ route('usuarios.index') }}">Usuarios</a> |
                @endcan
                <span>{{ auth()->user()->nombre }} ({{ auth()->user()->rol }})</span> |
                <form action="{{ route('logout') }}" method="POST" style="display:inline">
                    @csrf
                    <button type="submit">Salir</button>
                </form>
            </nav>
            <hr>
        </header>
    @endauth

    @if (session('success'))
        <p style="color:green; background-color:#90EE90; padding:10px"><strong>✅ {{ session('success') }}</strong></p>
    @endif

    @if (session('error'))
        <p style="color:red; background-color:#FFB6C1; padding:10px"><strong>❌ {{ session('error') }}</strong></p>
    @endif

    @if ($errors->any())
        <div style="color:red; background-color:#FFB6C1; padding:10px">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <main>
        @yield('content')
    </main>

    @auth
        <hr>
        <footer>
            <p>&copy; 2024 Laboratorio Óptico Trimax - Sistema Automatizado de Tickets y Alarmas</p>
            <p>Con monitoreo en tiempo real, asignación automática y notificaciones por WhatsApp/Email</p>
            <p>Desarrollado por Bach. Renato Ruiz y Bach. Frank Sánchez</p>
        </footer>
    @endauth
</body>

</html>
