<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit;
}

$arquivo = "sobras.json";
$codigo_cancelar = $_POST['codigo_cancelar'] ?? null;

if ($codigo_cancelar && file_exists($arquivo)) {

    $sobras = json_decode(file_get_contents($arquivo), true);

    foreach ($sobras as &$sobra) {
        if ($sobra['codigo'] === $codigo_cancelar) {

            // 🔥 Remove os dados da reserva
            unset($sobra['reservada']);
            unset($sobra['reservada_por']);
            unset($sobra['codigo_projeto']);

            break;
        }
    }

    file_put_contents($arquivo, json_encode($sobras, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

header("Location: " . $_SERVER['HTTP_REFERER']);
exit;