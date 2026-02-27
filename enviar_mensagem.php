<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    http_response_code(403);
    exit();
}

$remetente = $_SESSION['usuario'];
$dados = json_decode(file_get_contents("php://input"), true);

$destinatario = $dados['destinatario'] ?? '';
$mensagem = $dados['mensagem'] ?? '';

if ($destinatario == '' || $mensagem == '') {
    exit();
}

$arquivo = "messages.json";

if (!file_exists($arquivo)) {
    file_put_contents($arquivo, "{}");
}

$todas = json_decode(file_get_contents($arquivo), true);

if (!isset($todas[$destinatario])) {
    $todas[$destinatario] = [];
}

$todas[$destinatario][] = [
    "de" => $remetente,
    "mensagem" => $mensagem,
    "data" => date("Y-m-d H:i")
];

file_put_contents($arquivo, json_encode($todas, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "ok";