<div class="topbar">
    <div class="container-fluid">
        <div class="align-items-center row g-3">
            <!-- Columna izquierda: Toggle + Título -->
            <div class="col-12 col-lg-6">
                <div class="d-flex align-items-center gap-3">
                    <button class="mobile-toggle" id="btnToggle">
                        <i class="bi bi-list"></i>
                    </button>
                    <div class="topbar-title">
                        <h1>
                            <i class="bi bi-speedometer2"></i>
                            <span>Dashboard</span>
                        </h1>
                        <div class="subtitle muted">Panel de control y métricas del sistema</div>
                    </div>
                </div>
            </div>

            <!-- Columna derecha: Última conexión + Acciones -->
            <div class="col-12 col-lg-6">
                <div class="d-flex flex-wrap justify-content-lg-end align-items-center gap-2">
                    <div class="d-xl-block me-2 last-connection d-none">
                        Última conexión: <strong>21 Nov 2025 — 09:45</strong>
                    </div>
                    <button class="btn-outline">
                        <i class="bi bi-download"></i>
                        <span class="d-sm-inline d-none">Exportar</span>
                    </button>
                    <a href="{{ route('tickets.create') }}" class="btn-primary">
                        <i class="bi bi-plus-circle"></i>
                        <span class="d-sm-inline d-none">Nuevo Ticket</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
