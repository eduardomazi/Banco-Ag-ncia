// Aguarda o carregamento completo do documento HTML antes de executar o codigo.
$(document).ready(function () {
    // Preenche o campo de mes com o mes atual (formato AAAA-MM).
    $("#mes").val(new Date().toISOString().slice(0, 7));

    // Funcao que converte a data do banco (AAAA-MM-DD) para o formato brasileiro (DD/MM/AAAA).
    function formatarData(data) {
        if (!data) return "-";
        var partes = data.split("-");
        return partes[2] + "/" + partes[1] + "/" + partes[0];
    }

    // Seleciona a tabela pelo id tabela-viagens e inicializa o plugin DataTables.
    var tabela = $("#tabela-viagens").DataTable({
                // Mostra os botoes (B), a busca (f), o seletor de quantidade (l), a tabela (rt) e a paginacao (ip).
        dom: "Bflrtip",

        // Botoes de exportacao do relatorio.
        buttons: [
            {
                extend: "excelHtml5",
                text: "Exportar Excel",
                title: function () { return "Viagens - " + $("#mes").val(); },
                filename: function () { return "viagens-" + $("#mes").val(); }
            },
            {
                extend: "pdfHtml5",
                text: "Exportar PDF",
                orientation: "landscape",
                pageSize: "A4",
                title: function () { return "Viagens - " + $("#mes").val(); },
                filename: function () { return "viagens-" + $("#mes").val(); }
            },
            {
                extend: "print",
                text: "Imprimir",
                title: function () { return "Viagens - " + $("#mes").val(); }
            },
            {
                extend: "copyHtml5",
                text: "Copiar"
            }
        ],
        // Define o endereco PHP que sera chamado e os parametros enviados na URL.
        ajax: {
            url: "../controller/viagemcontroller.php",
            data: function (d) {
                // Envia a acao listar e o mes escolhido no campo de mes.
                d.acao = "listar";
                d.mes = $("#mes").val();
            }
        },

        // Define as colunas da tabela e quais campos do JSON cada coluna deve exibir.
        columns: [
            // Exibe o localizador da viagem.
            { data: "localizador" },

            // Exibe o destino da viagem.
            { data: "destino" },

            // Exibe a data de saida no formato brasileiro.
            { data: "data_saida", render: formatarData },

            // Exibe a data de retorno no formato brasileiro.
            { data: "data_retorno", render: formatarData },

            // Exibe o nome de quem reservou e paga a viagem.
            { data: "pagante" },

            // Exibe o telefone do pagante.
            { data: "telefone" },

            // Exibe a quantidade de passageiros da viagem.
            { data: "qtd_passageiros" },

            // Exibe os nomes de todos os passageiros, separados por virgula.
            { data: "passageiros" },

            // Exibe o valor total em reais.
            {
                data: "valor_total",
                render: function (valor) {
                    return Number(valor).toLocaleString("pt-BR", {
                        style: "currency",
                        currency: "BRL"
                    });
                }
            },

            // Exibe o status da viagem.
            { data: "status" }
        ],

        // Traduz os textos padroes do DataTables para portugues.
                    // Traduz os textos padroes do DataTables para portugues.
        language: {
            loadingRecords: "Carregando...",
            processing: "Processando...",
            emptyTable: "Nenhuma viagem encontrada neste mes.",
            zeroRecords: "Nenhum resultado encontrado.",
            search: "Pesquisar:",
            lengthMenu: "Mostrar _MENU_ registros por pagina",
            info: "Mostrando _START_ ate _END_ de _TOTAL_ registros",
            infoEmpty: "Mostrando 0 ate 0 de 0 registros",
            infoFiltered: "(filtrado de _MAX_ registros no total)",
            paginate: {
                first: "Primeira",
                previous: "Anterior",
                next: "Proxima",
                last: "Ultima"
            }
        }
    });

    // Quando o mes for trocado, recarrega a tabela com as viagens do novo mes.
    $("#mes").on("change", function () {
        tabela.ajax.reload();
    });
});