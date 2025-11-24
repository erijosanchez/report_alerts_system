@extends('layouts.app')

@section('title', 'Iniciar Sesión')

@section('content')
<div>
    <h2>Iniciar Sesión</h2>
    
    <form method="POST" action="{{ route('login') }}">
        @csrf
        
        <p>
            <label>Email:</label><br>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus>
        </p>
        
        <p>
            <label>Contraseña:</label><br>
            <input type="password" name="password" required>
        </p>
        
        <p>
            <button type="submit">Iniciar Sesión</button>
        </p>
    </form>
    
    <hr>
    <h3>Usuarios de Prueba:</h3>
    <ul>
        <li><strong>Admin:</strong> admin@trimax.com / admin123</li>
        <li><strong>Técnico:</strong> tecnico@trimax.com / tecnico123</li>
        <li><strong>Cliente:</strong> cliente@example.com / cliente123</li>
    </ul>
</div>
@endsection