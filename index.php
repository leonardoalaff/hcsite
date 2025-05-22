<?php
$arquivo = "sobras.json";

// Lê as sobras existentes (ou cria uma lista vazia)
$sobras = file_exists($arquivo) ? json_decode(file_get_contents($arquivo), true) : [];

// Se o formulário foi enviado e não é requisição de remoção
if ($_SERVER["REQUEST_METHOD"] === "POST" && !isset($_POST["remover_codigo"])) {
    $descricao    = $_POST["descricao"]    ?? "";
    $espessura    = $_POST["espessura"]    ?? "";
    $largura      = $_POST["largura"]      ?? "";
    $comprimento  = $_POST["comprimento"]  ?? "";
    $material     = $_POST["material"]     ?? "";
    $localizacao  = $_POST["localizacao"]  ?? "";
    $tiposobra    = $_POST["tiposobra"]    ?? "sobraregular"; // valor padrão

    // Valida valor do tipo de sobra
    if (!in_array($tiposobra, ["sobraregular", "irregular"])) {
        $tiposobra = "sobraregular";
    }

    $imagem = "";

    // Trata o upload de imagem
    if (isset($_FILES["imagem"]) && $_FILES["imagem"]["error"] == 0) {
        $pasta_destino = "uploads/";
        if (!file_exists($pasta_destino)) {
            mkdir($pasta_destino, 0755, true);
        }

        $nome_arquivo = basename($_FILES["imagem"]["name"]);
        $caminho_final = $pasta_destino . time() . "_" . $nome_arquivo;

        if (move_uploaded_file($_FILES["imagem"]["tmp_name"], $caminho_final)) {
            $imagem = $caminho_final;
        }
    }

    // Gera novo código sequencial
    $maior_codigo = 0;
    foreach ($sobras as $sobra) {
        $num = intval($sobra["codigo"]);
        if ($num > $maior_codigo) {
            $maior_codigo = $num;
        }
    }
    $novo_codigo = str_pad($maior_codigo + 1, 4, "0", STR_PAD_LEFT);

    // Monta nova sobra
    $nova_sobra = [
        "codigo"      => $novo_codigo,
        "tiposobra"   => $tiposobra,
        "descricao"   => $descricao,
        "espessura"   => $espessura,
        "largura"     => $largura,
        "comprimento" => $comprimento,
        "material"    => $material,
        "localizacao" => $localizacao,
        "imagem"      => $imagem
    ];

    // Adiciona à lista e salva
    $sobras[] = $nova_sobra;
    file_put_contents($arquivo, json_encode($sobras, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}
?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site HC</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <div class="menu">
        <div class="menu-icon menu-icon-perfil">
            <h1 class="texto-menu texto-menu-perfil">Perfil</h1>
            <div style='width: 200px; height: 200px; background: #1E1E1E; position: relative; z-index: 999;' class="card-perfis">
                <div class='box-conta'>
                    <h2 class="tipo-perfil">Detalhamento</h2>
                </div>

                <div class='box-conta'>
                    <h2 class="tipo-perfil2">Encarregado</h2>
                </div>

                <div class='box-conta'>
                    <h2 class="tipo-perfil3">Operador</h2>
                </div>
            </div>
        </div>

        <div class="menu-icon menu-icon-add-sobra">
            <h1 class="texto-menu texto-menu-add-sobra">Add Sobra</h1>
        </div>
    </div>

    <div style='background: #007fc7; justify-content:center; align-items: center;' class="card-add-sobra"> 
        <div class="fechar-card-sobra"></div>

        <form style='position:relative; top: 0; width: 90%; height: 73.5%;' class="form-card-sobra" action="" method="post" enctype="multipart/form-data">

        <select name="tiposobra" id="tiposobra" style="background: white; color: #1E1E1E; padding: 5px; border-radius: 10px; border: none; width: 40%">
            <option style="background: white; color: #1E1E1E;" value="sobraregular">Sobra regular</option>
            <option style="background: white; color: #1E1E1E;" value="irregular">Sobra irregular</option>  
        </select> <br>

            <label style='color: white;' for="descricao">Descrição</label>
            <input style='color: white; outline: none; border: 1px solid white; width: 40%' type="text" name="descricao"><br>

            <label style='color: white' for="espessura">Espessura</label>
            <input style='color: white; outline: none; border: 1px solid white; width: 40%' type="number" name="espessura"><br>

            <label style='color: white' for="largura">Largura</label>
            <input style='color: white; outline: none; border: 1px solid white; width: 40%' type="number" name="largura"><br>

            <label style='color: white' for="comprimento">Comprimento</label>
            <input style='color: white; outline: none; border: 1px solid white; width: 40%' type="number" name="comprimento"><br>

            <label style='color: white' for="material">Material</label>
            <input style='color: white; outline: none; border: 1px solid white; width: 40%' type="text" name="material"><br>

            <label style='color: white' for="localizacao">Localização</label>
            <input style='color: white; outline: none; border: 1px solid white; width: 40%' type="text" name="localizacao"><br>

            <label style='color: white' for="imagem">Adicionar arquivo</label>
            <input style='color: white; outline: none; border: 1px solid white; width: 40%' type="file" name="imagem"><br>

            <input style='background: white; border: none; position: relative;; width: 40%' type="submit" value="Adicionar sobra">
        </form>
    </div>


    <div class="box-senha">
        <form class="formulario-senha" action="">
            <label class="titulo-senha" for="senha">Senha</label>
            <input class="campo-senha" type="password">

            <input class="botao-senha" type="button" value="Entrar">
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

        <!-- FORMULÁRIO DE FILTRO -->
        <form class="form-filtro" method="get" action="listar_sobras.php" style="margin: 40px 0;">
            <label style='font-size: 1.4em; margin-right: 1%' for="codigo">Código:</label>
            <input style='border: 1px solid white; color: white; margin-right: 5%' type="text" name="codigo" id="codigo">
            <label style='font-size: 1.4em; margin-right: 1%' for="material">Material:</label>
            <input style='border: 1px solid white; color: white; margin-right: 5%' type="text" name="material" id="material">

            <label style='font-size: 1.4em; margin-right: 1%' for="espessura">Espessura:</label>
            <input style='border: 1px solid white; color: white; margin-right: 5%' type="number" name="espessura" id="espessura" step="any">

            <button style='background: #1E90FF; color: white; border: none; padding: 7px; width: 10%; position: relative; top: 75%; border-radius: 10px' type="submit">FILTRAR</button>
        </form>
    </main>

    <footer></footer>

    <!-- Modal da imagem -->
    <div id="modal-imagem" class="modal-imagem">
        <img id="imagem-expandida" src="">
    </div>

    <script src="javascript/script.js"></script>
</body>
</html>
