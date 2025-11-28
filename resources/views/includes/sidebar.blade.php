<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <i class="bi bi-shield-check"></i>
            <div class="logo-text">
                <h4>TRIMAX</h4>
                <p>Sistema de Tickets</p>
            </div>
        </div>
    </div>

    <nav class="menu">
        <div class="menu-section">Principal</div>
        <a class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
            <i class="bi bi-speedometer2"></i>
            <span>Dashboard</span>
        </a>
        <a class="menu-item {{ request()->routeIs('tickets.*') ? 'active' : '' }}" href="{{ route('tickets.index') }}">
            <i class="bi bi-ticket-perforated"></i>
            <span>Tickets</span>
        </a>

        <div class="menu-section">Monitoreo</div>
        @can('ver-monitoreo')
            <a class="menu-item {{ request()->routeIs('monitoreo.*') ? 'active' : '' }}" href="{{ route('monitoreo.index') }}">
                <i class="bi bi-display"></i>
                <span>Monitoreo de Sedes</span>
            </a>
        @endcan
        @can('ver-alarmas')
            <a class="menu-item {{ request()->routeIs('alarmas.*') ? 'active' : '' }}" href="{{ route('alarmas.index') }}">
                <i class="bi bi-bell"></i>
                <span>Alarmas</span>
            </a>
        @endcan

        <div class="menu-section">Administración</div>
        @can('gestionar-sedes')
            <a class="menu-item {{ request()->routeIs('sedes.*') ? 'active' : '' }}" href="{{ route('sedes.index') }}">
                <i class="bi bi-building"></i>
                <span>Sedes</span>
            </a>
        @endcan
        @can('gestionar-usuarios')
            <a class="menu-item {{ request()->routeIs('usuarios.*') ? 'active' : '' }}" href="{{ route('usuarios.index') }}">
                <i class="bi bi-people"></i>
                <span>Usuarios</span>
            </a>
        @endcan

        <div class="menu-section">Reportes</div>
        <a class="menu-item {{ request()->routeIs('reportes.*') ? 'active' : '' }}" href="{{ route('reportes.index') }}">
            <i class="bi bi-graph-up"></i>
            <span>Reportes Generales</span>
        </a>
    </nav>

    <div class="user-info">
        <div class="avatar">{{ strtoupper(substr(auth()->user()->nombre, 0, 1)) }}</div>
        <div class="user-details">
            <h6>{{ auth()->user()->nombre }} {{ auth()->user()->apellido }}</h6>
            <p>{{ ucfirst(auth()->user()->rol) }}</p>
        </div>
        <div style="margin-left:auto;">
            <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" title="Cerrar sesión" style="background: none; border: none; color:rgba(255,255,255,0.9); cursor: pointer;">
                    <i class="bi-box-arrow-right bi" style="font-size:18px"></i>
                </button>
            </form>
        </div>
    </div>
</aside>