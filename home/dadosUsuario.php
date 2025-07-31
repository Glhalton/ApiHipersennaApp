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

        echo json_encode([
            "sucesso" => true,
            "quantidade_vistorias" => $vistorias["quantidade"]
        ]);

        $stmt->close();
        $conn->close();


    } catch(Exception $e){
        http_response_code($e->getCode() ?: 500);
        echo json_encode([
            "sucesso" => false, 
            "mensagem" => $e->getMessage()
        ]);
    }

?>