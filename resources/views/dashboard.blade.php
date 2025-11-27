@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <!-- STATS GRID -->
    <div class="row g-3 g-lg-4">
        <!-- Card 1 -->
        <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
            <div class="card">
                <div class="metric-top">
                    <div>
                        <div class="metric-label">Total Tickets</div>
                        <div class="metric-value" data-target="{{ $stats['total'] }}" id="m1">0</div>
                        <div class="metric-foot">
                            <span class="pill positive">
                                <i class="bi bi-arrow-up"></i> +12%
                            </span>
                        </div>
                    </div>
                    <div class="metric-icon">
                        <i class="bi bi-ticket-perforated"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2 -->
        <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
            <div class="card">
                <div class="metric-top">
                    <div>
                        <div class="metric-label">Abiertos</div>
                        <div class="metric-value" data-target="{{ $stats['abiertos'] }}" id="m2">0</div>
                        <div class="metric-foot">
                            <span class="pill negative">
                                <i class="bi bi-arrow-down"></i> -5%
                            </span>
                        </div>
                    </div>
                    <div class="metric-icon">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 3 -->
        <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
            <div class="card">
                <div class="metric-top">
                    <div>
                        <div class="metric-label">En Proceso</div>
                        <div class="metric-value" data-target="{{ $stats['en_proceso'] }}" id="m3">0</div>
                        <div class="metric-foot">
                            <span class="pill positive">
                                <i class="bi bi-arrow-up"></i> +8%
                            </span>
                        </div>
                    </div>
                    <div class="metric-icon">
                        <i class="bi bi-arrow-repeat"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 4 -->
        <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
            <div class="card">
                <div class="metric-top">
                    <div>
                        <div class="metric-label">Resueltos</div>
                        <div class="metric-value" data-target="{{ $stats['resueltos'] }}" id="m4">0</div>
                        <div class="metric-foot">
                            <span class="pill positive">
                                <i class="bi bi-arrow-up"></i> +15%
                            </span>
                        </div>
                    </div>
                    <div class="metric-icon">
                        <i class="bi bi-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ADDITIONAL STATS ROW -->
    <div class="mt-1 row g-3 g-lg-4">
        <div class="col-12 col-sm-6 col-lg-4">
            <div class="card">
                <div class="metric-label">Tiempo Respuesta</div>
                <div style="font-size:20px;font-weight:800;color:var(--color-10);margin-top:8px;">
                    {{ round($stats['tiempo_respuesta_promedio'] ?? 0) }} minutos</div>
                <div class="mt-2 muted">
                    <i class="bi bi-arrow-down" style="color:#0b944f;margin-right:8px"></i>
                    -15min
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-4">
            <div class="card">
                <div class="metric-label">Tiempo Resolución</div>
                <div style="font-size:20px;font-weight:800;color:var(--color-10);margin-top:8px;">
                    {{ round($stats['tiempo_resolucion_promedio'] ?? 0) }} minutos</div>
                <div class="mt-2 muted">
                    <i class="bi bi-arrow-down" style="color:#0b944f;margin-right:8px"></i>
                    -30min
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-4">
            <div class="card">
                <div class="metric-label">Calificación</div>
                <div style="font-size:20px;font-weight:800;color:var(--color-10);margin-top:8px;">
                    {{ number_format($stats['calificacion_promedio'] ?? 0, 1) }} / 5</div>
                <div class="mt-2 muted">
                    <i class="bi bi-arrow-up" style="color:#d97706;margin-right:8px"></i>
                    +0.2
                </div>
            </div>
        </div>
    </div>

    <!-- TABLE -->
    <div class="table-card">
        <div class="card">
            <div class="table-top">
                <h5>
                    <i class="bi bi-ticket-perforated" style="color:var(--color-6)"></i>
                    Tickets Recientes
                </h5>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('tickets.create') }}" class="btn-primary">
                        <i class="bi bi-plus-circle"></i>
                        <span class="d-sm-inline d-none">Nuevo</span>
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Título</th>
                            <th>Cliente</th>
                            <th>Prioridad</th>
                            <th>Estado</th>
                            @if (auth()->user()->esStaff())
                                <th>Técnico</th>
                            @endif
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tickets as $ticket)
                            <tr>
                                <td><span class="badge-ticket">{{ $ticket->numero_ticket }}</span></td>
                                <td><strong>{{ $ticket->titulo }}</strong></td>
                                <td>{{ $ticket->usuario->nombre }} {{ $ticket->usuario->apellido }}</td>
                                <td>
                                    <span style="display:inline-flex;align-items:center;">
                                        <span class="priority-dot" style="background:#ef4444"></span>
                                        <span class="bg-danger badge">{{ $ticket->prioridad->nombre }}</span>
                                    </span>
                                </td>
                                <td><span class="bg-warning text-dark badge">{{ $ticket->estado }}</span></td>
                                @if (auth()->user()->esStaff())
                                    <td>{{ $ticket->tecnico ? $ticket->tecnico->nombre : 'Sin asignar' }}</td>
                                @endif
                                <td>{{ $ticket->fecha_apertura->format('d/m/Y H:i') }}</td>
                                <td class="table-actions">
                                    <a href="{{ route('tickets.show', $ticket->id) }}" class="btn-outline-primary btn btn-sm" title="Ver">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No hay tickets recientes.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
@endsection
