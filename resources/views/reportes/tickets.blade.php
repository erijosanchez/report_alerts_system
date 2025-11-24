<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Tickets - Trimax</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .report-container {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .stat-card h3 {
            font-size: 2.5rem;
            margin: 0;
        }

        .stat-card p {
            margin: 0;
            opacity: 0.9;
        }

        .chart-container {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .filters {
            background: #e9ecef;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
        }

        .badge-estado {
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 500;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="report-container">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1><i class="bi bi-file-earmark-bar-graph"></i> Reporte de Tickets</h1>
                    <p class="text-muted">Período: {{ date('d/m/Y', strtotime($desde)) }} -
                        {{ date('d/m/Y', strtotime($hasta)) }}</p>
                </div>
                <div>
                    <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Volver
                    </a>
                    <a href="{{ route('reportes.pdf', 'tickets') }}" class="btn btn-danger">
                        <i class="bi bi-file-pdf"></i> Exportar PDF
                    </a>
                </div>
            </div>

            <!-- Filtros -->
            <div class="filters">
                <form method="GET" action="{{ route('reportes.tickets') }}">
                    <div class="row">
                        <div class="col-md-4">
                            <label class="form-label">Fecha Inicio</label>
                            <input type="date" name="desde" class="form-control"
                                value="{{ date('Y-m-d', strtotime($desde)) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Fecha Fin</label>
                            <input type="date" name="hasta" class="form-control"
                                value="{{ date('Y-m-d', strtotime($hasta)) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-funnel"></i> Filtrar
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Estadísticas Principales -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stat-card">
                        <h3>{{ $resumen['total'] }}</h3>
                        <p>Total Tickets</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                        <h3>{{ $resumen['por_estado']['cerrado'] ?? 0 }}</h3>
                        <p>Cerrados</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card" style="background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);">
                        <h3>{{ $resumen['por_estado']['en_proceso'] ?? 0 }}</h3>
                        <p>En Proceso</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);">
                        <h3>{{ $resumen['por_estado']['abierto'] ?? 0 }}</h3>
                        <p>Abiertos</p>
                    </div>
                </div>
            </div>

            <!-- Gráficos -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="chart-container">
                        <h5><i class="bi bi-pie-chart"></i> Tickets por Categoría</h5>
                        <canvas id="chartCategorias"></canvas>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="chart-container">
                        <h5><i class="bi bi-bar-chart"></i> Tickets por Prioridad</h5>
                        <canvas id="chartPrioridades"></canvas>
                    </div>
                </div>
            </div>

            <!-- Métricas Adicionales -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="bi bi-clock-history text-primary" style="font-size: 2rem;"></i>
                            <h4 class="mt-2">{{ $resumen['tiempo_promedio'] }} hrs</h4>
                            <p class="text-muted">Tiempo Promedio Resolución</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="bi bi-robot text-success" style="font-size: 2rem;"></i>
                            <h4 class="mt-2">{{ $resumen['tickets_generados_auto'] }}</h4>
                            <p class="text-muted">Generados Automáticamente</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="bi bi-percent text-warning" style="font-size: 2rem;"></i>
                            <h4 class="mt-2">
                                {{ round((($resumen['por_estado']['cerrado'] ?? 0) / max($resumen['total'], 1)) * 100, 1) }}%
                            </h4>
                            <p class="text-muted">Tasa de Cierre</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de Tickets -->
            <h5 class="mb-3"><i class="bi bi-table"></i> Detalle de Tickets</h5>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Título</th>
                            <th>Categoría</th>
                            <th>Prioridad</th>
                            <th>Estado</th>
                            <th>Asignado</th>
                            <th>Creado</th>
                            <th>Tiempo Resolución</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tickets as $ticket)
                            <tr>
                                <td><strong>#{{ $ticket->id }}</strong></td>
                                <td>{{ $ticket->titulo }}</td>
                                <td><span class="badge bg-info">{{ $ticket->categoria->nombre }}</span></td>
                                <td>
                                    @if ($ticket->prioridad->nombre == 'Alta')
                                        <span class="badge bg-danger">{{ $ticket->prioridad->nombre }}</span>
                                    @elseif($ticket->prioridad->nombre == 'Media')
                                        <span class="badge bg-warning">{{ $ticket->prioridad->nombre }}</span>
                                    @else
                                        <span class="badge bg-success">{{ $ticket->prioridad->nombre }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($ticket->estado == 'cerrado')
                                        <span class="badge-estado bg-success">Cerrado</span>
                                    @elseif($ticket->estado == 'en_proceso')
                                        <span class="badge-estado bg-warning">En Proceso</span>
                                    @else
                                        <span class="badge-estado bg-danger">Abierto</span>
                                    @endif
                                </td>
                                <td>{{ $ticket->asignado ? $ticket->asignado->nombre : 'Sin asignar' }}</td>
                                <td>{{ $ticket->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    @if ($ticket->fecha_cierre)
                                        {{ round($ticket->created_at->diffInHours($ticket->fecha_cierre), 1) }} hrs
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        // Gráfico de Categorías
        const ctxCategorias = document.getElementById('chartCategorias');
        new Chart(ctxCategorias, {
            type: 'pie',
            data: {
                labels: {!! json_encode($resumen['por_categoria']->keys()) !!},
                datasets: [{
                    data: {!! json_encode($resumen['por_categoria']->values()) !!},
                    backgroundColor: [
                        '#667eea', '#764ba2', '#28a745', '#ffc107', '#dc3545'
                    ]
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

        // Gráfico de Prioridades
        const ctxPrioridades = document.getElementById('chartPrioridades');
        new Chart(ctxPrioridades, {
            type: 'bar',
            data: {
                labels: {!! json_encode($resumen['por_prioridad']->keys()) !!},
                datasets: [{
                    label: 'Tickets',
                    data: {!! json_encode($resumen['por_prioridad']->values()) !!},
                    backgroundColor: ['#28a745', '#ffc107', '#dc3545']
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    </script>
</body>

</html>
