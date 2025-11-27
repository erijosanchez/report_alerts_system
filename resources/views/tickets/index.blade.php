@extends('layouts.app')

@section('title', 'Tickets')

@section('content')

    <!-- STATS BAR -->
    <div class="stats-bar">
        <div class="stats-bar-content">
            <div class="stat-item">
                <div class="stat-item-value" style="color: var(--color-6);">{{ $stats['total'] }}</div>
                <div class="stat-item-label">Total</div>
            </div>
            <div class="stat-divider"></div>
            <div class="stat-item">
                <div class="stat-item-value" style="color: #f59e0b;">{{ $stats['abiertos'] }}</div>
                <div class="stat-item-label">Abiertos</div>
            </div>
            <div class="stat-divider"></div>
            <div class="stat-item">
                <div class="stat-item-value" style="color: var(--color-5);">{{ $stats['en_proceso'] }}</div>
                <div class="stat-item-label">En Proceso</div>
            </div>
            <div class="stat-divider"></div>
            <div class="stat-item">
                <div class="stat-item-value" style="color: #10b981;">{{ $stats['resueltos'] }}</div>
                <div class="stat-item-label">Resueltos</div>
            </div>
            <div class="stat-divider"></div>
            <div class="stat-item">
                <div class="stat-item-value" style="color: #ef4444;">{{ $stats['criticos'] }}</div>
                <div class="stat-item-label">Críticos</div>
            </div>
        </div>
    </div>

    <!-- FILTERS -->
    <div class="filters-card">
        <h5><i class="bi bi-funnel"></i> Filtros</h5>
        <form method="GET" action="{{ route('tickets.index') }}">
            <div class="row g-3">
                <div class="col-12 col-md-6 col-lg-2">
                    <input type="text" name="busqueda" class="form-control" placeholder="🔍 Buscar por ID o título..." value="{{ request('busqueda') }}">
                </div>
                <div class="col-6 col-md-3 col-lg-2">
                    <select name="estado" class="form-select">
                        <option value="">Todos los estados</option>
                        <option value="abierto" {{ request('estado') == 'abierto' ? 'selected' : '' }}>Abierto</option>
                        <option value="en_proceso" {{ request('estado') == 'en_proceso' ? 'selected' : '' }}>En Proceso</option>
                        <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                        <option value="resuelto" {{ request('estado') == 'resuelto' ? 'selected' : '' }}>Resuelto</option>
                        <option value="cerrado" {{ request('estado') == 'cerrado' ? 'selected' : '' }}>Cerrado</option>
                    </select>
                </div>
                <div class="col-6 col-md-3 col-lg-2">
                    <select name="prioridad_id" class="form-select">
                        <option value="">Prioridades</option>
                        @foreach($prioridades as $prioridad)
                            <option value="{{ $prioridad->id }}" {{ request('prioridad_id') == $prioridad->id ? 'selected' : '' }}>
                                {{ $prioridad->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @if(auth()->user()->esAdmin())
                <div class="col-6 col-md-3 col-lg-2">
                    <select name="tecnico_id" class="form-select">
                        <option value="">Técnicos</option>
                        @foreach($tecnicos as $tecnico)
                            <option value="{{ $tecnico->id }}" {{ request('tecnico_id') == $tecnico->id ? 'selected' : '' }}>
                                {{ $tecnico->nombre }}
                            </option>
                        @endforeach
                        <option value="sin_asignar" {{ request('tecnico_id') == 'sin_asignar' ? 'selected' : '' }}>Sin asignar</option>
                    </select>
                </div>
                @endif
                <div class="col-6 col-md-3 col-lg-2">
                    <select name="origen" class="form-select">
                        <option value="">Origen</option>
                        <option value="manual" {{ request('origen') == 'manual' ? 'selected' : '' }}>Manual</option>
                        <option value="automatico" {{ request('origen') == 'automatico' ? 'selected' : '' }}>Automático</option>
                    </select>
                </div>
                <div class="col-6 col-md-3 col-lg-1">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
                <div class="col-6 col-md-3 col-lg-1">
                    <a href="{{ route('tickets.index') }}" class="btn-filter-clear w-100">
                        <i class="bi bi-x-circle"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- TICKETS GRID -->
    <div class="tickets-grid">
        @forelse($tickets as $ticket)
            @php
                $prioridadNombre = strtolower($ticket->prioridad->nombre);
                $prioridadClass = match($prioridadNombre) {
                    'urgente', 'crítica' => 'critica',
                    'alta' => 'alta',
                    'media' => 'media',
                    'baja' => 'baja',
                    default => 'media'
                };
                
                $prioridadColor = match($prioridadNombre) {
                    'urgente', 'crítica' => ['badge' => 'danger', 'text' => '#dc2626'],
                    'alta' => ['badge' => 'warning text-dark', 'text' => '#d97706'],
                    'media' => ['badge' => 'warning', 'text' => '#f59e0b'],
                    'baja' => ['badge' => 'success', 'text' => '#10b981'],
                    default => ['badge' => 'secondary', 'text' => '#6b7280']
                };

                $estadoColor = match($ticket->estado) {
                    'abierto' => 'style="background: var(--color-6); color: white;"',
                    'en_proceso' => 'style="background: var(--color-5); color: white;"',
                    'pendiente' => 'class="bg-warning text-dark"',
                    'resuelto' => 'class="bg-success"',
                    'cerrado' => 'class="bg-secondary"',
                    default => 'class="bg-secondary"'
                };

                $opacidad = in_array($ticket->estado, ['resuelto', 'cerrado']) ? 'style="opacity: 0.85;"' : '';
            @endphp

            <div class="ticket-card {{ $prioridadClass }}" {!! $opacidad !!}>
                <div class="ticket-header">
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <span class="ticket-id">{{ $ticket->numero_ticket }}</span>
                        <span class="badge bg-{{ $prioridadColor['badge'] }}">{{ ucfirst($ticket->prioridad->nombre) }}</span>
                        @if($ticket->origen === 'automatico')
                            <span class="badge badge-auto">
                                <i class="bi bi-robot"></i> Automático
                            </span>
                        @endif
                    </div>
                    <span class="badge" {!! $estadoColor !!}>{{ ucfirst(str_replace('_', ' ', $ticket->estado)) }}</span>
                </div>

                <div class="ticket-title">
                    {{ $ticket->titulo }}
                </div>

                <p class="ticket-desc">
                    {{ Str::limit($ticket->descripcion, 120) }}
                </p>

                <div class="ticket-meta">
                    <div class="ticket-meta-item">
                        <i class="bi bi-person-circle"></i>
                        <span>{{ $ticket->usuario->nombre }} {{ $ticket->usuario->apellido }}</span>
                    </div>
                    
                    @if($ticket->tecnico)
                        <div class="ticket-meta-item">
                            <i class="bi bi-person-badge"></i>
                            <span><strong>{{ $ticket->tecnico->nombre }}</strong></span>
                        </div>
                    @else
                        <div class="ticket-meta-item text-warning-custom">
                            <i class="bi bi-person-badge"></i>
                            <strong>Sin asignar</strong>
                        </div>
                    @endif

                    @if($ticket->categoria)
                        <div class="ticket-meta-item">
                            <i class="bi bi-tag"></i>
                            <span>{{ $ticket->categoria->nombre }}</span>
                        </div>
                    @endif

                    <div class="ticket-meta-item">
                        <i class="bi bi-clock"></i>
                        <span>{{ $ticket->fecha_apertura->diffForHumans() }}</span>
                    </div>

                    @if($ticket->estado === 'resuelto' && $ticket->tiempo_resolucion_minutos)
                        <div class="ticket-meta-item text-success-custom">
                            <i class="bi bi-check-circle"></i>
                            <strong>Resuelto en {{ round($ticket->tiempo_resolucion_minutos / 60, 1) }}h</strong>
                        </div>
                    @endif
                </div>

                <div class="ticket-footer">
                    <div class="ticket-actions">
                        <a href="{{ route('tickets.show', $ticket->id) }}" class="btn-action btn-action-primary">
                            <i class="bi bi-eye"></i> Ver Detalle
                        </a>
                        
                        @if(auth()->user()->esStaff() && $ticket->estado !== 'resuelto' && $ticket->estado !== 'cerrado')
                            <form action="{{ route('tickets.cambiar-estado', $ticket->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="estado" value="resuelto">
                                <button type="submit" class="btn-action btn-action-success">
                                    <i class="bi bi-check-circle"></i> Resolver
                                </button>
                            </form>
                        @endif

                        @if(auth()->user()->esAdmin() && !$ticket->tecnico)
                            <button class="btn-action btn-action-warning" data-bs-toggle="modal" data-bs-target="#asignarModal{{ $ticket->id }}">
                                <i class="bi bi-person-plus"></i> Asignar
                            </button>
                        @endif

                        @if($ticket->comentarios_count > 0)
                            <button class="btn-action btn-action-outline">
                                <i class="bi bi-chat-dots"></i> {{ $ticket->comentarios_count }}
                            </button>
                        @endif
                    </div>

                    @if($ticket->estado === 'resuelto' && $ticket->calificacion)
                        <div>
                            <span class="badge bg-warning text-dark">
                                <i class="bi bi-star-fill"></i> {{ number_format($ticket->calificacion, 1) }}
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <i class="bi bi-inbox" style="font-size: 3rem; color: var(--color-6);"></i>
                <p class="mt-3 text-muted">No se encontraron tickets</p>
                <a href="{{ route('tickets.create') }}" class="btn btn-primary mt-2">
                    <i class="bi bi-plus-circle"></i> Crear Nuevo Ticket
                </a>
            </div>
        @endforelse
    </div>

    <!-- PAGINATION -->
    @if($tickets->hasPages())
        <div class="pagination-wrapper">
            <span class="pagination-info">
                Mostrando {{ $tickets->firstItem() }} - {{ $tickets->lastItem() }} de {{ $tickets->total() }} tickets
            </span>
            {{ $tickets->links() }}
        </div>
    @endif

@endsection