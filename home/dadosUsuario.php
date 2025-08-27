<?php

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");


if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
try {

    include_once "../conexao.php";

    $rawInput = file_get_contents("php://input");
    $entrada = json_decode($rawInput, true);

    if ($entrada === null) {
        throw new Exception("Dados JSON invalidos");
    }

    $userId = $entrada["userId"] ?? "";

    if (empty($userId)) {
        http_response_code(400);
        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Erro, id vazio"
        ]);
        exit;
    }


    $sql = "SELECT COUNT(*) AS quantidade FROM validade WHERE colaborador_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $vistorias = $result->fetch_assoc();

    $sql2 = "SELECT name FROM usuarios where id = ?";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param("s", $userId);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    $primeiroNome = $result2->fetch_assoc();


    echo json_encode([
        "sucesso" => true,
        "quantidade_vistorias" => $vistorias["quantidade"],
        "primeiroNome" => ucwords($primeiroNome["name"])
    ]);

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