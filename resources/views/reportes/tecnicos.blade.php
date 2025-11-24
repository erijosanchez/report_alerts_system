<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Técnicos - Trimax</title>
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

        .tecnico-card {
            border: 2px solid #e9ecef;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s;
        }

        .tecnico-card:hover {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .performance-bar {
            height: 30px;
            background: #e9ecef;
            border-radius: 15px;
            overflow: hidden;
            position: relative;
        }

        .performance-fill {
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            transition: width 0.5s;
        }

        .performance-excellent {
            background: linear-gradient(90deg, #28a745 0%, #20c997 100%);
        }

        .performance-good {
            background: linear-gradient(90deg, #17a2b8 0%, #138496 100%);
        }

        .performance-average {
            background: linear-gradient(90deg, #ffc107 0%, #ff9800 100%);
        }

        .performance-poor {
            background: linear-gradient(90deg, #dc3545 0%, #c82333 100%);
        }

        .metric-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 10px;
        }

        .metric-box h4 {
            margin: 0;
            color: #667eea;
        }

        .metric-box p {
            margin: 5px 0 0 0;
            color: #6c757d;
            font-size: 0.9rem;
        }

        .filters {
            background: #e9ecef;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        .ranking-badge {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.2rem;
            color: white;
        }

        .ranking-1 {
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
        }

        .ranking-2 {
            background: linear-gradient(135deg, #C0C0C0 0%, #A9A9A9 100%);
        }

        .ranking-3 {
            background: linear-gradient(135deg, #CD7F32 0%, #8B4513 100%);
        }

        .ranking-other {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="report-container">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1><i class="bi bi-people-fill"></i> Reporte de Rendimiento de Técnicos</h1>
                    <p class="text-muted">Período: {{ date('d/m/Y', strtotime($desde)) }} -
                        {{ date('d/m/Y', strtotime($hasta)) }}</p>
                </div>
                <div>
                    <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Volver
                    </a>
                    <a href="{{ route('reportes.pdf', 'tecnicos') }}" class="btn btn-danger">
                        <i class="bi bi-file-pdf"></i> Exportar PDF
                    </a>
                </div>
            </div>

            <!-- Filtros -->
            <div class="filters">
                <form method="GET" action="{{ route('reportes.tecnicos') }}">
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

            <!-- Resumen General -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="metric-box">
                        <h4>{{ count($estadisticas) }}</h4>
                        <p>Total Técnicos</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="metric-box">
                        <h4>{{ collect($estadisticas)->sum('tickets_asignados') }}</h4>
                        <p>Tickets Asignados</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="metric-box">
                        <h4>{{ collect($estadisticas)->sum('tickets_cerrados') }}</h4>
                        <p>Tickets Cerrados</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="metric-box">
                        <h4>{{ round(collect($estadisticas)->avg('tasa_cumplimiento_sla'), 1) }}%</h4>
                        <p>SLA Promedio</p>
                    </div>
                </div>
            </div>

            <!-- Ranking de Técnicos -->
            @foreach ($estadisticas as $index => $stat)
                <div class="tecnico-card">
                    <div class="row align-items-center">
                        <div class="col-md-1 text-center">
                            <span class="ranking-badge ranking-{{ $index < 3 ? $index + 1 : 'other' }}">
                                #{{ $index + 1 }}
                            </span>
                        </div>

                        <div class="col-md-3">
                            <h5 class="mb-1">
                                <i class="bi bi-person-circle"></i> {{ $stat['tecnico'] }}
                            </h5>
                            @if ($index == 0)
                                <span class="badge bg-warning"><i class="bi bi-trophy-fill"></i> Mejor desempeño</span>
                            @endif
                        </div>

                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-3 text-center">
                                    <div class="metric-box">
                                        <h4>{{ $stat['tickets_asignados'] }}</h4>
                                        <p>Asignados</p>
                                    </div>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="metric-box">
                                        <h4 class="text-success">{{ $stat['tickets_cerrados'] }}</h4>
                                        <p>Cerrados</p>
                                    </div>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="metric-box">
                                        <h4 class="text-warning">{{ $stat['tickets_pendientes'] }}</h4>
                                        <p>Pendientes</p>
                                    </div>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="metric-box">
                                        <h4 class="text-info">{{ $stat['tiempo_promedio_resolucion'] }} hrs</h4>
                                        <p>Tiempo Prom.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3">
                                <label class="form-label"><strong>Cumplimiento SLA</strong></label>
                                <div class="performance-bar">
                                    <div class="performance-fill 
                                    @if ($stat['tasa_cumplimiento_sla'] >= 90) performance-excellent
                                    @elseif($stat['tasa_cumplimiento_sla'] >= 75) performance-good
                                    @elseif($stat['tasa_cumplimiento_sla'] >= 60) performance-average
                                    @else performance-poor @endif"
                                        style="width: {{ $stat['tasa_cumplimiento_sla'] }}%">
                                        {{ $stat['tasa_cumplimiento_sla'] }}%
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Gráficos Comparativos -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5><i class="bi bi-bar-chart"></i> Tickets por Técnico</h5>
                            <canvas id="chartTickets"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5><i class="bi bi-graph-up"></i> Cumplimiento SLA</h5>
                            <canvas id="chartSLA"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        // Gráfico de Tickets
        const ctxTickets = document.getElementById('chartTickets');
        new Chart(ctxTickets, {
            type: 'bar',
            data: {
                labels: {!! json_encode(array_column($estadisticas, 'tecnico')) !!},
                datasets: [{
                    label: 'Cerrados',
                    data: {!! json_encode(array_column($estadisticas, 'tickets_cerrados')) !!},
                    backgroundColor: '#28a745'
                }, {
                    label: 'Pendientes',
                    data: {!! json_encode(array_column($estadisticas, 'tickets_pendientes')) !!},
                    backgroundColor: '#ffc107'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        stacked: true
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true
                    }
                }
            }
        });

        // Gráfico de SLA
        const ctxSLA = document.getElementById('chartSLA');
        new Chart(ctxSLA, {
            type: 'radar',
            data: {
                labels: {!! json_encode(array_column($estadisticas, 'tecnico')) !!},
                datasets: [{
                    label: 'Cumplimiento SLA (%)',
                    data: {!! json_encode(array_column($estadisticas, 'tasa_cumplimiento_sla')) !!},
                    backgroundColor: 'rgba(102, 126, 234, 0.2)',
                    borderColor: '#667eea',
                    pointBackgroundColor: '#667eea'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    r: {
                        beginAtZero: true,
                        max: 100
                    }
                }
            }
        });
    </script>
</body>

</html>
