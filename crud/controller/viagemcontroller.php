<?php
// Todas as respostas deste arquivo sao em JSON.
header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . "/../function/conexao.php";
require_once __DIR__ . "/../model/viagem.php";

// Responde com uma mensagem de erro em JSON e encerra.
function responderErro($codigo, $mensagem) {
    http_response_code($codigo);
    echo json_encode(["erro" => $mensagem]);
    exit;
}

// So aceita envio por POST.
function exigirPost() {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        responderErro(405, "Metodo nao permitido.");
    }
}

// Le o JSON enviado pelo JavaScript.
function lerJson() {
    $dados = json_decode(file_get_contents("php://input"), true);
    return is_array($dados) ? $dados : [];
}

// Confere os campos obrigatorios do cadastro e da edicao.
function camposObrigatoriosOk($d) {
    return !empty($d["pagante"]["nome"]) && !empty($d["pagante"]["cpf"])
        && !empty($d["viagem"]["localizador"]) && !empty($d["viagem"]["destino"])
        && !empty($d["viagem"]["data_venda"]) && !empty($d["viagem"]["data_saida"])
        && isset($d["viagem"]["valor_total"]) && $d["viagem"]["valor_total"] !== "";
}

// Explica qual valor esta repetido (erro 1062 do MySQL).
function mensagemDuplicado($e) {
    $msg = $e->getMessage();
    if (strpos($msg, "localizador") !== false) {
        return "Ja existe outra viagem com este localizador.";
    }
    if (strpos($msg, "pagantes") !== false) {
        return "Ja existe um pagante com este CPF.";
    }
    return "CPF repetido na lista de passageiros desta viagem.";
}

$acao = $_GET["acao"] ?? "";
$viagem = new Viagem($con);

try {
    // Lista as viagens de um mes (?acao=listar&mes=2026-10).
    if ($acao === "listar") {
        $mes = $_GET["mes"] ?? date("Y-m");

        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $mes)) {
            responderErro(400, "Mes invalido. Use o formato AAAA-MM.");
        }

        echo json_encode(["data" => $viagem->listar($mes)]);
        exit;
    }

    // Lista os passageiros de uma viagem (?acao=passageiros&id=1).
    if ($acao === "passageiros") {
        $id = (int) ($_GET["id"] ?? 0);
        echo json_encode(["data" => $viagem->listarPassageiros($id)]);
        exit;
    }

    // Busca uma viagem completa para a tela de edicao (?acao=buscar&id=1).
    if ($acao === "buscar") {
        $id = (int) ($_GET["id"] ?? 0);
        $dados = $viagem->buscar($id);

        if (!$dados) {
            responderErro(404, "Viagem nao encontrada.");
        }

        echo json_encode(["data" => $dados]);
        exit;
    }

    // Cadastra uma viagem nova.
    if ($acao === "cadastrar") {
        exigirPost();
        $dados = lerJson();

        if (!camposObrigatoriosOk($dados)) {
            responderErro(400, "Preencha os campos obrigatorios.");
        }

        echo json_encode(["sucesso" => true, "id" => $viagem->cadastrar($dados)]);
        exit;
    }

    // Salva as alteracoes de uma viagem existente.
    if ($acao === "editar") {
        exigirPost();
        $dados = lerJson();

        if (empty($dados["id"]) || !camposObrigatoriosOk($dados)) {
            responderErro(400, "Preencha os campos obrigatorios.");
        }

        if (!$viagem->editar($dados)) {
            responderErro(404, "Viagem nao encontrada.");
        }

        echo json_encode(["sucesso" => true]);
        exit;
    }

    // Exclui uma viagem e os passageiros dela.
    if ($acao === "excluir") {
        exigirPost();
        $id = (int) (lerJson()["id"] ?? 0);

        if ($id <= 0) {
            responderErro(400, "Id invalido.");
        }

        if (!$viagem->excluir($id)) {
            responderErro(404, "Viagem nao encontrada.");
        }

        echo json_encode(["sucesso" => true]);
        exit;
    }

    responderErro(400, "Acao invalida.");
} catch (mysqli_sql_exception $e) {
    // Os detalhes ficam no log do PHP (xampp/apache/logs/error.log).
    error_log($e->getMessage());

    if ($e->getCode() == 1062) {
        responderErro(409, mensagemDuplicado($e));
    }
    responderErro(500, "Erro ao acessar o banco de dados.");
} catch (Throwable $e) {
    error_log($e->getMessage());
    responderErro(500, "Erro inesperado ao processar o pedido.");
}