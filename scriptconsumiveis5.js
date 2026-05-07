let graficoAmpInstance = null;
let historicoCompleto = [];
let filtroMesSelecionado = "";
let assinaturaFiltroMes = "";

const INTERVALO_ATUALIZACAO_MS = 2000;
const FUSO_HORARIO = "America/Sao_Paulo";

function doisDigitos(valor) {
    return String(valor).padStart(2, "0");
}

function obterPartesDataSaoPaulo(data = new Date()) {
    const partes = new Intl.DateTimeFormat("pt-BR", {
        timeZone: FUSO_HORARIO,
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
        second: "2-digit",
        hour12: false
    }).formatToParts(data);

    return partes.reduce((acc, parte) => {
        if (parte.type !== "literal") {
            acc[parte.type] = parte.value;
        }
        return acc;
    }, {});
}

function obterDataHoraSaoPaulo() {
    const partes = obterPartesDataSaoPaulo();
    return `${partes.day}/${partes.month}/${partes.year} ${partes.hour}:${partes.minute}:${partes.second}`;
}

function movimentar() {
    const item = document.getElementById("item").value;
    const amp = document.getElementById("amp").value;
    const tipo = document.getElementById("tipo").value;
    const quantidade = document.getElementById("quantidade").value;

    fetch("movimentar.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            item,
            amp,
            tipo,
            quantidade,
            data: obterDataHoraSaoPaulo(),
            fuso: FUSO_HORARIO
        })
    })
    .then(r => r.text())
    .then(alert)
    .then(() => location.reload());
}

