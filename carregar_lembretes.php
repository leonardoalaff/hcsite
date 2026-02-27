<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    echo json_encode([]);
    exit();
}

$usuario = $_SESSION['usuario'];
$arquivo = "lembretes.json";

if (!file_exists($arquivo)) {
    echo json_encode([]);
    exit();
}

$todos = json_decode(file_get_contents($arquivo), true);

if (isset($todos[$usuario])) {
    echo json_encode($todos[$usuario]);
} else {
    echo json_encode([]);
}