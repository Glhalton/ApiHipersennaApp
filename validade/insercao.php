<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS");

// Ler input só uma vez
$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

try {

    include_once "../conexao.php";

    $input = json_decode($rawInput, true);
    if($input === null) {
        throw new Exception('Dados JSON invalidos');
    }

    $entrada = json_decode(file_get_contents("php://input"), true); 

    $codigoProduto = $entrada["codProd"] ?? "";
    $tipoInsercao = $entrada["tipoInsercao"] ?? "";
    $dataVencimentoOrigin = $entrada["dataVencimento"] ?? "";
    $codigoBonus = $entrada["codBonus"] ?? "";
    $quantidade = $entrada["quantidade"] ?? "";
    $observacao = $observacao["observacao"] ?? "";


    $dataVencimentoFormat = date('Y-m-d', strtotime($dataVencimentoOrigin));

    echo $dataVencimentoFormat;

    if (empty($codigoProduto) || empty($tipoInsercao) || empty($dataVencimentoFormat) || empty($codigoBonus) || empty($quantidade)){
        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Preencha todos os campos obrigatorios."
        ]);
        exit;
    }

    $sql = "INSERT INTO validade(
        codigoProduto,
        tipoInsercao,
        dataVencimento,
        codigoBonus,
        quantidade,
        textoObservacao
    ) VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $codigoProduto, $tipoInsercao, $dataVencimento, $codigoBonus, $quantidade, $observacao);

    if($stmt->execute()){
        echo json_encode([
            "sucesso" => true,
            "mensagem" => "Validade cadastrada com sucesso!",
            "id_inserido" => $stmt->insert_id
        ]);
    } else{
        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Erro ao cadastrar validade: " . $stmt->error
        ]);
    }

    $stmt->close();
    $conn->close();
        
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    ob_end_clean();
    echo json_encode(['sucesso' => false, 'mensagem' => $e->getMessage()]);
    exit;
}""