<?php
$arquivo = "estoque.json";
if (!file_exists($arquivo)) {
    file_put_contents($arquivo, json_encode([], JSON_PRETTY_PRINT));
}
$dados = json_decode(file_get_contents($arquivo), true);
if (!is_array($dados)) $dados = [];

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Estoque CNC</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<button onclick="window.location.href='index.php'" class="btn-voltar">
    ⬅ Voltar
</button>


<h1>Controle de Estoque CNC</h1>

<table>
<thead>
<tr>
    <th>Item</th>
    <th>Amperes</th>
    <th>Quantidade</th>
    <th>Mínimo</th>
</tr>
</thead>

<tbody>
<?php
foreach ($dados as $item => $amps) {
    foreach ($amps as $amp => $info) {

        $qtd = $info["quantidade"];
        $min = $info["minimo"];
        $alerta = ($qtd <= $min) ? "alerta" : "";

        echo "
        <tr class='$alerta'>
            <td>$item</td>
            <td>{$amp}A</td>
            <td>$qtd</td>
            <td>$min</td>
        </tr>";
    }
}
?>
</tbody>
</table>

<h2>Movimentar Estoque</h2>

<select id="item">
<?php foreach ($dados as $item => $info) echo "<option>$item</option>"; ?>
</select>

<select id="amp">
    <option value="50">50A</option>
    <option value="130">130A</option>
    <option value="200">200A</option>
</select>

<select id="tipo">
    <option value="entrada">Entrada</option>
    <option value="saida">Saída</option>
</select>

<input type="number" id="quantidade" placeholder="Quantidade">
<button onclick="movimentar()">Aplicar</button>

<h2>Histórico</h2>
<div id="historico"></div>

<h2>Consumo por Amperagem</h2>
<canvas id="graficoAmp" width="400" height="200"></canvas>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script src="script.js"></script>

</body>
</html>
