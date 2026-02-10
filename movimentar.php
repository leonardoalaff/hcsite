<?php
$dados = json_decode(file_get_contents("php://input"), true);
$estoque = json_decode(file_get_contents("estoque.json"), true);
$historico = json_decode(file_get_contents("historico.json"), true);

$item = $dados["item"];
$qtd = (int)$dados["quantidade"];
$tipo = $dados["tipo"];

if ($tipo === "saida") {
    $estoque[$item]["quantidade"] -= $qtd;
} else {
    $estoque[$item]["quantidade"] += $qtd;
}

$historico[] = [
    "item" => $item,
    "tipo" => $tipo,
    "quantidade" => $qtd,
    "data" => date("d/m/Y H:i")
];

file_put_contents("estoque.json", json_encode($estoque, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
file_put_contents("historico.json", json_encode($historico, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "Movimentação registrada!";
