let graficoAmpInstance = null;

function movimentar() {
    fetch("movimentar.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            item: document.getElementById("item").value,
            amp: document.getElementById("amp").value,
            tipo: document.getElementById("tipo").value,
            quantidade: document.getElementById("quantidade").value
        })
    })
    .then(r => r.text())
    .then(alert)
    .then(() => location.reload());
}

function carregarHistorico() {
    fetch("historico.json")
        .then(r => r.json())
        .then(dados => {
            let html = "<table><thead><tr><th>Item</th><th>Amperes</th><th>Movimento</th><th>Qtd</th><th>Data</th></tr></thead><tbody>";

            dados.reverse().forEach(h => {
                const tipo = h.tipo === "entrada" ? "Entrada" : "Saída";
                html += `<tr>
                    <td>${h.item}</td>
                    <td>${h.amp}A</td>
                    <td>${tipo}</td>
                    <td>${h.quantidade}</td>
                    <td>${h.data}</td>
                </tr>`;
            });

            html += "</tbody></table>";
            document.getElementById("historico").innerHTML = html;
        })
        .catch(() => {
            document.getElementById("historico").innerHTML = "<p style='padding:16px'>Não foi possível carregar o histórico.</p>";
        });
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

function gerarGrafico() {
    fetch("historico.json")
        .then(r => r.json())
        .then(dados => {
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
        })
        .catch(() => {
            const fallback = document.getElementById("graficoAmp");
            if (fallback) {
                const contexto = fallback.getContext("2d");
                contexto.clearRect(0, 0, fallback.width, fallback.height);
            }
        });
}

carregarHistorico();
gerarGrafico();
