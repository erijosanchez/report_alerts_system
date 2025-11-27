@extends('layouts.app')

@section('title', 'Gestión de Usuarios')

@section('content')
    <!-- MENSAJES DE ÉXITO/ERROR -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- STATS ROW -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <i class="bi bi-people-fill" style="font-size: 2rem; color: var(--color-6);"></i>
                    <h3 class="mt-2 mb-0">{{ $stats['total'] }}</h3>
                    <p class="text-muted mb-0">Total Usuarios</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <i class="bi bi-shield-fill-check" style="font-size: 2rem; color: #6366f1;"></i>
                    <h3 class="mt-2 mb-0">{{ $stats['admins'] }}</h3>
                    <p class="text-muted mb-0">Administradores</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <i class="bi bi-tools" style="font-size: 2rem; color: #f59e0b;"></i>
                    <h3 class="mt-2 mb-0">{{ $stats['tecnicos'] }}</h3>
                    <p class="text-muted mb-0">Técnicos</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <i class="bi bi-person-fill" style="font-size: 2rem; color: #10b981;"></i>
                    <h3 class="mt-2 mb-0">{{ $stats['clientes'] }}</h3>
                    <p class="text-muted mb-0">Clientes</p>
                </div>
            </div>
        </div>
    </div>

    <div class="table-card">
        <div class="table-header">
            <h5><i class="bi bi-people-fill"></i> Lista de Usuarios</h5>
            <div class="d-flex gap-2 flex-wrap">
                <form method="GET" action="{{ route('usuarios.index') }}" class="d-flex gap-2">
                    <input type="text" name="busqueda" class="form-control" placeholder="🔍 Buscar usuario..."
                        value="{{ request('busqueda') }}" style="width: 200px;">

                    <select name="rol" class="form-select" style="width: 150px;">
                        <option value="">Todos los roles</option>
                        <option value="admin" {{ request('rol') == 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="tecnico" {{ request('rol') == 'tecnico' ? 'selected' : '' }}>Técnico</option>
                        <option value="cliente" {{ request('rol') == 'cliente' ? 'selected' : '' }}>Cliente</option>
                    </select>

                    <select name="estado" class="form-select" style="width: 130px;">
                        <option value="">Todos</option>
                        <option value="1" {{ request('estado') === '1' ? 'selected' : '' }}>Activos</option>
                        <option value="0" {{ request('estado') === '0' ? 'selected' : '' }}>Inactivos</option>
                    </select>

                    <button type="submit" class="btn btn-outline-primary">
                        <i class="bi bi-search"></i>
                    </button>

                    <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i>
                    </a>
                </form>

                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#crearUsuarioModal">
                    <i class="bi bi-plus-circle"></i> Nuevo Usuario
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Teléfono</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($usuarios as $usuario)
                        @php
                            $rolColor = match ($usuario->rol) {
                                'admin' => ['bg' => '#6366f1', 'icon' => 'bi-shield-fill-check'],
                                'tecnico' => ['bg' => '#f59e0b', 'icon' => 'bi-tools'],
                                'cliente' => ['bg' => '#10b981', 'icon' => 'bi-person-fill'],
                                default => ['bg' => '#6b7280', 'icon' => 'bi-person'],
                            };

                            $iniciales = strtoupper(substr($usuario->nombre, 0, 1) . substr($usuario->apellido, 0, 1));
                        @endphp
                        <tr style="opacity: {{ $usuario->estado ? '1' : '0.6' }};">
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                                        style="width: 40px; height: 40px; background: {{ $rolColor['bg'] }}; color: white; font-weight: bold;">
                                        {{ $iniciales }}
                                    </div>
                                    <div>
                                        <div class="fw-bold">{{ $usuario->nombre }} {{ $usuario->apellido }}</div>
                                        @if ($usuario->cargo)
                                            <small class="text-muted">{{ $usuario->cargo }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>{{ $usuario->email }}</td>
                            <td>
                                <span class="badge" style="background: {{ $rolColor['bg'] }};">
                                    <i class="bi {{ $rolColor['icon'] }}"></i> {{ ucfirst($usuario->rol) }}
                                </span>
                            </td>
                            <td>{{ $usuario->telefono ?? '-' }}</td>
                            <td>
                                <form action="{{ route('usuarios.toggle', $usuario->id) }}" method="POST"
                                    style="display: inline;">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                        class="badge border-0 {{ $usuario->estado ? 'bg-success' : 'bg-secondary' }}"
                                        style="cursor: pointer;">
                                        <i class="bi bi-circle-fill"></i> {{ $usuario->estado ? 'Activo' : 'Inactivo' }}
                                    </button>
                                </form>
                            </td>
                            <td class="table-actions">
                                <button class="btn-table btn-table-outline" title="Editar" data-bs-toggle="modal"
                                    data-bs-target="#editarUsuarioModal{{ $usuario->id }}">
                                    <i class="bi bi-pencil"></i>
                                </button>

                                @if ($usuario->id !== auth()->id())
                                    <form action="{{ route('usuarios.destroy', $usuario->id) }}" method="POST"
                                        style="display: inline;"
                                        onsubmit="return confirm('¿Estás seguro de eliminar este usuario?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-table btn-table-danger" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>

                        <!-- MODAL EDITAR USUARIO -->
                        <div class="modal fade" id="editarUsuarioModal{{ $usuario->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title"><i class="bi bi-pencil"></i> Editar Usuario</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form action="{{ route('usuarios.update', $usuario->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-body">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">Nombre *</label>
                                                    <input type="text" name="nombre" class="form-control"
                                                        value="{{ $usuario->nombre }}" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Apellido *</label>
                                                    <input type="text" name="apellido" class="form-control"
                                                        value="{{ $usuario->apellido }}" required>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label">Email *</label>
                                                    <input type="email" name="email" class="form-control"
                                                        value="{{ $usuario->email }}" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Teléfono</label>
                                                    <input type="text" name="telefono" class="form-control"
                                                        value="{{ $usuario->telefono }}">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Cargo</label>
                                                    <input type="text" name="cargo" class="form-control"
                                                        value="{{ $usuario->cargo }}">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Rol *</label>
                                                    <select name="rol" class="form-select" required>
                                                        <option value="admin"
                                                            {{ $usuario->rol == 'admin' ? 'selected' : '' }}>Administrador
                                                        </option>
                                                        <option value="tecnico"
                                                            {{ $usuario->rol == 'tecnico' ? 'selected' : '' }}>Técnico
                                                        </option>
                                                        <option value="cliente"
                                                            {{ $usuario->rol == 'cliente' ? 'selected' : '' }}>Cliente
                                                        </option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Estado</label>
                                                    <select name="estado" class="form-select">
                                                        <option value="1" {{ $usuario->estado ? 'selected' : '' }}>
                                                            Activo</option>
                                                        <option value="0" {{ !$usuario->estado ? 'selected' : '' }}>
                                                            Inactivo</option>
                                                    </select>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label">Nueva Contraseña</label>
                                                    <input type="password" name="password" class="form-control"
                                                        placeholder="Dejar en blanco para mantener actual">
                                                    <small class="text-muted">Mínimo 6 caracteres</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Cancelar</button>
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
                            <td colspan="6" class="text-center py-5">
                                <i class="bi bi-people" style="font-size: 3rem; color: var(--color-6);"></i>
                                <p class="mt-3 text-muted">No hay usuarios registrados</p>
                                <button class="btn btn-primary mt-2" data-bs-toggle="modal"
                                    data-bs-target="#crearUsuarioModal">
                                    <i class="bi bi-plus-circle"></i> Crear Primer Usuario
                                </button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($usuarios->hasPages())
            <div class="pagination-wrapper">
                <span class="pagination-info">
                    Mostrando {{ $usuarios->firstItem() }} - {{ $usuarios->lastItem() }} de {{ $usuarios->total() }}
                    usuarios
                </span>
                {{ $usuarios->appends(request()->query())->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL CREAR USUARIO -->
    <div class="modal fade" id="crearUsuarioModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Nuevo Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('usuarios.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nombre *</label>
                                <input type="text" name="nombre" class="form-control" placeholder="Ej: Juan"
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Apellido *</label>
                                <input type="text" name="apellido" class="form-control" placeholder="Ej: Pérez"
                                    required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Email *</label>
                                <input type="email" name="email" class="form-control"
                                    placeholder="ejemplo@correo.com" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Teléfono</label>
                                <input type="text" name="telefono" class="form-control" placeholder="999 999 999">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Cargo</label>
                                <input type="text" name="cargo" class="form-control"
                                    placeholder="Ej: Técnico Senior">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Rol *</label>
                                <select name="rol" class="form-select" required>
                                    <option value="">Seleccionar...</option>
                                    <option value="admin">Administrador</option>
                                    <option value="tecnico">Técnico</option>
                                    <option value="cliente">Cliente</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Contraseña *</label>
                                <input type="password" name="password" class="form-control"
                                    placeholder="Mínimo 6 caracteres" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Crear Usuario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
