<?php
// Inicia o bloco de codigo PHP.

// Cria a classe Usuario, que representa a model de usuario no MVC.
class Usuario
{
    // Declara uma propriedade privada para guardar a conexao com o banco.
    private $con;

    // Cria o metodo construtor, executado automaticamente ao criar um objeto Usuario.
    public function __construct($con)
    {
        // Guarda a conexao recebida dentro da propriedade da classe.
        $this->con = $con;
    }

    // Cria o metodo responsavel por listar todos os usuarios cadastrados.
    public function listar()
    {
        // Monta o comando SQL que busca os usuarios e o tipo de usuario.
        $sql = "
            SELECT
                usuario.id,
                usuario.nome,
                usuario.email,
                usuario.telefone,
                usuario.cpf,
                usuario.datanascimento,
                usuario.login,
                usuario.datacadastro,
                usuario.dataultimoacesso,
                tipousuario.descricao AS tipousuario
            FROM usuario
            LEFT JOIN tipousuario
                ON tipousuario.id = usuario.id_tipousuario
            ORDER BY usuario.id ASC
        ";

        // Executa o comando SQL no banco de dados.
        $resultado = $this->con->query($sql);

        // Cria um array vazio para armazenar os usuarios encontrados.
        $usuarios = [];

        // Verifica se a consulta falhou.
        if (!$resultado) {
            // Retorna o array vazio caso aconteca algum erro na consulta.
            return $usuarios;
        }

        // Percorre cada linha retornada pelo banco de dados.
        while ($linha = $resultado->fetch_assoc()) {
            // Adiciona a linha atual dentro do array de usuarios.
            $usuarios[] = $linha;
        }

        // Retorna o array completo com todos os usuarios encontrados.
        return $usuarios;
    }
}
?>
