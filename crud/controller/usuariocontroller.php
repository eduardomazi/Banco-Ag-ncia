<?php
// Inicia o bloco de codigo PHP.

// Define que todas as respostas deste arquivo serao enviadas em formato JSON.
header("Content-Type: application/json; charset=utf-8");

// Permite que este arquivo seja chamado por JavaScript a partir do mesmo projeto.
header("Access-Control-Allow-Origin: *");

// Importa o arquivo responsavel por criar a conexao com o banco de dados.
require_once __DIR__ . "/../function/conexao.php";

// Importa o arquivo da model Usuario, responsavel por consultar o banco.
require_once __DIR__ . "/../model/usuario.php";

// Pega a acao enviada pela URL, por exemplo: ?acao=listar.
$acao = $_GET["acao"] ?? "";

// Cria um objeto da model Usuario, enviando a conexao com o banco.
$usuario = new Usuario($con);

// Verifica se a acao solicitada foi listar.
if ($acao === "listar") {
    // Chama o metodo listar da model e guarda os usuarios retornados.
    $usuarios = $usuario->listar();

    // Envia os usuarios em JSON dentro da chave data, formato usado pelo DataTables.
    echo json_encode(["data" => $usuarios]);

    // Encerra a execucao para nao continuar lendo o restante do arquivo.
    exit;
}

// Define o codigo HTTP 400, indicando que a requisicao foi feita de forma incorreta.
http_response_code(400);

// Envia uma mensagem de erro em JSON quando a acao nao existe.
echo json_encode(["erro" => "Acao invalida."]);
?>
