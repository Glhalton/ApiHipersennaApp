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
            "mensagem" => "Sem dados a exibir",
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