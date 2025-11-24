<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    public function index()
    {
        $this->authorize('gestionar-usuarios');
        $usuarios = Usuario::orderBy('nombre')->get();
        return view('usuarios.index', compact('usuarios'));
    }

    public function store(Request $request)
    {
        $this->authorize('gestionar-usuarios');
        
        $validated = $request->validate([
            'nombre' => 'required|max:100',
            'apellido' => 'required|max:100',
            'email' => 'required|email|unique:usuarios,email',
            'password' => 'required|min:6',
            'rol' => 'required|in:admin,tecnico,cliente',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        Usuario::create($validated);

        return back()->with('success', 'Usuario creado correctamente');
    }
}
