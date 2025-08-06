<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS");

try {

    include_once "../conexao.php";

    $rawInput = file_get_contents("php://input");
    $entrada = json_decode($rawInput, true);

    if ($entrada === null) {
        throw new Exception("Dados JSON invalidos: " . $rawInput);
    }

    $codigoFilial = $entrada["codFilial"] ?? "";
    $codigoProduto = $entrada["codProd"] ?? "";
    $dataVencimentoOrigin = $entrada["dataVencimento"] ?? "";
    $quantidade = $entrada["quantidade"] ?? "";
    $observacao = $entrada["observacao"] ?? "";
    $userId = $entrada["userId"] ?? "";
    $dataAtual = date('Y-m-d');

    date_default_timezone_set('America/Sao_Paulo');
    $dataVencimentoFormat = date('Y-m-d', strtotime($dataVencimentoOrigin));

    if (empty($codigoFilial) || empty($codigoProduto) || empty($dataVencimentoFormat) || empty($quantidade)) {
        http_response_code(400);
        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Preencha todos os campos obrigatorios."
        ]);
        exit;
    }

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

    $sql = "INSERT INTO validade(
            cod_filial,
            cod_produto,
            data_validade,
            quantidade,
            texto_obs,
            criado_em,
            colaborador_id
        ) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssss", $codigoFilial, $codigoProduto, $dataVencimentoFormat, $quantidade, $observacao, $dataAtual, $userId);

    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode([
            "sucesso" => true,
            "mensagem" => "Validade cadastrada com sucesso!",
        ]);
    } else {
        http_response_code(401);
        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Erro ao cadastrar validade: " . $stmt->error
        ]);
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    ob_end_clean();
    echo json_encode([
        'sucesso' => false,
        'mensagem' => $e->getMessage()
    ]);
}

?>