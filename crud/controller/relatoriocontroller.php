<?php
// Todas as respostas deste arquivo sao em JSON.
header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . "/../function/conexao.php";
require_once __DIR__ . "/../model/relatorio.php";

$acao = $_GET["acao"] ?? "";
$relatorio = new Relatorio($con);

// Totais do mes para os cartoes do dashboard.
if ($acao === "resumo") {
    $mes = $_GET["mes"] ?? date("Y-m");

    if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $mes)) {
        http_response_code(400);
        echo json_encode(["erro" => "Mes invalido. Use o formato AAAA-MM."]);
        exit;
    }

    echo json_encode(["data" => $relatorio->resumo($mes)]);
    exit;
}

// Viagens que saem nos proximos 7 dias.
if ($acao === "proximas") {
    echo json_encode(["data" => $relatorio->proximasSaidas()]);
    exit;
}

// Relatorios por ano: mensal, destinos e clientes.
if (in_array($acao, ["mensal", "destinos", "clientes"])) {
    $ano = $_GET["ano"] ?? date("Y");

    if (!preg_match('/^\d{4}$/', $ano)) {
        http_response_code(400);
        echo json_encode(["erro" => "Ano invalido."]);
        exit;
    }

    // Escolhe o metodo da model de acordo com a acao pedida.
    $metodos = [
        "mensal"   => "faturamentoMensal",
        "destinos" => "destinos",
        "clientes" => "clientes"
    ];
    $metodo = $metodos[$acao];

    echo json_encode(["data" => $relatorio->$metodo((int) $ano)]);
    exit;
}

http_response_code(400);
echo json_encode(["erro" => "Acao invalida."]);
?>