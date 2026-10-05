<?php
// Model responsavel pelas consultas do dashboard e dos relatorios.
// Totais de vendas e relatorios usam a DATA DA VENDA.
// "Saidas no mes" e "proximas saidas" usam a DATA DE SAIDA.
class Relatorio {
    private $con;

    public function __construct($con) {
        $this->con = $con;
    }

    // Executa uma consulta com prepared statement e devolve o resultado.
    private function consultar($sql, $tipos = "", $params = []) {
        $stmt = $this->con->prepare($sql);
        if ($tipos !== "") {
            $stmt->bind_param($tipos, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result();
    }

    // Totais de um mes (AAAA-MM): vendas, faturamento, canceladas e saidas.
    public function resumo($mes) {
        $inicio = $mes . "-01";
        $fim = date("Y-m-d", strtotime($inicio . " +1 month"));

        // Vendas feitas no mes (pela data da venda).
        $sql = "SELECT COUNT(*) AS total_viagens,
                       COALESCE(SUM(CASE WHEN status <> 'cancelada' THEN valor_total ELSE 0 END), 0) AS faturamento,
                       COALESCE(SUM(status = 'cancelada'), 0) AS canceladas
                FROM viagens
                WHERE data_venda >= ? AND data_venda < ?";
        $resumo = $this->consultar($sql, "ss", [$inicio, $fim])->fetch_assoc();

        // Viagens que saem no mes (pela data de saida), sem as canceladas.
        $sql = "SELECT COUNT(*) AS saidas_mes
                FROM viagens
                WHERE data_saida >= ? AND data_saida < ?
                  AND status <> 'cancelada'";
        $saidas = $this->consultar($sql, "ss", [$inicio, $fim])->fetch_assoc();

        $resumo["saidas_mes"] = $saidas["saidas_mes"];
        return $resumo;
    }

     // Viagens que saem de hoje ate daqui a 15 dias, sem as canceladas.
    public function proximasSaidas() {
        $sql = "SELECT v.localizador, v.destino, v.data_saida,
                       pg.nome AS pagante, pg.telefone
                FROM viagens v
                JOIN pagantes pg ON pg.id = v.id_pagante
                WHERE v.data_saida BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 15 DAY)
                  AND v.status <> 'cancelada'
                ORDER BY v.data_saida";

        return $this->con->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    // Quantidade de vendas e faturamento de cada mes de um ano.
    public function faturamentoMensal($ano) {
        $sql = "SELECT MONTH(data_venda) AS mes, COUNT(*) AS qtd, SUM(valor_total) AS faturamento
                FROM viagens
                WHERE YEAR(data_venda) = ? AND status <> 'cancelada'
                GROUP BY MONTH(data_venda)
                ORDER BY mes";

        return $this->consultar($sql, "i", [$ano])->fetch_all(MYSQLI_ASSOC);
    }

    // Os 10 destinos mais vendidos no ano.
    public function destinos($ano) {
        $sql = "SELECT destino, COUNT(*) AS qtd, SUM(valor_total) AS faturamento
                FROM viagens
                WHERE YEAR(data_venda) = ? AND status <> 'cancelada'
                GROUP BY destino
                ORDER BY qtd DESC, faturamento DESC
                LIMIT 10";

        return $this->consultar($sql, "i", [$ano])->fetch_all(MYSQLI_ASSOC);
    }

    // Os 10 pagantes que mais gastaram no ano.
    public function clientes($ano) {
        $sql = "SELECT pg.nome, pg.telefone, COUNT(v.id) AS qtd, SUM(v.valor_total) AS total
                FROM viagens v
                JOIN pagantes pg ON pg.id = v.id_pagante
                WHERE YEAR(v.data_venda) = ? AND v.status <> 'cancelada'
                GROUP BY pg.id
                ORDER BY total DESC
                LIMIT 10";

        return $this->consultar($sql, "i", [$ano])->fetch_all(MYSQLI_ASSOC);
    }
}