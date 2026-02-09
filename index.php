<?php

session_start();
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
    <link rel="stylesheet" href="css14/style.css">
    <link rel="stylesheet" href="css-mobile6/mobile.css">
</head>
<body>
    <div class="menu">
        <div class="menu-icon menu-icon-perfil">
            <h1 class="texto-menu texto-menu-perfil">Perfil</h1>
            <div class="card-perfis">
                <div class='box-conta'>
                    <h2 class="tipo-perfil1 tipo-perfil">Detalhamento</h2>
                </div>

                <div class='box-conta'>
                    <h2 class="tipo-perfil2 tipo-perfil">Encarregado</h2>
                </div>

                <div class='box-conta'>
                    <h2 class="tipo-perfil3 tipo-perfil">Operador</h2>
                </div>
            </div>
        </div>

        <?php
        $perfil = $_SESSION['perfil'] ?? 'visitante';
        if ($perfil === 'detalhamento' || $perfil === 'encarregado'): ?>
            <div class="menu-icon menu-icon-add-sobra">
                <h1 class="texto-menu texto-menu-add-sobra">Add Sobra</h1>
            </div>

        <?php endif; ?>

    </div>

    <div class="card-add-sobra">
  <div class="fechar-card-sobra"></div>

  <form class="form-card-sobra" method="post" enctype="multipart/form-data">
    
    <div class="form-group">
      <label for="tiposobra">Tipo de sobra</label>
      <select name="tiposobra" id="tiposobra">
        <option value="sobraregular">Sobra regular</option>
        <option value="irregular">Sobra irregular</option>
      </select>
    </div>

    <div class="form-group">
      <label for="descricao">Descrição</label>
      <input type="text" name="descricao">
    </div>

    <div class="form-row">
      <div class="form-group">
        <label for="espessura">Espessura</label>
        <input type="float" name="espessura">
      </div>
      <div class="form-group">
        <label for="largura">Dimensões</label>
        <input type="text" name="largura">
      </div>
      <div class="form-group">
        <label for="comprimento">Quantidade</label>
        <input type="number" name="comprimento">
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label for="material">Material</label>
        <input type="text" name="material">
      </div>
      <div class="form-group">
        <label for="localizacao">Localização</label>
        <input type="text" name="localizacao">
      </div>
    </div>

    <div class="form-group img">
      <label for="imagem">Adicionar arquivo</label>
      <input class="escolher-arquivo" type="file" name="imagem">
    </div>

    <input type="submit" value="Adicionar sobra">
  </form>
</div>


    <div class="box-senha">
    <form class="formulario-senha" action="login.php" method="POST">
        <input type="hidden" name="perfil" id="perfil_escolhido" value="">
        <label class="titulo-senha" for="senha">Senha</label>
        <input class="campo-senha" id="campoSenha" type="password" name="senha">
        <input class="botao-senha" id="btnEntrar" type="submit" value="Entrar">
    </form>
</div>

    <div class="box-s1">
            <video src="css14/video2.mp4" autoplay muted loop></video>
    <div class="gradiente"></div>

        <header>
        <h1>CONTROLE DE ESTOQUE DETALHAMENTO</h1>
        <div class="abrir-menu">
            <div class="linha-menu"></div>
            <div class="linha-menu2"></div>
        </div>
    </header>

    <main>
        <!-- FORMULÁRIO DE FILTRO -->
         <div class="box-filtro">
          <form class="form-filtro" method="get" action="listar_sobras.php">

          <div class="box-li-filtro">
            <label class="label-filtro-codigo label-filtro" for="codigo">Código:</label>
            <input class="input-filtro-codigo input-filtro" type="text" name="codigo" id="codigo">
          </div>

          <div class="box-li-filtro">
            <label class="label-filtro-material label-filtro" for="material">Material:</label>
            <input class="input-filtro-material input-filtro" type="text" name="material" id="material">
          </div>

          <div class="box-li-filtro">
            <label class="label-filtro-espessura label-filtro" for="espessura">Espessura:</label>
            <input class="input-filtro-espessura input-filtro" type="number" name="espessura" id="espessura" step="any">
          </div>

            <button class="btn-sobras" style='padding: 7px; position: relative; border-radius: 5px' type="submit">Buscar sobras</button> 
        </form>
         </div>
        
        <section class="sessao1" id="carrossel">

            <div class="box-card-avisos">

                <div class="card-avisos card-avisos1"><h1>CONSUMÍVEIS CNC</h1></div>

                <div class="card-avisos card-avisos2"><h1>MEDIÇÃO</h1></div>

                <div class="card-avisos card-avisos3"><h1>PAPEL IMPRESSORA</h1></div>

                <div class="card-avisos card-avisos4"><h1>ESTOQUE FILIAL</h1></div>

                <div class="card-avisos card-avisos5"><h1></h1>NOVA UNIDADE</div>

                <div class="card-avisos card-avisos4"><h1>PERFIS</h1></div>
                <div class="card-avisos card-avisos4"><h1>PERFIS</h1></div>
                <div class="card-avisos card-avisos4"><h1>PERFIS</h1></div>
                <div class="card-avisos card-avisos4"><h1>PERFIS</h1></div>
                <div class="card-avisos card-avisos4"><h1>PERFIS</h1></div>


            </div>
            

        </section>

        
        
    </main>
    </div>










        <!-------------------------FERRAMENTAS------------------------->
    <section class="s-ferramentas">

    <h1>FERRAMENTAS</h1>

    <div class="container">
    <h2>Conversor de Medidas</h2>

    <input type="number" id="mm" placeholder="Milímetros (mm)">
    <button onclick="mmParaPol()">Converter para polegadas</button>

    <input type="text" id="pol" placeholder="Polegadas (in)">
    <button onclick="polParaMm()">Converter para milímetros</button>

    <div class="resultado" id="resultado"></div>
</div>

    </section>


    <section class="sessao3">

        <div class="box-tabelas">

            <div class="box-btn-tabelas">
                <button id="btnacotubo">Catalogo Aço Tubo</button>

            <button id="btndjafer">Catalogo Djafer</button>
            </div>
            

        <div id="boxiframe1"><iframe src="https://acotubo.com.br/wp-content/uploads/2022/10/Catalogo-tubos-barras.pdf" frameborder="0"></iframe></div>
    

        <div id="boxiframe2"><iframe src="https://www.djafer.com.br/wp-content/uploads/2020/06/Cata%CC%81logo-de-Produtos-Djafer.pdf" frameborder="0"></iframe></div></div>

    </section>









    <footer></footer>

    <!-- Modal da imagem -->
    <div id="modal-imagem" class="modal-imagem">
        <img id="imagem-expandida" src="">
    </div>



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

    <script src="javascript5/script.js"></script>
</body>
</html>
