// Formata um numero como dinheiro brasileiro (R$ 1.234,56).
function moeda(valor) {
    return Number(valor || 0).toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
}

// Converte a data do banco (AAAA-MM-DD) para DD/MM/AAAA.
function formatarData(data) {
    if (!data) return "-";
    var partes = data.split("-");
    return partes[2] + "/" + partes[1] + "/" + partes[0];
}

// Devolve o mes atual no formato AAAA-MM, usando o horario do computador.
function mesAtual() {
    var hoje = new Date();
    return hoje.getFullYear() + "-" + String(hoje.getMonth() + 1).padStart(2, "0");
}

// Textos do DataTables em portugues.
var idiomaDataTables = {
    loadingRecords: "Carregando...",
    processing: "Processando...",
    emptyTable: "Nenhum registro encontrado.",
    zeroRecords: "Nenhum resultado encontrado.",
    search: "Pesquisar:",
    lengthMenu: "Mostrar _MENU_ registros por pagina",
    info: "Mostrando _START_ ate _END_ de _TOTAL_ registros",
    infoEmpty: "Mostrando 0 ate 0 de 0 registros",
    infoFiltered: "(filtrado de _MAX_ registros no total)",
    paginate: { first: "Primeira", previous: "Anterior", next: "Proxima", last: "Ultima" }
};