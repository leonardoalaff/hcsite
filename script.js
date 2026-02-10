function movimentar() {
    fetch("movimentar.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            item: document.getElementById("item").value,
            tipo: document.getElementById("tipo").value,
            quantidade: document.getElementById("quantidade").value
        })
    })
    .then(r => r.text())
    .then(alert)
    .then(() => location.reload());
}