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
        let html = "<table border='1'><tr><th>Item</th><th>Amperes</th><th>Tipo</th><th>Qtd</th><th>Data</th></tr>";

        dados.reverse().forEach(h => {
            html += `<tr>
                <td>${h.item}</td>
                <td>${h.amp}A</td>
                <td>${h.tipo}</td>
                <td>${h.quantidade}</td>
                <td>${h.data}</td>
            </tr>`;
        });

        html += "</table>";
        document.getElementById("historico").innerHTML = html;
    });
}

carregarHistorico();

















function gerarGrafico() {
    fetch("historico.json")
    .then(r => r.json())
    .then(dados => {

        let consumo = { "50": 0, "130": 0, "200": 0 };

        dados.forEach(h => {
            if (h.tipo !== "saida") return;

            if (!h.amp) return; // IGNORA registros antigos sem amperagem

            let amp = String(h.amp);
            let qtd = parseInt(h.quantidade) || 0;

            if (consumo[amp] !== undefined) {
                consumo[amp] += qtd;
            }
        });

        console.log("DEBUG consumo:", consumo);

        const ctx = document.getElementById("graficoAmp");

        new Chart(ctx, {
            type: "bar",
            data: {
                labels: ["50A", "130A", "200A"],
                datasets: [{
                    label: "Consumo CNC",
                    data: [consumo["50"], consumo["130"], consumo["200"]],
                    backgroundColor: ["#3498db", "#f39c12", "#e74c3c"]
                }]
            }
        });
    });
}

gerarGrafico();
