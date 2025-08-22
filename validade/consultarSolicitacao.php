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

    
    $userId = $entrada["userId"] ?? "";
    
    $sql = "SELECT 
    	sv.id as solicitacao_id,
        sv.criado_em,
        sv.analista_id,
        sv.status,
        sv.cod_filial,
        sp.cod_produto
    FROM solicitacao_vistoria sv 
    INNER JOIN solicitacao_produtos sp 
    ON sv.id = sp.solicitacao_vistoria_id
    WHERE sv.conferente_id = ?
    ORDER BY sv.id";


    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $dados = [];
    $solicitacaoInfo = [];
    $solicitacoes = [];

    while ($row = $result->fetch_assoc()) {
        $id = $row['solicitacao_id'];

        // se ainda não existe essa solicitação no array, cria
        if (!isset($solicitacoes[$id])) {
            $solicitacoes[$id] = [
                "cod_filial" => $row["cod_filial"],
                "status" => $row["status"],
                "dataSolicitacao" => $row["criado_em"],
                "analistaId" => $row["analista_id"],
                "produtos" => [] // array para os produtos
            ];
        }

        // adiciona o produto à solicitação
        $solicitacoes[$id]["produtos"][] = [
            "cod_produto" => $row["cod_produto"]
        ];
    }

    // reindexa para ficar um array simples, não associativo
    $solicitacoes = array_values($solicitacoes);

    http_response_code(200);
    echo json_encode([
        "sucesso" => true,
        "solicitacoes" => $solicitacoes
    ]);


    // if ($result->num_rows >= 1) {

    //     http_response_code(200);
    //     echo json_encode([
    //         "sucesso" => true,
    //         "dados" => $dados,
    //         "solicitacaoInfo" => $solicitacaoInfo
    //     ]);
    // } else {
    //     http_response_code(401);
    //     echo json_encode([
    //         "sucesso" => false,
    //         "mensagem" => "Nenhuma solicitação encontrada."
    //     ]);
    // }

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