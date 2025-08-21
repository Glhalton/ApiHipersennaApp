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
    $codConferente = $entrada["codConferente"] ?? "";
    date_default_timezone_set('America/Sao_Paulo');
    $dataAtual = date('Y-m-d');

    $sql1 = "INSERT INTO solicitacao_vistoria(
            cod_filial,
            conferente_id,
            analista_id,
            criado_em
        ) VALUES (?, ?, ?, ?)";
    $stmtInsert1 = $conn->prepare($sql1);

    $sql2 = "INSERT INTO solicitacao_produtos(
            cod_produto,
            solicitacao_vistoria_id
        ) VALUES (?, ?)";
    $stmtInsert2 = $conn->prepare($sql2);


    if (empty($codFilial) || empty($userId) || empty($itens) || empty($codConferente)) {
        throw new Exception("Preencha todos os campos obrigatórios.");
    }

    $stmtInsert1->bind_param("iiis", $codFilial,$codConferente, $userId, $dataAtual);

    if (!$stmtInsert1->execute()) {
        throw new Exception("Erro ao cadastrar solicitação de validade: " . $stmtInsert1->error);
    }

    $last_id = $conn->insert_id;

    foreach ($itens as $item) {
        $codigoProduto = $item["codProd"] ?? "";

        if (empty($codigoProduto)) {
            throw new Exception("Preencha todos os campos obrigatórios.");
        }

        $stmtInsert2->bind_param("ii", $codigoProduto, $last_id);

        if (!$stmtInsert2->execute()) {
            throw new Exception("Erro ao cadastrar produtos da solicitação de validade: " . $stmtInsert2->error);
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