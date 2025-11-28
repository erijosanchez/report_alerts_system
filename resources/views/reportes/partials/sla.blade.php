<!-- RESUMEN SLA -->
<div class="mb-4 row g-3">
    <div class="col-md-3">
        <div class="text-center card">
            <div class="card-body">
                <h6 class="text-muted">Total Tickets con SLA</h6>
                <h3 style="color: var(--color-6);">{{ $data['total_tickets'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="text-center card">
            <div class="card-body">
                <h6 class="text-muted">Cumplidos</h6>
                <h3 style="color: #10b981;">{{ $data['tickets_cumplidos'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="text-center card">
            <div class="card-body">
                <h6 class="text-muted">Vencidos</h6>
                <h3 style="color: #ef4444;">{{ $data['tickets_vencidos'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="text-center card">
            <div class="card-body">
                <h6 class="text-muted">% Cumplimiento</h6>
                <h2 style="color: {{ $data['porcentaje_cumplimiento'] >= 90 ? '#10b981' : '#ef4444' }};">
                    {{ $data['porcentaje_cumplimiento'] }}%
                </h2>
            </div>
        </div>
    </div>
</div>

<!-- GRÁFICO GENERAL -->
<div class="mb-4 row g-3">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h6><i class="bi bi-pie-chart"></i> Cumplimiento SLA General</h6>
                <canvas id="chartSLAGeneral"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h6><i class="bi bi-bar-chart"></i> SLA por Prioridad</h6>
                <canvas id="chartSLAPrioridad"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- TABLA SLA POR PRIORIDAD -->
<div class="mb-4 card">
    <div class="card-body">
        <h6 class="mb-3"><i class="bi bi-clock-history"></i> Cumplimiento SLA por Prioridad</h6>
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Prioridad</th>
                        <th>Total Tickets</th>
                        <th>Cumplidos</th>
                        <th>Vencidos</th>
                        <th>% Cumplimiento</th>
                        <th>Indicador</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data['por_prioridad'] as $prioridad => $stats)
                        <tr>
                            <td>
                                @php
                                    $prioridadBadge = match (strtolower($prioridad)) {
                                        'urgente', 'crítica' => 'bg-danger',
                                        'alta' => 'bg-warning text-dark',
                                        'media' => 'bg-warning',
                                        'baja' => 'bg-success',
                                        default => 'bg-secondary',
                                    };
                                @endphp
                                <span class="badge {{ $prioridadBadge }}">{{ $prioridad }}</span>
                            </td>
                            <td><strong>{{ $stats['total'] }}</strong></td>
                            <td><span class="bg-success badge">{{ $stats['cumplidos'] }}</span></td>
                            <td><span class="bg-danger badge">{{ $stats['vencidos'] }}</span></td>
                            <td>
                                <strong style="color: {{ $stats['porcentaje'] >= 90 ? '#10b981' : '#ef4444' }};">
                                    {{ $stats['porcentaje'] }}%
                                </strong>
                            </td>
                            <td>
                                <div class="progress" style="width: 150px; height: 10px;">
                                    <div class="progress-bar"
                                        style="width: {{ $stats['porcentaje'] }}%; background: {{ $stats['porcentaje'] >= 90 ? '#10b981' : '#ef4444' }};">
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- TICKETS VENCIDOS -->
@if ($data['tickets_vencidos_detalle']->count() > 0)
    <div class="card">
        <div class="card-body">
            <h6 class="mb-3"><i class="bi bi-exclamation-triangle"></i> Tickets con SLA Vencido</h6>
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Título</th>
                            <th>Prioridad</th>
                            <th>Técnico</th>
                            <th>SLA Vencimiento</th>
                            <th>Fecha Cierre</th>
                            <th>Tiempo Excedido</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data['tickets_vencidos_detalle'] as $ticket)
                            <tr>
                                <td><span class="badge-ticket">{{ $ticket->numero_ticket }}</span></td>
                                <td>{{ Str::limit($ticket->titulo, 40) }}</td>
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
                                <td>{{ $ticket->tecnico->nombre ?? 'Sin asignar' }}</td>
                                <td>{{ $ticket->sla_vencimiento ? $ticket->sla_vencimiento->format('d/m/Y H:i') : '-' }}
                                </td>
                                <td>{{ $ticket->fecha_cierre ? $ticket->fecha_cierre->format('d/m/Y H:i') : '-' }}</td>
                                <td>
                                    @if ($ticket->sla_vencimiento && $ticket->fecha_cierre)
                                        <span class="bg-danger badge">
                                            {{ $ticket->sla_vencimiento->diffInHours($ticket->fecha_cierre) }} hrs
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        // SLA General
        new Chart(document.getElementById('chartSLAGeneral'), {
            type: 'doughnut',
            data: {
                labels: ['Cumplidos', 'Vencidos'],
                datasets: [{
                    data: [{{ $data['tickets_cumplidos'] }}, {{ $data['tickets_vencidos'] }}],
                    backgroundColor: ['#10b981', '#ef4444']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = {{ $data['total_tickets'] }};
                                const value = context.parsed;
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(2) : 0;
                                return context.label + ': ' + value + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });

        // SLA por Prioridad
        const prioridades = {!! json_encode(array_keys($data['por_prioridad'])) !!};
        const porcentajes = {!! json_encode(array_column($data['por_prioridad'], 'porcentaje')) !!};

        new Chart(document.getElementById('chartSLAPrioridad'), {
            type: 'bar',
            data: {
                labels: prioridades,
                datasets: [{
                    label: 'Cumplimiento SLA (%)',
                    data: porcentajes,
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
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                }
            }
        });
    </script>
@endpush
