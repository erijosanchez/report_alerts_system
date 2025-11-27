<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    public function index(Request $request)
    {
        //$this->authorize('gestionar-usuarios');
        
        $query = Usuario::query();
        
        // Búsqueda
        if ($request->filled('busqueda')) {
            $query->where(function($q) use ($request) {
                $q->where('nombre', 'like', '%' . $request->busqueda . '%')
                  ->orWhere('apellido', 'like', '%' . $request->busqueda . '%')
                  ->orWhere('email', 'like', '%' . $request->busqueda . '%');
            });
        }
        
        // Filtro por rol
        if ($request->filled('rol')) {
            $query->where('rol', $request->rol);
        }
        
        // Filtro por estado
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        
        $usuarios = $query->orderBy('nombre')->paginate(10);
        
        // Estadísticas
        $stats = [
            'total' => Usuario::count(),
            'admins' => Usuario::where('rol', 'admin')->count(),
            'tecnicos' => Usuario::where('rol', 'tecnico')->count(),
            'clientes' => Usuario::where('rol', 'cliente')->count(),
            'activos' => Usuario::where('estado', true)->count(),
        ];
        
        return view('usuarios.index', compact('usuarios', 'stats'));
    }

    public function store(Request $request)
    {
        //$this->authorize('gestionar-usuarios');
        
        $validated = $request->validate([
            'nombre' => 'required|max:100',
            'apellido' => 'required|max:100',
            'email' => 'required|email|unique:usuarios,email',
            'password' => 'required|min:6',
            'rol' => 'required|in:admin,tecnico,cliente',
            'telefono' => 'nullable|max:20',
            'cargo' => 'nullable|max:100',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['estado'] = true; // Activo por defecto
        
        Usuario::create($validated);

        return back()->with('success', 'Usuario creado correctamente');
    }

    public function update(Request $request, $id)
    {
        //$this->authorize('gestionar-usuarios');
        
        $usuario = Usuario::findOrFail($id);
        
        $validated = $request->validate([
            'nombre' => 'required|max:100',
            'apellido' => 'required|max:100',
            'email' => 'required|email|unique:usuarios,email,' . $id,
            'rol' => 'required|in:admin,tecnico,cliente',
            'telefono' => 'nullable|max:20',
            'cargo' => 'nullable|max:100',
            'estado' => 'boolean',
        ]);
        
        // Solo actualizar password si se proporciona
        if ($request->filled('password')) {
            $request->validate(['password' => 'min:6']);
            $validated['password'] = Hash::make($request->password);
        }
        
        $usuario->update($validated);

        return back()->with('success', 'Usuario actualizado correctamente');
    }

    public function toggleEstado($id)
    {
        //$this->authorize('gestionar-usuarios');
        
        $usuario = Usuario::findOrFail($id);
        $usuario->update(['estado' => !$usuario->estado]);
        
        $mensaje = $usuario->estado ? 'Usuario activado correctamente' : 'Usuario desactivado correctamente';
        
        return back()->with('success', $mensaje);
    }

    public function destroy($id)
    {
        //$this->authorize('gestionar-usuarios');
        
        $usuario = Usuario::findOrFail($id);
        
        // No permitir eliminar al usuario logueado
        if ($usuario->id === auth()->id()) {
            return back()->with('error', 'No puedes eliminar tu propio usuario');
        }
        
        $usuario->delete();
        
        return back()->with('success', 'Usuario eliminado correctamente');
    }
}