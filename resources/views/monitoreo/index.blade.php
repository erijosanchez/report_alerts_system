@extends('layouts.app')

@section('title', 'Monitoreo de Sedes')

@section('content')
    <!-- STATUS OVERVIEW -->
    <div class="status-overview">
        <h5><i class="bi bi-graph-up"></i> Resumen General</h5>
        <div class="status-grid">
            <div class="status-item online">
                <div class="status-icon">
                    <i class="bi bi-check-circle-fill" style="color: var(--success);"></i>
                </div>
                <div class="status-value" style="color: var(--success);">{{ $stats['sedes_online'] }}</div>
                <div class="status-label" style="color: var(--success);">Online</div>
            </div>
            <div class="status-item offline">
                <div class="status-icon">
                    <i class="bi bi-x-circle-fill" style="color: var(--danger);"></i>
                </div>
                <div class="status-value" style="color: var(--danger);">{{ $stats['sedes_offline'] }}</div>
                <div class="status-label" style="color: var(--danger);">Offline</div>
            </div>
            <div class="status-item degraded">
                <div class="status-icon">
                    <i class="bi bi-exclamation-triangle-fill" style="color: var(--warning);"></i>
                </div>
                <div class="status-value" style="color: var(--warning);">{{ $stats['sedes_degradado'] }}</div>
                <div class="status-label" style="color: var(--warning);">Degradado</div>
            </div>
            <div class="status-item uptime">
                <div class="status-icon">
                    <i class="bi bi-speedometer2" style="color: var(--color-6);"></i>
                </div>
                <div class="status-value" style="color: var(--color-6);">{{ $stats['uptime_general'] }}%</div>
                <div class="status-label" style="color: var(--color-6);">Uptime</div>
            </div>
        </div>
    </div>

    <!-- SEDES LIST -->
    <div class="sedes-header">
        <h5><i class="bi bi-building"></i> Estado de Sedes</h5>
        <span class="badge">{{ $stats['total_sedes'] }} Sedes</span>
    </div>

    @foreach ($sedes as $sede)
        @php
            $estadoClass = match ($sede->estado_conexion) {
                'online' => 'online',
                'offline' => 'offline',
                'degradado' => 'degraded',
                default => 'online',
            };

            $estadoLabel = match ($sede->estado_conexion) {
                'online' => 'ONLINE',
                'offline' => 'OFFLINE',
                'degradado' => 'DEGRADADO',
                default => 'DESCONOCIDO',
            };

            // Buscar ticket automático asociado si está offline
            $ticketAsociado = null;
            if ($sede->estado_conexion === 'offline') {
                $ticketAsociado = $ticketsAutomaticos->where('sede_id', $sede->id)->first();
            }

            // Determinar si la latencia es alta (>500ms = degradado)
            $latenciaAlta = isset($sede->ultima_latencia) && $sede->ultima_latencia > 500;
        @endphp

        <div class="sede-card {{ $estadoClass }}">
            <div class="sede-header">
                <div>
                    <span class="sede-code">{{ $sede->codigo }}</span>
                    <h3 class="sede-title">{{ $sede->nombre }}</h3>
                    <p class="sede-location">
                        <i class="bi bi-geo-alt"></i> {{ $sede->direccion ?? 'Dirección no especificada' }}
                    </p>
                </div>
                <div class="status-indicator {{ $estadoClass }}">
                    <span class="pulse {{ $estadoClass }}"></span>
                    {{ $estadoLabel }}
                </div>
            </div>

            <div class="sede-info">
                <div class="info-item">
                    <i class="bi bi-hdd-network"></i>
                    <div>
                        <div class="info-label">IP Principal</div>
                        <div class="info-value">{{ $sede->ip_monitoreo }}</div>
                    </div>
                </div>
                <div class="info-item">
                    <i class="bi bi-clock-history"></i>
                    <div>
                        <div class="info-label">Última Verificación</div>
                        <div
                            class="info-value {{ $sede->estado_conexion === 'offline' ? 'text-danger-custom' : ($sede->estado_conexion === 'online' ? 'text-success-custom' : '') }}">
                            {{ $sede->ultima_verificacion ? $sede->ultima_verificacion->diffForHumans() : 'Nunca' }}
                        </div>
                    </div>
                </div>

                @if ($sede->estado_conexion === 'online' || $sede->estado_conexion === 'degradado')
                    <div class="info-item">
                        <i class="bi bi-speedometer"></i>
                        <div>
                            <div class="info-label">Latencia</div>
                            <div class="info-value {{ $latenciaAlta ? 'text-warning-custom' : '' }}">
                                {{ $sede->ultima_latencia ? round($sede->ultima_latencia) . 'ms' : 'N/A' }}
                            </div>
                        </div>
                    </div>
                @endif

                <div class="info-item">
                    <i class="bi bi-arrow-repeat"></i>
                    <div>
                        <div class="info-label">Intervalo</div>
                        <div class="info-value">{{ $sede->intervalo_monitoreo ?? 5 }} minutos</div>
                    </div>
                </div>

                @if ($sede->estado_conexion === 'offline')
                    <div class="info-item">
                        <i class="bi bi-exclamation-triangle-fill text-danger-custom"></i>
                        <div>
                            <div class="info-label">Estado</div>
                            <div class="info-value text-danger-custom">Caída detectada</div>
                        </div>
                    </div>
                @elseif($sede->estado_conexion === 'degradado')
                    <div class="info-item">
                        <i class="bi bi-exclamation-triangle text-warning-custom"></i>
                        <div>
                            <div class="info-label">Estado</div>
                            <div class="info-value">Alta latencia</div>
                        </div>
                    </div>
                @else
                    <div class="info-item">
                        <i class="bi bi-check-circle-fill text-success-custom"></i>
                        <div>
                            <div class="info-label">Servicios</div>
                            <div class="info-value">{{ $sede->servicios_monitoreados ?? 'Web, ERP, POS' }}</div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- ALERTAS -->
            @if ($sede->estado_conexion === 'offline' && $ticketAsociado)
                <div class="sede-alert danger">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <div>
                        <strong>Alerta Crítica:</strong> Se ha generado automáticamente el ticket
                        <strong>{{ $ticketAsociado->numero_ticket }}</strong> y se han enviado notificaciones.
                    </div>
                </div>
            @elseif($sede->estado_conexion === 'degradado')
                <div class="sede-alert warning">
                    <i class="bi bi-exclamation-triangle"></i>
                    <div>
                        <strong>Advertencia:</strong> La conexión presenta alta latencia.
                        Se está monitoreando de cerca.
                    </div>
                </div>
            @endif

            <!-- MÉTRICAS (solo para sedes online) -->
            @if ($sede->estado_conexion === 'online')
                <div class="sede-metrics">
                    <div class="metric">
                        <div class="metric-value">{{ $sede->uptime_30d }}%</div>
                        <div class="metric-label">Uptime 30d</div>
                    </div>
                    <div class="metric">
                        <div class="metric-value">
                            {{ $sede->latencia_promedio ? round($sede->latencia_promedio) . 'ms' : 'N/A' }}</div>
                        <div class="metric-label">Latencia Prom.</div>
                    </div>
                    <div class="metric">
                        <div class="metric-value">{{ number_format($sede->checks_exitosos) }}</div>
                        <div class="metric-label">Checks Exitosos</div>
                    </div>
                    <div class="metric">
                        <div class="metric-value">{{ $sede->incidentes }}</div>
                        <div class="metric-label">Incidentes</div>
                    </div>
                </div>
            @endif

            <!-- ACCIONES -->
            <div class="sede-actions">
                <form action="{{ route('monitoreo.forzar', $sede->id) }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit"
                        class="btn-action {{ $sede->estado_conexion === 'offline' ? 'btn-action-danger' : ($sede->estado_conexion === 'degradado' ? 'btn-action-warning' : 'btn-action-outline') }}">
                        <i class="bi bi-arrow-clockwise"></i> Forzar Check
                    </button>
                </form>

                @if ($ticketAsociado)
                    <a href="{{ route('tickets.show', $ticketAsociado->id) }}" class="btn-action btn-action-outline">
                        <i class="bi bi-ticket-perforated"></i> Ver Ticket
                    </a>
                @endif
            </div>
        </div>
    @endforeach

    @if ($sedes->isEmpty())
        <div class="text-center py-5">
            <i class="bi bi-building" style="font-size: 3rem; color: var(--color-6);"></i>
            <p class="mt-3 text-muted">No hay sedes configuradas para monitoreo</p>
        </div>
    @endif

    
@endsection
