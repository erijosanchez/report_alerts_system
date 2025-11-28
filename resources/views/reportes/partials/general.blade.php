<!-- STATS PRINCIPALES -->
<div class="mb-4 row g-3">
    <!-- Tickets -->
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h6 class="mb-3 text-muted"><i class="bi bi-ticket-perforated"></i> Tickets</h6>
                <h3 class="mb-3" style="color: var(--color-6);">{{ $data['tickets']['total'] }}</h3>
                <div class="d-flex justify-content-between text-sm">
                    <span class="text-success">Resueltos: {{ $data['tickets']['resueltos'] }}</span>
                    <span class="text-danger">Abiertos: {{ $data['tickets']['abiertos'] }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Sedes -->
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h6 class="mb-3 text-muted"><i class="bi bi-building"></i> Sedes</h6>
                <h3 class="mb-3" style="color: var(--color-6);">{{ $data['sedes']['total'] }}</h3>
                <div class="d-flex justify-content-between text-sm">
                    <span class="text-success">Online: {{ $data['sedes']['online'] }}</span>
                    <span class="text-danger">Offline: {{ $data['sedes']['offline'] }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Alarmas -->
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h6 class="mb-3 text-muted"><i class="bi bi-bell"></i> Alarmas</h6>
                <h3 class="mb-3" style="color: var(--color-6);">{{ $data['alarmas']['total'] }}</h3>
                <div class="d-flex justify-content-between text-sm">
                    <span class="text-danger">Críticas: {{ $data['alarmas']['criticas'] }}</span>
                    <span class="text-warning">Advertencias: {{ $data['alarmas']['advertencias'] }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- SLA -->
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h6 class="mb-3 text-muted"><i class="bi bi-clock-history"></i> SLA</h6>
                <h3 class="mb-3" style="color: {{ $data['sla']['cumplimiento'] >= 90 ? '#10b981' : '#ef4444' }};">
                    {{ $data['sla']['cumplimiento'] }}%
                </h3>
                <div class="text-sm">
                    <span class="text-muted">Vencidos: {{ $data['sla']['tickets_vencidos'] }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- TIEMPOS PROMEDIO -->
<div class="mb-4 row g-3">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h6><i class="bi bi-speedometer2"></i> Tiempo Promedio de Respuesta</h6>
                <h2 style="color: var(--color-10);">{{ $data['tiempos']['respuesta_promedio'] }} min</h2>
                <p class="mb-0 text-muted">Tiempo desde apertura hasta primera respuesta</p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h6><i class="bi bi-check-circle"></i> Tiempo Promedio de Resolución</h6>
                <h2 style="color: var(--color-10);">{{ round($data['tiempos']['resolucion_promedio'] / 60, 1) }} hrs
                </h2>
                <p class="mb-0 text-muted">Tiempo desde apertura hasta resolución</p>
            </div>
        </div>
    </div>
</div>

<!-- GRÁFICOS -->
<div class="row g-3">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h6><i class="bi bi-pie-chart"></i> Tickets por Estado</h6>
                <canvas id="chartEstadosGeneral"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h6><i class="bi bi-bar-chart"></i> Estado de Sedes</h6>
                <canvas id="chartSedesGeneral"></canvas>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        // Gráfico Tickets
        new Chart(document.getElementById('chartEstadosGeneral'), {
            type: 'doughnut',
            data: {
                labels: ['Abiertos', 'En Proceso', 'Resueltos', 'Cerrados'],
                datasets: [{
                    data: [
                        {{ $data['tickets']['abiertos'] }},
                        {{ $data['tickets']['en_proceso'] }},
                        {{ $data['tickets']['resueltos'] }},
                        {{ $data['tickets']['cerrados'] }}
                    ],
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

        // Gráfico Sedes
        new Chart(document.getElementById('chartSedesGeneral'), {
            type: 'bar',
            data: {
                labels: ['Online', 'Offline', 'Degradado'],
                datasets: [{
                    label: 'Sedes',
                    data: [
                        {{ $data['sedes']['online'] }},
                        {{ $data['sedes']['offline'] }},
                        {{ $data['sedes']['degradado'] }}
                    ],
                    backgroundColor: ['#10b981', '#ef4444', '#f59e0b']
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
