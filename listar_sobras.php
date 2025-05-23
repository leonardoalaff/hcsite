<?php
session_start();
$arquivo = "sobras.json";

// Lê as sobras do arquivo
$sobras = file_exists($arquivo) ? json_decode(file_get_contents($arquivo), true) : [];

// Processa ocultação, se houver
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ocultar_codigo'])) {
    $codigo_ocultar = $_POST['ocultar_codigo'];

    foreach ($sobras as &$sobra) {
        if ($sobra['codigo'] === $codigo_ocultar) {
            $sobra['oculta'] = true;
            break;
        }
    }

    file_put_contents($arquivo, json_encode($sobras, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    // Redireciona para evitar reenvio do formulário
    header("Location: listar_sobras.php?" . http_build_query($_GET));
    exit;
}

// Pega filtros via GET
$filtro_material = $_GET['material'] ?? '';
$filtro_espessura = $_GET['espessura'] ?? '';
$filtro_codigo = $_GET['codigo'] ?? '';

// Função para filtrar sobras visíveis
function filtrarSobras($sobras, $material, $espessura, $codigo) {
    return array_filter($sobras, function($sobra) use ($material, $espessura, $codigo) {
        if (!empty($sobra['oculta'])) return false; // ignora ocultas

        $material_ok = $material === '' || stripos($sobra['material'], $material) !== false;
        $espessura_ok = $espessura === '' || $sobra['espessura'] == $espessura;
        $codigo_ok = $codigo === '' || stripos($sobra['codigo'], $codigo) !== false;

        return $material_ok && $espessura_ok && $codigo_ok;
    });
}

$sobras_filtradas = filtrarSobras($sobras, $filtro_material, $filtro_espessura, $filtro_codigo);

header('Content-Type: text/html; charset=utf-8');
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Listar Sobras</title>
    <link rel="stylesheet" href="css2/estilo.css">
    <link rel="stylesheet" href="mobile.css">
</head>
<body>
    <h2>Sobras Encontradas:</h2>

    <?php if (!empty($sobras_filtradas)): ?>
        <?php foreach ($sobras_filtradas as $sobra): ?>
            <div class="sobra-card">
                Código: <strong><?= htmlspecialchars($sobra["codigo"]) ?></strong><br>
                Tipo: <strong><?= isset($sobra["tiposobra"]) && $sobra["tiposobra"] === "irregular" ? "Sobra irregular" : "Sobra regular" ?></strong><br>
                Descrição: <strong><?= htmlspecialchars($sobra["descricao"]) ?></strong><br>
                Espessura: <strong><?= htmlspecialchars($sobra["espessura"]) ?></strong><br>
                Largura: <strong><?= htmlspecialchars($sobra["largura"]) ?></strong><br>
                Comprimento: <strong><?= htmlspecialchars($sobra["comprimento"]) ?></strong><br>
               Material: <strong><?= htmlspecialchars($sobra["material"]) ?></strong><br>
                Localização: <strong><?= htmlspecialchars($sobra["localizacao"]) ?></strong><br>

                <?php if (!empty($sobra["imagem"])): ?>
                    <img src="<?= htmlspecialchars($sobra["imagem"]) ?>" alt="Imagem da sobra" class="sobra-img">

                <?php endif; ?>

                <?php
$perfil = $_SESSION['perfil'] ?? 'visitante';
if ($perfil === 'detalhamento' || $perfil === 'encarregado'):
?>
    <form method="POST" style="margin-top:10px;">
        <input type="hidden" name="ocultar_codigo" value="<?= htmlspecialchars($sobra["codigo"]) ?>">
        <button type="submit" class="remover-btn">Remover sobra</button>
    </form>
<?php endif; ?>

            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Nenhuma sobra encontrada com os filtros aplicados.</p>
    <?php endif; ?>

    <a href="index.php" class="voltar-btn">Voltar</a>

    <script>
    document.querySelectorAll('.sobra-img').forEach(img => {
        img.addEventListener('click', () => {
            img.classList.toggle('expanded');
            document.body.classList.toggle('no-scroll', img.classList.contains('expanded'));
        });
    });
</script> 
</body>
</html>
