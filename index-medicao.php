<?php
$arquivo = "estoquemedicao.json";

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
<title>Estoque - Instrumentos de Medição</title>
<link rel="stylesheet" href="styleconsumiveis2.css">
</head>
<body>

<button onclick="window.location.href='index.php'" class="btn-voltar">
    ⬅ Voltar
</button>

<h1>Controle de Instrumentos de Medição</h1>

<table>
<thead>
<tr>
    <th>Tipo</th>
    <th>Modelo</th>
    <th>Código</th>
    <th>Local</th>
    <th>Status</th>
    <th>Quantidade</th>
</tr>
</thead>

<tbody>
<?php
foreach ($dados as $instrumento) {

    $qtd = (int)$instrumento['quantidade'];
    $alerta = ($qtd <= 2) ? "alerta-zero" : "";

    echo "
    <tr class='$alerta'>
        <td>{$instrumento['tipo']}</td>
        <td>{$instrumento['modelo']}</td>
        <td>{$instrumento['codigo']}</td>
        <td>{$instrumento['local']}</td>
        <td>{$instrumento['status']}</td>
        <td>{$instrumento['quantidade']}</td>
    </tr>";
}
?>
</tbody>

</table>

<h2>Adicionar Instrumento</h2>

<select id="tipo">
    <option value="Trena">Trena</option>
    <option value="Paquímetro">Paquímetro</option>
    <option value="Esquadro">Esquadro</option>
</select>

<select id="modelo">
    <option value="5m">Trena 5m</option>
    <option value="15m">Trena 15m</option>
    <option value="25m">Trena 25m</option>
    <option value="30m">Trena 30m</option>
    <option value="200mm">Paquímetro 200mm</option>
    <option value="300mm">Paquímetro 300mm</option>
    <option value='8"'>Esquadro 8"</option>
    <option value='12"'>Esquadro 12"</option>
</select>

<input type="text" id="codigo" placeholder="Código de rastreio">
<input type="text" id="local" placeholder="Local do instrumento">
<input type="number" id="quantidade" placeholder="Quantidade" min="1">

<button onclick="adicionar()">Adicionar</button>

<h2>Movimentar Instrumento</h2>

<input type="text" id="codigoMov" placeholder="Código do instrumento">
<input type="number" id="novaQuantidade" placeholder="Nova quantidade" min="0">
<input type="text" id="novoLocal" placeholder="Novo local">

<select id="novoStatus">
    <option value="Disponivel">Disponível</option>
    <option value="Em uso">Em uso</option>
</select>

<button onclick="movimentar()">Atualizar</button>

<h2>Remover Instrumento</h2>

<input type="text" id="codigoRemover" placeholder="Código do instrumento">
<button onclick="remover()" style="background:#dc3545;color:white;">
    Remover
</button>


<script src="scriptmedicao2.js"></script>

</body>
</html>
