<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Alarmas - Trimax</title>
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

        .alarma-card {
            border-left: 5px solid;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            background: #f8f9fa;
        }

        .alarma-critical {
            border-left-color: #dc3545;
            background: #fff5f5;
        }

        .alarma-warning {
            border-left-color: #ffc107;
            background: #fffbf0;
        }

        .alarma-info {
            border-left-color: #17a2b8;
            background: #f0f9ff;
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 20px;
        }

        .stat-card h3 {
            font-size: 2.5rem;
            margin: 0;
        }

        .stat-card p {
            margin: 5px 0 0 0;
            opacity: 0.9;
        }

        .filters {
            background: #e9ecef;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        .timeline {
            position: relative;
            padding-left: 30px;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 10px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #dee2e6;
        }

        .timeline-item {
            position: relative;
            padding-bottom: 20px;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -24px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #667eea;
            border: 2px solid white;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="report-container">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1><i class="bi bi-bell-fill"></i> Reporte de Alarmas</h1>
                    <p class="text-muted">Período: {{ date('d/m/Y', strtotime($desde)) }} -
                        {{ date('d/m/Y', strtotime($hasta)) }}</p>
                </div>
                <div>
                    <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Volver
                    </a>
                    <a href="{{ route('reportes.pdf', 'alarmas') }}" class="btn btn-danger">
                        <i class="bi bi-file-pdf"></i> Exportar PDF
                    </a>
                </div>
            </div>

            <!-- Filtros -->
            <div class="filters">
                <form method="GET" action="{{ route('reportes.alarmas') }}">
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

            <!-- Estadísticas -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stat-card">
                        <h3>{{ $resumen['total'] }}</h3>
                        <p>Total Alarmas</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);">
                        <h3>{{ $resumen['por_nivel']['critical'] ?? 0 }}</h3>
                        <p>Críticas</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card" style="background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);">
                        <h3>{{ $resumen['por_nivel']['warning'] ?? 0 }}</h3>
                        <p>Advertencias</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card" style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);">
                        <h3>{{ $resumen['por_nivel']['info'] ?? 0 }}</h3>
                        <p>Informativas</p>
                    </div>
                </div>
            </div>

            <!-- Estado de Envío -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5><i class="bi bi-send-check"></i> Estado de Envío</h5>
                            <div class="d-flex justify-content-around mt-3">
                                <div class="text-center">
                                    <h3 class="text-success">{{ $resumen['enviadas'] }}</h3>
                                    <p class="text-muted">Enviadas</p>
                                </div>
                                <div class="text-center">
                                    <h3 class="text-warning">{{ $resumen['pendientes'] }}</h3>
                                    <p class="text-muted">Pendientes</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5><i class="bi bi-pie-chart"></i> Distribución por Tipo</h5>
                            <canvas id="chartTipos"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Timeline de Alarmas -->
            <h5 class="mb-3"><i class="bi bi-clock-history"></i> Línea de Tiempo</h5>
            <div class="timeline">
                @foreach ($alarmas as $alarma)
                    <div class="timeline-item">
                        <div class="alarma-card alarma-{{ $alarma->nivel }}">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center mb-2">
                                        @if ($alarma->nivel == 'critical')
                                            <span class="badge bg-danger me-2">
                                                <i class="bi bi-exclamation-triangle-fill"></i> CRÍTICO
                                            </span>
                                        @elseif($alarma->nivel == 'warning')
                                            <span class="badge bg-warning me-2">
                                                <i class="bi bi-exclamation-circle-fill"></i> ALERTA
                                            </span>
                                        @else
                                            <span class="badge bg-info me-2">
                                                <i class="bi bi-info-circle-fill"></i> INFO
                                            </span>
                                        @endif

                                        <small class="text-muted">
                                            <i class="bi bi-clock"></i>
                                            {{ $alarma->created_at->format('d/m/Y H:i:s') }}
                                        </small>
                                    </div>

                                    <h6 class="mb-1">{{ $alarma->titulo }}</h6>
                                    <p class="mb-2">{{ $alarma->mensaje }}</p>

                                    <div class="d-flex gap-2">
                                        <span class="badge bg-secondary">
                                            <i class="bi bi-tag"></i> {{ $alarma->tipo_alarma }}
                                        </span>
                                        <span class="badge bg-primary">
                                            <i class="bi bi-ticket"></i> Ticket #{{ $alarma->ticket_id }}
                                        </span>
                                        @if ($alarma->enviada)
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle"></i> Enviada
                                            </span>
                                        @else
                                            <span class="badge bg-warning">
                                                <i class="bi bi-hourglass-split"></i> Pendiente
                                            </span>
                                        @endif
                                    </div>

                                    @if ($alarma->canales)
                                        <div class="mt-2">
                                            <small class="text-muted">
                                                <i class="bi bi-send"></i> Canales:
                                                @foreach (json_decode($alarma->canales) as $canal)
                                                    <span class="badge bg-light text-dark">{{ $canal }}</span>
                                                @endforeach
                                            </small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        // Gráfico de Tipos de Alarmas
        const ctxTipos = document.getElementById('chartTipos');
        new Chart(ctxTipos, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($resumen['por_tipo']->keys()) !!},
                datasets: [{
                    data: {!! json_encode($resumen['por_tipo']->values()) !!},
                    backgroundColor: [
                        '#dc3545',
                        '#ffc107',
                        '#17a2b8',
                        '#667eea',
                        '#28a745'
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
    </script>
</body>

</html>
