<?php

    header("Content-Type: application/json");
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS");

    try{

        include_once "../conexao.php";

        $rawInput = file_get_contents("php://input");
        $entrada = json_decode($rawInput, true);

        if($entrada === null){
            throw new Exception("Dados JSON inválidos: " . $rawInput);
        }

        $nomeCompleto = strtolower($entrada["nomeCompleto"] ?? "");
        $email = strtolower($entrada["email"] ?? "");
        $telefone = $entrada["telefone"] ?? "";
        $endereco = $entrada["endereco"] ?? "";
        $username = strtolower($entrada["username"] ?? "");
        $password = $entrada["password"] ?? "";

        $password_crypto= password_hash($password, PASSWORD_DEFAULT);

        
        if (empty($nomeCompleto) || empty($email) || empty($username) || empty($password)){
            http_response_code(400);
            echo json_encode([
                "sucesso" => false,
                "mensagem" => "Preencha todos os campos obrigatórios."
            ]);
            exit;
        }

        $sql = "INSERT INTO usuarios( 
            name, 
            email,
            telefone,
            endereco, 
            username, 
            password
        ) VALUES ( ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssss", $nomeCompleto, $email, $telefone, $endereco, $username, $password_crypto);

        if($stmt->execute()){
            http_response_code(200);
            echo json_encode([
                "sucesso" => true,
                "mensagem" => "Usuário cadastrado com sucesso!",
            ]);
        }else{
            http_response_code(401);
            echo json_encode([
                "sucesso" => false,
                "mensagem" => "Erro ao cadastrar o usuário: " . $stmt->error
            ]);
        }

        $stmt->close();
        $conn->close();
        
    } catch (Exception $e){
        http_response_code($e->getCode() ?: 500);
        echo json_encode([
            "sucesso" =>false,
            "mensagem" => $e->getMessage()
        ]);
    }
    
?>