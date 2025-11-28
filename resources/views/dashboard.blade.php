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
                                <i class="bi bi-arrow-up"></i> +{{ $stats['variacion_total'] ?? 0 }}%
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
                            <span class="pill {{ ($stats['variacion_abiertos'] ?? 0) >= 0 ? 'negative' : 'positive' }}">
                                <i class="bi bi-arrow-{{ ($stats['variacion_abiertos'] ?? 0) >= 0 ? 'up' : 'down' }}"></i>
                                {{ ($stats['variacion_abiertos'] ?? 0) >= 0 ? '+' : '' }}{{ $stats['variacion_abiertos'] ?? 0 }}%
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
                            <span class="pill {{ ($stats['variacion_proceso'] ?? 0) >= 0 ? 'positive' : 'negative' }}">
                                <i class="bi bi-arrow-{{ ($stats['variacion_proceso'] ?? 0) >= 0 ? 'up' : 'down' }}"></i>
                                {{ ($stats['variacion_proceso'] ?? 0) >= 0 ? '+' : '' }}{{ $stats['variacion_proceso'] ?? 0 }}%
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
                            <span class="pill {{ ($stats['variacion_resueltos'] ?? 0) >= 0 ? 'positive' : 'negative' }}">
                                <i class="bi bi-arrow-{{ ($stats['variacion_resueltos'] ?? 0) >= 0 ? 'up' : 'down' }}"></i>
                                {{ ($stats['variacion_resueltos'] ?? 0) >= 0 ? '+' : '' }}{{ $stats['variacion_resueltos'] ?? 0 }}%
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
                    <i class="bi bi-arrow-{{ ($stats['variacion_tiempo_respuesta'] ?? 0) <= 0 ? 'down' : 'up' }}"
                        style="color:{{ ($stats['variacion_tiempo_respuesta'] ?? 0) <= 0 ? '#0b944f' : '#dc2626' }};margin-right:8px"></i>
                    {{ ($stats['variacion_tiempo_respuesta'] ?? 0) > 0 ? '+' : '' }}{{ $stats['variacion_tiempo_respuesta'] ?? 0 }}min
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-4">
            <div class="card">
                <div class="metric-label">Tiempo Resolución</div>
                <div style="font-size:20px;font-weight:800;color:var(--color-10);margin-top:8px;">
                    {{ round($stats['tiempo_resolucion_promedio'] ?? 0) }} minutos</div>
                <div class="mt-2 muted">
                    <i class="bi bi-arrow-{{ ($stats['variacion_tiempo_resolucion'] ?? 0) <= 0 ? 'down' : 'up' }}"
                        style="color:{{ ($stats['variacion_tiempo_resolucion'] ?? 0) <= 0 ? '#0b944f' : '#dc2626' }};margin-right:8px"></i>
                    {{ ($stats['variacion_tiempo_resolucion'] ?? 0) > 0 ? '+' : '' }}{{ $stats['variacion_tiempo_resolucion'] ?? 0 }}min
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-4">
            <div class="card">
                <div class="metric-label">Calificación</div>
                <div style="font-size:20px;font-weight:800;color:var(--color-10);margin-top:8px;">
                    {{ number_format($stats['calificacion_promedio'] ?? 0, 1) }} / 5</div>
                <div class="mt-2 muted">
                    <i class="bi bi-arrow-{{ ($stats['variacion_calificacion'] ?? 0) >= 0 ? 'up' : 'down' }}"
                        style="color:{{ ($stats['variacion_calificacion'] ?? 0) >= 0 ? '#0b944f' : '#dc2626' }};margin-right:8px"></i>
                    {{ ($stats['variacion_calificacion'] ?? 0) >= 0 ? '+' : '' }}{{ number_format($stats['variacion_calificacion'] ?? 0, 1) }}
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="crearTicketModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Nuevo Ticket</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('tickets.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Título *</label>
                                <input type="text" name="titulo" class="form-control"
                                    placeholder="Ej: Problema con impresora" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Categoría *</label>
                                <select name="categoria_id" class="form-select" required>
                                    <option value="">Seleccionar...</option>
                                    @foreach ($categorias as $categoria)
                                        <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Prioridad *</label>
                                <select name="prioridad_id" class="form-select" required>
                                    <option value="">Seleccionar...</option>
                                    @foreach ($prioridades as $prioridad)
                                        <option value="{{ $prioridad->id }}">{{ $prioridad->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Descripción *</label>
                                <textarea name="descripcion" class="form-control" rows="5"
                                    placeholder="Describe el problema con el mayor detalle posible..." required></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Crear Ticket
                        </button>
                    </div>
                </form>
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
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#crearTicketModal">
                        <i class="bi bi-plus-circle"></i> Nuevo
                    </button>
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
                                    @php
                                        $prioridadColors = [
                                            'baja' => ['dot' => '#10b981', 'badge' => 'success'],
                                            'media' => ['dot' => '#f59e0b', 'badge' => 'warning'],
                                            'alta' => ['dot' => '#ef4444', 'badge' => 'danger'],
                                            'urgente' => ['dot' => '#dc2626', 'badge' => 'danger'],
                                        ];
                                        $prioridadNombre = strtolower($ticket->prioridad->nombre);
                                        $color = $prioridadColors[$prioridadNombre] ?? [
                                            'dot' => '#6b7280',
                                            'badge' => 'secondary',
                                        ];
                                    @endphp
                                    <span style="display:inline-flex;align-items:center;">
                                        <span class="priority-dot" style="background:{{ $color['dot'] }}"></span>
                                        <span
                                            class="badge bg-{{ $color['badge'] }}">{{ $ticket->prioridad->nombre }}</span>
                                    </span>
                                </td>
                                <td><span class="bg-warning text-dark badge">{{ $ticket->estado }}</span></td>
                                @if (auth()->user()->esStaff())
                                    <td>{{ $ticket->tecnico ? $ticket->tecnico->nombre : 'Sin asignar' }}</td>
                                @endif
                                <td>{{ $ticket->fecha_apertura->format('d/m/Y H:i') }}</td>
                                <td class="table-actions">
                                    <a href="{{ route('tickets.show', $ticket->id) }}"
                                        class="btn-outline-primary btn btn-sm" title="Ver">
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
