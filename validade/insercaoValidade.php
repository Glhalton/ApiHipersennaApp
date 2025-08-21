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
    $codFilial = $entrada["codFilial"] ?? "";
    date_default_timezone_set('America/Sao_Paulo');
    $dataAtual = date('Y-m-d');

    $sql1 = "INSERT INTO validade(
            cod_filial,
            criado_em,
            colaborador_id
        ) VALUES (?, ?, ?)";
    $stmtInsert1 = $conn->prepare($sql1);

    $sql2 = "INSERT INTO validade_produtos(
            cod_produto,
            data_validade,
            quantidade,
            validade_id,
            texto_obs
        ) VALUES (?, ?, ?, ?, ?)";
    $stmtInsert2 = $conn->prepare($sql2);


    if (empty($codFilial) || empty($userId) || empty($itens)) {
        throw new Exception("Preencha todos os campos obrigatórios.");
    }

    $stmtInsert1->bind_param("isi", $codFilial, $dataAtual, $userId);

    if (!$stmtInsert1->execute()) {
        throw new Exception("Erro ao cadastrar validade: " . $stmtInsert1->error);
    }

    $last_id = $conn->insert_id;

    foreach ($itens as $item) {
        $codigoProduto = $item["codProd"] ?? "";
        $dataVencimento = date('Y-m-d', strtotime($item["dataVencimento"])) ?? "";
        $quantidade = $item["quantidade"] ?? "";
        $texto_obs = $item["observacao"] ??"";

        if (empty($codigoProduto) || empty($dataVencimento) || empty($quantidade)) {
            throw new Exception("Preencha todos os campos obrigatórios.");
        }

        $stmtInsert2->bind_param("isiis", $codigoProduto, $dataVencimento, $quantidade, $last_id, $texto_obs);

        if (!$stmtInsert2->execute()) {
            throw new Exception("Erro ao cadastrar produtos da validade: " . $stmtInsert2->error);
        }

    }

    $stmtInsert1->close();
    $stmtInsert2->close();
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