<?php
    header("Content-Type: application/json");

    include_once "../conexao.php";

    $entrada = json_decode(file_get_contents("php://input"), true);

    $nomeCompleto = $entrada["nomeCompleto"] ?? "";
    $email = $entrada["email"] ?? "";
    $telefone = $entrada["telefone"] ?? "";
    $endereco = $entrada["endereco"] ?? "";
    $username = $entrada["username"] ?? "";
    $password = $entrada["password"] ?? "";

    if (empty($nomeCompleto) || empty($email) || empty($username) || empty($password)){
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
    $stmt->bind_param("ssssss", $nomeCompleto, $email, $telefone, $endereco,         $username, $password);
    
    if($stmt->execute()){
        echo json_encode([
            "sucesso" => true,
            "mensagem" => "Usuário cadastrado com sucesso!",
            "id_inserido" => $stmt-> insert_id
        ]);
    } else{
        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Erro ao cadastrar o usuário: " . $stmt->error
        ]);
    }

    $stmt->close();
    $conn->close();

?>