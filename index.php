<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site HC</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="menu">
        <div class="menu-icon menu-icon-perfil">
            <h1 class="texto-menu texto-menu-perfil">Perfil</h1>
        </div>

        <div class="menu-icon menu-icon-add-sobra">
            <h1 class="texto-menu texto-menu-add-sobra">Add Sobra</h1>
        </div>
    </div>

    <div class="card-add-sobra"> 
    <div class="fechar-card-sobra"></div>

    <form class="form-card-sobra" action="" method="post" enctype="multipart/form-data">

    <label for="descricao">Descrição</label>
    <input type="text" name="descricao"><br>

    <label for="espessura">Espessura</label>
    <input type="number" name="espessura"><br>

    <label for="largura">Largura</label>
    <input type="number" name="largura"><br>

    <label for="comprimento">Comprimento</label>
    <input type="number" name="comprimento"><br>

    <label for="material">Material</label>
    <input type="text" name="material"><br>

    <label for="localizacao">Localização</label>
    <input type="text" name="localizacao"><br>

    <label for="imagem">Adicionar arquivo</label>
    <input type="file" name="imagem"><br>

    <input type="submit" value="Adicionar sobra">

</form>
</div>


    

    <header>
        <h1>Controle de sobras HC</h1>
    </header>

    <main>
        
        <section class="sessao1">
            <div class="card-avisos"></div>
            <div class="card-avisos"></div>
            <div class="card-avisos"></div>
            <div class="card-avisos"></div>
        </section>

            <div class="box-sobras">
                <div class="sobras">

<?php
class Sobra {
    public $codigo;
    public $descricao;
    public $espessura;
    public $largura;
    public $comprimento;
    public $imagem;
    public $material;
    public $localizacao;

    function __construct($codigo, $descricao, $espessura, $largura, $comprimento, $imagem, $material, $localizacao) {
        $this->codigo = $codigo;
        $this->descricao = $descricao;
        $this->espessura = $espessura;
        $this->largura = $largura;
        $this->comprimento = $comprimento;
        $this->imagem = $imagem;
        $this->material = $material;
        $this->localizacao = $localizacao;
    }

    function adicionar() {
        return "Código: $this->codigo, Descrição: $this->descricao, Espessura: $this->espessura, Largura: $this->largura, Comprimento: $this->comprimento, Material: $this->material, Localização: $this->localizacao, Imagem: $this->imagem";
    }
}

$arquivo = "sobras.json";

// Lê sobras existentes
$sobras = [];
if (file_exists($arquivo)) {
    $sobras = json_decode(file_get_contents($arquivo), true) ?? [];
}

// Remover sobra
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["remover_codigo"])) {
    $codigo_para_remover = $_POST["remover_codigo"];
    $sobras = array_filter($sobras, function ($sobra) use ($codigo_para_remover) {
        return $sobra["codigo"] !== $codigo_para_remover;
    });
    file_put_contents($arquivo, json_encode(array_values($sobras), JSON_PRETTY_PRINT));

    // 🔄 Recarrega o JSON atualizado para refletir a remoção
    $sobras = json_decode(file_get_contents($arquivo), true) ?? [];

    header("Location: " . $_SERVER["PHP_SELF"]);
    exit;
}

// Adicionar nova sobra
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST["remover_codigo"])) {
    $descricao = $_POST["descricao"] ?? "";
    $espessura = $_POST["espessura"] ?? "";
    $largura = $_POST["largura"] ?? "";
    $comprimento = $_POST["comprimento"] ?? "";
    $material = $_POST["material"] ?? "";
    $localizacao = $_POST["localizacao"] ?? "";
    $imagem = "";

    // Upload da imagem
    if (isset($_FILES["imagem"]) && $_FILES["imagem"]["error"] == 0) {
        $pasta_destino = "uploads/";
        if (!file_exists($pasta_destino)) {
            mkdir($pasta_destino, 0755, true);
        }

        $nome_arquivo = basename($_FILES["imagem"]["name"]);
        $caminho_final = $pasta_destino . time() . "_" . $nome_arquivo;

        if (move_uploaded_file($_FILES["imagem"]["tmp_name"], $caminho_final)) {
            $imagem = $caminho_final;
        } else {
            echo "<p class='php-output'>Erro ao salvar a imagem.</p>";
        }
    }

   // Gera próximo código único baseado no maior código existente (mesmo após remoções)
$maior_codigo = 0;

foreach ($sobras as $sobra_existente) {
    // Remove os zeros à esquerda antes de converter para número
    $codigo_int = intval(ltrim($sobra_existente["codigo"], "0"));
    if ($codigo_int > $maior_codigo) {
        $maior_codigo = $codigo_int;
    }
}

$proximo_codigo = str_pad($maior_codigo + 1, 4, "0", STR_PAD_LEFT);

    $sobra = new Sobra($proximo_codigo, $descricao, $espessura, $largura, $comprimento, $imagem, $material, $localizacao);

    $nova_sobra = [
        "codigo" => $sobra->codigo,
        "descricao" => $sobra->descricao,
        "espessura" => $sobra->espessura,
        "largura" => $sobra->largura,
        "comprimento" => $sobra->comprimento,
        "imagem" => $sobra->imagem,
        "material" => $sobra->material,
        "localizacao" => $sobra->localizacao
    ];

    $sobras[] = $nova_sobra;
    file_put_contents($arquivo, json_encode($sobras, JSON_PRETTY_PRINT));

    echo "<div class='php-output'>";
    echo "<h2 style='backgound: transparent; color: green;'>Sobra salva com sucesso:</h2>";
    echo "<p>" . $sobra->adicionar() . "</p>";
    echo "</div>";
}

