@extends('layouts.app')

@section('title', 'Crear Ticket')

@section('content')
    <h2>Crear Nuevo Ticket</h2>

    <form method="POST" action="{{ route('tickets.store') }}">
        @csrf

        <p>
            <label>Título:*</label><br>
            <input type="text" name="titulo" value="{{ old('titulo') }}" required style="width:500px">
        </p>

        <p>
            <label>Descripción:*</label><br>
            <textarea name="descripcion" rows="6" required style="width:500px">{{ old('descripcion') }}</textarea>
        </p>

        <p>
            <label>Categoría:*</label><br>
            <select name="categoria_id" required>
                <option value="">Seleccionar...</option>
                @foreach ($categorias as $cat)
                    <option value="{{ $cat->id }}" {{ old('categoria_id') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->nombre }}
                    </option>
                @endforeach
            </select>
        </p>

        <p>
            <label>Prioridad:*</label><br>
            <select name="prioridad_id" required>
                <option value="">Seleccionar...</option>
                @foreach ($prioridades as $pri)
                    <option value="{{ $pri->id }}" {{ old('prioridad_id') == $pri->id ? 'selected' : '' }}>
                        {{ $pri->nombre }} ({{ $pri->descripcion }})
                    </option>
                @endforeach
            </select>
        </p>

        <p>
            <button type="submit">Crear Ticket</button>
            <a href="{{ route('tickets.index') }}"><button type="button">Cancelar</button></a>
        </p>
    </form>
@endsection
