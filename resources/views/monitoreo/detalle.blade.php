@extends('layouts.app')

@section('title', 'Detalle Monitoreo - ' . $sede->nombre)

@section('content')
    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="card">
            <h2><i class="bi bi-hdd-network-fill" style="color: var(--color-6);"></i> {{ $sede->nombre }}</h2>
            <p class="text-muted mb-0">
                <i class="bi bi-geo-alt"></i> {{ $sede->direccion }}, {{ $sede->ciudad }}
            </p>
            <p class="text-muted">
                <i class="bi bi-upc-scan"></i> Código: <strong>{{ $sede->codigo }}</strong>
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('monitoreo.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
            <form action="{{ route('monitoreo.forzar', $sede->id) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-arrow-clockwise"></i> Forzar Check
                </button>
            </form>
        </div>
    </div>

    <!-- ESTADO PRINCIPAL -->
    @php
        $estadoClass = match ($sede->estado_conexion) {
            'online' => 'online',
            'offline' => 'offline',
            'degradado' => 'degraded',
            default => 'offline',
        };

        $estadoLabel = match ($sede->estado_conexion) {
            'online' => 'SEDE OPERATIVA',
            'offline' => 'SEDE FUERA DE LÍNEA',
            'degradado' => 'CONEXIÓN DEGRADADA',
            default => 'ESTADO DESCONOCIDO',
        };

        $estadoIcon = match ($sede->estado_conexion) {
            'online' => 'bi-check-circle-fill text-success',
            'offline' => 'bi-x-circle-fill text-danger',
            'degradado' => 'bi-exclamation-circle-fill text-warning',
            default => 'bi-question-circle-fill',
        };
    @endphp

    <div class="sede-card {{ $estadoClass }} mb-4">
        <div class="row align-items-center">
            <div class="col-md-2 text-center">
                <div class="pulse {{ $estadoClass }}"
                    style="width: 80px; height: 80px; border-radius: 50%; background: white; display: inline-flex; align-items: center; justify-content: center; margin: 0 auto;">
                    <i class="bi {{ $estadoIcon }}" style="font-size: 3rem;"></i>
                </div>
            </div>
            <div class="col-md-10">
                <h3 class="mb-2">{{ $estadoLabel }}</h3>
                <p class="mb-1">
                    <i class="bi bi-clock"></i> Último chequeo:
                    <strong>{{ $sede->ultima_verificacion ? $sede->ultima_verificacion->diffForHumans() : 'Nunca' }}</strong>
                </p>
                <p class="mb-0">
                    <i class="bi bi-arrow-repeat"></i> Intervalo de monitoreo:
                    <strong>{{ $sede->intervalo_monitoreo ?? 5 }} minutos</strong>
                </p>
            </div>
        </div>
    </div>

    <!-- MÉTRICAS PRINCIPALES -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="bi bi-percent" style="font-size: 2rem; color: var(--color-6);"></i>
                    <h3 class="mt-2 mb-0" style="color: var(--color-10);">{{ number_format($uptime, 1) }}%</h3>
                    <p class="text-muted mb-0">Uptime (24h)</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="bi bi-speedometer2" style="font-size: 2rem; color: #10b981;"></i>
                    <h3 class="mt-2 mb-0" style="color: var(--color-10);">
                        {{ $historial->where('resultado', 'success')->avg('latencia_ms') ? round($historial->where('resultado', 'success')->avg('latencia_ms')) : 0 }}ms
                    </h3>
                    <p class="text-muted mb-0">Latencia Promedio</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="bi bi-exclamation-triangle" style="font-size: 2rem; color: #ef4444;"></i>
                    <h3 class="mt-2 mb-0" style="color: var(--color-10);">
                        {{ $historial->where('resultado', 'fail')->count() }}
                    </h3>
                    <p class="text-muted mb-0">Incidentes (24h)</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="bi bi-ticket-detailed" style="font-size: 2rem; color: #f59e0b;"></i>
                    <h3 class="mt-2 mb-0" style="color: var(--color-10);">
                        {{ $sede->tickets->count() }}
                    </h3>
                    <p class="text-muted mb-0">Tickets Generados</p>
                </div>
            </div>
        </div>
    </div>

    <!-- INFORMACIÓN DE CONEXIÓN Y SERVICIOS -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="mb-3"><i class="bi bi-info-circle" style="color: var(--color-6);"></i> Información de
                        Conexión</h5>
                    <table class="table table-borderless mb-0">
                        <tbody>
                            <tr>
                                <th width="40%">IP Principal</th>
                                <td>
                                    <code>{{ $sede->ip_monitoreo }}</code>
                                    <button onclick="copiarIP('{{ $sede->ip_monitoreo }}')"
                                        class="btn btn-sm btn-outline-secondary ms-2">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                </td>
                            </tr>
                            @if ($sede->ip_backup)
                                <tr>
                                    <th>IP Backup</th>
                                    <td>
                                        <code>{{ $sede->ip_backup }}</code>
                                        <button onclick="copiarIP('{{ $sede->ip_backup }}')"
                                            class="btn btn-sm btn-outline-secondary ms-2">
                                            <i class="bi bi-clipboard"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endif
                            <tr>
                                <th>Estado</th>
                                <td>
                                    @if ($sede->activa)
                                        <span class="badge bg-success">Activa</span>
                                    @else
                                        <span class="badge bg-secondary">Inactiva</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Intervalo Monitoreo</th>
                                <td>{{ $sede->intervalo_monitoreo ?? 5 }} minutos</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="mb-3"><i class="bi bi-gear" style="color: var(--color-6);"></i> Servicios Monitoreados</h5>
                    <div class="p-2">
                        @if ($sede->servicios_monitoreados)
                            @foreach (explode(',', $sede->servicios_monitoreados) as $servicio)
                                <span class="badge me-2 mb-2"
                                    style="background: var(--color-6); font-size: 0.9rem; padding: 8px 15px;">
                                    <i class="bi bi-check-circle"></i> {{ strtoupper(trim($servicio)) }}
                                </span>
                            @endforeach
                        @else
                            <span class="badge bg-primary me-2 mb-2" style="font-size: 0.9rem; padding: 8px 15px;">
                                <i class="bi bi-check-circle"></i> WEB
                            </span>
                            <span class="badge bg-primary me-2 mb-2" style="font-size: 0.9rem; padding: 8px 15px;">
                                <i class="bi bi-check-circle"></i> ERP
                            </span>
                            <span class="badge bg-primary me-2 mb-2" style="font-size: 0.9rem; padding: 8px 15px;">
                                <i class="bi bi-check-circle"></i> POS
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- HISTORIAL DE LOGS -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="mb-3"><i class="bi bi-clock-history" style="color: var(--color-6);"></i> Historial de Monitoreo
                (Últimas 24 horas)</h5>

            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Estado</th>
                            <th>Tipo</th>
                            <th>Latencia</th>
                            <th>Detalles</th>
                            <th>Fecha y Hora</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($historial as $log)
                            <tr>
                                <td>
                                    @if ($log->resultado == 'success')
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle"></i> Exitoso
                                        </span>
                                    @elseif($log->resultado == 'fail')
                                        <span class="badge bg-danger">
                                            <i class="bi bi-x-circle"></i> Fallido
                                        </span>
                                    @else
                                        <span class="badge bg-warning">
                                            <i class="bi bi-hourglass-split"></i> Timeout
                                        </span>
                                    @endif
                                </td>
                                <td><strong>{{ strtoupper($log->tipo_chequeo ?? 'PING') }}</strong></td>
                                <td>
                                    @if ($log->latencia_ms)
                                        <span class="badge bg-info">{{ round($log->latencia_ms) }} ms</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($log->detalles)
                                        <small class="text-muted">{{ Str::limit($log->detalles, 50) }}</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <small>
                                        {{ $log->fecha_chequeo->format('d/m/Y H:i:s') }}
                                        <span class="text-muted">({{ $log->fecha_chequeo->diffForHumans() }})</span>
                                    </small>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <i class="bi bi-inbox" style="font-size: 2rem; color: var(--color-6);"></i>
                                    <p class="mt-2 text-muted">No hay logs registrados en las últimas 24 horas</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TICKETS RELACIONADOS -->
    @if ($sede->tickets->count() > 0)
        <div class="card">
            <div class="card-body">
                <h5 class="mb-3"><i class="bi bi-ticket-detailed" style="color: var(--color-6);"></i> Tickets
                    Relacionados</h5>

                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Título</th>
                                <th>Estado</th>
                                <th>Prioridad</th>
                                <th>Asignado</th>
                                <th>Creado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sede->tickets->take(10) as $ticket)
                                <tr>
                                    <td><span class="badge-ticket">{{ $ticket->numero_ticket }}</span></td>
                                    <td><strong>{{ Str::limit($ticket->titulo, 40) }}</strong></td>
                                    <td>
                                        @php
                                            $estadoBadge = match ($ticket->estado) {
                                                'abierto' => 'bg-primary',
                                                'en_proceso' => 'bg-warning',
                                                'resuelto' => 'bg-success',
                                                'cerrado' => 'bg-secondary',
                                                default => 'bg-secondary',
                                            };
                                        @endphp
                                        <span
                                            class="badge {{ $estadoBadge }}">{{ ucfirst(str_replace('_', ' ', $ticket->estado)) }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $prioridadBadge = match (strtolower($ticket->prioridad->nombre)) {
                                                'urgente', 'crítica' => 'bg-danger',
                                                'alta' => 'bg-warning text-dark',
                                                'media' => 'bg-warning',
                                                'baja' => 'bg-success',
                                                default => 'bg-secondary',
                                            };
                                        @endphp
                                        <span class="badge {{ $prioridadBadge }}">{{ $ticket->prioridad->nombre }}</span>
                                    </td>
                                    <td>{{ $ticket->tecnico ? $ticket->tecnico->nombre : 'Sin asignar' }}</td>
                                    <td>{{ $ticket->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="table-actions">
                                        <a href="{{ route('tickets.show', $ticket->id) }}"
                                            class="btn-table btn-table-primary" title="Ver">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <script>
        function copiarIP(ip) {
            navigator.clipboard.writeText(ip).then(() => {
                alert('IP copiada: ' + ip);
            }).catch(err => {
                console.error('Error al copiar:', err);
            });
        }
    </script>
@endsection
