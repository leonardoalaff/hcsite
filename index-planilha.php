<?php
$arquivo = "estoque.json";
if (!file_exists($arquivo)) {
    file_put_contents($arquivo, json_encode([], JSON_PRETTY_PRINT));
}
$dados = json_decode(file_get_contents($arquivo), true);
if (!is_array($dados)) $dados = [];

$totalRegistros = 0;
$totalGeral = 0;
$itensCriticos = 0;
$amperagensAtivas = [];

foreach ($dados as $item => $amps) {
    foreach ($amps as $amp => $info) {
        $qtd = isset($info['quantidade']) ? (int) $info['quantidade'] : 0;
        $min = isset($info['minimo']) ? (int) $info['minimo'] : 0;

        $totalRegistros++;
        $totalGeral += $qtd;
        if ($qtd <= $min) {
            $itensCriticos++;
        }
        $amperagensAtivas[(string) $amp] = true;
    }
}

$amperagensAtivas = count($amperagensAtivas);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Estoque CNC</title>
<link rel="stylesheet" href="styleconsumiveis3.css">
</head>
<body class="cnc-dashboard">

<div class="page-shell">
    <button onclick="window.location.href='index.php'" class="btn-voltar">
        ⬅ Voltar
    </button>

    <section class="hero-panel">
        <div class="hero-copy">
            <span class="eyebrow">HC Indústria</span>
            <h1>Controle de Estoque CNC</h1>
            <p>Visual moderno, leitura rápida do estoque e movimentação em uma única tela.</p>
        </div>

        <div class="hero-stats">
            <article class="mini-card highlight-card">
                <span class="mini-label">Registros monitorados</span>
                <strong><?= $totalRegistros ?></strong>
            </article>
            <article class="mini-card">
                <span class="mini-label">Quantidade total</span>
                <strong><?= $totalGeral ?></strong>
            </article>
            <article class="mini-card">
                <span class="mini-label">Itens em alerta</span>
                <strong><?= $itensCriticos ?></strong>
            </article>
            <article class="mini-card">
                <span class="mini-label">Amperagens ativas</span>
                <strong><?= $amperagensAtivas ?></strong>
            </article>
        </div>
    </section>

    <section class="dashboard-grid">
        <article class="glass-card panel-wide">
            <div class="panel-header">
                <div>
                    <span class="panel-kicker">Visão geral</span>
                    <h2>Estoque atual</h2>
                </div>
                <span class="panel-badge"><?= $totalRegistros ?> linhas</span>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Amperes</th>
                            <th>Quantidade</th>
                            <th>Mínimo</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($dados as $item => $amps): ?>
                        <?php foreach ($amps as $amp => $info): ?>
                            <?php
                                $qtd = isset($info['quantidade']) ? (int) $info['quantidade'] : 0;
                                $min = isset($info['minimo']) ? (int) $info['minimo'] : 0;
                                $alerta = ($qtd <= $min);
                            ?>
                            <tr class="<?= $alerta ? 'alerta' : '' ?>">
                                <td><?= htmlspecialchars($item) ?></td>
                                <td><?= htmlspecialchars($amp) ?>A</td>
                                <td><?= $qtd ?></td>
                                <td><?= $min ?></td>
                                <td>
                                    <span class="status-pill <?= $alerta ? 'status-alerta' : 'status-ok' ?>">
                                        <?= $alerta ? 'Atenção' : 'Estável' ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </article>

        <article class="glass-card action-card">
            <div class="panel-header compact">
                <div>
                    <span class="panel-kicker">Operação</span>
                    <h2>Movimentar estoque</h2>
                </div>
            </div>

            <div class="form-grid">
                <label class="field">
                    <span>Item</span>
                    <select id="item">
                        <?php foreach ($dados as $item => $info): ?>
                            <option><?= htmlspecialchars($item) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label class="field">
                    <span>Amperagem</span>
                    <select id="amp">
                        <option value="50">50A</option>
                        <option value="130">130A</option>
                        <option value="200">200A</option>
                    </select>
                </label>

                <label class="field">
                    <span>Movimento</span>
                    <select id="tipo">
                        <option value="entrada">Entrada</option>
                        <option value="saida">Saída</option>
                    </select>
                </label>

                <label class="field field-full">
                    <span>Quantidade</span>
                    <input type="number" id="quantidade" placeholder="Informe a quantidade">
                </label>
            </div>

            <button class="primary-action" onclick="movimentar()">Aplicar movimentação</button>
        </article>

        <article class="glass-card chart-card">
            <div class="panel-header compact">
                <div>
                    <span class="panel-kicker">Análise</span>
                    <h2>Consumo por amperagem</h2>
                </div>
                <span class="panel-badge muted">Painel analítico</span>
            </div>

            <div class="chart-metrics">
                <article class="metric-chip">
                    <span>Total de saídas</span>
                    <strong id="metricTotalSaidas">0</strong>
                </article>
                <article class="metric-chip">
                    <span>Faixa mais usada</span>
                    <strong id="metricPicoAmp">--</strong>
                </article>
                <article class="metric-chip">
                    <span>Média por faixa</span>
                    <strong id="metricMediaAmp">0</strong>
                </article>
            </div>

            <div class="chart-wrap modern-chart-wrap">
                <canvas id="graficoAmp" width="400" height="220"></canvas>
            </div>

            <div class="chart-legend" aria-hidden="true">
                <span><i class="legend-dot bars"></i> Volume por amperagem</span>
                <span><i class="legend-dot line"></i> Tendência de consumo</span>
            </div>
        </article>

        <article class="glass-card history-card panel-wide">
            <div class="panel-header">
                <div>
                    <span class="panel-kicker">Rastreamento</span>
                    <h2>Histórico de movimentações</h2>
                </div>
                <span class="panel-badge muted">Atualização automática</span>
            </div>
            <div id="historico"></div>
        </article>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="scriptconsumiveis2.js"></script>

</body>
</html>
