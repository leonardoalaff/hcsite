<?php

session_start();
date_default_timezone_set('America/Sao_Paulo');

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
    <link rel="stylesheet" href="css30/style.css">
    <link rel="stylesheet" href="css-mobile10/mobile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <div class="tela-movel" aria-hidden="true"></div>
    <main class="mobile-app-home" aria-label="Página inicial mobile">
        <section class="mobile-app-top">
            <div>
                <span class="mobile-eyebrow">HC Indústria</span>
                <h1>Controle interno</h1>
                <p>Estoque, medição e ferramentas em uma tela rápida para celular.</p>
            </div>
            <button class="mobile-profile-pill" type="button" onclick="window.location.href='<?php echo isset($_SESSION['usuario']) ? 'logout.php' : 'login.php'; ?>'">
                <i class="fa-solid fa-user"></i>
            </button>
        </section>

        <section class="mobile-hero-card">
            <span class="mobile-status-dot"><i></i> Sistema online</span>
            <h2>Painel operacional</h2>
            <p>Acesse os módulos principais, cadastre sobras e consulte ferramentas sem perder tempo.</p>
            <div class="mobile-hero-actions">
                <button type="button" class="mobile-primary-action" id="mobileAddSobraBtn">
                    <i class="fa-solid fa-plus"></i> Nova sobra
                </button>
                <button type="button" class="mobile-secondary-action" onclick="toggleMensagens()">
                    <i class="fa-solid fa-comment-dots"></i> Mensagens
                </button>
            </div>
        </section>

        <section class="mobile-search-card">
            <div class="mobile-section-title">
                <span>Busca rápida</span>
                <strong>Sobras</strong>
            </div>
            <form class="mobile-search-form" method="get" action="listar_sobras.php">
                <label>
                    <span>Código</span>
                    <input type="text" name="codigo" placeholder="Ex: 0012">
                </label>
                <label>
                    <span>Material</span>
                    <input type="text" name="material" placeholder="A36, SAC, inox...">
                </label>
                <label>
                    <span>Espessura</span>
                    <input type="number" name="espessura" step="any" placeholder="mm">
                </label>
                <button type="submit">Buscar no estoque</button>
            </form>
        </section>

        <section class="mobile-modules">
            <div class="mobile-section-title">
                <span>Acessos</span>
                <strong>Módulos principais</strong>
            </div>
            <div class="mobile-module-grid">
                <a href="index-planilha.php" class="mobile-module-card featured">
                    <i class="fa-solid fa-boxes-stacked"></i>
                    <strong>Consumíveis CNC</strong>
                    <small>Controle de itens e movimentações</small>
                </a>
                <a href="index-medicao.php" class="mobile-module-card">
                    <i class="fa-solid fa-ruler-combined"></i>
                    <strong>Medição</strong>
                    <small>Registros e acompanhamento</small>
                </a>
                <button type="button" class="mobile-module-card" onclick="document.querySelector('.s-ferramentas')?.scrollIntoView({behavior:'smooth'});">
                    <i class="fa-solid fa-screwdriver-wrench"></i>
                    <strong>Ferramentas</strong>
                    <small>Conversor e normas</small>
                </button>
                <button type="button" class="mobile-module-card" onclick="toggleMensagens()">
                    <i class="fa-solid fa-inbox"></i>
                    <strong>Mensagens</strong>
                    <small>Caixa de entrada interna</small>
                </button>
            </div>
        </section>

        <nav class="mobile-bottom-nav" aria-label="Navegação mobile">
            <a href="index.php" class="active"><i class="fa-solid fa-house"></i><span>Início</span></a>
            <a href="listar_sobras.php"><i class="fa-solid fa-magnifying-glass"></i><span>Sobras</span></a>
            <button type="button" id="mobileAddSobraBtnNav"><i class="fa-solid fa-plus"></i><span>Adicionar</span></button>
            <a href="index-medicao.php"><i class="fa-solid fa-ruler"></i><span>Medição</span></a>
        </nav>
    </main>

    <div class="menu">
        <div class="menu-icon menu-icon-perfil" id="menuConta">


    <span class="nome-conta">
