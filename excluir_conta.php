<?php
session_start();
require_once("excluir_conta.php"); // seu arquivo de conexão

// Verifica se está logado
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

$usuario = $_SESSION['usuario'];

// Prepara e executa exclusão
$stmt = $conn->prepare("DELETE FROM usuarios WHERE usuario = ?");
$stmt->bind_param("s", $usuario);
$stmt->execute();

// Encerra sessão
session_unset();
session_destroy();

// Redireciona
header("Location: index.php");
exit();
?>