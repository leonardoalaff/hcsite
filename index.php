<?php

session_start();
$arquivo = "sobras.json";

// Lê as sobras existentes (ou cria uma lista vazia)
if (!file_exists($arquivo)) {
    file_put_contents($arquivo, "[]");
}

$conteudo = file_get_contents($arquivo);
$sobras = json_decode($conteudo, true);

if (!is_array($sobras)) {
    $sobras = [];
}


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
    <link rel="stylesheet" href="css20/style.css">
    <link rel="stylesheet" href="css-mobile7/mobile.css">
</head>
<body>
    <div class="tela-movel"><h1>VERSÃO MOBILE EM BREVE</h1></div>

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

            <div class="container2">
    <h1>📝 Lista de tarefas</h1>

    <div class="input-area">
      <input type="text" id="titulo" placeholder="Título da tarefa">
      <textarea id="descricao" placeholder="Descrição..."></textarea>
      <button onclick="adicionarLembrete()">Adicionar</button>
    </div>

    <div id="lista-lembretes"></div>
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
        <input type="number" name="espessura" step="0.01">

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



<div style="background:#111;padding:10px;border-radius:8px;color:white;">
<h3>Desenho Técnico CAD</h3>

<input id="cmd" placeholder="X 1000" onkeydown="if(event.key==='Enter'){event.preventDefault(); executarComando();}">
<button type="button" onclick="executarComando()">
<button onclick="novoProjeto()">Novo</button>

<p id="cadInfo"></p>

<canvas id="cadCanvas" width="1050" height="400" style="border:2px solid #555;background:#000;"></canvas>
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
            <video id="bgVideo" src="css20/video5.mp4" autoplay muted></video>
<div class="gradiente" id="gradiente"></div>

    <div class="gradiente"></div>

        <header>
        <h1></h1>
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

            <button class="btn-sobras" style='border-radius: 5px' type="submit">Buscar sobras</button> 
        </form>
         </div>
        
        <section class="sessao1" id="carrossel">

            <div class="box-card-avisos">

                <a href="index-planilha.php" class="link-planilhas"><div class="card-avisos card-avisos1"><h1>CONSUMÍVEIS CNC</h1></div></a>

                <a href="index-medicao.php" class="link-planilhas"><div class="card-avisos card-avisos2"><h1>MEDIÇÃO</h1></div></a>

                <div class="card-avisos card-avisos3"><h1>PAPEL IMPRESSORA</h1></div>

                <div class="card-avisos card-avisos4"><h1>ESTOQUE FILIAL</h1></div>

                <div class="card-avisos card-avisos5"><h1>NOVA UNIDADE</h1></div>

                <div class="card-avisos card-avisos4"><h1>PERFIS</h1></div>
                <div class="card-avisos card-avisos4"><h1>PERFIS</h1></div>
                <div class="card-avisos card-avisos4"><h1>PERFIS</h1></div>
                <div class="card-avisos card-avisos4"><h1>PERFIS</h1></div>
                <div class="card-avisos card-avisos4"><h1>PERFIS</h1></div>


            </div>
</div>
            

        </section>

        
        
    </main>
    </div>










        <!-------------------------FERRAMENTAS------------------------->
    <section class="s-ferramentas">

    <h1>FERRAMENTAS</h1>

    <div class="bg-s-ferramentas">

    <div class="container">
    <h2>Conversor de Medidas</h2>

    <input type="number" id="mm" placeholder="Milímetros (mm)">
    <button onclick="mmParaPol()">Converter para polegadas</button>

    <input type="text" id="pol" placeholder="Polegadas (in)">
    <button onclick="polParaMm()">Converter para milímetros</button>

    <div class="resultado" id="resultado"></div>
</div>


<div class="container3 normas-pintura">

<h2>Normas de Pintura</h2>

<input type="text" id="clientePintura" placeholder="Norma + cliente (ex: U21/20 - Usiminas)">
<input type="text" id="fundoPintura" placeholder="Fundo">
<input type="text" id="acabamentoPintura" placeholder="Acabamento">

<button onclick="salvarNorma()">Salvar Norma</button>

<select id="listaNormas" onchange="mostrarNorma()">
    <option value="">Selecione uma norma</option>
</select>

<textarea id="normaSelecionada" readonly></textarea>

<div class="btn-pintura">
    <button onclick="copiarFundo()">Copiar Fundo</button>
    <button onclick="copiarAcabamento()">Copiar Acabamento</button>
</div>

<button onclick="removerNorma()">Remover Norma</button>

</div>


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
const canvas = document.getElementById("cadCanvas");
const ctx = canvas.getContext("2d");

let escala = 0.07; // 1px = 1mm
let pontos = [{x:100,y:200}]; // ponto inicial

function desenhar(){
    ctx.clearRect(0,0,canvas.width,canvas.height);
    ctx.beginPath();
    ctx.moveTo(pontos[0].x, pontos[0].y);

    for(let i=1;i<pontos.length;i++){
        ctx.lineTo(pontos[i].x, pontos[i].y);
    }

    ctx.strokeStyle="lime";
    ctx.lineWidth=2;
    ctx.stroke();
}

function executarComando(){
    let c = document.getElementById("cmd").value.trim().toUpperCase();
    let p = pontos[pontos.length-1];

    let partes = c.split(" ");

    if(partes[0]=="X"){
        let dx = parseFloat(partes[1]);
        pontos.push({x:p.x+dx*escala, y:p.y});
    }

    if(partes[0]=="Y"){
        let dy = parseFloat(partes[1]);
        pontos.push({x:p.x, y:p.y-dy*escala});
    }

    if(partes[0]=="ANG"){
        let ang = parseFloat(partes[1]) * Math.PI/180;
        let comp = parseFloat(partes[2]);
        pontos.push({
            x: p.x + Math.cos(ang)*comp*escala,
            y: p.y - Math.sin(ang)*comp*escala
        });
    }

    desenhar();
}

function novoProjeto(){
    pontos = [{x:100,y:200}];
    desenhar();
}
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

    <script src="javascript12/script.js"></script>
</body>
</html>
