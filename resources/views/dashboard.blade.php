@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<h2>Dashboard - Bienvenido {{ auth()->user()->nombre }}</h2>

<h3>Estadísticas</h3>
<table border="1">
    <tr>
        <th>Total Tickets</th>
        <th>Abiertos</th>
        <th>En Proceso</th>
        <th>Resueltos</th>
        <th>Cerrados</th>
    </tr>
    <tr>
        <td>{{ $stats['total'] }}</td>
        <td>{{ $stats['abiertos'] }}</td>
        <td>{{ $stats['en_proceso'] }}</td>
        <td>{{ $stats['resueltos'] }}</td>
        <td>{{ $stats['cerrados'] }}</td>
    </tr>
</table>

<p>
    <strong>Tiempo Promedio Respuesta:</strong> {{ round($stats['tiempo_respuesta_promedio'] ?? 0) }} minutos<br>
    <strong>Tiempo Promedio Resolución:</strong> {{ round($stats['tiempo_resolucion_promedio'] ?? 0) }} minutos<br>
    <strong>Calificación Promedio:</strong> {{ number_format($stats['calificacion_promedio'] ?? 0, 1) }} / 5
</p>

<hr>

<h3>Tickets Recientes</h3>
<p><a href="{{ route('tickets.create') }}"><button>Crear Nuevo Ticket</button></a></p>

<table border="1" width="100%">
    <thead>
        <tr>
            <th>Número</th>
            <th>Título</th>
            <th>Estado</th>
            <th>Prioridad</th>
            <th>Cliente</th>
            @if(auth()->user()->esStaff())
                <th>Técnico</th>
            @endif
            <th>Fecha</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @forelse($tickets as $ticket)
        <tr>
            <td><strong>{{ $ticket->numero_ticket }}</strong></td>
            <td>{{ $ticket->titulo }}</td>
            <td>{{ $ticket->estado }}</td>
            <td>{{ $ticket->prioridad->nombre }}</td>
            <td>{{ $ticket->usuario->nombre }} {{ $ticket->usuario->apellido }}</td>
            @if(auth()->user()->esStaff())
                <td>{{ $ticket->tecnico ? $ticket->tecnico->nombre : 'Sin asignar' }}</td>
            @endif
            <td>{{ $ticket->fecha_apertura->format('d/m/Y H:i') }}</td>
            <td><a href="{{ route('tickets.show', $ticket->id) }}">Ver</a></td>
        </tr>
        @empty
        <tr>
            <td colspan="{{ auth()->user()->esStaff() ? 8 : 7 }}">No hay tickets disponibles</td>
        </tr>
        @endforelse
    </tbody>
</table>

<p><a href="{{ route('tickets.index') }}">Ver todos los tickets</a></p>
@endsection
