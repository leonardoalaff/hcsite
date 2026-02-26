<?php
session_start();
$erro = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $usuario = trim($_POST['usuario']);
    $senha = trim($_POST['senha']);

    $usuarios = json_decode(file_get_contents("usuarios.json"), true);

    foreach ($usuarios as $u) {
        if ($u['usuario'] === $usuario && password_verify($senha, $u['senha'])) {

            $_SESSION['usuario'] = $u['usuario'];
            $_SESSION['perfil'] = $u['perfil'];

            header('Location: index.php');
            exit;
        }
    }

    $erro = "Usuário ou senha inválidos.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="auth.css">
</head>
<body>

<div class="auth-container">
    <h2>Bem-vindo</h2>

    <?php if($erro): ?>
        <div class="error"><?= $erro ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="usuario" placeholder="Usuário" required>
        <input type="password" name="senha" placeholder="Senha" required>
        <button type="submit">Entrar</button>
    </form>

    <a href="registrar.php">Criar nova conta</a>
</div>

</body>
</html>