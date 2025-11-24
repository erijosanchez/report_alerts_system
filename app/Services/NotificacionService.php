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
    public function enviarEmail(Alarma $alarma)
    {
        try {
            foreach ($alarma->destinatarios as $destinatario) {
                if (!empty($destinatario['email'])) {
                    Mail::raw($alarma->mensaje, function ($message) use ($destinatario, $alarma) {
                        $message->to($destinatario['email'], $destinatario['nombre'])
                            ->subject($alarma->titulo);
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
    public function enviarWhatsApp(Alarma $alarma)
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

            $twilio = new \Twilio\Rest\Client($twilioSid, $twilioToken);

            foreach ($alarma->destinatarios as $destinatario) {
                if (!empty($destinatario['telefono'])) {
                    $numeroDestino = 'whatsapp:' . $destinatario['telefono'];

                    $twilio->messages->create(
                        $numeroDestino,
                        [
                            'from' => $twilioWhatsApp,
                            'body' => $alarma->mensaje
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
     * Enviar SMS (alternativa o complemento a WhatsApp)
     */
    public function enviarSMS(Alarma $alarma)
    {
        try {
            $twilioSid = env('TWILIO_ACCOUNT_SID');
            $twilioToken = env('TWILIO_AUTH_TOKEN');
            $twilioPhone = env('TWILIO_PHONE_FROM');

            if (!$twilioSid || !$twilioToken || !$twilioPhone) {
                Log::warning("Twilio no configurado para SMS.");
                return false;
            }

            $twilio = new \Twilio\Rest\Client($twilioSid, $twilioToken);

            foreach ($alarma->destinatarios as $destinatario) {
                if (!empty($destinatario['telefono'])) {
                    $twilio->messages->create(
                        $destinatario['telefono'],
                        [
                            'from' => $twilioPhone,
                            'body' => $alarma->mensaje
                        ]
                    );

                    Log::info("SMS enviado a {$destinatario['telefono']} para alarma #{$alarma->id}");
                }
            }
            return true;
        } catch (\Exception $e) {
            Log::error("Error enviando SMS: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Notificar asignación de ticket a técnico
     */
    public function notificarAsignacion(Ticket $ticket, Usuario $tecnico)
    {
        try {
            $mensaje = "Nuevo ticket asignado:\n\n" .
                "Ticket: {$ticket->numero_ticket}\n" .
                "Título: {$ticket->titulo}\n" .
                "Prioridad: {$ticket->prioridad->nombre}\n" .
                "Cliente: {$ticket->usuario->nombre}\n\n" .
                "Por favor, revisa el ticket en el sistema.";

            // Email
            Mail::raw($mensaje, function ($message) use ($tecnico, $ticket) {
                $message->to($tecnico->email, $tecnico->nombre)
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