// --- FILTROS via GET ---
$filtro_material = $_GET['material'] ?? '';
$filtro_espessura = $_GET['espessura'] ?? '';
$filtro_codigo = $_GET['codigo'] ?? '';

// Função para filtrar as sobras
function filtrarSobras($sobras, $material, $espessura, $codigo) {
    return array_filter($sobras, function($sobra) use ($material, $espessura, $codigo) {
        $material_ok = true;
        $espessura_ok = true;
        $codigo_ok = true;

        if ($material !== '') {
            $material_ok = (stripos($sobra['material'], $material) !== false);
        }
        if ($espessura !== '') {
            $espessura_ok = ($sobra['espessura'] == $espessura);
        }
        if ($codigo !== '') {
            $codigo_ok = (stripos($sobra['codigo'], $codigo) !== false);
        }

        return $material_ok && $espessura_ok && $codigo_ok;
    });
}

$sobras_filtradas = filtrarSobras($sobras, $filtro_material, $filtro_espessura, $filtro_codigo);
?>

<!-- FORMULÁRIO DE FILTRO -->
<form class="form-filtro" method="get" style="margin-bottom: 20px;">
    <label style='font-size: 1.4em;' for="codigo">Código:</label>
    <input style='border: 1px solid white; position: relative; left: -3%; color: white;' type="text" name="codigo" id="codigo" value="<?php echo htmlspecialchars($filtro_codigo); ?>">

    <label style='font-size: 1.4em;' for="material" style="margin-left:20px;">Material:</label>
    <input style='border: 1px solid white; position: relative; left: -3%; color: white;' type="text" name="material" id="material" value="<?php echo htmlspecialchars($filtro_material); ?>">

    <label style='font-size: 1.4em;' for="espessura" style="margin-left:20px;">Espessura:</label>
    <input style='border: 1px solid white; position: relative; left: -3%; color: white;' type="number" name="espessura" id="espessura" value="<?php echo htmlspecialchars($filtro_espessura); ?>" step="any">

    <button style='background: #1E90FF; color: white; border: none; padding: 7px; width: 10%; position: relative; top: 63%' type="submit" style="margin-left:20px;">FILTRAR</button>
    <a href="<?php echo $_SERVER['PHP_SELF']; ?>" style="margin-left:10px; background: transparent; color: white; text-decoration: none; position: relative; top: 150%; left: -7%; font-size: 1.1em;">Limpar</a>
</form>

<?php
// Exibe as sobras filtradas
if (!empty($sobras_filtradas)) {
    echo "<div class='php-output'>";
    echo "<h2 style='background: #1E1E1E;'>Sobras Cadastradas:</h2>";
    foreach ($sobras_filtradas as $index => $sobra) {
        echo "<div style='padding:10px; margin-bottom:10px; background: #1E1E1E; border: 1px solid #E0E0E0'>";
        echo "<strong>Código:</strong> " . htmlspecialchars($sobra["codigo"] ?? "N/A") . "<br>";
        echo "Descrição: " . htmlspecialchars($sobra["descricao"] ?? "") . "<br>";
        echo "Espessura: " . htmlspecialchars($sobra["espessura"] ?? "") . "<br>";
        echo "Largura: " . htmlspecialchars($sobra["largura"] ?? "") . "<br>";
        echo "Comprimento: " . htmlspecialchars($sobra["comprimento"] ?? "") . "<br>";
        echo "Material: " . htmlspecialchars($sobra["material"] ?? "") . "<br>";
        echo "Localização: " . htmlspecialchars($sobra["localizacao"] ?? "") . "<br>";

        if (!empty($sobra["imagem"])) {
            $imgSrc = htmlspecialchars($sobra["imagem"]);
            echo "<img src='$imgSrc' class='miniatura' width='100' data-img='$imgSrc'><br>";
        }

        // Botão de Remover
        echo "<form method='post' style='margin-top:10px'>";
        echo "<input type='hidden' name='remover_codigo' value='" . htmlspecialchars($sobra["codigo"]) . "'>";
        echo "<input type='submit' value='Remover' style='background:red;color:white;border:none;padding:5px 10px;cursor:pointer'>";
        echo "</form>";

        echo "</div>";
    }
    echo "</div>";
} else {
    echo "<p style='background: transparent; color: #E0E0E0; margin: 5%'>Nenhuma sobra encontrada com os filtros aplicados.</p>";
}
?>

                </div>
            </div>
        </section>
    </main>

    <footer></footer>




<!-- Modal da imagem -->
<div id="modal-imagem" class="modal-imagem">
    <img id="imagem-expandida" src="">
</div>





    <script src="script.js"></script>
</body>
</html>