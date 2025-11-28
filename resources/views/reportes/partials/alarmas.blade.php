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
                <h6 class="text-muted">Activas</h6>
                <h3 style="color: #ef4444;">{{ $data['activas'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="text-center card">
            <div class="card-body">
                <h6 class="text-muted">Resueltas</h6>
                <h3 style="color: #10b981;">{{ $data['resueltas'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="text-center card">
            <div class="card-body">
                <h6 class="text-muted">Tiempo Promedio de Resolución</h6>
                <h3 style="color: var(--color-10);">{{ $data['tiempo_promedio_resolucion'] }} min</h3>
            </div>
        </div>
    </div>
</div>

<!-- GRÁFICOS -->
<div class="mb-4 row g-3">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h6><i class="bi bi-pie-chart"></i> Por Nivel</h6>
                <canvas id="chartPorNivel"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h6><i class="bi bi-bar-chart"></i> Por Tipo</h6>
                <canvas id="chartPorTipo"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- TABLA -->
<div class="card">
    <div class="card-body">
        <h6 class="mb-3"><i class="bi bi-bell"></i> Detalle de Alarmas</h6>
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Nivel</th>
                        <th>Tipo</th>
                        <th>Mensaje</th>
                        <th>Sede</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data['alarmas'] as $alarma)
                        <tr>
                            <td>
                                @php
                                    $nivelBadge = match ($alarma->nivel) {
                                        'critical' => 'bg-danger',
                                        'warning' => 'bg-warning',
                                        'info' => 'bg-info',
                                        default => 'bg-secondary',
                                    };
                                @endphp
                                <span class="badge {{ $nivelBadge }}">{{ strtoupper($alarma->nivel) }}</span>
                            </td>
                            <td>{{ ucfirst(str_replace('_', ' ', $alarma->tipo_alarma)) }}</td>
                            <td>{{ Str::limit($alarma->mensaje, 50) }}</td>
                            <td>{{ $alarma->ticket->sede->nombre ?? '-' }}</td>
                            <td>
                                @if ($alarma->activa)
                                    <span class="bg-danger badge">Activa</span>
                                @else
                                    <span class="bg-success badge">Resuelta</span>
                                @endif
                            </td>
                            <td>{{ $alarma->created_at->format('d/m/Y H:i') }}</td>
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
        // Por Nivel
        new Chart(document.getElementById('chartPorNivel'), {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($data['por_nivel']->keys()) !!},
                datasets: [{
                    data: {!! json_encode($data['por_nivel']->values()) !!},
                    backgroundColor: ['#ef4444', '#f59e0b', '#3b82f6']
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

        // Por Tipo
        new Chart(document.getElementById('chartPorTipo'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($data['por_tipo']->keys()) !!},
                datasets: [{
                    label: 'Cantidad',
                    data: {!! json_encode($data['por_tipo']->values()) !!},
                    backgroundColor: '#6366f1'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
@endpush
