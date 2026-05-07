<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

$arquivoEstoque = __DIR__ . '/estoque.json';

function responder(bool $ok, string $mensagem, int $status = 200, array $extra = []): void
{
    http_response_code($status);
    echo json_encode(array_merge([
        'ok' => $ok,
        'mensagem' => $mensagem,
    ], $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

if (!file_exists($arquivoEstoque)) {
    file_put_contents($arquivoEstoque, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
}

$entrada = json_decode(file_get_contents('php://input'), true);

if (!is_array($entrada)) {
    responder(false, 'Dados inválidos.', 400);
}

$item = trim((string)($entrada['item'] ?? ''));
$amp = trim((string)($entrada['amp'] ?? ''));
$quantidade = filter_var($entrada['quantidade'] ?? null, FILTER_VALIDATE_INT, [
    'options' => [
        'min_range' => 0,
    ],
]);

if ($item === '' || $amp === '' || $quantidade === false) {
    responder(false, 'Informe item, amperagem e uma quantidade válida.', 400);
}

$estoque = json_decode(file_get_contents($arquivoEstoque), true);

if (!is_array($estoque)) {
    $estoque = [];
}

if (!isset($estoque[$item]) || !is_array($estoque[$item]) || !isset($estoque[$item][$amp]) || !is_array($estoque[$item][$amp])) {
    responder(false, 'Item não encontrado no estoque.', 404);
}

$estoque[$item][$amp]['quantidade'] = (int) $quantidade;

if (!isset($estoque[$item][$amp]['minimo'])) {
    $estoque[$item][$amp]['minimo'] = 0;
}

$salvo = file_put_contents($arquivoEstoque, json_encode($estoque, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);

if ($salvo === false) {
    responder(false, 'Não foi possível salvar a nova quantidade.', 500);
}

responder(true, 'Quantidade atualizada com sucesso.', 200, [
    'item' => $item,
    'amp' => $amp,
    'quantidade' => (int) $quantidade,
    'minimo' => (int)($estoque[$item][$amp]['minimo'] ?? 0),
]);
