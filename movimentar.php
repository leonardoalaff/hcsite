<?php
$arquivo = "estoque.json";
$historicoArquivo = "historico.json";

$dados = json_decode(file_get_contents("php://input"), true);

$item = $dados["item"];
$amp = $dados["amp"];
$tipo = $dados["tipo"];
$qtd = (int)$dados["quantidade"];

$estoque = json_decode(file_get_contents($arquivo), true);

if ($tipo == "entrada") {
    $estoque[$item][$amp]["quantidade"] += $qtd;
} else {
    $estoque[$item][$amp]["quantidade"] -= $qtd;
}

file_put_contents($arquivo, json_encode($estoque, JSON_PRETTY_PRINT));

// HISTÓRICO
$historico = file_exists($historicoArquivo) ? json_decode(file_get_contents($historicoArquivo), true) : [];

$historico[] = [
    "item" => $item,
    "amp" => $amp,
    "tipo" => $tipo,
    "quantidade" => $qtd,
    "data" => date("d/m/Y H:i")
];

file_put_contents($historicoArquivo, json_encode($historico, JSON_PRETTY_PRINT));

echo "Movimentação registrada!";
