<?php
$arquivo = "estoquemedicao.json";
$historicoArquivo = "historicomedicao.json";

$dados = json_decode(file_get_contents("php://input"), true);

if (!$dados || !isset($dados["acao"])) {
    echo "Requisição inválida.";
    exit;
}

$acao = $dados["acao"];

$estoque = file_exists($arquivo) ? json_decode(file_get_contents($arquivo), true) : [];
if (!is_array($estoque)) $estoque = [];

$historico = file_exists($historicoArquivo) ? json_decode(file_get_contents($historicoArquivo), true) : [];
if (!is_array($historico)) $historico = [];


// ADICIONAR
if ($acao == "adicionar") {

    $novo = [
        "tipo" => $dados["tipo"],
        "modelo" => $dados["modelo"],
        "codigo" => $dados["codigo"],
        "local" => $dados["local"],
        "status" => "Disponivel",
        "quantidade" => (int)$dados["quantidade"]
    ];

    $estoque[] = $novo;

    $historicoMsg = "Instrumento {$dados['codigo']} adicionado com quantidade {$dados['quantidade']}.";
}


// MOVIMENTAR
if ($acao == "movimentar") {

    foreach ($estoque as &$item) {
        if ($item["codigo"] == $dados["codigo"]) {
            $item["status"] = $dados["status"];
            $item["local"] = $dados["local"];
            $item["quantidade"] = (int)$dados["quantidade"];
        }
    }

    $historicoMsg = "Instrumento {$dados['codigo']} atualizado.";
}


// REMOVER
if ($acao == "remover") {

    $codigoRemover = $dados["codigo"];
    $novoEstoque = [];
    $removido = false;

    foreach ($estoque as $item) {
        if ($item["codigo"] != $codigoRemover) {
            $novoEstoque[] = $item;
        } else {
            $removido = true;
        }
    }

    $estoque = $novoEstoque;

    if ($removido) {
        $historicoMsg = "Instrumento {$codigoRemover} removido do estoque.";
    } else {
        $historicoMsg = "Tentativa de remoção: instrumento {$codigoRemover} não encontrado.";
    }
}


file_put_contents($arquivo, json_encode($estoque, JSON_PRETTY_PRINT));

$historico[] = [
    "mensagem" => $historicoMsg,
    "data" => date("d/m/Y H:i")
];

file_put_contents($historicoArquivo, json_encode($historico, JSON_PRETTY_PRINT));

echo "Operação realizada com sucesso!";
