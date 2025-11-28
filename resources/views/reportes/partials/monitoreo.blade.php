<!-- RESUMEN GENERAL -->
<div class="mb-4 row g-3">
    <div class="col-md-12">
        <div class="card">
            <div class="text-center card-body">
                <h6 class="text-muted">Uptime General</h6>
                <h2 style="color: {{ $data['uptime_general'] >= 99 ? '#10b981' : '#ef4444' }};">
                    {{ number_format($data['uptime_general'], 2) }}%
                </h2>
            </div>
        </div>
    </div>
</div>

<!-- TABLA DE SEDES -->
<div class="card">
    <div class="card-body">
        <h6 class="mb-3"><i class="bi bi-building"></i> Estadísticas por Sede</h6>
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Sede</th>
                        <th>Estado</th>
                        <th>Uptime</th>
                        <th>Latencia Promedio</th>
                        <th>Checks Exitosos</th>
                        <th>Checks Fallidos</th>
                        <th>Incidentes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data['estadisticas'] as $stat)
                        <tr>
                            <td><strong>{{ $stat['sede']->nombre }}</strong></td>
                            <td>
                                @php
                                    $estadoClass = match ($stat['sede']->estado_conexion) {
                                        'online' => ['class' => 'online', 'label' => 'Online'],
                                        'offline' => ['class' => 'offline', 'label' => 'Offline'],
                                        'degradado' => ['class' => 'degraded', 'label' => 'Degradado'],
                                        default => ['class' => 'offline', 'label' => 'Desconocido'],
                                    };
                                @endphp
                                <span class="status-badge {{ $estadoClass['class'] }}">
                                    <span class="status-dot {{ $estadoClass['class'] }}"></span>
                                    {{ $estadoClass['label'] }}
                                </span>
                            </td>
                            <td>
                                <strong
                                    style="color: {{ $stat['uptime'] >= 99 ? '#10b981' : ($stat['uptime'] >= 95 ? '#f59e0b' : '#ef4444') }};">
                                    {{ number_format($stat['uptime'], 2) }}%
                                </strong>
                            </td>
                            <td>{{ round($stat['latencia_promedio']) }} ms</td>
                            <td><span class="bg-success badge">{{ $stat['checks_exitosos'] }}</span></td>
                            <td><span class="bg-danger badge">{{ $stat['checks_fallidos'] }}</span></td>
                            <td><span class="bg-warning badge">{{ $stat['incidentes'] }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
