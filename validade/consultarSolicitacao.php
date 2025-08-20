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

    $sql = "SELECT * FROM solicitacao_vistoria WHERE conferente_id = ? ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $dados = $result->fetch_assoc();


    if ($result->num_rows >= 1) {
        http_response_code(200);
        echo json_encode([
            "sucesso" => true,
            "dados" => $dados,
            "codProd" => $dados["cod_produto"],
            "filial" => $dados["cod_filial"],
            "status" => $dados["status"],
            "dataSolicitacao" => $dados["criado_em"]
        ]);
    } else {
        http_response_code(401);
        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Nenhuma solicitação encontrada."
        ]);
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => $e->getMessage()
    ]);
}

?>