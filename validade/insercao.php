<?php
    header("Content-Type: application/json");

    include_once "../conexao.php";

    $entrada = json_decode(file_get_contents("php://input"), true); 

    $codigoProduto = $entrada["codProd"] ?? "";
    $tipoInsercao = $entrada["tipoInsercao"] ?? "";
    $dataVencimentoOrigin = $entrada["dataVencimento"] ?? "";
    $codigoBonus = $entrada["codBonus"] ?? "";
    $quantidade = $entrada["quantidade"] ?? "";
    $observacao = $observacao["observacao"] ?? "";


    $dataVencimentoFormat = date('Y-m-d', strtotime($dataVencimentoOrigin));

    echo $dataVencimentoFormat;

    if (empty($codigoProduto) || empty($tipoInsercao) || empty($dataVencimento) || empty($codigoBonus) || empty($quantidade)){
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

?>