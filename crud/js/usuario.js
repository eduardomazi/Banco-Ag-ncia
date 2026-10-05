// Aguarda o carregamento completo do documento HTML antes de executar o codigo.
$(document).ready(function () {
    // Seleciona a tabela pelo id tabela-usuarios e inicializa o plugin DataTables.
    $("#tabela-usuarios").DataTable({
        // Define o endereco PHP que sera chamado para buscar os usuarios no banco.
        ajax: "../controller/usuariocontroller.php?acao=listar",

        // Define as colunas da tabela e quais campos do JSON cada coluna deve exibir.
        columns: [
            // Exibe o campo id retornado pelo banco de dados.
            { data: "id" },

            // Exibe o campo nome retornado pelo banco de dados.
            { data: "nome" },

            // Exibe o campo email retornado pelo banco de dados.
            { data: "email" },

            // Exibe o campo telefone retornado pelo banco de dados.
            { data: "telefone" },

            // Exibe o campo cpf retornado pelo banco de dados.
            { data: "cpf" },

            // Exibe o campo datanascimento retornado pelo banco de dados.
            { data: "datanascimento" },

            // Exibe o campo login retornado pelo banco de dados.
            { data: "login" },

            // Exibe o apelido tipousuario criado no SELECT da model.
            { data: "tipousuario" },

            // Exibe o campo datacadastro retornado pelo banco de dados.
            { data: "datacadastro" }
        ],

        // Traduz os textos padroes do DataTables para portugues.
        language: {
            // Texto exibido enquanto os dados estao sendo carregados.
            loadingRecords: "Carregando...",

            // Texto exibido enquanto a tabela esta processando os dados.
            processing: "Processando...",

            // Texto exibido quando nenhum registro e encontrado.
            emptyTable: "Nenhum usuario encontrado.",

            // Texto exibido quando a busca nao encontra resultados.
            zeroRecords: "Nenhum resultado encontrado.",

            // Texto do campo de pesquisa.
            search: "Pesquisar:",

            // Texto do seletor de quantidade de registros por pagina.
            lengthMenu: "Mostrar _MENU_ registros por pagina",

            // Texto informando quais registros estao sendo exibidos.
            info: "Mostrando _START_ ate _END_ de _TOTAL_ registros",

            // Texto exibido quando nao ha registros para mostrar.
            infoEmpty: "Mostrando 0 ate 0 de 0 registros",

            // Texto exibido quando a tabela esta filtrada.
            infoFiltered: "(filtrado de _MAX_ registros no total)",

            // Textos dos botoes de paginacao.
            paginate: {
                // Texto do botao para a primeira pagina.
                first: "Primeira",

                // Texto do botao para a pagina anterior.
                previous: "Anterior",

                // Texto do botao para a proxima pagina.
                next: "Proxima",

                // Texto do botao para a ultima pagina.
                last: "Ultima"
            }
        }
    });
});
