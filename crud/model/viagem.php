<?php
// Model responsavel por consultar e gravar as viagens no banco.
class Viagem {
    // Guarda a conexao com o banco.
    private $con;

    // Recebe a conexao ao criar o objeto.
    public function __construct($con) {
        $this->con = $con;
    }

    // Lista as viagens de um mes, no formato AAAA-MM (ex: 2026-10).
    public function listar($mes) {
        // Calcula o primeiro dia do mes e o primeiro dia do mes seguinte.
        $inicio = $mes . "-01";
        $fim = date("Y-m-d", strtotime($inicio . " +1 month"));

        // Busca a viagem, o pagante e os passageiros juntos em uma linha so.
        $sql = "SELECT v.id, v.localizador, v.destino, v.data_saida, v.data_retorno,
                       v.valor_total, v.status,
                       pg.nome AS pagante, pg.telefone,
                       COUNT(p.id) AS qtd_passageiros,
                       GROUP_CONCAT(p.nome SEPARATOR ', ') AS passageiros
                FROM viagens v
                JOIN pagantes pg ON pg.id = v.id_pagante
                LEFT JOIN passageiros p ON p.id_viagem = v.id
                WHERE v.data_saida >= ? AND v.data_saida < ?
                GROUP BY v.id
                ORDER BY v.data_saida";

        // Usa prepared statement para evitar SQL injection.
        $stmt = $this->con->prepare($sql);
        $stmt->bind_param("ss", $inicio, $fim);
        $stmt->execute();

        // Devolve todas as linhas como lista de arrays associativos.
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Lista os passageiros de uma viagem, com CPF e data de nascimento.
    public function listarPassageiros($idViagem) {
        $sql = "SELECT nome, cpf, data_nascimento
                FROM passageiros
                WHERE id_viagem = ?
                ORDER BY nome";

        $stmt = $this->con->prepare($sql);
        $stmt->bind_param("i", $idViagem);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Troca texto vazio por NULL, para o banco aceitar campos opcionais.
    private function vazioParaNull($valor) {
        $valor = trim($valor ?? "");
        return $valor === "" ? null : $valor;
    }

    // Cadastra pagante, viagem e passageiros. Se algo falhar, nada e gravado.
    public function cadastrar($d) {
        // Faz o mysqli lancar excecoes quando houver erro no SQL.
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        $this->con->begin_transaction();

        try {
            $pg = $d["pagante"];
            $v = $d["viagem"];

            // Procura o pagante pelo CPF. Se ja existe, reaproveita o cadastro.
            $stmt = $this->con->prepare("SELECT id FROM pagantes WHERE cpf = ?");
            $stmt->bind_param("s", $pg["cpf"]);
            $stmt->execute();
            $existente = $stmt->get_result()->fetch_assoc();

            if ($existente) {
                $idPagante = $existente["id"];
            } else {
                $stmt = $this->con->prepare(
                    "INSERT INTO pagantes (nome, cpf, data_nascimento, email, telefone, endereco, cep)
                     VALUES (?, ?, ?, ?, ?, ?, ?)"
                );
                $nasc = $this->vazioParaNull($pg["data_nascimento"] ?? "");
                $email = $this->vazioParaNull($pg["email"] ?? "");
                $tel = $this->vazioParaNull($pg["telefone"] ?? "");
                $end = $this->vazioParaNull($pg["endereco"] ?? "");
                $cep = $this->vazioParaNull($pg["cep"] ?? "");
                $stmt->bind_param("sssssss", $pg["nome"], $pg["cpf"], $nasc, $email, $tel, $end, $cep);
                $stmt->execute();
                $idPagante = $this->con->insert_id;
            }

            // Grava a viagem com a data da venda informada na tela.
            $stmt = $this->con->prepare(
                "INSERT INTO viagens (localizador, data_venda, id_pagante, destino, data_saida, data_retorno, valor_total, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $dataVenda = $v["data_venda"];
            $retorno = $this->vazioParaNull($v["data_retorno"] ?? "");
            $valor = (float) $v["valor_total"];
            $status = $v["status"] ?? "confirmada";
            $stmt->bind_param("ssisssds", $v["localizador"], $dataVenda, $idPagante, $v["destino"],
                              $v["data_saida"], $retorno, $valor, $status);
            $stmt->execute();
            $idViagem = $this->con->insert_id;

            // Grava os passageiros acompanhantes.
            $stmt = $this->con->prepare(
                "INSERT INTO passageiros (id_viagem, nome, cpf, data_nascimento) VALUES (?, ?, ?, ?)"
            );
            foreach ($d["passageiros"] ?? [] as $p) {
                $cpf = $this->vazioParaNull($p["cpf"] ?? "");
                $nasc = $this->vazioParaNull($p["data_nascimento"] ?? "");
                $stmt->bind_param("isss", $idViagem, $p["nome"], $cpf, $nasc);
                $stmt->execute();
            }

            // Tudo certo: confirma a gravacao.
            $this->con->commit();
            return $idViagem;
        } catch (Throwable $e) {
            // Deu erro: desfaz tudo o que foi gravado nesta chamada.
            $this->con->rollback();
            throw $e;
        }
    }
}