<?php

namespace App\Services;

use App\Models\Alarma;
use App\Models\Ticket;
use App\Models\Usuario;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificacionService
{
    /**
     * Enviar notificación por email
     */
    public function enviarEmail(Alarma $alarma, array $destinatarios)
    {
        try {
            foreach ($destinatarios as $destinatario) {
                if (!empty($destinatario['email'])) {
                    $mensaje = $alarma->mensaje . "\n\n" . $alarma->detalle;

                    Mail::raw($mensaje, function ($message) use ($destinatario, $alarma) {
                        $message->to($destinatario['email'], $destinatario['nombre'])
                            ->subject($alarma->mensaje);
                    });

                    Log::info("Email enviado a {$destinatario['email']} para alarma #{$alarma->id}");
                }
            }
            return true;
        } catch (\Exception $e) {
            Log::error("Error enviando email: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Enviar notificación por WhatsApp (Twilio)
     */
    public function enviarWhatsApp(Alarma $alarma, array $destinatarios)
    {
        try {
            // Configuración de Twilio
            $twilioSid = env('TWILIO_ACCOUNT_SID');
            $twilioToken = env('TWILIO_AUTH_TOKEN');
            $twilioWhatsApp = env('TWILIO_WHATSAPP_FROM'); // ej: whatsapp:+14155238886

            if (!$twilioSid || !$twilioToken || !$twilioWhatsApp) {
                Log::warning("Twilio no configurado. Saltando envío de WhatsApp.");
                return false;
            }

            // Requiere composer require twilio/sdk
            if (!class_exists('\Twilio\Rest\Client')) {
                Log::warning("Twilio SDK no instalado. Ejecuta: composer require twilio/sdk");
                return false;
            }

            $twilio = new \Twilio\Rest\Client($twilioSid, $twilioToken);
            $mensaje = $alarma->mensaje . "\n\n" . $alarma->detalle;

            foreach ($destinatarios as $destinatario) {
                if (!empty($destinatario['telefono'])) {
                    $numeroDestino = 'whatsapp:' . $destinatario['telefono'];

                    $twilio->messages->create(
                        $numeroDestino,
                        [
                            'from' => $twilioWhatsApp,
                            'body' => $mensaje
                        ]
                    );

                    Log::info("WhatsApp enviado a {$destinatario['telefono']} para alarma #{$alarma->id}");
                }
            }
            return true;
        } catch (\Exception $e) {
            Log::error("Error enviando WhatsApp: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Notificar asignación de ticket a técnico
     */
    public function notificarAsignacion(Ticket $ticket, Usuario $tecnico)
    {
        try {
            $mensaje = "✅ Nuevo ticket asignado:\n\n" .
                "📋 Ticket: {$ticket->numero_ticket}\n" .
                "📝 Título: {$ticket->titulo}\n" .
                "⚡ Prioridad: {$ticket->prioridad->nombre}\n" .
                "👤 Cliente: {$ticket->usuario->nombre}\n\n" .
                "Por favor, revisa el ticket en el sistema.";

            // Email
            Mail::raw($mensaje, function ($message) use ($tecnico, $ticket) {
                $message->to($tecnico->email, $tecnico->nombre . ' ' . $tecnico->apellido)
                    ->subject("Nuevo Ticket Asignado: {$ticket->numero_ticket}");
            });

            Log::info("Notificación de asignación enviada a {$tecnico->email}");

            return true;
        } catch (\Exception $e) {
            Log::error("Error notificando asignación: {$e->getMessage()}");
            return false;
        }
    }
}
