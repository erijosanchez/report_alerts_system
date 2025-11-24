@extends('layouts.app')

@section('title', 'Ticket ' . $ticket->numero_ticket)

@section('content')
    <h2>Ticket {{ $ticket->numero_ticket }}</h2>

    <h3>Información del Ticket</h3>
    <table border="1">
        <tr>
            <th>Campo</th>
            <th>Valor</th>
        </tr>
        <tr>
            <td><strong>Número:</strong></td>
            <td>{{ $ticket->numero_ticket }}</td>
        </tr>
        <tr>
            <td><strong>Título:</strong></td>
            <td>{{ $ticket->titulo }}</td>
        </tr>
        <tr>
            <td><strong>Descripción:</strong></td>
            <td>{{ nl2br(e($ticket->descripcion)) }}</td>
        </tr>
        <tr>
            <td><strong>Estado:</strong></td>
            <td>{{ $ticket->estado }}</td>
        </tr>
        <tr>
            <td><strong>Prioridad:</strong></td>
            <td>{{ $ticket->prioridad->nombre }}</td>
        </tr>
        <tr>
            <td><strong>Categoría:</strong></td>
            <td>{{ $ticket->categoria->nombre }}</td>
        </tr>
        <tr>
            <td><strong>Cliente:</strong></td>
            <td>{{ $ticket->usuario->nombre }} {{ $ticket->usuario->apellido }} ({{ $ticket->usuario->email }})</td>
        </tr>
        <tr>
            <td><strong>Técnico Asignado:</strong></td>
            <td>{{ $ticket->tecnico ? $ticket->tecnico->nombre . ' ' . $ticket->tecnico->apellido : 'Sin asignar' }}</td>
        </tr>
        <tr>
            <td><strong>Fecha Apertura:</strong></td>
            <td>{{ $ticket->fecha_apertura->format('d/m/Y H:i:s') }}</td>
        </tr>
        @if ($ticket->fecha_cierre)
            <tr>
                <td><strong>Fecha Cierre:</strong></td>
                <td>{{ $ticket->fecha_cierre->format('d/m/Y H:i:s') }}</td>
            </tr>
        @endif
    </table>

    <hr>

    @can('asignar-ticket')
        @if ($ticket->estado !== 'cerrado')
            <h3>Asignar Técnico</h3>
            <form method="POST" action="{{ route('tickets.asignar', $ticket->id) }}">
                @csrf
                <select name="tecnico_id" required>
                    <option value="">Seleccionar técnico...</option>
                    @foreach ($tecnicos as $tec)
                        <option value="{{ $tec->id }}" {{ $ticket->tecnico_id == $tec->id ? 'selected' : '' }}>
                            {{ $tec->nombre }} {{ $tec->apellido }}
                        </option>
                    @endforeach
                </select>
                <button type="submit">Asignar</button>
            </form>
            <hr>
        @endif
    @endcan

    @if (auth()->user()->esStaff() && $ticket->estado !== 'cerrado')
        <h3>Cambiar Estado</h3>
        <form method="POST" action="{{ route('tickets.estado', $ticket->id) }}">
            @csrf
            <select name="estado" required>
                <option value="">Seleccionar...</option>
                <option value="abierto">Abierto</option>
                <option value="en_proceso">En Proceso</option>
                <option value="pendiente">Pendiente</option>
                <option value="resuelto">Resuelto</option>
                <option value="cerrado">Cerrado</option>
            </select>
            <button type="submit">Cambiar Estado</button>
        </form>
        <hr>
    @endif

    <h3>Comentarios ({{ $ticket->comentarios->count() }})</h3>

    @if ($ticket->estado !== 'cerrado')
        <h4>Agregar Comentario</h4>
        <form method="POST" action="{{ route('tickets.comentarios', $ticket->id) }}">
            @csrf
            <textarea name="comentario" rows="4" style="width:100%" required></textarea><br>
            <button type="submit">Agregar Comentario</button>
        </form>
        <hr>
    @endif

    @forelse($ticket->comentarios as $comentario)
        <div style="border:1px solid #ccc; padding:10px; margin:10px 0;">
            <p>
                <strong>{{ $comentario->usuario->nombre }} {{ $comentario->usuario->apellido }}</strong>
                ({{ $comentario->usuario->rol }})
                - {{ $comentario->created_at->format('d/m/Y H:i') }}
            </p>
            <p>{{ nl2br(e($comentario->comentario)) }}</p>
        </div>
    @empty
        <p>No hay comentarios aún.</p>
    @endforelse

    <hr>
    <p><a href="{{ route('tickets.index') }}">Volver a la lista</a></p>
@endsection