function escaparHTML(valor) {
    return String(valor ?? "")
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function extrairComponentesData(dataTexto) {
    if (!dataTexto && dataTexto !== 0) return null;

    if (typeof dataTexto === "number") {
        const dataNumerica = new Date(dataTexto > 9999999999 ? dataTexto : dataTexto * 1000);
        if (!Number.isNaN(dataNumerica.getTime())) {
            const partes = obterPartesDataSaoPaulo(dataNumerica);
            return {
                dia: partes.day,
                mes: partes.month,
                ano: partes.year,
                hora: partes.hour,
                minuto: partes.minute,
                segundo: partes.second
            };
        }
    }

    const texto = String(dataTexto).trim();
    if (!texto) return null;

    // Formato brasileiro: 07/05/2026 19:32 ou 07/05/2026 19:32:15
    const br = texto.match(/^(\d{2})\/(\d{2})\/(\d{4})(?:\s+(\d{2}):(\d{2})(?::(\d{2}))?)?/);
    if (br) {
        return {
            dia: br[1],
            mes: br[2],
            ano: br[3],
            hora: br[4] || "00",
            minuto: br[5] || "00",
            segundo: br[6] || "00"
        };
    }

    // Formato SQL/ISO sem fuso: 2026-05-07 19:32:15 ou 2026-05-07T19:32
    const isoSemFuso = texto.match(/^(\d{4})-(\d{2})-(\d{2})(?:[T\s](\d{2}):(\d{2})(?::(\d{2}))?)?$/);
    if (isoSemFuso) {
        return {
            dia: isoSemFuso[3],
            mes: isoSemFuso[2],
            ano: isoSemFuso[1],
            hora: isoSemFuso[4] || "00",
            minuto: isoSemFuso[5] || "00",
            segundo: isoSemFuso[6] || "00"
        };
    }

    // ISO com fuso/Z: converte para America/Sao_Paulo antes de exibir e filtrar.
    const data = new Date(texto);
    if (!Number.isNaN(data.getTime())) {
        const partes = obterPartesDataSaoPaulo(data);
        return {
            dia: partes.day,
            mes: partes.month,
            ano: partes.year,
            hora: partes.hour,
            minuto: partes.minute,
            segundo: partes.second
        };
    }

    return null;
}

function obterDataHistorico(registro) {
    return registro?.data ?? registro?.data_local ?? registro?.criado_em ?? registro?.created_at ?? registro?.timestamp ?? "";
}

function formatarDataHistorico(registro) {
    const componentes = extrairComponentesData(obterDataHistorico(registro));
    if (!componentes) return String(obterDataHistorico(registro) || "--");

    return `${componentes.dia}/${componentes.mes}/${componentes.ano} ${componentes.hora}:${componentes.minuto}:${componentes.segundo}`;
}

function obterChaveMesRegistro(registro) {
    const componentes = extrairComponentesData(obterDataHistorico(registro));
    if (!componentes) return "";
    return `${componentes.ano}-${componentes.mes}`;
}

function gerarUltimosMeses(qtdMeses = 12) {
    const hoje = new Date();
    const partesHoje = obterPartesDataSaoPaulo(hoje);
    const meses = [];

    for (let i = 0; i < qtdMeses; i++) {
        const data = new Date(Number(partesHoje.year), Number(partesHoje.month) - 1 - i, 1);
        meses.push(`${data.getFullYear()}-${doisDigitos(data.getMonth() + 1)}`);
    }

    return meses;
}

function formatarMesAno(chaveMes) {
    if (!chaveMes) return "";

    const [ano, mes] = chaveMes.split("-");
    const data = new Date(Number(ano), Number(mes) - 1, 1);

    const texto = data.toLocaleDateString("pt-BR", {
        month: "long",
        year: "numeric"
    });

    return texto.charAt(0).toUpperCase() + texto.slice(1);
}

function obterHistoricoFiltrado() {
    if (!filtroMesSelecionado) {
        return historicoCompleto;
    }

    return historicoCompleto.filter(h => obterChaveMesRegistro(h) === filtroMesSelecionado);
}

function renderizarOpcoesFiltroMes() {
    const select = document.getElementById("filtroMesHistorico");
    if (!select) return;

    const mesesComHistorico = historicoCompleto
        .map(h => obterChaveMesRegistro(h))
        .filter(Boolean);

    const meses = [...new Set([
        ...mesesComHistorico,
        ...gerarUltimosMeses(12)
    ])].sort().reverse();

    const filtroAindaExiste = !filtroMesSelecionado || meses.includes(filtroMesSelecionado);
    if (!filtroAindaExiste) {
        filtroMesSelecionado = "";
    }

    const novaAssinatura = `${meses.join("|")}::${filtroMesSelecionado}`;

    // Evita reconstruir o select a cada 2 segundos enquanto o usuário está tentando abrir as opções.
    if (novaAssinatura === assinaturaFiltroMes && select.value === filtroMesSelecionado) {
        return;
    }

    if (document.activeElement === select && assinaturaFiltroMes) {
        return;
    }

    select.innerHTML = '<option value="">Todos os meses</option>';

    meses.forEach(chaveMes => {
        const option = document.createElement("option");
        option.value = chaveMes;
        option.textContent = formatarMesAno(chaveMes);
        option.selected = chaveMes === filtroMesSelecionado;
        select.appendChild(option);
    });

    assinaturaFiltroMes = novaAssinatura;
}

function renderizarHistorico() {
    const historico = document.getElementById("historico");
    if (!historico) return;

    const dadosFiltrados = obterHistoricoFiltrado();

    if (!dadosFiltrados.length) {
        const mensagemFiltro = filtroMesSelecionado
            ? `Não existem movimentações registradas em ${formatarMesAno(filtroMesSelecionado)}.`
            : "Novas movimentações aparecerão aqui automaticamente.";

        historico.innerHTML = `
            <div class="history-empty">
                <strong>Nenhuma movimentação encontrada.</strong>
                <span>${escaparHTML(mensagemFiltro)}</span>
            </div>
        `;
        return;
    }

    let html = "<table><thead><tr><th>Item</th><th>Amperes</th><th>Movimento</th><th>Qtd</th><th>Data</th></tr></thead><tbody>";

    dadosFiltrados.slice().reverse().forEach(h => {
        const tipo = h.tipo === "entrada" ? "Entrada" : "Saída";
        const amp = h.amp ? `${escaparHTML(h.amp)}A` : "--";

        html += `<tr>
            <td>${escaparHTML(h.item)}</td>
            <td>${amp}</td>
            <td>${tipo}</td>
            <td>${escaparHTML(h.quantidade)}</td>
            <td>${escaparHTML(formatarDataHistorico(h))}</td>
        </tr>`;
    });

    html += "</tbody></table>";
    historico.innerHTML = html;
}

function atualizarMetricas(consumo) {
    const valores = Object.values(consumo);
    const totalSaidas = valores.reduce((acc, valor) => acc + valor, 0);
    const media = valores.length ? Math.round(totalSaidas / valores.length) : 0;

    let picoAmp = "--";
    let picoValor = -1;

    Object.entries(consumo).forEach(([amp, valor]) => {
        if (valor > picoValor) {
            picoValor = valor;
            picoAmp = `${amp}A`;
        }
    });

    document.getElementById("metricTotalSaidas").textContent = totalSaidas;
    document.getElementById("metricPicoAmp").textContent = totalSaidas > 0 ? picoAmp : "--";
    document.getElementById("metricMediaAmp").textContent = media;
}

function gerarGrafico(dados = []) {
    const consumo = { "50": 0, "130": 0, "200": 0 };

    dados.forEach(h => {
        if (h.tipo !== "saida" || !h.amp) return;

        const amp = String(h.amp);
        const qtd = parseInt(h.quantidade, 10) || 0;

        if (consumo[amp] !== undefined) {
            consumo[amp] += qtd;
        }
    });

    atualizarMetricas(consumo);

    const canvas = document.getElementById("graficoAmp");
    if (!canvas) return;

    const ctx = canvas.getContext("2d");

    if (graficoAmpInstance) {
        graficoAmpInstance.destroy();
    }

    const gradienteBarras = ctx.createLinearGradient(0, 0, 0, 260);
    gradienteBarras.addColorStop(0, "rgba(13, 110, 253, 0.95)");
    gradienteBarras.addColorStop(1, "rgba(13, 110, 253, 0.25)");

    const gradienteLinha = ctx.createLinearGradient(0, 0, 320, 0);
    gradienteLinha.addColorStop(0, "rgba(15, 23, 42, 0.95)");
    gradienteLinha.addColorStop(1, "rgba(13, 110, 253, 0.95)");

    const areaBackgroundPlugin = {
        id: "areaBackgroundPlugin",
        beforeDraw(chart) {
            const { ctx: chartCtx, chartArea } = chart;
            if (!chartArea) return;

            chartCtx.save();
            const bg = chartCtx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
            bg.addColorStop(0, "rgba(13, 110, 253, 0.08)");
            bg.addColorStop(1, "rgba(255, 255, 255, 0.02)");
            chartCtx.fillStyle = bg;
            chartCtx.fillRect(chartArea.left, chartArea.top, chartArea.right - chartArea.left, chartArea.bottom - chartArea.top);
            chartCtx.restore();
        }
    };

    graficoAmpInstance = new Chart(ctx, {
        type: "bar",
        data: {
            labels: ["50A", "130A", "200A"],
            datasets: [
                {
                    type: "bar",
                    label: "Volume por amperagem",
                    data: [consumo["50"], consumo["130"], consumo["200"]],
                    backgroundColor: gradienteBarras,
                    borderColor: "rgba(13, 110, 253, 0.95)",
                    borderWidth: 1,
                    borderRadius: 18,
                    borderSkipped: false,
                    maxBarThickness: 42,
                    categoryPercentage: 0.62,
                    barPercentage: 0.78
                },
                {
                    type: "line",
                    label: "Tendência de consumo",
                    data: [consumo["50"], consumo["130"], consumo["200"]],
                    borderColor: gradienteLinha,
                    backgroundColor: "rgba(13, 110, 253, 0.12)",
                    borderWidth: 3,
                    pointRadius: 5,
                    pointHoverRadius: 6,
                    pointBackgroundColor: "#ffffff",
                    pointBorderColor: "#0d6efd",
                    pointBorderWidth: 3,
                    tension: 0.42,
                    fill: false
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: "index",
                intersect: false
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: "rgba(15, 23, 42, 0.94)",
                    titleColor: "#fff",
                    bodyColor: "#e2e8f0",
                    displayColors: false,
                    padding: 12,
                    cornerRadius: 14,
                    callbacks: {
                        label(context) {
                            return ` ${context.dataset.label}: ${context.raw}`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false,
                        drawBorder: false
                    },
                    border: {
                        display: false
                    },
                    ticks: {
                        color: "#475569",
                        font: {
                            size: 12,
                            weight: "700"
                        }
                    }
                },
                y: {
                    beginAtZero: true,
                    border: {
                        display: false
                    },
                    grid: {
                        color: "rgba(148, 163, 184, 0.18)",
                        drawBorder: false,
                        tickLength: 0
                    },
                    ticks: {
                        color: "#64748b",
                        precision: 0,
                        padding: 10,
                        font: {
                            weight: "600"
                        }
                    }
                }
            }
        },
        plugins: [areaBackgroundPlugin]
    });
}


function atualizarResumoEstoqueTabela() {
    const botoesQuantidade = Array.from(document.querySelectorAll(".editable-quantity"));
    const totalRegistros = botoesQuantidade.length;
    const quantidadeTotal = botoesQuantidade.reduce((total, botao) => {
        return total + (parseInt(botao.dataset.quantidadeAtual || botao.textContent, 10) || 0);
    }, 0);

    const itensCriticos = botoesQuantidade.filter(botao => {
        const quantidade = parseInt(botao.dataset.quantidadeAtual || botao.textContent, 10) || 0;
        const minimo = parseInt(botao.dataset.minimo, 10) || 0;
        return quantidade <= minimo;
    }).length;

    const amperagens = new Set(
        botoesQuantidade
            .map(botao => String(botao.dataset.amp || "").trim())
            .filter(Boolean)
    );

    const statRegistros = document.getElementById("statRegistrosMonitorados");
    const statQuantidade = document.getElementById("statQuantidadeTotal");
    const statCriticos = document.getElementById("statItensCriticos");
    const statAmperagens = document.getElementById("statAmperagensAtivas");

    if (statRegistros) statRegistros.textContent = totalRegistros;
    if (statQuantidade) statQuantidade.textContent = quantidadeTotal;
    if (statCriticos) statCriticos.textContent = itensCriticos;
    if (statAmperagens) statAmperagens.textContent = amperagens.size;
}

function atualizarStatusLinhaQuantidade(botaoQuantidade, quantidade) {
    const linha = botaoQuantidade.closest("tr");
    if (!linha) return;

    const minimo = parseInt(botaoQuantidade.dataset.minimo, 10) || 0;
    const emAlerta = quantidade <= minimo;
    const status = linha.querySelector(".status-pill");

    linha.classList.toggle("alerta", emAlerta);

    if (status) {
        status.classList.toggle("status-alerta", emAlerta);
        status.classList.toggle("status-ok", !emAlerta);
        status.textContent = emAlerta ? "Atenção" : "Estável";
    }
}

function restaurarBotaoQuantidade(input, botao, valor) {
    botao.textContent = valor;
    botao.dataset.quantidadeAtual = String(valor);
    input.replaceWith(botao);
    atualizarStatusLinhaQuantidade(botao, valor);
    atualizarResumoEstoqueTabela();
}

function ativarEdicaoQuantidade(botao) {
    if (!botao || botao.dataset.editando === "1") return;

    botao.dataset.editando = "1";

    const valorAtual = parseInt(botao.dataset.quantidadeAtual || botao.textContent, 10) || 0;
    const input = document.createElement("input");
    input.type = "number";
    input.min = "0";
    input.step = "1";
    input.inputMode = "numeric";
    input.value = valorAtual;
    input.className = "quantity-inline-input";
    input.setAttribute("aria-label", "Alterar quantidade do item");

    botao.replaceWith(input);
    input.focus();
    input.select();

    let finalizado = false;

    function cancelar() {
        if (finalizado) return;
        finalizado = true;
        delete botao.dataset.editando;
        input.replaceWith(botao);
    }

    function salvar() {
        if (finalizado) return;

        const novaQuantidade = parseInt(input.value, 10);

        if (!Number.isInteger(novaQuantidade) || novaQuantidade < 0) {
            alert("Informe uma quantidade válida, igual ou maior que zero.");
            input.focus();
            input.select();
            return;
        }

        if (novaQuantidade === valorAtual) {
            cancelar();
            return;
        }

        finalizado = true;
        input.disabled = true;

        fetch("atualizar_quantidade.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                item: botao.dataset.item,
                amp: botao.dataset.amp,
                quantidade: novaQuantidade
            })
        })
        .then(async resposta => {
            const texto = await resposta.text();
            let dados = null;

            try {
                dados = JSON.parse(texto);
            } catch (erro) {
                throw new Error(texto || "Não foi possível atualizar a quantidade.");
            }

            if (!resposta.ok || !dados.ok) {
                throw new Error(dados.mensagem || "Não foi possível atualizar a quantidade.");
            }

            delete botao.dataset.editando;
            restaurarBotaoQuantidade(input, botao, parseInt(dados.quantidade, 10) || 0);
        })
        .catch(erro => {
            alert(erro.message || "Não foi possível atualizar a quantidade.");
            delete botao.dataset.editando;
            input.disabled = false;
            input.replaceWith(botao);
        });
    }

    input.addEventListener("keydown", event => {
        if (event.key === "Enter") {
            event.preventDefault();
            salvar();
        }

        if (event.key === "Escape") {
            event.preventDefault();
            cancelar();
        }
    });

    input.addEventListener("blur", salvar);
}

