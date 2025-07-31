<?php

    $host = "127.0.0.1";
    $usuario = "root";
    $senha = "1234";
    $banco = "hipersennaapp";
    $conn = new mysqli($host, $usuario, $senha, $banco);

    if($conn->connect_error){
        die("Falha na conexao: " . $conn->connect_error);
    }
    
?>
