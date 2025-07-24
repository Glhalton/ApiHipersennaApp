<?php

header("Content-Type: application/json");

include_once "../conexao.php";


$entrada = json_decode(file_get_contents("php://input"), true);

$username = $entrada["username"] ?? "";
$password = $entrada["password"] ?? "";

$sql = "SELECT id FROM usuarios WHERE username = ? AND password = ? LIMIT 1";
$stmt = $conn->prepare($sql );
$stmt->bind_param("ss", $username, $password);
$stmt->execute();
$result = $stmt->get_result();

$user = $result->fetch_assoc();

if($result->num_rows === 1){
    echo json_encode(["sucesso" => true, "mensagem" => "Login correto!", "userId" => $user["id"]]);
} else{
    echo json_encode(["sucesso" => false, "mensagem" => "Usuário ou senha incorreta."]);
}

$stmt->close();
$conn->close();

?>