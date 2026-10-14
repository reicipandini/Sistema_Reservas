<?php
session_start();
include_once "conexao.php";
$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST["email"];
    $senha = $_POST["senha"];

    $query = mysqli_query($conexao, "SELECT * FROM usuarios WHERE email='$email' AND senha='$senha'");
    
    if (mysqli_num_rows($query) > 0) {
        $usuario = mysqli_fetch_assoc($query);
        
        $_SESSION["usuario_id"]     = $usuario["id"];
        $_SESSION["usuario_nome"]   = $usuario["nome"];
        $_SESSION["usuario_perfil"] = $usuario["perfil"];

        header("Location: index.php");
        exit;
    } else {
        $erro = "E-mail ou senha incorretos!";
    }
}
?>

<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Sistema de Reservas</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="logo-title">Sistema de Reservas</div>
            <div class="sub-title">Salas & Laboratórios</div>
        </div>

        <?php if ($erro !== ""): ?>
            <div class="alert alert-erro"><?php echo $erro; ?></div>
        <?php endif; ?>

        <form method="POST">
            <label>E-mail de Acesso:</label>
            <input type="email" name="email" required placeholder="seu.email@escola.com">

            <label>Senha:</label>
            <input type="password" name="senha" required placeholder="••••••••">

            <button type="submit" class="btn btn-block">Entrar no Sistema</button>
        </form>
    </div>

</body>
</html>