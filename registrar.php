<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $usuario = trim($_POST['usuario']);
    $senha = trim($_POST['senha']);
    $perfil = $_POST['perfil'];

    if ($usuario == '' || $senha == '') {
        echo "Preencha todos os campos.";
        exit;
    }

    $usuarios = json_decode(file_get_contents("usuarios.json"), true);

    // Verificar se já existe
    foreach ($usuarios as $u) {
        if ($u['usuario'] === $usuario) {
            echo "Usuário já existe!";
            exit;
        }
    }

    $novoUsuario = [
        "usuario" => $usuario,
        "senha" => password_hash($senha, PASSWORD_DEFAULT),
        "perfil" => $perfil
    ];

    $usuarios[] = $novoUsuario;

    file_put_contents("usuarios.json", json_encode($usuarios, JSON_PRETTY_PRINT));

    echo "Usuário criado com sucesso! <a href='index.php'>Voltar</a>";
    exit;
}
?>

<form method="POST">
    <h2>Criar Conta</h2>

    <input type="text" name="usuario" placeholder="Usuário"><br><br>
    <input type="password" name="senha" placeholder="Senha"><br><br>

    <button type="submit">Cadastrar</button>
</form>