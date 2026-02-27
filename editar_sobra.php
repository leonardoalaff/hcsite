<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: index.php');
    exit;
}

$id = $_GET['id'] ?? '';
$arquivo = "sobras.json";

$sobras = file_exists($arquivo) ? json_decode(file_get_contents($arquivo), true) : [];

$sobra = null;
foreach ($sobras as &$item) {
    if ($item['codigo'] == $id) {
        $sobra = &$item;
        break;
    }
}

if ($sobra == null) {
    echo "Sobra não encontrada.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sobra['descricao'] = $_POST['descricao'];
    $sobra['espessura'] = $_POST['espessura'];
    $sobra['largura'] = $_POST['largura'];
    $sobra['comprimento'] = $_POST['comprimento'];
    $sobra['material'] = $_POST['material'];
    $sobra['localizacao'] = $_POST['localizacao'];

    file_put_contents($arquivo, json_encode($sobras, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    header("Location: listar_sobras.php");

    exit;
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Editar Sobra</title>
    <link rel="stylesheet" href="css10/estilo.css">
    <link rel="stylesheet" href="css-mobile6/mobile2.css">
</head>
<body class="corpo-editar">
    <h1 class="t-h1-editar-sobra">Editar Sobra <?= htmlspecialchars($sobra['codigo']) ?> </h1>

    <div class="box-editar-sobra">
    <form class="form-editar-sobra" method="POST">
        <label>Descrição:<input name="descricao" value="<?= htmlspecialchars($sobra['descricao']) ?>"> </label><br>
        <label>Espessura:<input name="espessura" value="<?= htmlspecialchars($sobra['espessura']) ?>"> </label><br>
        <label>Dimensões:<input name="largura" value="<?= htmlspecialchars($sobra['largura']) ?>"> </label><br>
        <label>Quantidade:<input name="comprimento" value="<?= htmlspecialchars($sobra['comprimento']) ?>"> </label><br>
        <label>Material:<input name="material" value="<?= htmlspecialchars($sobra['material']) ?>"> </label><br>
        <label>Localização:<input name="localizacao" value="<?= htmlspecialchars($sobra['localizacao']) ?>"> </label><br>
        <input class="btn-salvar-editar" type="submit" value="Salvar">
    </form>
    </div>

    <a class="btn-voltar-editar" href="listar_sobras.php">Voltar</a>

</body>
</html>
