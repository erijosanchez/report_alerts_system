@extends('layouts.app')

@section('title', 'Alarmas')

@section('content')
    <h2>🔔 Sistema de Alarmas</h2>

    <h3>Estadísticas</h3>
    <table border="1">
        <tr>
            <th>Total Alarmas</th>
            <th>Pendientes</th>
            <th>Críticas Activas</th>
        </tr>
        <tr>
            <td>{{ $stats['total'] }}</td>
            <td>{{ $stats['pendientes'] }}</td>
            <td style="background-color:#FFB6C1">{{ $stats['criticas'] }}</td>
        </tr>
    </table>

    <hr>

    <h3>Historial de Alarmas</h3>
    <table border="1" width="100%">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Tipo</th>
                <th>Nivel</th>
                <th>Título</th>
                <th>Ticket</th>
                <th>Estado</th>
                <th>Canales</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($alarmas as $alarma)
                <tr
                    style="background-color:
            @if ($alarma->nivel == 'critical') #FFB6C1
            @elseif($alarma->nivel == 'warning') #FFD700
            @else #E0E0E0 @endif
        ">
                    <td>{{ $alarma->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $alarma->tipo_alarma }}</td>
                    <td>
                        @if ($alarma->nivel == 'critical')
                            🔴 CRÍTICO
                        @elseif($alarma->nivel == 'warning')
                            ⚠️ ALERTA
                        @else
                            ℹ️ INFO
                        @endif
                    </td>
                    <td>{{ $alarma->titulo }}</td>
                    <td>
                        <a href="{{ route('tickets.show', $alarma->ticket_id) }}">
                            {{ $alarma->ticket->numero_ticket }}
                        </a>
                    </td>
                    <td>
                        @if ($alarma->enviada)
                            ✅ Enviada ({{ $alarma->fecha_envio->format('d/m H:i') }})
                        @else
                            ⏳ Pendiente
                        @endif
                    </td>
                    <td>
                        @foreach ($alarma->canales as $canal)
                            @if ($canal == 'email')
                                📧
                            @elseif($canal == 'whatsapp')
                                💬
                            @elseif($canal == 'sms')
                                📱
                            @endif
                        @endforeach
                    </td>
                    <td>
                        @if ($alarma->activa)
                            @can('gestionar-alarmas')
                                <form method="POST" action="{{ route('alarmas.desactivar', $alarma->id) }}"
                                    style="display:inline">
                                    @csrf
                                    <button type="submit">Desactivar</button>
                                </form>
                            @endcan
                        @else
                            <em>Desactivada</em>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">No hay alarmas registradas</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{ $alarmas->links() }}
@endsection
