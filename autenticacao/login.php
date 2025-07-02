<?php

header("Content-Type: application/json");

include_once "../conexao.php";


$entrada = json_decode(file_get_contents("php://input"), true);

$username = $entrada["username"] ?? "";
$password = $entrada["password"] ?? "";

$sql = "SELECT * FROM usuarios WHERE username = ? AND password = ? LIMIT 1";
$stmt = $conn->prepare($sql );
$stmt->bind_param("ss", $username, $password);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows === 1){
    echo json_encode(["sucesso" => true, "mensagem" => "Login correto!"]);
} else{
    echo json_encode(["sucesso" => false, "mensagem" => "Login incorreto."]);
}

$stmt->close();
$conn->close();

?>