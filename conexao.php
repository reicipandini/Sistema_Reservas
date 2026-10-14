<?php

$host = "localhost";
$usuario = "root";
$senha = "SenhaForte";
$banco = "sistema_reservas";

$conexao = new mysqli($host, $usuario, $senha, $banco);

if ($conexao->connect_error) {
    die("Erro na conexão: " . $conexao->connect_error);
}

$conexao->set_charset("utf8mb4");

?>