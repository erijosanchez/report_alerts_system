@extends('layouts.app')

@section('title', 'Monitoreo de Sedes')

@section('content')
    <h2>🖥️ Monitoreo en Tiempo Real de Sedes</h2>

    <h3>Resumen General</h3>
    <table border="1">
        <tr>
            <th>Total Sedes</th>
            <th style="background-color:#90EE90">Online</th>
            <th style="background-color:#FFB6C1">Offline</th>
            <th style="background-color:#FFD700">Degradado</th>
        </tr>
        <tr>
            <td>{{ $stats['total_sedes'] }}</td>
            <td><strong>{{ $stats['sedes_online'] }}</strong></td>
            <td><strong>{{ $stats['sedes_offline'] }}</strong></td>
            <td><strong>{{ $stats['sedes_degradado'] }}</strong></td>
        </tr>
    </table>

    <hr>

    <h3>Estado de Sedes</h3>
    <table border="1" width="100%">
        <thead>
            <tr>
                <th>Código</th>
                <th>Nombre</th>
                <th>Ciudad</th>
                <th>IP Principal</th>
                <th>Estado</th>
                <th>Última Verificación</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sedes as $sede)
                <tr
                    style="background-color: 
            @if ($sede->estado_conexion == 'online') #90EE90
            @elseif($sede->estado_conexion == 'offline') #FFB6C1
            @else #FFD700 @endif
        ">
                    <td><strong>{{ $sede->codigo }}</strong></td>
                    <td>{{ $sede->nombre }}</td>
                    <td>{{ $sede->ciudad }}</td>
                    <td>{{ $sede->ip_principal }}</td>
                    <td>
                        @if ($sede->estado_conexion == 'online')
                            ✅ ONLINE
                        @elseif($sede->estado_conexion == 'offline')
                            ❌ OFFLINE
                        @else
                            ⚠️ DEGRADADO
                        @endif
                    </td>
                    <td>{{ $sede->ultima_comprobacion ? $sede->ultima_comprobacion->format('d/m/Y H:i:s') : 'Nunca' }}</td>
                    <td>
                        <a href="{{ route('monitoreo.sede', $sede->id) }}">Ver Detalle</a>
                        @can('gestionar-monitoreo')
                            |
                            <form method="POST" action="{{ route('monitoreo.forzar', $sede->id) }}" style="display:inline">
                                @csrf
                                <button type="submit">Forzar Check</button>
                            </form>
                        @endcan
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <hr>

    <h3>Tickets Automáticos Activos ({{ $ticketsAutomaticos->count() }})</h3>
    @if ($ticketsAutomaticos->count() > 0)
        <table border="1" width="100%">
            <thead>
                <tr>
                    <th>Ticket</th>
                    <th>Sede</th>
                    <th>Título</th>
                    <th>Prioridad</th>
                    <th>Técnico</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($ticketsAutomaticos as $ticket)
                    <tr>
                        <td><strong>{{ $ticket->numero_ticket }}</strong></td>
                        <td>{{ $ticket->sede->nombre }}</td>
                        <td>{{ $ticket->titulo }}</td>
                        <td>{{ $ticket->prioridad->nombre }}</td>
                        <td>{{ $ticket->tecnico ? $ticket->tecnico->nombre : 'Sin asignar' }}</td>
                        <td>{{ $ticket->estado }}</td>
                        <td>{{ $ticket->fecha_apertura->format('d/m/Y H:i') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>✅ No hay tickets automáticos activos. Todas las sedes funcionan correctamente.</p>
    @endif

    <hr>
    <p><a href="{{ route('dashboard') }}">Volver al Dashboard</a></p>
@endsection
