<?php
declare(strict_types=1);

date_default_timezone_set('America/Sao_Paulo');
header('Content-Type: text/plain; charset=utf-8');

$arquivoEstoque = __DIR__ . '/estoque.json';
$arquivoHistorico = __DIR__ . '/historico.json';

if (!file_exists($arquivoEstoque)) {
    file_put_contents($arquivoEstoque, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
}

if (!file_exists($arquivoHistorico)) {
    file_put_contents($arquivoHistorico, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
}

$entrada = json_decode(file_get_contents('php://input'), true);
if (!is_array($entrada)) {
    http_response_code(400);
    echo 'Dados inválidos.';
    exit;
}

$item = trim((string)($entrada['item'] ?? ''));
$amp = trim((string)($entrada['amp'] ?? ''));
$tipo = trim((string)($entrada['tipo'] ?? ''));
$quantidade = (int)($entrada['quantidade'] ?? 0);

if ($item === '' || $amp === '' || !in_array($tipo, ['entrada', 'saida'], true) || $quantidade <= 0) {
    http_response_code(400);
    echo 'Preencha item, amperagem, movimento e uma quantidade válida.';
    exit;
}

$estoque = json_decode(file_get_contents($arquivoEstoque), true);
if (!is_array($estoque)) {
    $estoque = [];
}

if (!isset($estoque[$item]) || !is_array($estoque[$item])) {
    $estoque[$item] = [];
}

if (!isset($estoque[$item][$amp]) || !is_array($estoque[$item][$amp])) {
    $estoque[$item][$amp] = [
        'quantidade' => 0,
        'minimo' => 0,
    ];
}

$quantidadeAtual = (int)($estoque[$item][$amp]['quantidade'] ?? 0);

if ($tipo === 'entrada') {
    $estoque[$item][$amp]['quantidade'] = $quantidadeAtual + $quantidade;
} else {
    $estoque[$item][$amp]['quantidade'] = max(0, $quantidadeAtual - $quantidade);
}

$historico = json_decode(file_get_contents($arquivoHistorico), true);
if (!is_array($historico)) {
    $historico = [];
}

$historico[] = [
    'item' => $item,
    'amp' => $amp,
    'tipo' => $tipo,
    'quantidade' => $quantidade,
    'data' => date('d/m/Y H:i:s'),
    'timestamp' => time(),
    'fuso' => 'America/Sao_Paulo',
];

file_put_contents($arquivoEstoque, json_encode($estoque, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
file_put_contents($arquivoHistorico, json_encode($historico, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);

echo $tipo === 'entrada' ? 'Entrada registrada com sucesso.' : 'Saída registrada com sucesso.';
