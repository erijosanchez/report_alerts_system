<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\MonitoreoController;
use App\Http\Controllers\SedeController;
use App\Http\Controllers\AlarmaController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UsuarioController;

// Rutas públicas
Route::get('/', function () {
    return redirect('/login');
});

// Rutas de autenticación
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Rutas protegidas
Route::middleware(['auth'])->group(function () {

    // Dashboard y reportes

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/reportes/dashboard', [ReporteController::class, 'dashboard'])->name('dashboard');
    Route::get('/reportes/tickets', [ReporteController::class, 'reporteTickets'])->name('reportes.tickets');
    Route::get('/reportes/monitoreo', [ReporteController::class, 'reporteMonitoreo'])->name('reportes.monitoreo');
    Route::get('/reportes/alarmas', [ReporteController::class, 'reporteAlarmas'])->name('reportes.alarmas');
    Route::get('/reportes/tecnicos', [ReporteController::class, 'reporteTecnicos'])->name('reportes.tecnicos');
    Route::get('/reportes/{tipo}/pdf', [ReporteController::class, 'exportarPDF'])->name('reportes.pdf');

    // Tickets
    // Tickets
    // Tickets
    Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
    Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
    Route::post('/tickets/{id}/asignar', [TicketController::class, 'asignar'])->name('tickets.asignar');
    Route::patch('/tickets/{id}/estado', [TicketController::class, 'cambiarEstado'])->name('tickets.estado');
    Route::post('/tickets/{id}/comentarios', [TicketController::class, 'agregarComentario'])->name('tickets.comentarios');

    // Usuarios (solo staff)
    Route::middleware(['can:gestionar-usuarios'])->group(function () {
        Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
        Route::post('/usuarios', [UsuarioController::class, 'store'])->name('usuarios.store');
        Route::put('usuarios/{id}', [UsuarioController::class, 'update'])->name('usuarios.update');
        Route::patch('usuarios/{id}/toggle', [UsuarioController::class, 'toggleEstado'])->name('usuarios.toggle');
        Route::delete('usuarios/{id}', [UsuarioController::class, 'destroy'])->name('usuarios.destroy');
    });
    // Usuarios


    // Monitoreo (solo staff)
    Route::middleware(['can:ver-monitoreo'])->group(function () {
        Route::get('/monitoreo', [MonitoreoController::class, 'index'])->name('monitoreo.index');
        Route::get('/monitoreo/sede/{id}', [MonitoreoController::class, 'detalleSede'])->name('monitoreo.sede');
        Route::post('/monitoreo/sede/{id}/forzar', [MonitoreoController::class, 'forzarMonitoreo'])
            ->name('monitoreo.forzar')
            ->middleware('can:gestionar-monitoreo');
    });

    // Sedes (solo admin)
    Route::middleware(['can:gestionar-sedes'])->group(function () {
        Route::get('/sedes', [SedeController::class, 'index'])->name('sedes.index');
        Route::post('/sedes', [SedeController::class, 'store'])->name('sedes.store');
        Route::put('/sedes/{id}', [SedeController::class, 'update'])->name('sedes.update');
        Route::post('/sedes/{id}/toggle', [SedeController::class, 'toggleEstado'])->name('sedes.toggle');
    });

    // Alarmas (solo staff)
    Route::middleware(['can:ver-alarmas'])->group(function () {
        Route::get('/alarmas', [AlarmaController::class, 'index'])->name('alarmas.index');
        Route::post('/alarmas/{id}/desactivar', [AlarmaController::class, 'desactivar'])
            ->name('alarmas.desactivar')
            ->middleware('can:gestionar-alarmas');
    });
});

require __DIR__ . '/auth.php';
