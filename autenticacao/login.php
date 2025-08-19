<?php

//Cabeçalho
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");


if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {

    //Raliza a conexão com o banco de dados por meio dos dados do arquivo.
    include_once "../conexao.php";

    //Recebe os dados em formato JSON pela stream
    $rawInput = file_get_contents("php://input");

    //Transforma o JSON em uma array associativa PHP
    $entrada = json_decode($rawInput, true);

    if ($entrada === null) {
        throw new Exception("Dados JSON inválidos: " . $rawInput);
    }

    //Recupera o valor de cada chave da array associativa "entrada"
    $username = strtolower($entrada["username"] ?? "");
    $password = $entrada["password"] ?? "";

    //Verifica se os dados recebidos estão vazios
    if (empty($username) || empty($password)) {
        http_response_code(400);
        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Preencha todos os campos."
        ]);
        exit;
    }

    //Consulta SQL
    $sql = "SELECT id, password FROM usuarios WHERE username = ? ";
    $stmt = $conn->prepare($sql);

    //Define os dados que serão utilizados na consulta
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    //Verifica se teve resultado para enviar uma resposta
    if ($result->num_rows === 1 && password_verify($password, $user["password"])) {
        http_response_code(200);
        echo json_encode([
            "sucesso" => true,
            "mensagem" => "Login correto!",
            "userId" => $user["id"]
        ]);
    } else {
        http_response_code(401);
        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Usuário ou senha incorreta."
        ]);
    }

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