@extends('layouts.app')

@section('title', 'Alarmas')

@section('content')
    <!-- STATS -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon danger"><i class="bi bi-exclamation-triangle-fill"></i></div>
            <div class="stat-content">
                <h3>{{ $stats['criticas'] }}</h3>
                <p>Alarmas Críticas</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon warning"><i class="bi bi-exclamation-circle-fill"></i></div>
            <div class="stat-content">
                <h3>{{ $stats['advertencias'] }}</h3>
                <p>Advertencias</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon success"><i class="bi bi-check-circle-fill"></i></div>
            <div class="stat-content">
                <h3>{{ $stats['resueltas_hoy'] }}</h3>
                <p>Resueltas Hoy</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon info"><i class="bi bi-clock-history"></i></div>
            <div class="stat-content">
                <h3>{{ $stats['tiempo_promedio'] }}min</h3>
                <p>Tiempo Promedio</p>
            </div>
        </div>
    </div>

    <!-- ALARMAS LIST -->
    <div class="alarmas-header">
        <h5><i class="bi bi-bell-fill" style="color: var(--color-4);"></i> Alarmas Activas</h5>
        <div class="filters">
            <a href="{{ route('alarmas.index') }}"
                class="filter-btn {{ !request('tipo') || request('tipo') == 'todas' ? 'active' : '' }}">
                Todas
            </a>
            <a href="{{ route('alarmas.index', ['tipo' => 'criticas']) }}"
                class="filter-btn {{ request('tipo') == 'criticas' ? 'active' : '' }}">
                Críticas
            </a>
            <a href="{{ route('alarmas.index', ['tipo' => 'advertencias']) }}"
                class="filter-btn {{ request('tipo') == 'advertencias' ? 'active' : '' }}">
                Advertencias
            </a>
            <a href="{{ route('alarmas.index', ['tipo' => 'resueltas']) }}"
                class="filter-btn {{ request('tipo') == 'resueltas' ? 'active' : '' }}">
                Resueltas
            </a>
        </div>
    </div>

    @forelse($alarmas as $alarma)
        @php
            $tipoClass = match ($alarma->nivel) {
                'critical' => $alarma->activa ? 'critica' : 'resuelta',
                'warning' => $alarma->activa ? 'advertencia' : 'resuelta',
                'info' => $alarma->activa ? 'info' : 'resuelta',
                default => 'resuelta',
            };

            $tipoLabel = match ($alarma->nivel) {
                'critical' => $alarma->activa ? 'Crítica' : 'Resuelta',
                'warning' => $alarma->activa ? 'Advertencia' : 'Resuelta',
                'info' => $alarma->activa ? 'Información' : 'Resuelta',
                default => 'Desconocido',
            };

            $tipoIcon = match ($alarma->nivel) {
                'critical' => $alarma->activa ? 'bi-exclamation-triangle-fill' : 'bi-check-circle-fill',
                'warning' => $alarma->activa ? 'bi-exclamation-circle-fill' : 'bi-check-circle-fill',
                'info' => $alarma->activa ? 'bi-info-circle-fill' : 'bi-check-circle-fill',
                default => 'bi-bell-fill',
            };

            $emoji = match ($alarma->tipo_alarma) {
                'conexion_perdida' => '🚨',
                'servicio_caido' => '🔴',
                'alta_latencia' => '⚠️',
                'conexion_restaurada' => '✅',
                default => '🔔',
            };

            // Calcular duración si está resuelta
            $duracion = null;
            if (!$alarma->activa) {
                $duracion = $alarma->created_at->diffInMinutes($alarma->updated_at);
            }
        @endphp

        <div class="alarma-card {{ $tipoClass }}">
            <div class="alarma-header">
                <div class="alarma-type {{ $tipoClass }}">
                    <i class="bi {{ $tipoIcon }}"></i> {{ $tipoLabel }}
                </div>
                <div class="alarma-time">
                    <i class="bi bi-clock"></i> {{ $alarma->created_at->diffForHumans() }}
                </div>
            </div>

            <h4 class="alarma-title">{{ $emoji }} {{ $alarma->mensaje }}</h4>

            @if ($alarma->detalle)
                <p class="alarma-desc">{{ $alarma->detalle }}</p>
            @endif

            <div class="alarma-meta">
                @if ($alarma->ticket && $alarma->ticket->sede)
                    <div class="alarma-meta-item">
                        <i class="bi bi-building"></i> {{ $alarma->ticket->sede->nombre }}
                        ({{ $alarma->ticket->sede->codigo }})
                    </div>
                @endif

                @if ($alarma->ticket && $alarma->ticket->sede)
                    <div class="alarma-meta-item">
                        <i class="bi bi-hdd-network"></i> IP: {{ $alarma->ticket->sede->ip_monitoreo }}
                    </div>
                @endif

                @if ($alarma->tipo_alarma === 'alta_latencia' && $alarma->metadatos && isset($alarma->metadatos['latencia']))
                    <div class="alarma-meta-item">
                        <i class="bi bi-speedometer"></i> Latencia: {{ $alarma->metadatos['latencia'] }}ms
                    </div>
                @endif

                @if ($alarma->ticket)
                    <div class="alarma-meta-item">
                        <i class="bi bi-ticket-perforated"></i> Ticket: {{ $alarma->ticket->numero_ticket }}
                    </div>
                @endif

                @if ($alarma->ticket && $alarma->ticket->tecnico)
                    <div class="alarma-meta-item">
                        <i class="bi bi-person"></i> Asignado: {{ $alarma->ticket->tecnico->nombre }}
                    </div>
                @elseif($alarma->ticket && !$alarma->ticket->tecnico)
                    <div class="alarma-meta-item">
                        <i class="bi bi-person"></i> Sin asignar
                    </div>
                @endif

                @if (!$alarma->activa && $duracion)
                    <div class="alarma-meta-item">
                        <i class="bi bi-clock-history"></i> Duración: {{ $duracion }} min
                    </div>
                @endif

                @if (!$alarma->activa)
                    <div class="alarma-meta-item">
                        <i class="bi bi-person"></i> Resuelta por: {{ $alarma->resuelto_por ?? 'Sistema' }}
                    </div>
                @endif
            </div>

            <div class="alarma-actions">
                @if ($alarma->ticket)
                    <a href="{{ route('tickets.index', $alarma->ticket->id) }}" class="btn-action btn-action-primary">
                        <i class="bi bi-eye"></i> Ver Detalles
                    </a>
                @endif

                @if ($alarma->activa)
                    <form action="{{ route('alarmas.desactivar', $alarma->id) }}" method="POST" style="display: inline;">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn-action btn-action-success">
                            <i class="bi bi-check-circle"></i> Marcar Resuelta
                        </button>
                    </form>
                @endif

                @if ($alarma->ticket)
                    <a href="{{ route('tickets.index', $alarma->ticket->id) }}" class="btn-action-outline btn-action">
                        <i class="bi bi-ticket-perforated"></i> Ver Ticket
                    </a>
                @elseif($alarma->activa)
                    <a href="{{ route('tickets.create') }}?alarma_id={{ $alarma->id }}"
                        class="btn-action-outline btn-action">
                        <i class="bi bi-plus-circle"></i> Crear Ticket
                    </a>
                @endif

                @if ($alarma->ticket && $alarma->ticket->sede)
                    <a href="{{ route('monitoreo.detalle', $alarma->ticket->sede->id) }}"
                        class="btn-action-outline btn-action">
                        <i class="bi bi-graph-up"></i> Ver Historial
                    </a>
                @endif
            </div>
        </div>
    @empty
        <div class="py-5 text-center">
            <i class="bi bi-bell-slash" style="font-size: 3rem; color: var(--color-6);"></i>
            <p class="mt-3 text-muted">No hay alarmas registradas</p>
        </div>
    @endforelse

    <!-- PAGINATION -->
    @if ($alarmas->hasPages())
        <div class="mt-4 pagination-wrapper">
            <span class="pagination-info">
                Mostrando {{ $alarmas->firstItem() }} - {{ $alarmas->lastItem() }} de {{ $alarmas->total() }} alarmas
            </span>
            {{ $alarmas->links() }}
        </div>
    @endif
@endsection
