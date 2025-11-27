<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\Categoria;
use App\Models\Prioridad;
use App\Models\Usuario;
use App\Models\Comentario;


class TicketController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Ticket::with(['usuario', 'tecnico', 'categoria', 'prioridad']);

        // Filtrar según rol
        if ($user->esCliente()) {
            $query->where('usuario_id', $user->id);
        } elseif ($user->esTecnico()) {
            $query->where('tecnico_id', $user->id);
        }

        // Filtros adicionales
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('prioridad_id')) {
            $query->where('prioridad_id', $request->prioridad_id);
        }

        if ($request->filled('tecnico_id')) {
            if ($request->tecnico_id === 'sin_asignar') {
                $query->whereNull('tecnico_id');
            } else {
                $query->where('tecnico_id', $request->tecnico_id);
            }
        }

        if ($request->filled('origen')) {
            $query->where('origen', $request->origen);
        }

        if ($request->filled('busqueda')) {
            $query->where(function ($q) use ($request) {
                $q->where('numero_ticket', 'like', '%' . $request->busqueda . '%')
                    ->orWhere('titulo', 'like', '%' . $request->busqueda . '%');
            });
        }

        $tickets = $query->latest('fecha_apertura')->paginate(10);

        // Calcular estadísticas para la barra superior
        $statsQuery = Ticket::query();
        if ($user->esCliente()) {
            $statsQuery->where('usuario_id', $user->id);
        } elseif ($user->esTecnico()) {
            $statsQuery->where('tecnico_id', $user->id);
        }

        $stats = [
            'total' => $statsQuery->count(),
            'abiertos' => (clone $statsQuery)->where('estado', 'abierto')->count(),
            'en_proceso' => (clone $statsQuery)->where('estado', 'en_proceso')->count(),
            'resueltos' => (clone $statsQuery)->where('estado', 'resuelto')->count(),
            'criticos' => (clone $statsQuery)->whereHas('prioridad', function($q) {
                $q->where('nombre', 'urgente')->orWhere('nombre', 'alta');
            })->whereIn('estado', ['abierto', 'en_proceso'])->count(),
        ];

        // Obtener datos para los filtros
        $prioridades = Prioridad::ordenadoPorNivel()->get();
        $tecnicos = Usuario::where('rol', 'tecnico')->where('estado', true)->get();

        return view('tickets.index', compact('tickets', 'stats', 'prioridades', 'tecnicos'));
    }

    public function create()
    {
        $categorias = Categoria::activas()->get();
        $prioridades = Prioridad::ordenadoPorNivel()->get();
        return view('tickets.create', compact('categorias', 'prioridades'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'titulo' => 'required|max:200',
            'descripcion' => 'required',
            'categoria_id' => 'required|exists:categorias,id',
            'prioridad_id' => 'required|exists:prioridades,id',
        ]);

        $validated['usuario_id'] = auth()->id();
        $validated['fecha_apertura'] = now();

        $ticket = Ticket::create($validated);

        return redirect()->route('tickets.show', $ticket->id)
            ->with('success', 'Ticket creado exitosamente');
    }

    public function show($id)
    {
        $ticket = Ticket::with([
            'usuario',
            'tecnico',
            'categoria',
            'prioridad',
            'comentarios.usuario'
        ])->findOrFail($id);

        $user = auth()->user();

        // Verificar permisos
        if ($user->esCliente() && $ticket->usuario_id != $user->id) {
            abort(403, 'No tienes permiso para ver este ticket');
        }

        $tecnicos = [];
        if ($user->esAdmin()) {
            $tecnicos = Usuario::where('rol', 'tecnico')->where('estado', true)->get();
        }

        return view('tickets.show', compact('ticket', 'tecnicos'));
    }

    public function asignar(Request $request, $id)
    {
        //$this->authorize('asignar-ticket');

        $ticket = Ticket::findOrFail($id);
        $ticket->asignarTecnico($request->tecnico_id);

        return back()->with('success', 'Ticket asignado correctamente');
    }

    public function cambiarEstado(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);

        // Verificar permisos
        if (!auth()->user()->esStaff()) {
            abort(403);
        }

        $ticket->cambiarEstado($request->estado);

        return back()->with('success', 'Estado actualizado');
    }

    public function agregarComentario(Request $request, $id)
    {
        $validated = $request->validate([
            'comentario' => 'required'
        ]);

        Comentario::create([
            'ticket_id' => $id,
            'usuario_id' => auth()->id(),
            'comentario' => $validated['comentario'],
            'es_interno' => false
        ]);

        return back()->with('success', 'Comentario agregado');
    }
}