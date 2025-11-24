<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Monitoreo - Trimax</title>
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
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .sede-card {
            border: 2px solid #e9ecef;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s;
        }
        .sede-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        .sede-online {
            border-left: 5px solid #28a745;
        }
        .sede-offline {
            border-left: 5px solid #dc3545;
        }
        .sede-degradado {
            border-left: 5px solid #ffc107;
        }
        .uptime-bar {
            height: 30px;
            background: #e9ecef;
            border-radius: 15px;
            overflow: hidden;
            position: relative;
        }
        .uptime-fill {
            height: 100%;
            background: linear-gradient(90deg, #28a745 0%, #20c997 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
        .metric-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 15px;
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
        .status-badge {
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 600;
            display: inline-block;
        }
        .filters {
            background: #e9ecef;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="report-container">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1><i class="bi bi-hdd-network"></i> Reporte de Monitoreo de Sedes</h1>
                    <p class="text-muted">Últimos {{ $dias }} días</p>
                </div>
                <div>
                    <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Volver
                    </a>
                    <a href="{{ route('reportes.pdf', 'monitoreo') }}" class="btn btn-danger">
                        <i class="bi bi-file-pdf"></i> Exportar PDF
                    </a>
                </div>
            </div>

            <!-- Filtros -->
            <div class="filters">
                <form method="GET" action="{{ route('reportes.monitoreo') }}">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Período de análisis (días)</label>
                            <select name="dias" class="form-select">
                                <option value="7" {{ $dias == 7 ? 'selected' : '' }}>Últimos 7 días</option>
                                <option value="15" {{ $dias == 15 ? 'selected' : '' }}>Últimos 15 días</option>
                                <option value="30" {{ $dias == 30 ? 'selected' : '' }}>Últimos 30 días</option>
                                <option value="60" {{ $dias == 60 ? 'selected' : '' }}>Últimos 60 días</option>
                                <option value="90" {{ $dias == 90 ? 'selected' : '' }}>Últimos 90 días</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-funnel"></i> Aplicar Filtro
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
                        <p>Total Sedes</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="metric-box">
                        <h4>{{ collect($estadisticas)->where('estado_actual', 'online')->count() }}</h4>
                        <p>Sedes Online</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="metric-box">
                        <h4>{{ collect($estadisticas)->where('estado_actual', 'offline')->count() }}</h4>
                        <p>Sedes Offline</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="metric-box">
                        <h4>{{ round(collect($estadisticas)->avg('uptime'), 1) }}%</h4>
                        <p>Uptime Promedio</p>
                    </div>
                </div>
            </div>

            <!-- Tarjetas de Sedes -->
            @foreach($estadisticas as $sedeId => $stat)
            <div class="sede-card sede-{{ $stat['estado_actual'] }}">
                <div class="row">
                    <div class="col-md-3">
                        <h4>{{ $stat['nombre'] }}</h4>
                        <span class="status-badge bg-{{ $stat['estado_actual'] == 'online' ? 'success' : ($stat['estado_actual'] == 'offline' ? 'danger' : 'warning') }}">
                            <i class="bi bi-circle-fill"></i> {{ ucfirst($stat['estado_actual']) }}
                        </span>
                        <p class="text-muted mt-2 mb-0">
                            <small><i class="bi bi-clock"></i> Último chequeo: {{ $stat['ultimo_chequeo'] ? $stat['ultimo_chequeo']->format('d/m/Y H:i') : 'N/A' }}</small>
                        </p>
                    </div>
                    
                    <div class="col-md-9">
                        <div class="row">
                            <div class="col-md-4">
                                <label class="form-label"><strong>Uptime</strong></label>
                                <div class="uptime-bar">
                                    <div class="uptime-fill" style="width: {{ $stat['uptime'] }}%">
                                        {{ $stat['uptime'] }}%
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4 text-center">
                                <label class="form-label"><strong>Tiempo de Respuesta</strong></label>
                                <h3 class="text-primary mb-0">
                                    {{ $stat['tiempo_respuesta_promedio'] ? round($stat['tiempo_respuesta_promedio']) : 'N/A' }}
                                    @if($stat['tiempo_respuesta_promedio'])
                                        <small>ms</small>
                                    @endif
                                </h3>
                            </div>
                            
                            <div class="col-md-4 text-center">
                                <label class="form-label"><strong>Incidentes</strong></label>
                                <h3 class="text-danger mb-0">
                                    <i class="bi bi-exclamation-triangle-fill"></i> {{ $stat['total_incidentes'] }}
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach

            <!-- Gráfico de Tendencias -->
            <div class="card mt-4">
                <div class="card-body">
                    <h5><i class="bi bi-graph-up"></i> Tendencia de Uptime</h5>
                    <canvas id="chartUptime"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        // Gráfico de Uptime por Sede
        const ctxUptime = document.getElementById('chartUptime');
        new Chart(ctxUptime, {
            type: 'line',
            data: {
                labels: {!! json_encode(array_column($estadisticas, 'nombre')) !!},
                datasets: [{
                    label: 'Uptime (%)',
                    data: {!! json_encode(array_column($estadisticas, 'uptime')) !!},
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
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
</body>
</html>