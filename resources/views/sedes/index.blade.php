@extends('layouts.app')

@section('title', 'Gestión de Sedes')

@section('content')
    <h2>Gestión de Sedes</h2>

    <h3>Agregar Nueva Sede</h3>
    <form method="POST" action="{{ route('sedes.store') }}">
        @csrf
        <table>
            <tr>
                <td><label>Nombre:*</label></td>
                <td><input type="text" name="nombre" required></td>
            </tr>
            <tr>
                <td><label>Código:*</label></td>
                <td><input type="text" name="codigo" required placeholder="Ej: LIM01"></td>
            </tr>
            <tr>
                <td><label>Dirección:*</label></td>
                <td><input type="text" name="direccion" required></td>
            </tr>
            <tr>
                <td><label>Ciudad:*</label></td>
                <td><input type="text" name="ciudad" required></td>
            </tr>
            <tr>
                <td><label>IP Principal:*</label></td>
                <td><input type="text" name="ip_principal" required placeholder="192.168.1.1"></td>
            </tr>
            <tr>
                <td><label>IP Backup:</label></td>
                <td><input type="text" name="ip_backup" placeholder="192.168.1.2"></td>
            </tr>
            <tr>
                <td><label>Intervalo Monitoreo (min):*</label></td>
                <td><input type="number" name="intervalo_monitoreo" value="5" min="1" max="60" required>
                </td>
            </tr>
            <tr>
                <td colspan="2"><button type="submit">Agregar Sede</button></td>
            </tr>
        </table>
    </form>

    <hr>

    <h3>Sedes Registradas ({{ $sedes->count() }})</h3>
    <table border="1" width="100%">
        <thead>
            <tr>
                <th>Código</th>
                <th>Nombre</th>
                <th>Ciudad</th>
                <th>IP Principal</th>
                <th>IP Backup</th>
                <th>Intervalo</th>
                <th>Estado</th>
                <th>Última Verificación</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sedes as $sede)
                <tr>
                    <td><strong>{{ $sede->codigo }}</strong></td>
                    <td>{{ $sede->nombre }}</td>
                    <td>{{ $sede->ciudad }}</td>
                    <td>{{ $sede->ip_principal }}</td>
                    <td>{{ $sede->ip_backup ?? 'N/A' }}</td>
                    <td>{{ $sede->intervalo_monitoreo }} min</td>
                    <td>{{ $sede->activa ? '✅ Activa' : '❌ Inactiva' }}</td>
                    <td>{{ $sede->ultima_comprobacion ? $sede->ultima_comprobacion->format('d/m/Y H:i') : 'Nunca' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
