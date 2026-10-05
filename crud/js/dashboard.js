// Aguarda o carregamento completo do documento HTML antes de executar o codigo.
$(document).ready(function () {
    var URL = "../controller/relatoriocontroller.php";

    // Comeca no mes atual.
    $("#mes").val(mesAtual());

    // Busca os totais do mes escolhido e preenche os cartoes.
    function carregarResumo() {
        $.getJSON(URL, { acao: "resumo", mes: $("#mes").val() })
             .done(function (r) {
      $("#card-viagens").text(r.data.total_viagens);
      $("#card-faturamento").text(moeda(r.data.faturamento));
      $("#card-saidas-mes").text(r.data.saidas_mes);   // <- linha nova
      $("#card-canceladas").text(r.data.canceladas);
  })
    }

    // Tabela com as viagens que saem nos proximos 7 dias.
    var tabela = $("#tabela-proximas").DataTable({
        ajax: URL + "?acao=proximas",
        columns: [
            { data: "localizador" },
            { data: "destino" },
            { data: "data_saida", render: formatarData },
            { data: "pagante" },
            { data: "telefone" }
        ],
        paging: false,
        searching: false,
        info: false,
        language: Object.assign({}, idiomaDataTables, {
            emptyTable: "Nenhuma saída nos próximos 7 dias."
        })
    });

    // Quando a tabela carrega, mostra a quantidade no cartao.
    tabela.on("xhr.dt", function (e, settings, json) {
        $("#card-proximas").text(json ? json.data.length : "-");
    });

    // Troca de mes recarrega apenas os totais.
    $("#mes").on("change", carregarResumo);

    carregarResumo();
});