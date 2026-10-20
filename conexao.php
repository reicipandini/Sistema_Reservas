<?php
$host = getenv('DB_HOST');
$usuario = getenv('DB_USER');
$senha = getenv('DB_PASSWORD');
$banco = getenv('DB_NAME');

$conexao = new mysqli(
    $host,
    $usuario,
    $senha,
    $banco
);
?>
