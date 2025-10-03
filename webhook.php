<?php
// Configurar zona horaria de Perú
date_default_timezone_set('America/Lima');

const TOKEN_ANDERCODE = '9f4b2d7a1c8e5f60b3a2d9e4c7f1b6a8d2e3f4c5b6a7c8d9e0f1a2b3c4d5e6f';
const WEBHOOK_URL = 'https://botacry.ibsimportadora.pe/webhook.php';

function verificarToken($mode, $token, $challenge)
{
    try {
        if (isset($challenge) && isset($token) && $token == TOKEN_ANDERCODE) {
            echo $challenge;
            http_response_code(200);
        } else {
            http_response_code(403);
        }
    } catch (\Throwable $th) {
        http_response_code(403);
    }
}
function recibirMensaje($req)
{
    try {
        // Verificar si hay mensajes reales en el webhook
        if (isset($req['entry'][0]['changes'][0]['value']['messages'])) {
            $entry = $req['entry'][0];
            $changes = $entry['changes'][0];
            $value = $changes['value'];
            $objetomensaje = $value['messages'];
            $mensaje = $objetomensaje[0];

            // Solo procesar si tiene texto
            if (isset($mensaje['text']['body'])) {
                $comentario = $mensaje['text']['body'];
                $numero = $mensaje['from'];
                $messageId = $mensaje['id']; // ID único del mensaje
                $timestamp = date('d/m/Y H:i:s'); // Formato peruano: día/mes/año hora:minuto:segundo

                // Verificar si ya procesamos este mensaje
                $logContent = file_exists("log.txt") ? file_get_contents("log.txt") : "";

                // Si el mensaje ID ya está en el log, no lo procesar de nuevo
                if (strpos($logContent, $messageId) === false) {
                    $archivo = fopen("log.txt", "a");
                    if ($archivo) {
                        $logData = [
                            'fecha' => $timestamp,
                            'numero' => $numero,
                            'mensaje' => $comentario,
                            'message_id' => $messageId
                        ];
                        fwrite($archivo, json_encode($logData) . "\n");
                        fclose($archivo);
                    }
                }
            }
        }

        // Respuesta correcta en PHP
        http_response_code(200);
        echo "EVENT_RECEIVED";
    } catch (\Throwable $th) {
        http_response_code(200);
        echo "EVENT_RECEIVED";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    recibirMensaje($data);
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $mode = $_GET['hub_mode'] ?? '';
    $token = $_GET['hub_verify_token'] ?? '';
    $challenge = $_GET['hub_challenge'] ?? '';

    if ($mode === 'subscribe' && $token === TOKEN_ANDERCODE) {
        verificarToken($mode, $token, $challenge);
    } else {
        http_response_code(403);
    }
}
