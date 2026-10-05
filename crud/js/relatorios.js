// Aguarda o carregamento completo do documento HTML antes de executar o codigo.
$(document).ready(function () {
    var URL = "../controller/relatoriocontroller.php";
    var meses = ["Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho",
                 "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro"];
    var graficos = {};

    // Comeca no ano atual.
    $("#ano").val(new Date().getFullYear());

    // Botoes de Excel e PDF, com o ano no titulo e no nome do arquivo.
    function botoes(nome, titulo) {
        return [
            {
                extend: "excelHtml5",
                text: "Exportar Excel",
                title: function () { return titulo + " - " + $("#ano").val(); },
                filename: function () { return nome + "-" + $("#ano").val(); }
            },
            {
                extend: "pdfHtml5",
                text: "Exportar PDF",
                title: function () { return titulo + " - " + $("#ano").val(); },
                filename: function () { return nome + "-" + $("#ano").val(); }
            }
        ];
    }

    // Cria uma tabela simples (sem paginacao nem busca) com os botoes de exportacao.
    function criarTabela(id, colunas, nome, titulo) {
        return $(id).DataTable({
            columns: colunas,
            dom: "Brt",
            paging: false,
            ordering: false,
            buttons: botoes(nome, titulo),
            language: idiomaDataTables
        });
    }

    // Desenha (ou redesenha) um grafico de barras.
    function grafico(id, rotulos, valores, titulo, formatar, horizontal) {
        if (graficos[id]) graficos[id].destroy();

        graficos[id] = new Chart(document.getElementById(id), {
            type: "bar",
            data: {
                labels: rotulos,
                datasets: [{ label: titulo, data: valores, backgroundColor: "#4a90d9" }]
            },
            options: {
                indexAxis: horizontal ? "y" : "x",
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: function (ctx) { return formatar(ctx.raw); } } }
                }
            }
        });
    }

    var tMensal = criarTabela("#t-mensal", [
        { data: "mes" },
        { data: "qtd" },
        { data: "faturamento", render: moeda }
    ], "faturamento-mensal", "Faturamento por mês");

    var tDestinos = criarTabela("#t-destinos", [
        { data: "destino" },
        { data: "qtd" },
        { data: "faturamento", render: moeda }
    ], "destinos-mais-vendidos", "Destinos mais vendidos");

    var tClientes = criarTabela("#t-clientes", [
        { data: "nome" },
        { data: "telefone" },
        { data: "qtd" },
        { data: "total", render: moeda }
    ], "melhores-clientes", "Melhores clientes");

    // Mostra um aviso se alguma consulta falhar.
    function erro() {
        alert("Erro ao carregar o relatório. Veja o Console (F12).");
    }

    // Busca os tres relatorios do ano escolhido.
    function carregar() {
        var ano = $("#ano").val();

        // Faturamento por mes: preenche os 12 meses, mesmo os sem viagens.
        $.getJSON(URL, { acao: "mensal", ano: ano }).done(function (r) {
            var linhas = [], valores = [], total = 0;

            for (var i = 1; i <= 12; i++) {
                var achou = r.data.find(function (x) { return Number(x.mes) === i; });
                var qtd = achou ? Number(achou.qtd) : 0;
                var fat = achou ? Number(achou.faturamento) : 0;
                total += fat;
                valores.push(fat);
                linhas.push({ mes: meses[i - 1], qtd: qtd, faturamento: fat });
            }

            tMensal.clear().rows.add(linhas).draw();
            grafico("g-mensal", meses.map(function (m) { return m.slice(0, 3); }),
                    valores, "Faturamento", moeda, false);
            $("#total-ano").text("Faturamento de " + ano + ": " + moeda(total));
        }).fail(erro);

        // Destinos mais vendidos.
        $.getJSON(URL, { acao: "destinos", ano: ano }).done(function (r) {
            tDestinos.clear().rows.add(r.data).draw();
            grafico("g-destinos",
                    r.data.map(function (d) { return d.destino; }),
                    r.data.map(function (d) { return Number(d.qtd); }),
                    "Viagens", function (v) { return v + " viagem(ns)"; }, true);
        }).fail(erro);

        // Melhores clientes.
        $.getJSON(URL, { acao: "clientes", ano: ano }).done(function (r) {
            tClientes.clear().rows.add(r.data).draw();
            grafico("g-clientes",
                    r.data.map(function (c) { return c.nome; }),
                    r.data.map(function (c) { return Number(c.total); }),
                    "Total gasto", moeda, true);
        }).fail(erro);
    }

    // Trocar o ano recarrega os relatorios.
    $("#ano").on("change", carregar);

    carregar();
});