<?php
const TOKEN_ANDERCODE = '9f4b2d7a1c8e5f60b3a2d9e4c7f1b6a8d2e3f4c5b6a7c8d9e0f1a2b3c4d5e6f';
const WEBHOOK_URL = 'https://botacry.ibsimportadora.pe/webhook.php';

function verificarToken($req, $res)
{
    try {
        $token = $req['hub_verify_token'];
        $challenge = $req['hub_challenge'];

        if (isset($challenge) && isset($token) && $token == TOKEN_ANDERCODE) {
            $res->send($challenge);
        } else {
            $res->status(400)->send();
        }
    } catch (\Throwable $th) {
        $res->status(400)->send();
    }
}
function recibirMensaje($req, $res)
{
    try {
        $entry = $req['entry'][0];
        $changes = $entry['changes'][0];
        $value = $changes['value'];
        $objetomensaje = $value['messages'];
        $mensaje = $objetomensaje[0];
        $comentario = $mensaje['text']['body'];
        $numero = $mensaje['from'];
        $archivo = fopen("log.txt", "a");
        $texto = json_encode($comentario);
        fwrite($archivo, $texto . "\n");
        fclose($archivo);
        $res->send("EVENT_RECEIVED");
    } catch (\Throwable $th) {
        $res->send("EVENT_RECEIVED");
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    recibirMensaje($data, http_response_code());
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['hub_mode']) && isset($_GET['hub_verify_token']) && isset($_GET['hub_challenge']) && $_GET['hub_mode'] === 'subscribe' && $_GET['hub_verify_token'] === TOKEN_ANDERCODE) {
        echo $_GET['hub_challenge'];
    } else {
        http_response_code(403);
    }
}
