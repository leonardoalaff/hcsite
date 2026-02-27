<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    http_response_code(403);
    exit();
}

$usuario = $_SESSION['usuario'];

$dados = json_decode(file_get_contents("php://input"), true);

$arquivo = "lembretes.json";

if (!file_exists($arquivo)) {
    file_put_contents($arquivo, "{}");
}

$todos = json_decode(file_get_contents($arquivo), true);

$todos[$usuario] = $dados;

file_put_contents($arquivo, json_encode($todos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "ok";