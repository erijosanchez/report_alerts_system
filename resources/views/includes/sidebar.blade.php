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
        <a class="menu-item active" href="{{ route('dashboard') }}">
            <i class="bi bi-speedometer2"></i>
            <span>Dashboard</span>
        </a>
        <a class="menu-item" href="{{ route('tickets.index') }}">
            <i class="bi bi-ticket-perforated"></i>
            <span>Tickets</span>
        </a>

        <div class="menu-section">Monitoreo</div>
        @can('ver-monitoreo')
            <a class="menu-item" href="{{ route('monitoreo.index') }}">
                <i class="bi bi-display"></i>
                <span>Monitoreo de Sedes</span>
            </a>
        @endcan
        @can('ver-alarmas')
            <a class="menu-item" href="{{ route('alarmas.index') }}">
                <i class="bi bi-bell"></i>
                <span>Alarmas</span>
            </a>
        @endcan
        <div class="menu-section">Administración</div>
        @can('gestionar-sedes')
            <a class="menu-item" href="{{ route('sedes.index') }}">
                <i class="bi bi-building"></i>
                <span>Sedes</span>
            </a>
        @endcan
        @can('gestionar-usuarios')
        <a class="menu-item" href="{{ route('usuarios.index') }}">
            <i class="bi bi-people"></i>
            <span>Usuarios</span>
        </a>
        @endcan
        <a class="menu-item" href="#">
            <i class="bi bi-gear"></i>
            <span>Configuración</span>
        </a>

        <div class="menu-section">Reportes</div>
        <a class="menu-item" href="#">
            <i class="bi bi-graph-up"></i>
            <span>Estadísticas</span>
        </a>
        <a class="menu-item" href="#">
            <i class="bi bi-file-earmark-text"></i>
            <span>Reportes SLA</span>
        </a>
    </nav>

    <div class="user-info">
        <div class="avatar">A</div>
        <div class="user-details">
            <h6>{{ auth()->user()->nombre }}</h6>
            <p>{{ auth()->user()->rol }}</p>
        </div>
        <div style="margin-left:auto;">
            <a href="{{ route('logout') }}" title="Cerrar sesión" style="color:rgba(255,255,255,0.9)">
                <i class="bi-box-arrow-right bi" style="font-size:18px"></i>
            </a>
        </div>
    </div>
</aside>
