@extends('layouts.app')

@section('title', 'Usuarios')

@section('content')
<h2>Gestión de Usuarios</h2>

@if(auth()->user()->esAdmin())
    <h3>Crear Nuevo Usuario</h3>
    <form method="POST" action="{{ route('usuarios.store') }}">
        @csrf
        <p>
            <label>Nombre:*</label>
            <input type="text" name="nombre" required>
        </p>
        <p>
            <label>Apellido:*</label>
            <input type="text" name="apellido" required>
        </p>
        <p>
            <label>Email:*</label>
            <input type="email" name="email" required>
        </p>
        <p>
            <label>Contraseña:*</label>
            <input type="password" name="password" required>
        </p>
        <p>
            <label>Rol:*</label>
            <select name="rol" required>
                <option value="cliente">Cliente</option>
                <option value="tecnico">Técnico</option>
                <option value="admin">Administrador</option>
            </select>
        </p>
        <p>
            <button type="submit">Crear Usuario</button>
        </p>
    </form>
    <hr>
@endif

<h3>Lista de Usuarios</h3>
<table border="1" width="100%">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Email</th>
            <th>Rol</th>
            <th>Estado</th>
        </tr>
    </thead>
    <tbody>
        @foreach($usuarios as $usuario)
        <tr>
            <td>{{ $usuario->id }}</td>
            <td>{{ $usuario->nombre }} {{ $usuario->apellido }}</td>
            <td>{{ $usuario->email }}</td>
            <td>{{ $usuario->rol }}</td>
            <td>{{ $usuario->estado ? 'Activo' : 'Inactivo' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
