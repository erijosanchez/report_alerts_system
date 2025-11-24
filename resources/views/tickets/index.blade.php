@extends('layouts.app')

@section('title', 'Tickets')

@section('content')
    <h2>Gestión de Tickets</h2>

    <p><a href="{{ route('tickets.create') }}"><button>Crear Nuevo Ticket</button></a></p>

    <h3>Filtros</h3>
    <form method="GET" action="{{ route('tickets.index') }}">
        <label>Buscar:</label>
        <input type="text" name="busqueda" value="{{ request('busqueda') }}" placeholder="Número o título">

        <label>Estado:</label>
        <select name="estado">
            <option value="">Todos</option>
            <option value="abierto" {{ request('estado') == 'abierto' ? 'selected' : '' }}>Abierto</option>
            <option value="en_proceso" {{ request('estado') == 'en_proceso' ? 'selected' : '' }}>En Proceso</option>
            <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
            <option value="resuelto" {{ request('estado') == 'resuelto' ? 'selected' : '' }}>Resuelto</option>
            <option value="cerrado" {{ request('estado') == 'cerrado' ? 'selected' : '' }}>Cerrado</option>
        </select>

        <button type="submit">Buscar</button>
        <a href="{{ route('tickets.index') }}"><button type="button">Limpiar</button></a>
    </form>

    <hr>

    <h3>Lista de Tickets ({{ $tickets->total() }})</h3>
    <table border="1" width="100%">
        <thead>
            <tr>
                <th>Número</th>
                <th>Título</th>
                <th>Cliente</th>
                @if (auth()->user()->esStaff())
                    <th>Técnico</th>
                @endif
                <th>Categoría</th>
                <th>Prioridad</th>
                <th>Estado</th>
                <th>Fecha</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tickets as $ticket)
                <tr>
                    <td><strong>{{ $ticket->numero_ticket }}</strong></td>
                    <td>{{ $ticket->titulo }}</td>
                    <td>{{ $ticket->usuario->nombre }}</td>
                    @if (auth()->user()->esStaff())
                        <td>{{ $ticket->tecnico ? $ticket->tecnico->nombre : 'Sin asignar' }}</td>
                    @endif
                    <td>{{ $ticket->categoria->nombre }}</td>
                    <td>{{ $ticket->prioridad->nombre }}</td>
                    <td>{{ $ticket->estado }}</td>
                    <td>{{ $ticket->fecha_apertura->format('d/m/Y H:i') }}</td>
                    <td><a href="{{ route('tickets.show', $ticket->id) }}">Ver Detalle</a></td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ auth()->user()->esStaff() ? 9 : 8 }}">No se encontraron tickets</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{ $tickets->links() }}
@endsection
