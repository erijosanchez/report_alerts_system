<!-- RESUMEN -->
<div class="mb-4 row g-3">
    <div class="col-md-2">
        <div class="text-center card">
            <div class="card-body">
                <h6 class="text-muted">Total</h6>
                <h3 style="color: var(--color-6);">{{ $data['total'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="text-center card">
            <div class="card-body">
                <h6 class="text-muted">Automáticos</h6>
                <h3 style="color: #6366f1;">{{ $data['automaticos'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="text-center card">
            <div class="card-body">
                <h6 class="text-muted">Manuales</h6>
                <h3 style="color: #10b981;">{{ $data['manuales'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="text-center card">
            <div class="card-body">
                <h6 class="text-muted">T. Respuesta</h6>
                <h3 style="color: var(--color-10);">{{ $data['tiempo_respuesta_promedio'] }} min</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="text-center card">
            <div class="card-body">
                <h6 class="text-muted">T. Resolución</h6>
                <h3 style="color: var(--color-10);">{{ round($data['tiempo_resolucion_promedio'] / 60, 1) }} hrs</h3>
            </div>
        </div>
    </div>
</div>

<!-- GRÁFICOS -->
<div class="mb-4 row g-3">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h6><i class="bi bi-pie-chart"></i> Por Estado</h6>
                <canvas id="chartPorEstado"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h6><i class="bi bi-pie-chart"></i> Por Prioridad</h6>
                <canvas id="chartPorPrioridad"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h6><i class="bi bi-pie-chart"></i> Por Categoría</h6>
                <canvas id="chartPorCategoria"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- TABLA DE TICKETS -->
<div class="card">
    <div class="card-body">
        <h6 class="mb-3"><i class="bi-table bi"></i> Detalle de Tickets</h6>
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Título</th>
                        <th>Estado</th>
                        <th>Prioridad</th>
                        <th>Categoría</th>
                        <th>Cliente</th>
                        <th>Técnico</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data['tickets'] as $ticket)
                        <tr>
                            <td><span class="badge-ticket">{{ $ticket->numero_ticket }}</span></td>
                            <td>{{ Str::limit($ticket->titulo, 30) }}</td>
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
                                <span class="badge {{ $estadoBadge }}">{{ ucfirst($ticket->estado) }}</span>
                            </td>
                            <td>
                                @php
                                    $prioridadBadge = match (strtolower($ticket->prioridad->nombre)) {
                                        'urgente', 'crítica' => 'bg-danger',
                                        'alta' => 'bg-warning',
                                        'media' => 'bg-info',
                                        'baja' => 'bg-success',
                                        default => 'bg-secondary',
                                    };
                                @endphp
                                <span class="badge {{ $prioridadBadge }}">{{ $ticket->prioridad->nombre }}</span>
                            </td>
                            <td>{{ $ticket->categoria->nombre }}</td>
                            <td>{{ $ticket->usuario->nombre }}</td>
                            <td>{{ $ticket->tecnico->nombre ?? 'Sin asignar' }}</td>
                            <td>{{ $ticket->fecha_apertura->format('d/m/Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        // Por Estado
        new Chart(document.getElementById('chartPorEstado'), {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($data['por_estado']->keys()) !!},
                datasets: [{
                    data: {!! json_encode($data['por_estado']->values()) !!},
                    backgroundColor: ['#f59e0b', '#3b82f6', '#10b981', '#6b7280']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Por Prioridad
        new Chart(document.getElementById('chartPorPrioridad'), {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($data['por_prioridad']->keys()) !!},
                datasets: [{
                    data: {!! json_encode($data['por_prioridad']->values()) !!},
                    backgroundColor: ['#ef4444', '#f59e0b', '#3b82f6', '#10b981']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Por Categoría
        new Chart(document.getElementById('chartPorCategoria'), {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($data['por_categoria']->keys()) !!},
                datasets: [{
                    data: {!! json_encode($data['por_categoria']->values()) !!},
                    backgroundColor: ['#6366f1', '#8b5cf6', '#ec4899', '#f59e0b', '#10b981']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
@endpush
