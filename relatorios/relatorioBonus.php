<?php

    header("Content-Type: application/json");
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS");

    try {
        include_once("../conexao.php");

        $rawInput = file_get_contents("php://input");
        $entrada = json_encode($rawInput, true);

        if ($entrada === null) {
            throw new Exception("Dados JSON inválidos: " . $rawInput);
        }

        $filial = $entrada["codFilial"] ?? "";
        $codigoProduto = $entrada["codProd"] ?? "";
        $quantidadeDias = $entrada["quantDias"] ?? "";
        $dataInicial = $entrada["dataInicial"] ?? "";
        $dataFinal = $entrada["dataFinal"] ?? "    ";

        

        if (empty($filial) || empty($codigoProduto)) {
            http_response_code(400);
            echo json_encode([
                "sucesso" => false,
                "mensagem" => "Preencha todos os campos."
            ]);
            exit;
        }


    } catch (Exception $e) {
        http_response_code($e->getCode() ?: 500);
        echo json_encode([
            "sucesso" => false,
            "mensagem" => $e->getMessage()
        ]);
    }


?>