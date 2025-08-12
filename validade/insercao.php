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

    $userId = $entrada["userId"] ?? "";
    $itens = $entrada["itens"];

    date_default_timezone_set('America/Sao_Paulo');
    $dataAtual = date('Y-m-d');

    $sql = "INSERT INTO validade(
            cod_filial,
            cod_produto,
            data_validade,
            quantidade,
            texto_obs,
            criado_em,
            colaborador_id
        ) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmtInsert = $conn->prepare($sql);

    $sql = "SELECT * FROM produtos WHERE codauxiliar = ? ";
    $stmtSelect = $conn->prepare($sql);

    foreach ($itens as $item) {
        $codigoProduto = $item["codProd"] ?? "";
        $codigoFilial = $item["codFilial"] ?? "";
        $dataVencimentoOrigin = $item["dataVencimento"] ?? "";
        $dataVencimentoFormat = date('Y-m-d', strtotime($dataVencimentoOrigin));
        $quantidade = $item["quantidade"] ?? "";
        $observacao = $item["observacao"] ?? "";


        if (empty($codigoFilial) || empty($codigoProduto) || empty($dataVencimentoFormat) || empty($quantidade)) {
            throw new Exception("Preencha todos os campos obrigatórios.");
        }


        $stmtSelect->bind_param("s", $codigoProduto);
        $stmtSelect->execute();
        $result = $stmtSelect->get_result();

        if ($result->num_rows == 0) {
            throw new Exception("Código de produto inexistente: $codigoProduto");
        }


        $stmtInsert->bind_param("sssssss", $codigoFilial, $codigoProduto, $dataVencimentoFormat, $quantidade, $observacao, $dataAtual, $userId);

        if (!$stmtInsert->execute()) {
            throw new Exception("Erro ao cadastrar validade: " . $stmtInsert->error);
        } 

    }

    $stmtSelect->close();
    $stmtInsert->close();
    $conn->close();

   
        http_response_code(200);
        echo json_encode([
            "sucesso" => true,
            "mensagem" => "Validade cadastrada com sucesso!",
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