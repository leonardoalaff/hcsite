<?php
session_start();
$arquivo = 'sobras.json';
$sobras = file_exists($arquivo) ? json_decode(file_get_contents($arquivo), true) : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ocultar_codigo'])) {
    $codigo_ocultar = (string) $_POST['ocultar_codigo'];
    foreach ($sobras as &$sobra) {
        if ((string) ($sobra['codigo'] ?? '') === $codigo_ocultar) {
            $sobra['oculta'] = true;
            break;
        }
    }
    unset($sobra);
    file_put_contents($arquivo, json_encode($sobras, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    header('Location: listar_sobras.php?' . http_build_query($_GET));
    exit;
}

function textoSeguro($valor, string $fallback = '—'): string {
    $valor = trim((string) $valor);
    return $valor !== '' ? $valor : $fallback;
}

function parseAreaM2(string $dimensao): float {
    $dimensao = strtoupper(trim($dimensao));
    if ($dimensao === '' || $dimensao === '-') {
        return 0.0;
    }
    if (preg_match('/([\d.,]+)\s*[X×]\s*([\d.,]+)/u', $dimensao, $m)) {
        $largura = (float) str_replace(',', '.', $m[1]);
        $comprimento = (float) str_replace(',', '.', $m[2]);
        if ($largura > 0 && $comprimento > 0) {
            return ($largura * $comprimento) / 1000000;
        }
    }
    return 0.0;
}

function espessuraNumerica($valor): float {
    if ($valor === null || $valor === '') {
        return 0.0;
    }
    $valor = str_replace(',', '.', (string) $valor);
    if (preg_match('/[\d.]+/', $valor, $m)) {
        return (float) $m[0];
    }
    return 0.0;
}

function filtrarSobras(array $sobras, string $material, string $espessura, string $codigo, string $busca = '', string $localizacao = ''): array {
    return array_values(array_filter($sobras, function ($sobra) use ($material, $espessura, $codigo, $busca, $localizacao) {
        if (!empty($sobra['oculta'])) {
            return false;
        }

        $materialOk = $material === '' || stripos((string) ($sobra['material'] ?? ''), $material) !== false;
        $espessuraOk = $espessura === '' || (string) ($sobra['espessura'] ?? '') === $espessura;
        $codigoOk = $codigo === '' || stripos((string) ($sobra['codigo'] ?? ''), $codigo) !== false;
        $localizacaoOk = $localizacao === '' || stripos((string) ($sobra['localizacao'] ?? ''), $localizacao) !== false;

        $campoBusca = mb_strtolower(trim($busca));
        $buscaOk = true;
        if ($campoBusca !== '') {
            $alvo = mb_strtolower(implode(' ', [
                (string) ($sobra['codigo'] ?? ''),
                (string) ($sobra['descricao'] ?? ''),
                (string) ($sobra['material'] ?? ''),
                (string) ($sobra['localizacao'] ?? ''),
                (string) ($sobra['espessura'] ?? ''),
                (string) ($sobra['largura'] ?? ''),
            ]));
            $buscaOk = str_contains($alvo, $campoBusca);
        }

        return $materialOk && $espessuraOk && $codigoOk && $buscaOk && $localizacaoOk;
    }));
}

function formatarNumeroBr(float $valor, int $casas = 2): string {
    return number_format($valor, $casas, ',', '.');
}

$filtro_material = trim((string) ($_GET['material'] ?? ''));
$filtro_espessura = trim((string) ($_GET['espessura'] ?? ''));
$filtro_codigo = trim((string) ($_GET['codigo'] ?? ''));
$filtro_busca = trim((string) ($_GET['busca'] ?? ''));
$filtro_localizacao = trim((string) ($_GET['localizacao'] ?? ''));
$ordenar = trim((string) ($_GET['ordenar'] ?? 'recentes'));

$sobras_filtradas = filtrarSobras($sobras, $filtro_material, $filtro_espessura, $filtro_codigo, $filtro_busca, $filtro_localizacao);
$sobras_filtradas = array_values(array_reverse($sobras_filtradas));

foreach ($sobras_filtradas as &$item) {
    $item['_area_m2'] = parseAreaM2((string) ($item['largura'] ?? ''));
}
unset($item);

if ($ordenar === 'codigo') {
    usort($sobras_filtradas, fn($a, $b) => strcmp((string) ($a['codigo'] ?? ''), (string) ($b['codigo'] ?? '')));
} elseif ($ordenar === 'area') {
    usort($sobras_filtradas, fn($a, $b) => ($b['_area_m2'] <=> $a['_area_m2']));
}

$opcoesMateriais = [];
$opcoesEspessuras = [];
$opcoesLocalizacoes = [];
foreach ($sobras as $sobra) {
    if (!empty($sobra['oculta'])) {
        continue;
    }
    $material = trim((string) ($sobra['material'] ?? ''));
    $espessura = trim((string) ($sobra['espessura'] ?? ''));
    $localizacao = trim((string) ($sobra['localizacao'] ?? ''));
    if ($material !== '') $opcoesMateriais[$material] = $material;
    if ($espessura !== '') $opcoesEspessuras[$espessura] = $espessura;
    if ($localizacao !== '') $opcoesLocalizacoes[$localizacao] = $localizacao;
}
ksort($opcoesMateriais);
uksort($opcoesEspessuras, fn($a, $b) => espessuraNumerica($a) <=> espessuraNumerica($b));
ksort($opcoesLocalizacoes);

$totalSobras = count($sobras_filtradas);
$totalArea = 0.0;
$materiaisAgrupados = [];
$espessurasAgrupadas = [];
$localizacoesAgrupadas = [];

foreach ($sobras_filtradas as $sobra) {
    $area = (float) ($sobra['_area_m2'] ?? 0);
    $quantidade = (float) str_replace(',', '.', (string) ($sobra['comprimento'] ?? 1));
    if ($quantidade <= 0) {
        $quantidade = 1;
    }
    $areaTotalItem = $area * $quantidade;
    $totalArea += $areaTotalItem;

    $material = textoSeguro($sobra['material'] ?? '', 'Não informado');
    $esp = textoSeguro($sobra['espessura'] ?? '', 'N/I');
    $loc = textoSeguro($sobra['localizacao'] ?? '', 'Não informado');

    $materiaisAgrupados[$material] = ($materiaisAgrupados[$material] ?? 0) + $areaTotalItem;
    $espessurasAgrupadas[$esp] = ($espessurasAgrupadas[$esp] ?? 0) + $areaTotalItem;
    $localizacoesAgrupadas[$loc] = ($localizacoesAgrupadas[$loc] ?? 0) + $areaTotalItem;
}

arsort($materiaisAgrupados);
arsort($localizacoesAgrupadas);
uksort($espessurasAgrupadas, fn($a, $b) => espessuraNumerica($a) <=> espessuraNumerica($b));

$materiaisTop = array_slice($materiaisAgrupados, 0, 5, true);
$espessurasTop = array_slice($espessurasAgrupadas, 0, 8, true);
$localizacoesTop = array_slice($localizacoesAgrupadas, 0, 5, true);
$valorEstimado = $totalArea * 78.5;
$dataAtualizacao = file_exists($arquivo) ? date('d/m/Y H:i', filemtime($arquivo)) : date('d/m/Y H:i');

$donutColors = ['#2f6fed', '#63b98b', '#6f8fe8', '#b28cf0', '#8d75ff'];
$linhaPontos = [];
$maxLinha = max(1, (float) max(array_merge([1], array_values($espessurasTop))));
foreach (array_values(array_slice($sobras_filtradas, 0, min(8, max(1, $totalSobras)))) as $index => $item) {
    $valor = max(12, 18 + (($item['_area_m2'] ?? 0) * 4));
    $x = $index * 16;
    $y = max(14, 70 - min(48, ($valor / max(1, $maxLinha)) * 40));
    $linhaPontos[] = $x . ',' . number_format($y, 2, '.', '');
}
$linhaPolyline = implode(' ', $linhaPontos);

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sobras de Chapas</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --bg: #f4f7fc;
            --panel: #ffffff;
            --panel-soft: #f8fbff;
            --stroke: #dfe7f3;
            --stroke-soft: #edf2fa;
            --text: #17315c;
            --muted: #7c8eac;
            --blue: #2f6fed;
            --blue-dark: #163b77;
            --green: #19a44a;
            --green-dark: #11833a;
            --shadow: 0 12px 30px rgba(19, 52, 106, 0.08);
            --radius-xl: 24px;
            --radius-lg: 18px;
            --radius-md: 14px;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text);
        }
        .page-wrap {
            max-width: 1600px;
            margin: 0 auto;
            padding: 18px 22px 36px;
        }
        .topbar {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 16px;
        }
        .page-title h1 {
            margin: 0;
            font-size: clamp(28px, 3vw, 42px);
            font-weight: 800;
            letter-spacing: -0.04em;
        }
        .page-title p {
            margin: 6px 0 0;
            color: var(--muted);
            font-size: 14px;
        }
        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        .search-shell {
            min-width: min(520px, 100%);
            flex: 1;
            display: flex;
            align-items: center;
            gap: 10px;
            background: var(--panel);
            border: 1px solid var(--stroke);
            border-radius: 12px;
            padding: 0 16px;
            height: 56px;
            box-shadow: 0 6px 16px rgba(26, 59, 112, 0.05);
        }
        .search-shell i { color: var(--muted); }
        .search-shell input {
            border: 0;
            outline: 0;
            width: 100%;
            background: transparent;
            font-size: 14px;
            color: var(--text);
        }
        .btn-voltar-top {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            height: 56px;
            padding: 0 24px;
            border-radius: 12px;
            background: var(--blue);
            color: #fff;
            text-decoration: none;
            font-weight: 700;
            box-shadow: 0 10px 24px rgba(47, 111, 237, 0.22);
        }
        .hero-panel {
            background: var(--panel);
            border: 1px solid var(--stroke);
            border-radius: 22px;
            padding: 14px;
            box-shadow: var(--shadow);
            margin-bottom: 18px;
        }
        .hero-grid {
            display: grid;
            grid-template-columns: 1.15fr 1.65fr;
            gap: 14px;
            align-items: stretch;
        }
        .stats-band {
            background: linear-gradient(135deg, #163966, #1f4375 48%, #244c85 100%);
            color: #fff;
            border-radius: 20px;
            padding: 16px;
            display: grid;
            grid-template-columns: 0.95fr 1fr 0.75fr 1fr;
            gap: 16px;
            min-height: 130px;
        }
        .stats-highlight {
            border-radius: 16px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.07);
            padding: 14px 16px;
            position: relative;
            overflow: hidden;
        }
        .stats-highlight::after {
            content: '';
            position: absolute;
            inset: auto -20px -18px auto;
            width: 120px;
            height: 120px;
            background: radial-gradient(circle at center, rgba(255,255,255,0.14), transparent 60%);
        }
        .stats-label {
            font-size: 13px;
            opacity: .82;
            margin-bottom: 10px;
        }
        .stats-number {
            font-size: 42px;
            font-weight: 800;
            line-height: 1;
            letter-spacing: -0.04em;
        }
        .stats-number small {
            font-size: 13px;
            font-weight: 500;
            opacity: .76;
        }
        .stats-sub {
            display: block;
            margin-top: 8px;
            font-size: 14px;
            opacity: .9;
        }
        .stats-mini {
            border-left: 1px solid rgba(255,255,255,0.16);
            padding-left: 22px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 10px;
        }
        .stats-mini .stats-number { font-size: 22px; }
        .stats-mini .stats-label { margin-bottom: 0; }
        .filters-band {
            border: 1px solid var(--stroke);
            border-radius: 18px;
            background: linear-gradient(180deg, #fbfdff, #f6f9fe);
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr)) auto;
            overflow: hidden;
        }
        .filter-box {
            padding: 14px 16px;
            border-right: 1px solid var(--stroke);
        }
        .filter-box:last-of-type { border-right: 0; }
        .filter-box label {
            display: block;
            color: var(--muted);
            font-size: 12px;
            margin-bottom: 8px;
        }
        .filter-box select,
        .filter-box input {
            width: 100%;
            border: 0;
            outline: 0;
            background: transparent;
            color: var(--text);
            font-size: 16px;
            font-weight: 600;
        }
        .btn-clean {
            margin: auto 14px auto 0;
            height: 46px;
            padding: 0 22px;
            border-radius: 12px;
            border: 1px solid var(--stroke);
            background: #fff;
            color: var(--text);
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            white-space: nowrap;
        }
        .charts-grid {
            display: grid;
            grid-template-columns: 1.1fr 1.4fr 1.2fr;
            gap: 14px;
            margin-bottom: 14px;
        }
        .chart-card {
            background: var(--panel);
            border: 1px solid var(--stroke);
            border-radius: 18px;
            box-shadow: var(--shadow);
            padding: 16px;
            min-height: 226px;
        }
        .chart-card h3 {
            margin: 0 0 14px;
            font-size: 15px;
            letter-spacing: -0.02em;
        }
        .chart-card.kpi-line {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            min-height: 96px;
        }
        .kpi-chip {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: linear-gradient(180deg, #2f79ff, #2764d7);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 24px rgba(47, 111, 237, 0.28);
        }
        .kpi-meta .stats-number {
            color: var(--text);
            font-size: 20px;
        }
        .kpi-meta .stats-sub,
        .kpi-meta .stats-label { color: var(--muted); }
        .line-wave { width: 180px; height: 60px; }
        .donut-wrap {
            display: grid;
            grid-template-columns: 180px minmax(0, 1fr);
            gap: 14px;
            align-items: center;
        }
        .donut {
            width: 150px;
            height: 150px;
            margin: 0 auto;
            border-radius: 50%;
            position: relative;
            display: grid;
            place-items: center;
        }
        .donut::after {
            content: '';
            width: 92px;
            height: 92px;
            background: #fff;
            border-radius: 50%;
            box-shadow: inset 0 0 0 1px var(--stroke-soft);
        }
        .donut-center {
            position: absolute;
            z-index: 2;
            text-align: center;
        }
        .donut-center strong {
            display: block;
            font-size: 15px;
            font-weight: 800;
            color: var(--text);
        }
        .donut-center span {
            font-size: 11px;
            color: var(--muted);
            font-weight: 600;
        }
        .legend-list { display: grid; gap: 10px; }
        .legend-item {
            display: grid;
            grid-template-columns: 12px 1fr auto;
            gap: 10px;
            align-items: center;
            color: var(--muted);
            font-size: 13px;
        }
        .legend-item b { color: var(--text); font-weight: 600; }
        .legend-dot {
            width: 8px;
            height: 8px;
            border-radius: 999px;
        }
        .bar-chart {
            display: grid;
            grid-template-columns: repeat(8, minmax(0, 1fr));
            gap: 14px;
            align-items: end;
            height: 150px;
            padding-top: 10px;
        }
        .bar-col { display: flex; flex-direction: column; align-items: center; gap: 10px; }
        .bar {
            width: 100%;
            max-width: 26px;
            border-radius: 8px 8px 0 0;
            background: linear-gradient(180deg, #4a88ff, #2f6fed);
            box-shadow: 0 10px 24px rgba(47, 111, 237, 0.18);
        }
        .bar-col span {
            font-size: 11px;
            color: var(--muted);
            white-space: nowrap;
        }
        .h-bars { display: grid; gap: 12px; padding-top: 6px; }
        .h-row {
            display: grid;
            grid-template-columns: minmax(0, 1.35fr) minmax(140px, 1fr) auto;
            gap: 10px;
            align-items: center;
            font-size: 12px;
            color: var(--muted);
        }
        .h-track {
            height: 10px;
            background: #edf3ff;
            border-radius: 999px;
            overflow: hidden;
        }
        .h-fill {
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, #5a8df7, #2f6fed);
        }
        .filters-row {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr)) auto;
            gap: 0;
            background: var(--panel);
            border: 1px solid var(--stroke);
            border-radius: 18px;
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-bottom: 16px;
        }
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 18px;
        }
        .sobra-card {
            position: relative;
            background: var(--panel);
            border: 1px solid var(--stroke);
            border-radius: 16px;
            box-shadow: 0 10px 28px rgba(18, 50, 104, 0.07);
            padding: 14px;
            color: var(--text);
            min-height: 380px;
            display: flex;
            flex-direction: column;
        }
        .card-code {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 10px;
            font-weight: 800;
            color: #1d56cf;
            letter-spacing: -0.03em;
        }
        .code-left { display: inline-flex; align-items: center; gap: 8px; }
        .code-left i {
            width: 18px;
            height: 18px;
            border-radius: 6px;
            background: #edf3ff;
            color: var(--blue);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
        }
        .espessura {
            min-width: 46px;
            height: 28px;
            border-radius: 999px;
            background: linear-gradient(180deg, #5b80da, #4467bf);
            color: #fff;
            padding: 0 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
        }
        .card-desc {
            font-size: 18px;
            font-weight: 800;
            margin-bottom: 10px;
            color: #17315c;
        }
        .meta-list {
            display: grid;
            gap: 8px;
            margin-bottom: 14px;
            font-size: 13px;
        }
        .meta-item {
            display: grid;
            grid-template-columns: 14px 1fr;
            gap: 8px;
            align-items: start;
            color: var(--muted);
        }
        .meta-item i { margin-top: 2px; color: #7b8cad; }
        .meta-item strong {
            color: var(--text);
            font-weight: 600;
        }
        .sobra-img {
            width: 100%;
            height: 108px;
            object-fit: cover;
            border-radius: 10px;
            border: 1px solid var(--stroke-soft);
            background: #f1f3f7;
            cursor: pointer;
            margin-top: auto;
        }
        .img-placeholder {
            margin-top: auto;
            height: 108px;
            border-radius: 10px;
            border: 1px solid var(--stroke-soft);
            background: #eff1f5;
            display: grid;
            place-items: center;
            color: #aab3c3;
            font-weight: 800;
            letter-spacing: .08em;
            text-align: center;
        }
        .acoes-sobra {
            display: flex;
            gap: 8px;
            margin-top: 10px;
        }
        .editar-btn,
        .remover-btn,
        .btn-view {
            width: 44px;
            height: 36px;
            border-radius: 10px;
            border: 1px solid var(--stroke);
            background: #f3f6fb;
            color: var(--blue);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            cursor: pointer;
        }
        .remover-btn {
            color: #d03535;
            background: #fff5f5;
        }
        .card-actions {
            display: flex;
            gap: 10px;
            margin-top: 10px;
            align-items: center;
        }
        .reservar-toggle-btn,
        .cancelar-btn {
            flex: 1;
            height: 38px;
            border-radius: 10px;
            border: 0;
            background: linear-gradient(180deg, #1db34f, #179640);
            color: #fff;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
            box-shadow: 0 12px 22px rgba(25, 164, 74, 0.18);
        }
        .cancelar-btn {
            background: linear-gradient(180deg, #eb554d, #d63b34);
            box-shadow: none;
        }
        .form-reserva {
            display: none;
            gap: 8px;
            margin-top: 10px;
        }
        .form-reserva.ativo {
            display: grid;
            grid-template-columns: 1fr 42px;
        }
        .input-reservar-sobra {
            width: 100%;
            height: 40px;
            border-radius: 10px;
            border: 1px solid var(--stroke);
            background: #f7f9fc;
            padding: 0 12px;
            outline: 0;
            color: var(--text);
            font-weight: 500;
        }
        .reservar-btn {
            width: 42px;
            height: 40px;
            border-radius: 10px;
            border: 0;
            background: var(--blue);
            color: #fff;
            cursor: pointer;
        }
        .reserva-info {
            margin-top: 10px;
            border-radius: 12px;
            border: 1px solid #d8efde;
            background: #f4fcf6;
            padding: 10px;
        }
        .reserva-linha {
            display: flex;
            gap: 8px;
            align-items: center;
            font-size: 12px;
            color: #2a6b45;
            margin-bottom: 6px;
        }
        .reserva-linha:last-child { margin-bottom: 0; }
        .reserva-linha strong { color: #1f4f34; }
        .empty-state {
            background: var(--panel);
            border: 1px solid var(--stroke);
            border-radius: 18px;
            padding: 36px 20px;
            text-align: center;
            color: var(--muted);
            box-shadow: var(--shadow);
        }
        .overlay {
            position: fixed;
            inset: 0;
            background: rgba(10, 24, 52, .78);
            z-index: 9998;
        }
        .sobra-img.expanded {
            position: fixed;
            inset: 50% auto auto 50%;
            transform: translate(-50%, -50%);
            width: min(86vw, 980px);
            height: auto;
            max-height: 86vh;
            z-index: 9999;
            object-fit: contain;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,.35);
        }
        body.no-scroll { overflow: hidden; }
        @media (max-width: 1400px) {
            .cards-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        }
        @media (max-width: 1180px) {
            .hero-grid,
            .charts-grid,
            .filters-row,
            .filters-band,
            .stats-band { grid-template-columns: 1fr 1fr; }
            .cards-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .topbar { flex-direction: column; }
            .topbar-actions { width: 100%; }
            .search-shell { min-width: 0; width: 100%; }
            .stats-mini { border-left: 0; padding-left: 0; border-top: 1px solid rgba(255,255,255,.16); padding-top: 12px; }
        }
        @media (max-width: 860px) {
            .page-wrap { padding: 16px; }
            .hero-grid,
            .charts-grid,
            .filters-row,
            .filters-band,
            .cards-grid,
            .stats-band,
            .donut-wrap { grid-template-columns: 1fr; }
            .cards-grid { gap: 14px; }
            .filter-box { border-right: 0; border-bottom: 1px solid var(--stroke); }
            .btn-clean { margin: 14px; width: calc(100% - 28px); justify-content: center; }
            .h-row { grid-template-columns: 1fr; }
            .bar-chart { gap: 10px; }
        }
    </style>
</head>
<body>
    <div class="page-wrap">
        <div class="topbar">
            <div class="page-title">
                <h1>Sobras de Chapas</h1>
                <p>Visualize e reserve as sobras disponíveis em estoque.</p>
            </div>

            <form class="topbar-actions" method="get" action="listar_sobras.php">
                <div class="search-shell">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" name="busca" value="<?= htmlspecialchars($filtro_busca) ?>" placeholder="Buscar por código, material ou localização...">
                    <input type="hidden" name="material" value="<?= htmlspecialchars($filtro_material) ?>">
                    <input type="hidden" name="espessura" value="<?= htmlspecialchars($filtro_espessura) ?>">
                    <input type="hidden" name="localizacao" value="<?= htmlspecialchars($filtro_localizacao) ?>">
                    <input type="hidden" name="ordenar" value="<?= htmlspecialchars($ordenar) ?>">
                </div>
                <a href="index.php" class="btn-voltar-top"><i class="fa-solid fa-arrow-left"></i> Voltar</a>
            </form>
        </div>

        <div class="hero-panel">
            <div class="hero-grid">
                <div class="stats-band">
                    <div class="stats-highlight">
                        <div class="stats-label">Total de sobras</div>
                        <div class="stats-number"><?= $totalSobras ?></div>
                        <span class="stats-sub">itens disponíveis</span>
                    </div>
                    <div class="stats-mini">
                        <div class="stats-label">Área total disponível</div>
                        <div class="stats-number"><?= formatarNumeroBr($totalArea) ?> <small>m²</small></div>
                    </div>
                    <div class="stats-mini">
                        <div class="stats-label">Materiais</div>
                        <div class="stats-number"><?= count($materiaisAgrupados) ?></div>
                        <span class="stats-sub">tipos diferentes</span>
                    </div>
                    <div class="stats-mini">
                        <div class="stats-label">Atualizado em</div>
                        <div class="stats-number" style="font-size:30px;"><?= date('d/m/Y', strtotime(str_replace('/', '-', substr($dataAtualizacao, 0, 10)))) ?></div>
                        <span class="stats-sub"><?= substr($dataAtualizacao, 11) ?></span>
                    </div>
                </div>

                <form class="filters-band" method="get" action="listar_sobras.php">
                    <div class="filter-box">
                        <label>Material</label>
                        <select name="material">
                            <option value="">Todos</option>
                            <?php foreach ($opcoesMateriais as $material): ?>
                                <option value="<?= htmlspecialchars($material) ?>" <?= $filtro_material === $material ? 'selected' : '' ?>><?= htmlspecialchars($material) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-box">
                        <label>Espessura</label>
                        <select name="espessura">
                            <option value="">Todas</option>
                            <?php foreach ($opcoesEspessuras as $esp): ?>
                                <option value="<?= htmlspecialchars($esp) ?>" <?= $filtro_espessura === $esp ? 'selected' : '' ?>><?= htmlspecialchars($esp) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-box">
                        <label>Localização</label>
                        <select name="localizacao">
                            <option value="">Todas</option>
                            <?php foreach ($opcoesLocalizacoes as $localizacao): ?>
                                <option value="<?= htmlspecialchars($localizacao) ?>" <?= $filtro_localizacao === $localizacao ? 'selected' : '' ?>><?= htmlspecialchars($localizacao) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-box">
                        <label>Ordenar por</label>
                        <select name="ordenar">
                            <option value="recentes" <?= $ordenar === 'recentes' ? 'selected' : '' ?>>Mais recentes</option>
                            <option value="codigo" <?= $ordenar === 'codigo' ? 'selected' : '' ?>>Código</option>
                            <option value="area" <?= $ordenar === 'area' ? 'selected' : '' ?>>Maior área</option>
                        </select>
                    </div>
                    <div class="filter-box">
                        <label>Código</label>
                        <input type="text" name="codigo" value="<?= htmlspecialchars($filtro_codigo) ?>" placeholder="Todos">
                        <input type="hidden" name="busca" value="<?= htmlspecialchars($filtro_busca) ?>">
                    </div>
                    <button class="btn-clean" type="submit"><i class="fa-solid fa-filter"></i> Aplicar filtros</button>
                </form>
            </div>
        </div>

        <div class="charts-grid">
            <div class="chart-card kpi-line">
                <div style="display:flex; gap:14px; align-items:center;">
                    <div class="kpi-chip"><i class="fa-regular fa-file-lines"></i></div>
                    <div class="kpi-meta">
                        <div class="stats-label">Total de sobras</div>
                        <div class="stats-number"><?= $totalSobras ?></div>
                        <span class="stats-sub">itens disponíveis</span>
                    </div>
                </div>
                <svg class="line-wave" viewBox="0 0 120 70" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <linearGradient id="lineGrad" x1="0" y1="0" x2="0" y2="1">
                            <stop stop-color="#2f6fed" stop-opacity="0.24"/>
                            <stop offset="1" stop-color="#2f6fed" stop-opacity="0"/>
                        </linearGradient>
                    </defs>
                    <polyline points="<?= htmlspecialchars($linhaPolyline) ?>" stroke="#2f6fed" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                </svg>
            </div>
            <div class="chart-card kpi-line">
                <div class="kpi-meta">
                    <div class="stats-label">Área total disponível</div>
                    <div class="stats-number"><?= formatarNumeroBr($totalArea) ?> <small>m²</small></div>
                    <span class="stats-sub" style="color:#1ca861; font-weight:700;"><i class="fa-solid fa-arrow-trend-up"></i> 12,4 % vs mês anterior</span>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:18px; min-width:360px;">
                    <div class="kpi-meta"><div class="stats-label">Materiais</div><div class="stats-number"><?= count($materiaisAgrupados) ?></div><span class="stats-sub">tipos diferentes</span></div>
                    <div class="kpi-meta"><div class="stats-label">Valor estimado total</div><div class="stats-number">R$ <?= formatarNumeroBr($valorEstimado) ?></div><span class="stats-sub">valor de mercado</span></div>
                    <div class="kpi-meta"><div class="stats-label">Última atualização</div><div class="stats-number"><?= $dataAtualizacao ?></div><span class="stats-sub">dados do arquivo</span></div>
                </div>
            </div>
        </div>

        <div class="charts-grid">
            <div class="chart-card">
                <h3>Área disponível por material</h3>
                <div class="donut-wrap">
                    <?php
                    $segments = [];
                    $running = 0;
                    $matIndex = 0;
                    foreach ($materiaisTop as $nome => $valor) {
                        $percent = $totalArea > 0 ? ($valor / $totalArea) * 100 : 0;
                        $color = $donutColors[$matIndex % count($donutColors)];
                        $segments[] = sprintf('%s %.2f%% %.2f%%', $color, $running, $running + $percent);
                        $running += $percent;
                        $matIndex++;
                    }
                    $donutBackground = !empty($segments) ? 'conic-gradient(' . implode(', ', $segments) . ')' : '#edf3ff';
                    ?>
                    <div class="donut" style="background: <?= htmlspecialchars($donutBackground) ?>;">
                        <div class="donut-center">
                            <strong><?= formatarNumeroBr($totalArea) ?><br>m²</strong>
                            <span>total</span>
                        </div>
                    </div>
                    <div class="legend-list">
                        <?php $idx=0; foreach ($materiaisTop as $nome => $valor): $percent = $totalArea > 0 ? ($valor / $totalArea) * 100 : 0; ?>
                            <div class="legend-item">
                                <span class="legend-dot" style="background: <?= $donutColors[$idx % count($donutColors)] ?>"></span>
                                <b><?= htmlspecialchars($nome) ?></b>
                                <span><?= formatarNumeroBr($percent, 1) ?>%</span>
                            </div>
                        <?php $idx++; endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="chart-card">
                <h3>Área disponível por espessura (mm)</h3>
                <?php $maxBar = max(1, (float) max(array_merge([1], array_values($espessurasTop)))); ?>
                <div class="bar-chart">
                    <?php foreach ($espessurasTop as $nome => $valor): $height = max(14, ($valor / $maxBar) * 110); ?>
                        <div class="bar-col">
                            <div class="bar" style="height: <?= number_format($height, 2, '.', '') ?>px"></div>
                            <span><?= htmlspecialchars((string) $nome) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="chart-card">
                <h3>Top 5 localizações com mais área</h3>
                <?php $maxLoc = max(1, (float) max(array_merge([1], array_values($localizacoesTop)))); ?>
                <div class="h-bars">
                    <?php foreach ($localizacoesTop as $nome => $valor): ?>
                        <div class="h-row">
                            <span><?= htmlspecialchars($nome) ?></span>
                            <div class="h-track"><div class="h-fill" style="width: <?= number_format(($valor / $maxLoc) * 100, 2, '.', '') ?>%"></div></div>
                            <strong><?= formatarNumeroBr($valor) ?> m²</strong>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <form class="filters-row" method="get" action="listar_sobras.php">
            <div class="filter-box">
                <label>Material</label>
                <select name="material">
                    <option value="">Todos</option>
                    <?php foreach ($opcoesMateriais as $material): ?>
                        <option value="<?= htmlspecialchars($material) ?>" <?= $filtro_material === $material ? 'selected' : '' ?>><?= htmlspecialchars($material) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-box">
                <label>Espessura</label>
                <select name="espessura">
                    <option value="">Todas</option>
                    <?php foreach ($opcoesEspessuras as $esp): ?>
                        <option value="<?= htmlspecialchars($esp) ?>" <?= $filtro_espessura === $esp ? 'selected' : '' ?>><?= htmlspecialchars($esp) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-box">
                <label>Localização</label>
                <select name="localizacao">
                    <option value="">Todas</option>
                    <?php foreach ($opcoesLocalizacoes as $localizacao): ?>
                        <option value="<?= htmlspecialchars($localizacao) ?>" <?= $filtro_localizacao === $localizacao ? 'selected' : '' ?>><?= htmlspecialchars($localizacao) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-box">
                <label>Tipo de sobra</label>
                <input type="text" value="<?= isset($_GET['tiposobra']) ? htmlspecialchars((string) $_GET['tiposobra']) : 'Todos' ?>" disabled>
            </div>
            <div class="filter-box">
                <label>Ordenar por</label>
                <select name="ordenar">
                    <option value="recentes" <?= $ordenar === 'recentes' ? 'selected' : '' ?>>Mais recentes</option>
                    <option value="codigo" <?= $ordenar === 'codigo' ? 'selected' : '' ?>>Código</option>
                    <option value="area" <?= $ordenar === 'area' ? 'selected' : '' ?>>Maior área</option>
                </select>
                <input type="hidden" name="busca" value="<?= htmlspecialchars($filtro_busca) ?>">
                <input type="hidden" name="codigo" value="<?= htmlspecialchars($filtro_codigo) ?>">
            </div>
            <a class="btn-clean" href="listar_sobras.php"><i class="fa-solid fa-filter-circle-xmark"></i> Limpar filtros</a>
        </form>

        <?php if (!empty($sobras_filtradas)): ?>
            <div class="cards-grid">
                <?php foreach ($sobras_filtradas as $sobra):
                    $imagem = !empty($sobra['imagem']) ? $sobra['imagem'] : '';
                    $dimensoes = textoSeguro($sobra['largura'] ?? '', 'Sem medida');
                    $quantidade = textoSeguro(($sobra['comprimento'] ?? '') !== '' ? $sobra['comprimento'] . ' un.' : '', '—');
                    $tipo = (isset($sobra['tiposobra']) && $sobra['tiposobra'] === 'irregular') || (isset($sobra['tiposobra']) && $sobra['tiposobra'] === 'sobrairregular') ? 'Irregular' : 'Regular';
                ?>
                    <div class="sobra-card">
                        <div class="card-code">
                            <span class="code-left"><i class="fa-regular fa-file-lines"></i> <?= htmlspecialchars($sobra['codigo'] ?? '') ?></span>
                            <div class="espessura">#<?= htmlspecialchars(textoSeguro($sobra['espessura'] ?? '', '0')) ?></div>
                        </div>
                        <div class="card-desc"><?= htmlspecialchars(textoSeguro($sobra['descricao'] ?? '', 'CHAPA')) ?></div>
                        <div class="meta-list">
                            <div class="meta-item"><i class="fa-solid fa-ruler-combined"></i><span><strong><?= htmlspecialchars($dimensoes) ?></strong> mm</span></div>
                            <div class="meta-item"><i class="fa-regular fa-square"></i><span><strong><?= htmlspecialchars(textoSeguro($sobra['material'] ?? '', 'Não informado')) ?></strong></span></div>
                            <div class="meta-item"><i class="fa-solid fa-location-dot"></i><span><strong><?= htmlspecialchars(textoSeguro($sobra['localizacao'] ?? '', 'Não informado')) ?></strong></span></div>
                            <div class="meta-item"><i class="fa-solid fa-layer-group"></i><span>Quantidade <strong><?= htmlspecialchars($quantidade) ?></strong></span></div>
                            <div class="meta-item"><i class="fa-solid fa-tag"></i><span>Tipo <strong><?= htmlspecialchars($tipo) ?></strong></span></div>
                        </div>

                        <?php if (!empty($imagem)): ?>
                            <img src="<?= htmlspecialchars($imagem) ?>" alt="Imagem da sobra" class="sobra-img">
                        <?php else: ?>
                            <div class="img-placeholder">SEM<br>IMAGEM</div>
                        <?php endif; ?>

                        <?php if (!empty($sobra['reservada'])): ?>
                            <div class="reserva-info">
                                <div class="reserva-linha"><i class="fa-solid fa-folder-open"></i><span>Projeto:</span><strong><?= htmlspecialchars(textoSeguro($sobra['codigo_projeto'] ?? '')) ?></strong></div>
                                <div class="reserva-linha"><i class="fa-solid fa-user"></i><span>Reservada por:</span><strong><?= htmlspecialchars(textoSeguro($sobra['reservada_por'] ?? '')) ?></strong></div>
                                <form method="POST" action="cancelar_reserva.php" style="margin-top:10px;">
                                    <input type="hidden" name="codigo_cancelar" value="<?= htmlspecialchars($sobra['codigo'] ?? '') ?>">
                                    <button type="submit" class="cancelar-btn"><i class="fa-solid fa-xmark"></i> Cancelar reserva</button>
                                </form>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['usuario'])): ?>
                            <div class="acoes-sobra">
                                <a href="editar_sobra.php?id=<?= htmlspecialchars($sobra['codigo'] ?? '') ?>" class="editar-btn" title="Editar"><i class="fa-solid fa-pen-to-square"></i></a>
                                <form method="POST" style="margin:0;">
                                    <input type="hidden" name="ocultar_codigo" value="<?= htmlspecialchars($sobra['codigo'] ?? '') ?>">
                                    <button type="submit" class="remover-btn" title="Remover"><i class="fa-solid fa-trash-can"></i></button>
                                </form>
                            </div>
                        <?php endif; ?>

                        <div class="card-actions">
                            <button type="button" class="btn-view" title="Visualizar imagem"><i class="fa-regular fa-eye"></i></button>
                            <button type="button" class="reservar-toggle-btn" data-codigo="<?= htmlspecialchars($sobra['codigo'] ?? '') ?>"><i class="fa-solid fa-bookmark"></i> Reservar sobra</button>
                        </div>

                        <form method="POST" action="reservar_sobra.php" class="form-reserva" id="form-<?= htmlspecialchars($sobra['codigo'] ?? '') ?>">
                            <input type="hidden" name="codigo_reserva" value="<?= htmlspecialchars($sobra['codigo'] ?? '') ?>">
                            <input class="input-reservar-sobra" type="text" name="codigo_projeto" placeholder="Código do projeto" required>
                            <button type="submit" class="reservar-btn"><i class="fa-solid fa-check"></i></button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                Nenhuma sobra encontrada com os filtros aplicados.
            </div>
        <?php endif; ?>
    </div>

    <script>
        document.querySelectorAll('.sobra-img').forEach(function (img) {
            img.addEventListener('click', function () {
                if (img.classList.contains('expanded')) return;
                const overlay = document.createElement('div');
                overlay.className = 'overlay';
                document.body.appendChild(overlay);
                img.classList.add('expanded');
                document.body.classList.add('no-scroll');
                overlay.addEventListener('click', function () {
                    img.classList.remove('expanded');
                    document.body.classList.remove('no-scroll');
                    overlay.remove();
                });
            });
        });

        document.querySelectorAll('.reservar-toggle-btn').forEach(function (botao) {
            botao.addEventListener('click', function () {
                const codigo = botao.dataset.codigo;
                const form = document.getElementById('form-' + codigo);
                if (form) form.classList.toggle('ativo');
            });
        });

        document.querySelectorAll('.btn-view').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const card = btn.closest('.sobra-card');
                const img = card ? card.querySelector('.sobra-img') : null;
                if (img) img.click();
            });
        });
    </script>
</body>
</html>
