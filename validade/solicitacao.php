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

    $itens = $entrada["itens"];
    $userId = $entrada["userId"] ?? "";
    $status = "Aberto";
    $conferenteId = $userId;
    date_default_timezone_set('America/Sao_Paulo');
    $dataAtual = date('Y-m-d');

    $sql = "INSERT INTO solicitacao_vistoria(
            cod_produto,
            cod_filial,
            status,
            analista_id,
            conferente_id,
            criado_em
        ) VALUES (?, ?, ?, ?, ?, ?)";
    $stmtInsert = $conn->prepare($sql);

    $sql = "SELECT * FROM produtos WHERE codauxiliar = ? ";
    $stmtSelect = $conn->prepare($sql);

    foreach ($itens as $item) {
        $codigoProduto = $item["codProd"] ?? "";
        $codigoFilial = $item["codFilial"] ?? "";

        if (empty($codigoFilial) || empty($codigoProduto)) {
            throw new Exception("Preencha todos os campos obrigatórios.");
        }


        $stmtSelect->bind_param("s", $codigoProduto);
        $stmtSelect->execute();
        $result = $stmtSelect->get_result();

        if ($result->num_rows == 0) {
            throw new Exception("Código de produto inexistente: $codigoProduto");
        }


        $stmtInsert->bind_param("ssssss", $codigoProduto, $codigoFilial, $status, $userId, $conferenteId, $dataAtual);

        if (!$stmtInsert->execute()) {
            throw new Exception("Erro ao cadastrar solicitação: " . $stmtInsert->error);
        }

    }

    $stmtSelect->close();
    $stmtInsert->close();
    $conn->close();


    http_response_code(200);
    echo json_encode([
        "sucesso" => true,
        "mensagem" => "Solicitação cadastrada com sucesso!",
    ]);


} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    ob_end_clean();
    echo json_encode([
        'sucesso' => false,
        'mensagem' => $e->getMessage()
    ]);
}

?>