function atualizarDashboard() {
    fetch(`historico.json?v=${Date.now()}`)
        .then(r => r.json())
        .then(dados => {
            historicoCompleto = Array.isArray(dados) ? dados : [];
            renderizarOpcoesFiltroMes();
            renderizarHistorico();
            gerarGrafico(obterHistoricoFiltrado());
        })
        .catch(() => {
            const historico = document.getElementById("historico");
            if (historico) {
                historico.innerHTML = "<p style='padding:16px'>Não foi possível carregar o histórico.</p>";
            }
            gerarGrafico([]);
        });
}

document.addEventListener("DOMContentLoaded", () => {
    const filtroMes = document.getElementById("filtroMesHistorico");
    const limparFiltro = document.getElementById("limparFiltroHistorico");

    if (filtroMes) {
        filtroMes.addEventListener("change", () => {
            filtroMesSelecionado = filtroMes.value;
            renderizarHistorico();
            gerarGrafico(obterHistoricoFiltrado());
        });
    }

    if (limparFiltro) {
        limparFiltro.addEventListener("click", () => {
            filtroMesSelecionado = "";
            if (filtroMes) filtroMes.value = "";
            renderizarHistorico();
            gerarGrafico(obterHistoricoFiltrado());
        });
    }

    document.addEventListener("click", event => {
        const botaoQuantidade = event.target.closest(".editable-quantity");
        if (!botaoQuantidade) return;
        ativarEdicaoQuantidade(botaoQuantidade);
    });

    atualizarResumoEstoqueTabela();
    atualizarDashboard();
    setInterval(atualizarDashboard, INTERVALO_ATUALIZACAO_MS);
});
