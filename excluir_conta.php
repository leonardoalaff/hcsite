<?php
session_start();

// Verifica se está logado
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

$usuarioLogado = $_SESSION['usuario'];

// Lê o arquivo JSON
$usuarios = json_decode(file_get_contents("usuarios.json"), true);

// Filtra removendo o usuário logado
$usuariosAtualizados = [];

foreach ($usuarios as $u) {
    if ($u['usuario'] !== $usuarioLogado) {
        $usuariosAtualizados[] = $u;
    }
}

// Salva novamente no arquivo
file_put_contents("usuarios.json", json_encode($usuariosAtualizados, JSON_PRETTY_PRINT));

// Encerra sessão
session_unset();
session_destroy();

// Redireciona
header("Location: index.php");
exit();
?>