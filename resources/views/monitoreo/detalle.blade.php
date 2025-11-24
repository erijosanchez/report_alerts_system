<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle Monitoreo - {{ $sede->nombre }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .detail-container {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .status-card {
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            color: white;
        }

        .status-online {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        }

        .status-offline {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        }

        .status-degradado {
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
        }

        .status-desconocido {
            background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
        }

        .metric-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
            transition: all 0.3s;
        }

        .metric-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .metric-card h3 {
            color: #667eea;
            margin: 10px 0;
            font-size: 2rem;
        }

        .metric-card .icon {
            font-size: 2.5rem;
            color: #667eea;
        }

        .log-item {
            border-left: 4px solid;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            background: #f8f9fa;
        }

        .log-success {
            border-left-color: #28a745;
        }

        .log-failed {
            border-left-color: #dc3545;
        }

        .log-timeout {
            border-left-color: #ffc107;
        }

        .service-badge {
            padding: 10px 20px;
            border-radius: 20px;
            margin: 5px;
            display: inline-block;
            font-weight: 500;
        }

        .chart-container {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .uptime-ring {
            width: 150px;
            height: 150px;
            margin: 0 auto;
            position: relative;
        }

        .pulse-online {
            animation: pulse-green 2s infinite;
        }

        .pulse-offline {
            animation: pulse-red 2s infinite;
        }

        @keyframes pulse-green {

            0%,
            100% {
                box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7);
            }

            50% {
                box-shadow: 0 0 0 15px rgba(40, 167, 69, 0);
            }
        }

        @keyframes pulse-red {

            0%,
            100% {
                box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7);
            }

            50% {
                box-shadow: 0 0 0 15px rgba(220, 53, 69, 0);
            }
        }

        .action-buttons {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
        }

        .fab-button {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            margin-bottom: 10px;
        }

        .info-table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
        }

        .info-table th {
            background: #667eea;
            color: white;
            font-weight: 500;
            padding: 15px;
        }

        .info-table td {
            padding: 15px;
            border-bottom: 1px solid #e9ecef;
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
            box-shadow: 0 0 0 2px #667eea;
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="detail-container">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1><i class="bi bi-hdd-network-fill"></i> {{ $sede->nombre }}</h1>
                    <p class="text-muted mb-0">
                        <i class="bi bi-geo-alt"></i> {{ $sede->direccion }}, {{ $sede->ciudad }}
                    </p>
                    <p class="text-muted">
                        <i class="bi bi-upc-scan"></i> Código: <strong>{{ $sede->codigo }}</strong>
                    </p>
                </div>
                <div>
                    <a href="{{ route('monitoreo.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Volver
                    </a>
                    <button onclick="actualizarMonitoreo()" class="btn btn-primary">
                        <i class="bi bi-arrow-clockwise"></i> Actualizar
                    </button>
                </div>
            </div>

            <!-- Estado Principal -->
            <div class="status-card status-{{ $sede->estado_conexion }}">
                <div class="row align-items-center">
                    <div class="col-md-2 text-center">
                        <div class="pulse-{{ $sede->estado_conexion == 'online' ? 'online' : 'offline' }}"
                            style="width: 80px; height: 80px; border-radius: 50%; background: white; display: inline-flex; align-items: center; justify-content: center;">
                            @if ($sede->estado_conexion == 'online')
                                <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
                            @elseif($sede->estado_conexion == 'offline')
                                <i class="bi bi-x-circle-fill text-danger" style="font-size: 3rem;"></i>
                            @else
                                <i class="bi bi-exclamation-circle-fill text-warning" style="font-size: 3rem;"></i>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-10">
                        <h2 class="mb-2">
                            @if ($sede->estado_conexion == 'online')
                                ✓ SEDE OPERATIVA
                            @elseif($sede->estado_conexion == 'offline')
                                ✗ SEDE FUERA DE LÍNEA
                            @elseif($sede->estado_conexion == 'degradado')
                                ⚠ CONEXIÓN DEGRADADA
                            @else
                                ? ESTADO DESCONOCIDO
                            @endif
                        </h2>
                        <p class="mb-0">
                            <i class="bi bi-clock"></i> Último chequeo:
                            {{ $sede->ultima_comprobacion ? $sede->ultima_comprobacion->format('d/m/Y H:i:s') : 'Nunca' }}
                        </p>
                        <p class="mb-0">
                            <i class="bi bi-arrow-repeat"></i> Intervalo de monitoreo:
                            <strong>{{ $sede->intervalo_monitoreo }} minutos</strong>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Métricas Principales -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="metric-card">
                        <i class="bi bi-percent icon"></i>
                        <h3>{{ $uptime }}%</h3>
                        <p class="text-muted mb-0">Uptime (30 días)</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="metric-card">
                        <i class="bi bi-speedometer2 icon"></i>
                        <h3>{{ $tiempo_respuesta_promedio ? round($tiempo_respuesta_promedio) : 'N/A' }}</h3>
                        <p class="text-muted mb-0">
                            {{ $tiempo_respuesta_promedio ? 'ms promedio' : 'Sin datos' }}
                        </p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="metric-card">
                        <i class="bi bi-exclamation-triangle icon text-danger"></i>
                        <h3>{{ $total_incidentes }}</h3>
                        <p class="text-muted mb-0">Incidentes (30 días)</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="metric-card">
                        <i class="bi bi-ticket-detailed icon text-warning"></i>
                        <h3>{{ $tickets_generados }}</h3>
                        <p class="text-muted mb-0">Tickets Generados</p>
                    </div>
                </div>
            </div>

            <!-- Información de Conexión -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <h5 class="mb-3"><i class="bi bi-info-circle"></i> Información de Conexión</h5>
                    <table class="info-table table table-borderless mb-0">
                        <tbody>
                            <tr>
                                <th width="40%">IP Principal</th>
                                <td>
                                    <code>{{ $sede->ip_principal }}</code>
                                    <button onclick="copiarIP('{{ $sede->ip_principal }}')"
                                        class="btn btn-sm btn-outline-secondary ms-2">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                </td>
                            </tr>
                            @if ($sede->ip_backup)
                                <tr>
                                    <th>IP Backup</th>
                                    <td>
                                        <code>{{ $sede->ip_backup }}</code>
                                        <button onclick="copiarIP('{{ $sede->ip_backup }}')"
                                            class="btn btn-sm btn-outline-secondary ms-2">
                                            <i class="bi bi-clipboard"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endif
                            <tr>
                                <th>Estado</th>
                                <td>
                                    @if ($sede->activa)
                                        <span class="badge bg-success">Activa</span>
                                    @else
                                        <span class="badge bg-secondary">Inactiva</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Intervalo Monitoreo</th>
                                <td>{{ $sede->intervalo_monitoreo }} minutos</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="col-md-6">
                    <h5 class="mb-3"><i class="bi bi-gear"></i> Servicios Monitoreados</h5>
                    <div class="p-3">
                        @if ($sede->servicios_monitorear)
                            @foreach (json_decode($sede->servicios_monitorear) as $servicio)
                                <span class="service-badge bg-primary text-white">
                                    <i class="bi bi-check-circle"></i> {{ strtoupper($servicio) }}
                                </span>
                            @endforeach
                        @else
                            <p class="text-muted">No hay servicios configurados</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Gráficos -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="chart-container">
                        <h5><i class="bi bi-graph-up"></i> Tiempo de Respuesta (7 días)</h5>
                        <canvas id="chartTiempoRespuesta"></canvas>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="chart-container">
                        <h5><i class="bi bi-pie-chart"></i> Estado de Chequeos (30 días)</h5>
                        <canvas id="chartEstados"></canvas>
                    </div>
                </div>
            </div>

            <!-- Logs Recientes -->
            <h5 class="mb-3"><i class="bi bi-clock-history"></i> Logs Recientes (Últimos 20)</h5>
            <div class="timeline">
                @forelse($logs_recientes as $log)
                    <div class="timeline-item">
                        <div class="log-item log-{{ $log->resultado }}">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center mb-2">
                                        @if ($log->resultado == 'success')
                                            <span class="badge bg-success me-2">
                                                <i class="bi bi-check-circle"></i> EXITOSO
                                            </span>
                                        @elseif($log->resultado == 'failed')
                                            <span class="badge bg-danger me-2">
                                                <i class="bi bi-x-circle"></i> FALLIDO
                                            </span>
                                        @else
                                            <span class="badge bg-warning me-2">
                                                <i class="bi bi-hourglass-split"></i> TIMEOUT
                                            </span>
                                        @endif

                                        <strong>{{ strtoupper($log->tipo_chequeo) }}</strong>

                                        @if ($log->tiempo_respuesta_ms)
                                            <span class="badge bg-info ms-2">{{ $log->tiempo_respuesta_ms }} ms</span>
                                        @endif
                                    </div>

                                    @if ($log->detalles)
                                        <p class="mb-0 text-muted">{{ $log->detalles }}</p>
                                    @endif
                                </div>

                                <small class="text-muted ms-3">
                                    <i class="bi bi-calendar"></i> {{ $log->fecha_chequeo->format('d/m/Y') }}<br>
                                    <i class="bi bi-clock"></i> {{ $log->fecha_chequeo->format('H:i:s') }}
                                </small>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> No hay logs registrados para esta sede.
                    </div>
                @endforelse
            </div>

            <!-- Tickets Relacionados -->
            @if ($tickets_relacionados->count() > 0)
                <h5 class="mb-3 mt-4"><i class="bi bi-ticket-detailed"></i> Tickets Relacionados</h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Título</th>
                                <th>Estado</th>
                                <th>Prioridad</th>
                                <th>Asignado</th>
                                <th>Creado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tickets_relacionados as $ticket)
                                <tr>
                                    <td><strong>#{{ $ticket->id }}</strong></td>
                                    <td>{{ $ticket->titulo }}</td>
                                    <td>
                                        @if ($ticket->estado == 'cerrado')
                                            <span class="badge bg-success">Cerrado</span>
                                        @elseif($ticket->estado == 'en_proceso')
                                            <span class="badge bg-warning">En Proceso</span>
                                        @else
                                            <span class="badge bg-danger">Abierto</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span
                                            class="badge bg-{{ $ticket->prioridad->nombre == 'Alta' ? 'danger' : ($ticket->prioridad->nombre == 'Media' ? 'warning' : 'success') }}">
                                            {{ $ticket->prioridad->nombre }}
                                        </span>
                                    </td>
                                    <td>{{ $ticket->asignado ? $ticket->asignado->nombre : 'Sin asignar' }}</td>
                                    <td>{{ $ticket->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <a href="{{ route('tickets.show', $ticket->id) }}"
                                            class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <!-- Botones Flotantes -->
    <div class="action-buttons">
        <button onclick="ejecutarPingManual()" class="fab-button btn btn-success" title="Ejecutar Ping">
            <i class="bi bi-broadcast"></i>
        </button>
        <a href="{{ route('sedes.edit', $sede->id) }}" class="fab-button btn btn-warning" title="Editar Sede">
            <i class="bi bi-pencil"></i>
        </a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        // Gráfico de Tiempo de Respuesta
        const ctxTiempo = document.getElementById('chartTiempoRespuesta');
        const logsUltimos7Dias = @json($logs_ultimos_7_dias);

        new Chart(ctxTiempo, {
            type: 'line',
            data: {
                labels: logsUltimos7Dias.map(log => {
                    const fecha = new Date(log.fecha_chequeo);
                    return fecha.toLocaleDateString('es-PE', {
                            day: '2-digit',
                            month: '2-digit'
                        }) + ' ' +
                        fecha.toLocaleTimeString('es-PE', {
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                }),
                datasets: [{
                    label: 'Tiempo de Respuesta (ms)',
                    data: logsUltimos7Dias.map(log => log.tiempo_respuesta_ms),
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
                        ticks: {
                            callback: function(value) {
                                return value + ' ms';
                            }
                        }
                    }
                }
            }
        });

        // Gráfico de Estados
        const ctxEstados = document.getElementById('chartEstados');
        const estadisticas = @json($estadisticas_estados);

        new Chart(ctxEstados, {
            type: 'doughnut',
            data: {
                labels: ['Exitosos', 'Fallidos', 'Timeout'],
                datasets: [{
                    data: [
                        estadisticas.success || 0,
                        estadisticas.failed || 0,
                        estadisticas.timeout || 0
                    ],
                    backgroundColor: ['#28a745', '#dc3545', '#ffc107']
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

        // Función para actualizar monitoreo
        function actualizarMonitoreo() {
            location.reload();
        }

        // Función para copiar IP
        function copiarIP(ip) {
            navigator.clipboard.writeText(ip).then(() => {
                alert('IP copiada: ' + ip);
            });
        }

        // Función para ejecutar ping manual
        function ejecutarPingManual() {
            if (confirm('¿Ejecutar chequeo manual de la sede?')) {
                fetch('{{ route('monitoreo.ping', $sede->id) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message || 'Chequeo ejecutado');
                        location.reload();
                    })
                    .catch(error => {
                        alert('Error al ejecutar chequeo');
                        console.error(error);
                    });
            }
        }
    </script>
</body>

</html>
