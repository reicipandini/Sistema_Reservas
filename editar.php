<?php
session_start();

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

include_once "conexao.php";

$usuario_id_logado = $_SESSION["usuario_id"];
$perfil            = $_SESSION["usuario_perfil"];

if (!isset($_GET["id"])) {
    header("Location: index.php");
    exit;
}

$id = intval($_GET["id"]);

$resultado = mysqli_query($conexao, "SELECT * FROM reservas WHERE id=$id");

if (mysqli_num_rows($resultado) === 0) {
    echo "<script>alert('Reserva não encontrada!'); window.location.href='index.php';</script>";
    exit;
}

$reserva = mysqli_fetch_assoc($resultado);

$pode_editar = ($perfil === 'admin') || 
               ($reserva['usuario_id'] == $usuario_id_logado) || 
               ($reserva['nome_solicitante'] === $_SESSION["usuario_nome"]);

if (!$pode_editar) {
    echo "<script>alert('Você não tem permissão para editar esta reserva!'); window.location.href='index.php';</script>";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nome_solicitante = $_POST["nome_solicitante"];
    $categoria        = $_POST["categoria"];
    $sala_laboratorio = $_POST["sala_laboratorio"];
    $data_reserva     = $_POST["data_reserva"];
    $hora_inicio      = $_POST["hora_inicio"];
    $hora_fim         = $_POST["hora_fim"];
    $motivo           = $_POST["motivo"];

    if ($categoria === "Professor" && $motivo === "Manutenção / Reparos") {
        echo "<script>alert('Erro: A categoria Professor não pode realizar reservas para Manutenção!'); window.location.href='index.php';</script>";
        exit;
    }

    // VERIFICAÇÃO DE CONFLITO
    $query_conflito = "SELECT * FROM reservas 
                       WHERE sala_laboratorio = '$sala_laboratorio' 
                       AND data_reserva = '$data_reserva' 
                       AND id != $id 
                       AND ('$hora_inicio' < hora_fim AND '$hora_fim' > hora_inicio)";

    $resultado_conflito = mysqli_query($conexao, $query_conflito);

    if (mysqli_num_rows($resultado_conflito) > 0) {
        echo "<script>alert('Atenção: A sala $sala_laboratorio já possui outra reserva agendada neste horário!'); window.location.href='editar.php?id=$id';</script>";
        exit;
    }

    mysqli_query($conexao, "UPDATE reservas SET
        nome_solicitante='$nome_solicitante',
        categoria='$categoria',
        sala_laboratorio='$sala_laboratorio',
        data_reserva='$data_reserva',
        hora_inicio='$hora_inicio',
        hora_fim='$hora_fim',
        motivo='$motivo'
        WHERE id=$id
    ");

    header("Location: index.php");
    exit;
}

$categorias = ["Professor", "Aluno / Monitor", "Outro"];
if ($perfil === 'admin') {
    $categorias[] = "Equipe de Manutenção";
    $categorias[] = "Coordenação / Direção";
}

$salas = [
    "Sala de Aula 101",
    "Sala de Aula 102",
    "Sala de Aula 103",
    "Sala de Aula 104",
    "Sala de Aula 105",
    "Sala de Aula 106",
    "Lab. Informática 202",
    "Lab. Informática 203",
    "Lab. Automação 302",
    "Lab. Alimentos 303"
];

$motivos = ["Aula Prática / Teórica", "Reunião Rápida", "Evento / Palestra", "Estudo / Monitoria", "Outro"];
if ($perfil === 'admin') {
    $motivos[] = "Manutenção / Reparos";
}
?>

<!doctype html>
<html lang="pt-BR" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Editar Reserva - Sistema de Reservas</title>
    <!-- CDN Bootstrap 5 (CSS) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-light pb-5">

    <!-- NAVBAR BOOTSTRAP -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-body-tertiary border-bottom border-secondary mb-4 shadow-sm">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold text-info" href="index.php">Sistema de Reservas</a>
            <div class="d-flex align-items-center gap-3">
                <span class="text-light">Olá, <strong><?php echo $_SESSION["usuario_nome"]; ?></strong></span>
                <span class="badge bg-primary text-uppercase rounded-0"><?php echo $perfil; ?></span>
                <a href="login.php" class="btn btn-danger btn-sm rounded-0 fw-semibold">Sair</a>
            </div>
        </div>
    </nav>

    <div class="container my-4" style="max-width: 800px;">
        <h1 class="h3 mb-4 fw-normal border-start border-4 border-info ps-2">Editar Reserva #<?php echo $reserva["id"]; ?></h1>

        <div class="card bg-body-tertiary border-secondary rounded-0 p-4 shadow-sm">
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label text-uppercase small text-secondary fw-semibold">Nome do Solicitante:</label>
                    <input type="text" class="form-control rounded-0" name="nome_solicitante" value="<?php echo $reserva["nome_solicitante"]; ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label text-uppercase small text-secondary fw-semibold">Categoria do Solicitante:</label>
                    <select class="form-select rounded-0" name="categoria" id="categoria" required>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?php echo $cat; ?>" <?php echo ($reserva["categoria"] === $cat) ? 'selected' : ''; ?>>
                                <?php echo $cat; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label text-uppercase small text-secondary fw-semibold">Sala / Laboratório:</label>
                    <select class="form-select rounded-0" name="sala_laboratorio" required>
                        <?php foreach ($salas as $sala): ?>
                            <option value="<?php echo $sala; ?>" <?php echo ($reserva["sala_laboratorio"] === $sala) ? 'selected' : ''; ?>>
                                <?php echo $sala; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label text-uppercase small text-secondary fw-semibold">Motivo / Finalidade:</label>
                    <select class="form-select rounded-0" name="motivo" id="motivo" required>
                        <?php foreach ($motivos as $m): ?>
                            <option value="<?php echo $m; ?>" <?php echo ($reserva["motivo"] === $m) ? 'selected' : ''; ?>>
                                <?php echo $m; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label text-uppercase small text-secondary fw-semibold">Data da Reserva:</label>
                        <input type="date" class="form-control rounded-0" name="data_reserva" value="<?php echo $reserva["data_reserva"]; ?>" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label text-uppercase small text-secondary fw-semibold">Hora de Início:</label>
                        <input type="time" class="form-control rounded-0" name="hora_inicio" value="<?php echo $reserva["hora_inicio"]; ?>" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label text-uppercase small text-secondary fw-semibold">Hora de Fim:</label>
                        <input type="time" class="form-control rounded-0" name="hora_fim" value="<?php echo $reserva["hora_fim"]; ?>" required>
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary rounded-0 fw-semibold text-uppercase">Salvar Alterações</button>
                    <a href="index.php" class="btn btn-outline-secondary rounded-0 ms-2">Cancelar</a>
                </div>
            </form>
        </div>
    </div>

    <!-- CDN Bootstrap 5 (JS) -->
    <script href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
</body>
</html>