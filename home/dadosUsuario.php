<?php

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS");

try {

    include_once "../conexao.php";

    $rawInput = file_get_contents("php://input");
    $entrada = json_decode($rawInput, true);

    if($entrada === null) {
        throw new Exception("Dados JSON invalidos");
    }

    $userId = $entrada["userId"] ?? "";

    if(empty($userId)){
        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Erro, id vazio"
        ]);
        exit;
    }

    $sql = "SELECT COUNT(*) id FROM validade WHERE colaborador_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $userId);
    $stmt = $stmt->get_result();

    $vistorias = $result->fetch_assoc();

    if($result->num_rows === 1){
        echo json_encode([
            "sucesso" => true,
            "mensagem" => "Consulta com sucesso"
            "quantidade_vistorias" => $vistorias["id"]
        ])
    } else{
        echo json_encode(["sucesso" => false, "mensagem" => Sem vistorias])
    }

    $stmt->close();
    conn->close();
} catch(Exception $e){
    http_response_code($e->getCode() ?: 500);
    ob_end_clean();
    echo json_encode(['sucesso' => false, 'mensagem' => $e->getMessage()]);
        exit;
}



?>