<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🧪 PROBANDO WHATSAPP TWILIO\n";
echo "===========================\n\n";

$sid = env('TWILIO_ACCOUNT_SID');
$token = env('TWILIO_AUTH_TOKEN');
$from = env('TWILIO_WHATSAPP_FROM');
$to = 'whatsapp:+51943696802'; // Tu número

echo "Configuración:\n";
echo "- SID: " . substr($sid, 0, 10) . "...\n";
echo "- Token: " . substr($token, 0, 10) . "...\n";
echo "- From: {$from}\n";
echo "- To: {$to}\n\n";

if (!$sid || !$token || !$from) {
    die("❌ ERROR: Twilio no está configurado correctamente en .env\n");
}

try {
    $twilio = new \Twilio\Rest\Client($sid, $token);
    
    echo "Enviando mensaje...\n";
    
    $message = $twilio->messages->create(
        $to,
        [
            'from' => $from,
            'body' => "🚨 Prueba de WhatsApp desde Sistema Trimax\n\n✅ Si recibes este mensaje, todo está funcionando correctamente!"
        ]
    );
    
    echo "\n✅ WhatsApp enviado exitosamente!\n";
    echo "SID del mensaje: " . $message->sid . "\n";
    echo "Estado: " . $message->status . "\n";
    echo "\n📱 Revisa tu WhatsApp!\n";
    
} catch (\Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n\n";
    
    // Ayuda para errores comunes
    if (strpos($e->getMessage(), '21211') !== false) {
        echo "💡 El número no está registrado en el sandbox.\n";
        echo "   Envía 'join <codigo>' al WhatsApp de Twilio.\n";
    } elseif (strpos($e->getMessage(), '20003') !== false) {
        echo "💡 Credenciales incorrectas.\n";
        echo "   Verifica TWILIO_ACCOUNT_SID y TWILIO_AUTH_TOKEN en .env\n";
    }
}