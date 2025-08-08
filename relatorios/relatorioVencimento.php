<?php

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS");

try {

    include_once "../conexao.php";

    $rawInput = file_get_contents("php://input");
    $entrada = json_decode($rawInput, true);

    if ($entrada === null) {
        throw new Exception("Dados JSON inválidos: " . $rawInput);
    }

    $codigoFilial = $entrada["codFilial"] ?? "";
    $codigoProduto = $entrada["codProd"] ?? "";
    $quantidadeDias = $entrada["quantDias"] ?? "";
    $dataInicialOrigin = $entrada["dataInicial"] ?? "";
    $dataFinalOrigin = $entrada["dataFinal"] ?? "";

    date_default_timezone_set('America/Sao_Paulo');
    $dataInicialFormat = date('Y-m-d', strtotime($dataInicialOrigin));
    $dataFinalFormat = date('Y-m-d', strtotime($dataFinalOrigin));


    if (empty($codigoFilial) || empty($codigoProduto) || (empty($quantidadeDias) && empty($dataInicialOrigin) && empty($dataFinalOrigin))) {
        http_response_code(400);
        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Preencha todos os campos."
        ]);
        exit;
    }


    if (empty($quantidadeDias)) {
        if ($dataFinalFormat < $dataInicialFormat) {
            http_response_code(400);
            echo json_encode([
                "sucesso" => false,
                "mensagem" => "A data final não pode ser inferior a data inicial"
            ]);
            exit;
        }
        $dataInicial = $dataInicialFormat;
        $dataFinal = $dataFinalFormat;

    } else {

        $dataInicial = new DateTime();
        $intervalo = new DateInterval("P" . $quantidadeDias . "D");
        $dataFinal = $dataInicial->add($intervalo);
        $dataFinal = $dataFinal->format("Y-m-d");
        $dataInicial = $dataInicial->format("Y-m-d");

    }

    $sql = "    SELECT
        vp.id AS `id`,
        vp.cod_filial AS `filial`,
        vp.cod_produto AS `codprod`,
        p.descricao AS `desc`,
        p.codepto AS `dp`,
        vp.quantidade AS `quant`,
        p.codfornec AS `codfornec`,
        pc.codcomprador AS `codcomp`,
        DATE_FORMAT(vp.criado_em, '%d/%m/%Y') AS `data_insercao`,
        c.matricula AS `matricula_colaborador`,
        c.nome AS `nome_colaborador`,
        e.nome AS `nomecomprador`,
        DATE_FORMAT(vp.data_validade, '%d/%m/%Y') AS `data_validade`,
        DATEDIFF(vp.data_validade, CURDATE()) AS `dias_restantes`,
        pcest1.qtvendmes AS `g1`,
        pcest2.qtvendmes AS `g2`,
        pcest3.qtvendmes AS `g3`,
        pcest4.qtvendmes AS `g4`,
        pcest5.qtvendmes AS `g5`,
        pcest7.qtvendmes AS `g7`,
        vt.tratativa AS `tratativa`,
        vs.status AS `status`,
        vp.tratativa AS `idxtratativa`,
        vp.status AS `idxstatus`
    FROM
        validade_produto vp
    JOIN
        usuarios c ON c.id = vp.colaborador_id
    JOIN
        produtos p ON p.id = vp.cod_produto
    JOIN
        validade_tratativas vt ON vt.id = vp.tratativa
    JOIN
        validade_status vs ON vs.id = vp.status
    JOIN (
        SELECT codfilial, codprod, MIN(codcomprador) AS codcomprador
        FROM produto_comprador
        GROUP BY codfilial, codprod
    ) AS pc
        ON pc.codprod = vp.cod_produto 
        AND pc.codfilial = vp.cod_filial
    -- JOIN por filial específica para cada coluna:
    LEFT JOIN empregados e ON e.matricula = pc.codcomprador
    LEFT JOIN pcest AS pcest1 ON pcest1.codfilial = 1 AND pcest1.codprod = vp.cod_produto
    LEFT JOIN pcest AS pcest2 ON pcest2.codfilial = 2 AND pcest2.codprod = vp.cod_produto
    LEFT JOIN pcest AS pcest3 ON pcest3.codfilial = 3 AND pcest3.codprod = vp.cod_produto
    LEFT JOIN pcest AS pcest4 ON pcest4.codfilial = 4 AND pcest4.codprod = vp.cod_produto
    LEFT JOIN pcest AS pcest5 ON pcest5.codfilial = 5 AND pcest5.codprod = vp.cod_produto
    LEFT JOIN pcest AS pcest7 ON pcest7.codfilial = 7 AND pcest7.codprod = vp.cod_produto
    ";


    $sql = "SELECT * FROM produtos WHERE codauxiliar = ? ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $codigoProduto);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        http_response_code(400);
        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Codigo de produto inexistente."
        ]);
        exit;
    }

    $sql = "SELECT * FROM validade  WHERE cod_produto = ? AND cod_filial = ?  AND data_validade >= ? AND data_validade <= ?";
    $stmt = $conn->prepare($sql);

    $stmt->bind_param("ssss", $codigoProduto, $codigoFilial, $dataInicial, $dataFinal);
    $stmt->execute();
    $result = $stmt->get_result();
    $dados = [];

    while ($row = $result->fetch_assoc()) {
        $dados[] = $row;
    }

    if ($result->num_rows >= 1) {
        http_response_code(200);
        echo json_encode([
            "sucesso" => true,
            "mensagem" => "Dados consultados com sucesso",
            "dados" => $dados
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Sem dados de validade",
        ]);
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        "sucesso" => false,
        "mensagem" => $e->getMessage()
    ]);
}


?>