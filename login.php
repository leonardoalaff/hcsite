<?php
session_start();

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

    echo "Usuário ou senha inválidos.";
}
?>

<form method="POST">
    <h2>Login</h2>

    <input type="text" name="usuario" placeholder="Usuário"><br><br>
    <input type="password" name="senha" placeholder="Senha"><br><br>

    <button type="submit">Entrar</button>
</form>

<a href="registrar.php">Criar nova conta</a>