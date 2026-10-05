<?php
// Inicia o bloco de codigo PHP.

// Define o endereco do servidor onde o banco de dados esta hospedado.
$host = "localhost";

// Define o nome do usuario usado para acessar o banco de dados.
$usuario = "root";

// Define a senha usada para acessar o banco de dados.
$senha = "*Leoedudu01.";

// Define o nome do banco de dados que sera utilizado pelo sistema.
$banco_de_dados = "agencia_viagens";

// Desativa os relatorios automaticos de erro do mysqli.
mysqli_report(MYSQLI_REPORT_OFF);

// Cria uma nova conexao com o MySQL usando a classe mysqli.
// O simbolo @ evita que o PHP mostre um aviso tecnico na tela caso a conexao falhe.
// Assim, em vez de aparecer caminho do arquivo, IP ou usuario do banco,
// o sistema mostra apenas a mensagem JSON tratada no if abaixo.
$con = @new mysqli($host, $usuario, $senha, $banco_de_dados);

// Verifica se ocorreu algum erro ao tentar conectar com o banco de dados.
if ($con->connect_error) {
    // Define que a resposta enviada sera em formato JSON.
    header("Content-Type: application/json; charset=utf-8");

    // Define o codigo HTTP 500, indicando erro interno no servidor.
    http_response_code(500);

    // Envia uma mensagem de erro em JSON para quem chamou o arquivo.
    echo json_encode(["erro" => "Erro de conexao com o banco de dados."]);

    // Encerra a execucao do PHP, pois sem conexao nao e possivel continuar.
    exit;
}

// Define o conjunto de caracteres da conexao como utf8mb4 para suportar acentos.
$con->set_charset("utf8mb4");
?>
