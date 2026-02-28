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

$sobras_filtradas = array_reverse($sobras_filtradas);


header('Content-Type: text/html; charset=utf-8');
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Listar Sobras</title>
    <link rel="stylesheet" href="css11/estilo.css">
    <link rel="stylesheet" href="css-mobile6/mobile2.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <h2 class="t-sobras-encontradas">Sobras Encontradas:</h2>

    <?php if (!empty($sobras_filtradas)): ?>
        <?php foreach ($sobras_filtradas as $sobra): ?>
            <div class="sobra-card">
                Código: <strong><?= htmlspecialchars($sobra["codigo"]) ?></strong><br>
                Descrição: <strong><?= htmlspecialchars($sobra["descricao"]) ?></strong><br>
                Dimensões: <strong><?= htmlspecialchars($sobra["largura"]) ?></strong><br>
                Material: <strong><?= htmlspecialchars($sobra["material"]) ?></strong><br>
                Localização: <strong><?= htmlspecialchars($sobra["localizacao"]) ?></strong><br>
                Quantidade: <strong><?= htmlspecialchars($sobra["comprimento"]) ?>un.</strong><br>
                Tipo: <strong><?= isset($sobra["tiposobra"]) && $sobra["tiposobra"] === "irregular" ? "Sobra irregular" : "Sobra regular" ?></strong><br>
                <div class="espessura">#<?= htmlspecialchars($sobra["espessura"]) ?></div>



                <?php
$imagem = !empty($sobra["imagem"]) ? $sobra["imagem"] : "imagens/sem-imagem.png";
?>

<img src="<?= htmlspecialchars($imagem) ?>" alt="Imagem da sobra" class="sobra-img">


    <?php if (!empty($sobra['reservada']) && !empty($sobra['codigo_projeto'])): ?>
    <p class="sobra-reservada sobra-reservada2">Reservada para o projeto: <strong><?= htmlspecialchars($sobra['codigo_projeto']) ?></strong></p>
<?php endif; ?>



<?php if (!empty($sobra['reservada'])): ?>
    <p class="sobra-reservada">Reservada por: <strong><?=   htmlspecialchars($sobra['reservada_por']) ?></strong></p>
    <?php endif; ?>

<?php if (isset($_SESSION['usuario'])): ?>

<div class="acoes-sobra">
    <!-- Botão Editar -->
    <a href="editar_sobra.php?id=<?= htmlspecialchars($sobra["codigo"]) ?>" class="editar-btn">
        <i class="fa-solid fa-pen-to-square"></i>
    </a>

    <!-- Botão Remover -->
    <form method="POST" style="margin-top:10px;">
        <input type="hidden" name="ocultar_codigo" value="<?= htmlspecialchars($sobra["codigo"]) ?>">
        <button type="submit" class="remover-btn">
            <i class="fa-solid fa-trash-can"></i>
        </button>
    </form>
</div>

<?php endif; ?>
                
    

    <!-- Botão de reservar -->
    <button type="button" class="reservar-toggle-btn" data-codigo="<?= htmlspecialchars($sobra["codigo"]) ?>"><i class="fa-solid fa-bookmark"></i> Reservar sobra</button>

    <!-- Formulário de reserva -->
    <form method="POST" action="reservar_sobra.php" class="form-reserva" id="form-<?= htmlspecialchars($sobra["codigo"]) ?>" style="display:none; margin-top:5px;">
        <input class="input-reservar-sobra" type="hidden" name="codigo_reserva" value="<?= htmlspecialchars($sobra["codigo"]) ?>">
        <input class="input-reservar-sobra" type="text" name="codigo_projeto" placeholder="Código do projeto" required>
        <button type="submit" class="reservar-btn"><i class="fa-solid fa-check"></i></button>
    </form>

            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Nenhuma sobra encontrada com os filtros aplicados.</p>
    <?php endif; ?>

    <a href="index.php" class="voltar-btn">Voltar</a>

    <script>
    document.querySelectorAll('.sobra-img').forEach(img => {
    img.addEventListener('click', () => {
        if (!img.classList.contains('expanded')) {

            const overlay = document.createElement('div');
            overlay.classList.add('overlay');
            document.body.appendChild(overlay);

            img.classList.add('expanded');
            document.body.classList.add('no-scroll');

            overlay.addEventListener('click', () => {
                img.classList.remove('expanded');
                document.body.classList.remove('no-scroll');
                overlay.remove();
            });

        }
    });
});
</script> 


<script>
    document.querySelectorAll('.reservar-toggle-btn').forEach(botao => {
        botao.addEventListener('click', () => {
            const codigo = botao.dataset.codigo;
            const form = document.getElementById('form-' + codigo);
            if (form) {
                form.style.display = (form.style.display === 'none') ? 'block' : 'none';
            }
        });
    });
</script>
</body>
</html>
