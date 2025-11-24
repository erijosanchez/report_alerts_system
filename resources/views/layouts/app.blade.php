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
        <h1>Sistema de Gestión de Tickets - Trimax</h1>
        <nav>
            <a href="{{ route('dashboard') }}">Dashboard</a> |
            <a href="{{ route('tickets.index') }}">Tickets</a> |
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

    @if(session('success'))
        <p style="color:green"><strong>{{ session('success') }}</strong></p>
    @endif

    @if(session('error'))
        <p style="color:red"><strong>{{ session('error') }}</strong></p>
    @endif

    @if ($errors->any())
        <div style="color:red">
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
        <p>&copy; 2024 Laboratorio Óptico Trimax - Sistema de Gestión de Tickets</p>
        <p>Desarrollado por Bach. Renato Ruiz y Bach. Frank Sánchez</p>
    </footer>
    @endauth
</body>
</html>