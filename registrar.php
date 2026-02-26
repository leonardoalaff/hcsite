<?php
session_start();
$mensagem = "";
$erro = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $usuario = trim($_POST['usuario']);
    $senha = trim($_POST['senha']);
    $perfil = "usuario";

    if ($usuario == '' || $senha == '') {
        $erro = "Preencha todos os campos.";
    } else {

        $usuarios = json_decode(file_get_contents("usuarios.json"), true);

        foreach ($usuarios as $u) {
            if ($u['usuario'] === $usuario) {
                $erro = "Usuário já existe!";
                break;
            }
        }

        if (!$erro) {
            $novoUsuario = [
                "usuario" => $usuario,
                "senha" => password_hash($senha, PASSWORD_DEFAULT),
                "perfil" => $perfil
            ];

            $usuarios[] = $novoUsuario;

            file_put_contents("usuarios.json", json_encode($usuarios, JSON_PRETTY_PRINT));

            $mensagem = "Usuário criado com sucesso!";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Criar Conta</title>
    <link rel="stylesheet" href="auth.css">
</head>
<body>

<div class="auth-container">
    <h2>Criar Conta</h2>

    <?php if($erro): ?>
        <div class="error"><?= $erro ?></div>
    <?php endif; ?>

    <?php if($mensagem): ?>
        <div class="success"><?= $mensagem ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="usuario" placeholder="Usuário" required>
        <input type="password" name="senha" placeholder="Senha" required>
        <button type="submit">Cadastrar</button>
    </form>

    <a href="login.php">Voltar para login</a>
</div>

</body>
</html>