<?php
$dados = file_get_contents("php://input");

if ($dados) {
    file_put_contents("estoque.json", $dados);
    echo "Estoque salvo com sucesso!";
} else {
    echo "Erro ao salvar estoque.";
}
