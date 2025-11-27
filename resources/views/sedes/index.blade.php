@extends('layouts.app')

@section('title', 'Gestión de Sedes')

@section('content')
    <!-- MENSAJES DE ÉXITO/ERROR -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i> 
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="table-card">
        <div class="table-header">
            <h5><i class="bi bi-building"></i> Lista de Sedes</h5>
            <div class="d-flex gap-2">
                <form method="GET" action="{{ route('sedes.index') }}" class="search-box">
                    <input type="text" name="busqueda" placeholder="🔍 Buscar sede..." value="{{ request('busqueda') }}">
                </form>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#crearSedeModal">
                    <i class="bi bi-plus-circle"></i> Nueva Sede
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Sede</th>
                        <th>Ubicación</th>
                        <th>IP Principal</th>
                        <th>Estado</th>
                        <th>Uptime</th>
                        <th>Última Verificación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sedes as $sede)
                        @php
                            $estadoClass = match($sede->estado_conexion) {
                                'online' => 'online',
                                'offline' => 'offline',
                                'degradado' => 'degraded',
                                default => 'offline'
                            };
                            
                            $estadoLabel = match($sede->estado_conexion) {
                                'online' => 'Online',
                                'offline' => 'Offline',
                                'degradado' => 'Degradado',
                                default => 'Pendiente'
                            };

                            $uptimeColor = $sede->uptime_30d >= 99 ? 'var(--success)' : 
                                          ($sede->uptime_30d >= 95 ? 'var(--warning)' : 'var(--danger)');
                            
                            $codigoCorto = substr($sede->codigo, 0, 3);
                        @endphp
                        <tr>
                            <td>
                                <div class="sede-info">
                                    <div class="sede-avatar">{{ strtoupper($codigoCorto) }}</div>
                                    <div>
                                        <div class="sede-name">{{ $sede->nombre }}</div>
                                        <span class="sede-code">{{ $sede->codigo }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $sede->direccion }}, {{ $sede->ciudad }}</td>
                            <td><code>{{ $sede->ip_monitoreo }}</code></td>
                            <td>
                                <span class="status-badge {{ $estadoClass }}">
                                    <span class="status-dot {{ $estadoClass }}"></span> {{ $estadoLabel }}
                                </span>
                            </td>
                            <td><strong style="color: {{ $uptimeColor }};">{{ $sede->uptime_30d }}%</strong></td>
                            <td style="color: {{ $sede->estado_conexion === 'offline' ? 'var(--danger)' : '' }};">
                                {{ $sede->ultima_verificacion ? $sede->ultima_verificacion->diffForHumans() : 'Nunca' }}
                            </td>
                            <td class="table-actions">
                                <a href="{{ route('monitoreo.sede', $sede->id) }}" class="btn-table btn-table-primary" title="Ver">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <button class="btn-table btn-table-outline" title="Editar" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editarSedeModal{{ $sede->id }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                @if($sede->estado_conexion === 'offline')
                                    <form action="{{ route('monitoreo.forzar', $sede->id) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn-table btn-table-danger" title="Forzar Check">
                                            <i class="bi bi-arrow-clockwise"></i>
                                        </button>
                                    </form>
                                @else
                                    <button class="btn-table btn-table-outline" title="Configurar" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editarSedeModal{{ $sede->id }}">
                                        <i class="bi bi-gear"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>

                        <!-- MODAL EDITAR SEDE -->
                        <div class="modal fade" id="editarSedeModal{{ $sede->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title"><i class="bi bi-pencil"></i> Editar Sede</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form action="{{ route('sedes.update', $sede->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-body">
                                            <div class="row g-3">
                                                <div class="col-12">
                                                    <label class="form-label">Nombre de la Sede *</label>
                                                    <input type="text" name="nombre" class="form-control" value="{{ $sede->nombre }}" required>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label">Código (no editable)</label>
                                                    <input type="text" class="form-control" value="{{ $sede->codigo }}" disabled>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Ciudad *</label>
                                                    <input type="text" name="ciudad" class="form-control" value="{{ $sede->ciudad }}" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Estado</label>
                                                    <select name="activa" class="form-select">
                                                        <option value="1" {{ $sede->activa ? 'selected' : '' }}>Activa</option>
                                                        <option value="0" {{ !$sede->activa ? 'selected' : '' }}>Inactiva</option>
                                                    </select>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label">Dirección *</label>
                                                    <input type="text" name="direccion" class="form-control" value="{{ $sede->direccion }}" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">IP Principal *</label>
                                                    <input type="text" name="ip_monitoreo" class="form-control" value="{{ $sede->ip_monitoreo }}" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">IP Backup</label>
                                                    <input type="text" name="ip_backup" class="form-control" value="{{ $sede->ip_backup }}">
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label">Intervalo de Monitoreo (minutos) *</label>
                                                    <input type="number" name="intervalo_monitoreo" class="form-control" 
                                                           value="{{ $sede->intervalo_monitoreo ?? 5 }}" min="1" max="60" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bi bi-check-circle"></i> Guardar Cambios
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="bi bi-building" style="font-size: 3rem; color: var(--color-6);"></i>
                                <p class="mt-3 text-muted">No hay sedes registradas</p>
                                <button class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#crearSedeModal">
                                    <i class="bi bi-plus-circle"></i> Crear Primera Sede
                                </button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($sedes->hasPages())
            <div class="pagination-wrapper">
                <span class="pagination-info">
                    Mostrando {{ $sedes->firstItem() }} - {{ $sedes->lastItem() }} de {{ $sedes->total() }} sedes
                </span>
                {{ $sedes->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL CREAR SEDE -->
    <div class="modal fade" id="crearSedeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Nueva Sede</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('sedes.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Nombre de la Sede *</label>
                                <input type="text" name="nombre" class="form-control" placeholder="Ej: Sede Principal - Lima" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Código *</label>
                                <input type="text" name="codigo" class="form-control" placeholder="Ej: LIM01" required>
                                <small class="text-muted">Código único de identificación</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Ciudad *</label>
                                <input type="text" name="ciudad" class="form-control" placeholder="Ej: Lima" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Dirección *</label>
                                <input type="text" name="direccion" class="form-control" placeholder="Ej: Av. Principal 123" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">IP Principal *</label>
                                <input type="text" name="ip_monitoreo" class="form-control" placeholder="Ej: 192.168.1.10" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">IP Backup</label>
                                <input type="text" name="ip_backup" class="form-control" placeholder="Opcional">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Intervalo de Monitoreo (minutos) *</label>
                                <input type="number" name="intervalo_monitoreo" class="form-control" value="5" min="1" max="60" required>
                                <small class="text-muted">Cada cuántos minutos se verificará la conexión</small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Crear Sede
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection