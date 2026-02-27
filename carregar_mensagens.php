<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    echo json_encode([]);
    exit();
}

$usuario = $_SESSION['usuario'];
$arquivo = "messages.json";

if (!file_exists($arquivo)) {
    echo json_encode([]);
    exit();
}

$todas = json_decode(file_get_contents($arquivo), true);

$mensagens = $todas[$usuario] ?? [];

// 🔥 Ordenar da mais nova para a mais antiga
usort($mensagens, function($a, $b) {
    return strtotime($b['data']) - strtotime($a['data']);
});

echo json_encode($mensagens);