<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Estoque CNC</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<h1>Controle de Estoque CNC</h1>

<table>
<thead>
<tr>
    <th>Item</th>
    <th>Quantidade</th>
    <th>Mínimo</th>
</tr>
</thead>
<tbody>
<?php
$arquivo = "estoque.json";

if (!file_exists($arquivo)) {
    file_put_contents($arquivo, json_encode([], JSON_PRETTY_PRINT));
}

$dados = json_decode(file_get_contents($arquivo), true);
if (!is_array($dados)) $dados = [];

foreach ($dados as $item => $info) {
    $alerta = ($info["quantidade"] <= $info["minimo"]) ? "alerta" : "";
    echo "
    <tr class='$alerta'>
        <td>$item</td>
        <td>{$info["quantidade"]}</td>
        <td>{$info["minimo"]}</td>
    </tr>";
}
?>
</tbody>
</table>

<h2>Movimentar Estoque</h2>

<select id="item">
<?php foreach ($dados as $item => $info) echo "<option>$item</option>"; ?>
</select>

<select id="tipo">
    <option value="entrada">Entrada</option>
    <option value="saida">Saída</option>
</select>

<input type="number" id="quantidade" placeholder="Quantidade">
<button onclick="movimentar()">Aplicar</button>

<h2>Histórico</h2>
<div id="historico"></div>

<script src="script.js"></script>
</body>
</html>
