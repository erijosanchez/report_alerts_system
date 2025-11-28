<!-- TABLA DE TÉCNICOS -->
<div class="card">
    <div class="card-body">
        <h6 class="mb-3"><i class="bi bi-people"></i> Rendimiento de Técnicos</h6>
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Técnico</th>
                        <th>Tickets Asignados</th>
                        <th>Tickets Resueltos</th>
                        <th>Tickets Pendientes</th>
                        <th>T. Promedio Resolución</th>
                        <th>Tasa Resolución</th>
                        <th>Cumplimiento SLA</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data['estadisticas'] as $stat)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="d-flex align-items-center justify-content-center rounded-circle"
                                        style="width: 35px; height: 35px; background: #f59e0b; color: white; font-weight: bold; font-size: 0.8rem;">
                                        {{ strtoupper(substr($stat['tecnico']->nombre, 0, 1) . substr($stat['tecnico']->apellido, 0, 1)) }}
                                    </div>
                                    <strong>{{ $stat['tecnico']->nombre }} {{ $stat['tecnico']->apellido }}</strong>
                                </div>
                            </td>
                            <td><span class="bg-primary badge">{{ $stat['tickets_asignados'] }}</span></td>
                            <td><span class="bg-success badge">{{ $stat['tickets_resueltos'] }}</span></td>
                            <td><span class="bg-warning badge">{{ $stat['tickets_pendientes'] }}</span></td>
                            <td>{{ round($stat['tiempo_promedio_resolucion'] / 60, 1) }} hrs</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress" style="width: 100px; height: 8px;">
                                        <div class="progress-bar"
                                            style="width: {{ $stat['tasa_resolucion'] }}%; background: {{ $stat['tasa_resolucion'] >= 80 ? '#10b981' : '#f59e0b' }};">
                                        </div>
                                    </div>
                                    <span style="color: {{ $stat['tasa_resolucion'] >= 80 ? '#10b981' : '#f59e0b' }};">
                                        <strong>{{ $stat['tasa_resolucion'] }}%</strong>
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress" style="width: 100px; height: 8px;">
                                        <div class="progress-bar"
                                            style="width: {{ $stat['sla_cumplimiento'] }}%; background: {{ $stat['sla_cumplimiento'] >= 90 ? '#10b981' : '#ef4444' }};">
                                        </div>
                                    </div>
                                    <span
                                        style="color: {{ $stat['sla_cumplimiento'] >= 90 ? '#10b981' : '#ef4444' }};">
                                        <strong>{{ $stat['sla_cumplimiento'] }}%</strong>
                                    </span>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- GRÁFICOS -->
<div class="mt-4 row g-3">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h6><i class="bi bi-bar-chart"></i> Tickets Asignados por Técnico</h6>
                <canvas id="chartTicketsAsignados"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h6><i class="bi bi-bar-chart"></i> Cumplimiento SLA por Técnico</h6>
                <canvas id="chartSLATecnicos"></canvas>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        const tecnicos = {!! json_encode($data['estadisticas']->pluck('tecnico.nombre')->values()) !!};

        // Tickets Asignados
        new Chart(document.getElementById('chartTicketsAsignados'), {
            type: 'bar',
            data: {
                labels: tecnicos,
                datasets: [{
                    label: 'Tickets Asignados',
                    data: {!! json_encode($data['estadisticas']->pluck('tickets_asignados')->values()) !!},
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

        // SLA
        new Chart(document.getElementById('chartSLATecnicos'), {
            type: 'bar',
            data: {
                labels: tecnicos,
                datasets: [{
                    label: 'Cumplimiento SLA (%)',
                    data: {!! json_encode($data['estadisticas']->pluck('sla_cumplimiento')->values()) !!},
                    backgroundColor: function(context) {
                        const value = context.parsed.y;
                        return value >= 90 ? '#10b981' : '#ef4444';
                    }
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
                        beginAtZero: true,
                        max: 100
                    }
                }
            }
        });
    </script>
@endpush
