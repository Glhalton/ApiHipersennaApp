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

    $codigoProduto = $entrada["codProd"] ?? "";

    $sql = "SELECT descricao FROM produtos WHERE codauxiliar = ? ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $codigoProduto);
    $stmt->execute();
    $result = $stmt->get_result();
    $dados = $result->fetch_assoc();


    if ($result->num_rows == 1) {
        http_response_code(200);
        echo json_encode([
            "sucesso" => true,
            "produto" => $dados
        ]);
    } else {
        http_response_code(401);
        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Produto não encontrado"
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