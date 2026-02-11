function adicionar() {
    fetch("movimentarmedicao.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            acao: "adicionar",
            tipo: document.getElementById("tipo").value,
            modelo: document.getElementById("modelo").value,
            codigo: document.getElementById("codigo").value,
            local: document.getElementById("local").value,
            quantidade: document.getElementById("quantidade").value
        })
    })
    .then(r => r.text())
    .then(alert)
    .then(() => location.reload());
}

function movimentar() {
    fetch("movimentarmedicao.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            acao: "movimentar",
            codigo: document.getElementById("codigoMov").value,
            status: document.getElementById("novoStatus").value,
            local: document.getElementById("novoLocal").value,
            quantidade: document.getElementById("novaQuantidade").value
        })
    })
    .then(r => r.text())
    .then(alert)
    .then(() => location.reload());
}

function carregarHistorico() {
    fetch("historicomedicao.json")
    .then(r => r.json())
    .then(dados => {

        let html = "<table border='1'><tr><th>Registro</th><th>Data</th></tr>";

        dados.reverse().forEach(h => {
            html += `<tr>
                <td>${h.mensagem}</td>
                <td>${h.data}</td>
            </tr>`;
        });

        html += "</table>";

        document.getElementById("historico").innerHTML = html;
    });
}

carregarHistorico();







function remover() {

    if (!confirm("Tem certeza que deseja remover este instrumento?")) {
        return;
    }

    fetch("movimentarmedicao.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            acao: "remover",
            codigo: document.getElementById("codigoRemover").value
        })
    })
    .then(r => r.text())
    .then(alert)
    .then(() => location.reload());
}
