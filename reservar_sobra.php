<?php
session_start();

if (!isset($_SESSION['perfil']) || !in_array($_SESSION['perfil'], ['detalhamento', 'encarregado'])) {
    header('Location: index.php');
    exit;
}

$codigo_reserva = $_POST['codigo_reserva'] ?? null;
$codigo_projeto = trim($_POST['codigo_projeto'] ?? '');
$arquivo = 'sobras.json';

if ($codigo_reserva && $codigo_projeto && file_exists($arquivo)) {
    $sobras = json_decode(file_get_contents($arquivo), true);

    foreach ($sobras as &$sobra) {
        if ($sobra['codigo'] === $codigo_reserva) {
            $sobra['reservada'] = true;
            $sobra['reservada_por'] = $_SESSION['perfil'];
            $sobra['codigo_projeto'] = $codigo_projeto;
            break;
        }
    }

    file_put_contents($arquivo, json_encode($sobras, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

header('Location: ' . $_SERVER['HTTP_REFERER']);
exit;
