// Aguarda o carregamento completo do documento HTML antes de executar o codigo.
$(document).ready(function () {
    // Cria uma linha em branco para um passageiro acompanhante.
    function adicionarLinha() {
        $("#lista-passageiros").append(`
            <div class="linha-passageiro">
                <label>Nome <input type="text" class="p-nome"></label>
                <label>CPF <input type="text" class="p-cpf"></label>
                <label>Nascimento <input type="date" class="p-nascimento"></label>
                <button type="button" class="remover">Remover</button>
            </div>
        `);
    }

    // Botao que adiciona uma nova linha de passageiro.
    $("#add-passageiro").on("click", adicionarLinha);

    // Botao que remove a linha clicada.
    $("#lista-passageiros").on("click", ".remover", function () {
        $(this).closest(".linha-passageiro").remove();
    });

    // Quando o formulario for enviado, monta o JSON e manda para o controller.
    $("#form-viagem").on("submit", function (e) {
        e.preventDefault();

        // Junta os passageiros acompanhantes, ignorando linhas sem nome.
        var passageiros = [];
        $(".linha-passageiro").each(function () {
            var nome = $(this).find(".p-nome").val().trim();
            if (!nome) return;
            passageiros.push({
                nome: nome,
                cpf: $(this).find(".p-cpf").val(),
                data_nascimento: $(this).find(".p-nascimento").val()
            });
        });

        // Monta o objeto com todos os dados da tela.
        var dados = {
            pagante: {
                nome: $("#pg-nome").val().trim(),
                cpf: $("#pg-cpf").val().trim(),
                data_nascimento: $("#pg-nascimento").val(),
                telefone: $("#pg-telefone").val(),
                email: $("#pg-email").val(),
                endereco: $("#pg-endereco").val(),
                cep: $("#pg-cep").val()
            },
            viagem: {
                localizador: $("#v-localizador").val().trim(),
                data_venda: $("#v-venda").val(),
                destino: $("#v-destino").val().trim(),
                data_saida: $("#v-saida").val(),
                data_retorno: $("#v-retorno").val(),
                valor_total: $("#v-valor").val(),
                status: $("#v-status").val()
            },
            passageiros: passageiros
        };

        // Envia para o controller em formato JSON.
        $.ajax({
            url: "../controller/viagemcontroller.php?acao=cadastrar",
            method: "POST",
            contentType: "application/json",
            data: JSON.stringify(dados),
            dataType: "json"
        }).done(function () {
            alert("Viagem cadastrada com sucesso!");
            $("#form-viagem")[0].reset();
            $("#lista-passageiros").empty();
        }).fail(function (xhr) {
            var msg = xhr.responseJSON && xhr.responseJSON.erro
                ? xhr.responseJSON.erro
                : "Erro ao salvar. Veja o Console (F12).";
            alert(msg);
        });
    });
});