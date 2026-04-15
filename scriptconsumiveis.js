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

carregarHistorico();

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

            const ctx = document.getElementById("graficoAmp");

            new Chart(ctx, {
                type: "bar",
                data: {
                    labels: ["50A", "130A", "200A"],
                    datasets: [{
                        label: "Saídas registradas",
                        data: [consumo["50"], consumo["130"], consumo["200"]],
                        backgroundColor: [
                            "rgba(13, 110, 253, 0.82)",
                            "rgba(59, 130, 246, 0.72)",
                            "rgba(15, 23, 42, 0.82)"
                        ],
                        borderRadius: 14,
                        borderSkipped: false,
                        maxBarThickness: 48
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: "#475569",
                                font: {
                                    weight: "600"
                                }
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: "rgba(148, 163, 184, 0.18)"
                            },
                            ticks: {
                                color: "#64748b",
                                precision: 0
                            }
                        }
                    }
                }
            });
        });
}

gerarGrafico();