<?php
if (isset($_SESSION['usuario'])) {
    echo $_SESSION['usuario'];
} else {
    echo "Conta";
}
?>
</span>

    <div class="card-perfis submenu" id="dropdownConta">


<?php if (!isset($_SESSION['usuario'])): ?>

    <div class="box-conta" onclick="window.location.href='registrar.php'">
        <i class="fa-solid fa-user-plus icone-conta"></i>
        <h2 class="tipo-perfil">Criar nova conta</h2>
    </div>

    

    <div class="box-conta" onclick="window.location.href='login.php'">
        <i class="fa-solid fa-right-to-bracket icone-conta"></i>
        <h2 class="tipo-perfil">Logar</h2>
    </div>

<?php else: ?>

    <div class="box-conta" onclick="window.location.href='logout.php'">
        <i class="fa-solid fa-right-from-bracket icone-conta"></i>
        <h2 class="tipo-perfil">Sair</h2>
    </div>

    <div class="box-conta excluir-conta" onclick="confirmarExclusao()">
        <i class="fa-solid fa-trash icone-conta"></i>
        <h2 class="tipo-perfil">Excluir Conta</h2>
    </div>

<?php endif; ?>

</div>
</div>

        
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
            <video id="bgVideo" src="css21/video5.mp4" autoplay muted></video>
<div class="gradiente" id="gradiente"></div>





<div class="icone-mensagem" onclick="toggleMensagens()">
    <i class="fa-solid fa-comment-dots"></i>
</div>

<div class="container-mensagens">
  <h2>📩 Mensagens</h2>

  <div class="mensagem-form">
    <input type="text" id="destinatario" placeholder="Enviar para (nome do usuário)">
    <textarea id="mensagemTexto" placeholder="Digite sua mensagem"></textarea>
    <button onclick="enviarMensagem()">Enviar</button>
  </div>

  <h3>Caixa de entrada</h3>
  <div id="caixaMensagens" class="caixa-mensagens"></div>
