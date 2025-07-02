<?php
    $host = "localhost";
    $usuario = "root";
    $senha = "@Glhalton123";
    $banco = "hipersennaapp";

    $conn = new mysqli($host, $usuario, $senha, $banco);

    if($conn->connect_error){
        die("Falha na conexao: " . $conn->connect_error);
    }

?>
