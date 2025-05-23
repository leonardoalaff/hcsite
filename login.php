<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $perfil = $_POST['perfil'];
    $senha = $_POST['senha'];

    if ($perfil === 'detalhamento' && $senha === '1234') {
        $_SESSION['perfil'] = 'detalhamento';
        header('Location: index.php');
        exit;
    }

    if ($perfil === 'encarregado' && $senha === '12345') {
        $_SESSION['perfil'] = 'encarregado';
        header('Location: index.php');
        exit;
    }

    if ($perfil === 'operador' && $senha === '123') {
        $_SESSION['perfil'] = 'operador';
        header('Location: index.php');
        exit;
    }

    $_SESSION['perfil'] = 'visitante';
    header('Location: index.php');
    exit;
}