</div>





    <div class="gradiente"></div>

        <header>
        <h1></h1>
        <div class="abrir-menu">
            <div class="linha-menu"></div>
            <div class="linha-menu2"></div>
        </div>
    </header>

    <main class="home-dashboard">
        <section class="dashboard-topbar">
            <div class="topbar-copy">
                <span class="topbar-kicker">HC Indústria</span>
                <h1>Painel inicial</h1>
                <p>Atalhos rápidos, visão geral das rotinas e acesso direto aos principais módulos do sistema.</p>
            </div>

            <div class="topbar-actions">
                <button class="topbar-chip" type="button" onclick="window.location.href='index-planilha.php'">Consumíveis CNC</button>
                <button class="topbar-chip secondary" type="button" onclick="window.location.href='index-medicao.php'">Medição</button>
            </div>
        </section>

        <div class="dashboard-hero-banner">
            <div class="hero-banner-copy">
                <span class="hero-badge">Ambiente operacional</span>
                <h2>Central de controle da produção e do estoque</h2>
                <p>Organize sobras, acompanhe ferramentas e acesse recursos técnicos em um layout mais limpo e moderno.</p>

                <div class="hero-mini-stats">
                    <div class="hero-mini-card">
                        <strong>02</strong>
                        <span>Módulos ativos</span>
                    </div>
                    <div class="hero-mini-card">
                        <strong>04</strong>
                        <span>Áreas rápidas</span>
                    </div>
                    <div class="hero-mini-card">
                        <strong>24h</strong>
                        <span>Acesso interno</span>
                    </div>
                </div>
            </div>

            <div class="hero-banner-side">
                <div class="hero-side-card primary">
                    <span>Status</span>
                    <strong>Sistema pronto</strong>
                    <small>Menu lateral, mensagens e tarefas disponíveis.</small>
                </div>
                <div class="hero-side-grid">
                    <div class="hero-side-card small">
                        <span>Estoque</span>
                        <strong>Sobras</strong>
                    </div>
                    <div class="hero-side-card small">
                        <span>Controle</span>
                        <strong>Medição</strong>
                    </div>
                </div>
            </div>
        </div>

        <section class="box-filtro dashboard-filter-box">
          <form class="form-filtro dashboard-filter" method="get" action="listar_sobras.php">

          <div class="box-li-filtro">
            <label class="label-filtro-codigo label-filtro" for="codigo">Código</label>
            <input class="input-filtro-codigo input-filtro" type="text" name="codigo" id="codigo" placeholder="Ex: 0012">
          </div>

          <div class="box-li-filtro">
            <label class="label-filtro-material label-filtro" for="material">Material</label>
            <input class="input-filtro-material input-filtro" type="text" name="material" id="material" placeholder="Buscar material">
          </div>

          <div class="box-li-filtro">
            <label class="label-filtro-espessura label-filtro" for="espessura">Espessura</label>
            <input class="input-filtro-espessura input-filtro" type="number" name="espessura" id="espessura" step="any" placeholder="mm">
          </div>

            <button class="btn-sobras" type="submit">Buscar sobras</button> 
        </form>
         </section>
        
        <section class="sessao1 dashboard-shortcuts" id="carrossel">
            <div class="shortcuts-header">
                <div>
                    <span class="section-kicker">Acessos rápidos</span>
                    <h2>Módulos principais</h2>
                </div>
                <span class="shortcut-badge">Painel inicial</span>
            </div>

            <div class="box-card-avisos dashboard-cards-grid">
                <a href="index-planilha.php" class="link-planilhas dashboard-card-link">
                    <div class="card-avisos card-avisos1 dashboard-card dashboard-card-featured">
                        <span class="card-tag">Estoque</span>
                        <h1>Consumíveis CNC</h1>
                        <p>Controle visual e movimentação rápida dos itens de uso diário.</p>
                    </div>
                </a>

                <a href="index-medicao.php" class="link-planilhas dashboard-card-link">
                    <div class="card-avisos card-avisos2 dashboard-card">
                        <span class="card-tag">Produção</span>
                        <h1>Medição</h1>
                        <p>Acompanhe medidas e registros operacionais em uma tela dedicada.</p>
                    </div>
                </a>

                <div class="card-avisos card-avisos3 dashboard-card">
                    <span class="card-tag">Suprimentos</span>
                    <h1>Papel impressora</h1>
                    <p>Espaço reservado para controle e acompanhamento do consumo.</p>
                </div>

                <div class="card-avisos card-avisos4 dashboard-card">
                    <span class="card-tag">Expansão</span>
                    <h1>Estoque filial</h1>
                    <p>Área preparada para futuras rotinas entre unidades e setores.</p>
                </div>

                <div class="card-avisos card-avisos5 dashboard-card">
                    <span class="card-tag">Estrutura</span>
                    <h1>Nova unidade</h1>
                    <p>Centralize novos processos, cadastros e acompanhamento operacional.</p>
                </div>

                <div class="card-avisos card-avisos4 dashboard-card dashboard-card-soft">
                    <span class="card-tag">Equipe</span>
                    <h1>Perfis</h1>
                    <p>Área visual para contas, permissões e organização interna.</p>
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

<script>
function confirmarExclusao() {
    if (confirm("Tem certeza que deseja excluir sua conta? Esta ação não poderá ser desfeita.")) {
        window.location.href = "excluir_conta.php";
    }
}
</script>






<script>
function toggleMensagens() {
    const container = document.querySelector(".container-mensagens");
    container.classList.toggle("ativo");
}
</script>



    <script src="javascript14/script.js"></script>
</body>
</html>
