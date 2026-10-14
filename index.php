<?php
session_start();
if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}
include_once "conexao.php";

$usuario_id_logado = $_SESSION["usuario_id"];
$perfil            = $_SESSION["usuario_perfil"]; // 'admin' ou 'professor'

// PROCESSA O CADASTRO DE RESERVA
if ($_POST) {
    $nome_solicitante = $_POST["nome_solicitante"];
    $categoria        = $_POST["categoria"];
    $sala_laboratorio = $_POST["sala_laboratorio"];
    $data_reserva     = $_POST["data_reserva"];
    $hora_inicio      = $_POST["hora_inicio"];
    $hora_fim         = $_POST["hora_fim"];
    $motivo           = $_POST["motivo"];

    // Trava no Backend: Professor não faz manutenção
    if ($categoria === "Professor" && $motivo === "Manutenção / Reparos") {
        echo "<script>alert('Erro: Categoria Professor não pode agendar Manutenção!'); window.location.href='index.php';</script>";
        exit;
    }

    // VERIFICAÇÃO DE CONFLITO DE HORÁRIO
    $query_conflito = "SELECT * FROM reservas 
                       WHERE sala_laboratorio = '$sala_laboratorio' 
                       AND data_reserva = '$data_reserva' 
                       AND ('$hora_inicio' < hora_fim AND '$hora_fim' > hora_inicio)";

    $resultado_conflito = mysqli_query($conexao, $query_conflito);

    if (mysqli_num_rows($resultado_conflito) > 0) {
        echo "<script>alert('Atenção: A sala $sala_laboratorio já possui uma reserva agendada neste horário!'); window.location.href='index.php';</script>";
        exit;
    }

    mysqli_query($conexao, "INSERT INTO reservas (usuario_id, nome_solicitante, categoria, sala_laboratorio, data_reserva, hora_inicio, hora_fim, motivo) 
    VALUES ('$usuario_id_logado', '$nome_solicitante', '$categoria', '$sala_laboratorio', '$data_reserva', '$hora_inicio', '$hora_fim', '$motivo')");
    
    header("Location: index.php");
    exit;
}
?>

<!doctype html>
<html lang="pt-BR" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reserva de Salas e Laboratórios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light text-dark pb-5">

    <!-- NAVBAR  -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom border-secondary-subtle mb-4 shadow-sm">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold text-primary" href="index.php">Sistema de Reservas</a>
            <div class="d-flex align-items-center gap-3">
                <span class="text-dark">Olá, <strong><?php echo $_SESSION["usuario_nome"]; ?></strong></span>
                <span class="badge bg-primary text-uppercase rounded-0"><?php echo $perfil; ?></span>
                <a href="login.php" class="btn btn-danger btn-sm rounded-0 fw-semibold">Sair</a>
            </div>
        </div>
    </nav>
    <div class="container my-4">

        <!-- TELA 1: LISTA DE RESERVAS -->
        <div id="secao-lista" class="view-section">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 fw-normal border-start border-4 border-info ps-2">Reservas Cadastradas</h1>
                <button class="btn btn-info rounded-0 fw-bold text-uppercase" onclick="trocarTela('secao-form')">+ Nova Reserva</button>
            </div>

            <div class="table-responsive">
                <table class="table table-light table-hover table-bordered align-middle rounded-0 shadow-sm">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Solicitante</th>
                            <th>Categoria</th>
                            <th>Sala / Laboratório</th>
                            <th>Motivo</th>
                            <th>Data</th>
                            <th>Horário</th>
                            <th class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $resultado = mysqli_query($conexao, "SELECT * FROM reservas ORDER BY id DESC");
                        while ($reserva = mysqli_fetch_assoc($resultado)) {
                            
                            $pode_editar = ($perfil === 'admin') || ($reserva['usuario_id'] == $usuario_id_logado) || ($reserva['nome_solicitante'] === $_SESSION["usuario_nome"]);

                            echo "<tr>";
                            echo "<td>" . $reserva["id"] . "</td>";
                            echo "<td>" . $reserva["nome_solicitante"] . "</td>";
                            echo "<td>" . $reserva["categoria"] . "</td>";
                            echo "<td>" . $reserva["sala_laboratorio"] . "</td>";
                            echo "<td>" . $reserva["motivo"] . "</td>";
                            echo "<td>" . date('d/m/Y', strtotime($reserva["data_reserva"])) . "</td>";
                            echo "<td>" . $reserva["hora_inicio"] . " às " . $reserva["hora_fim"] . "</td>";
                            
                          echo "<td class='text-center align-middle'>";

                            if ($pode_editar) {
                                echo "<div class='d-flex justify-content-center gap-2'>";
                                echo "  <a href='editar.php?id=" . $reserva["id"] . "' class='btn btn-primary btn-sm rounded-0 fw-semibold px-2 py-1' style='font-size: 12px;'>Editar</a>";
                                echo "  <a href='excluir.php?id=" . $reserva["id"] . "' class='btn btn-danger btn-sm rounded-0 fw-semibold px-2 py-1' onclick='return confirmarExclusao()' style='font-size: 12px;'>Excluir</a>";
                                echo "</div>";
                            } else {
                                echo "<span class='badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-0 fw-normal px-2 py-1'>Somente Leitura</span>";
                            }
                            echo "</td>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TELA 2: FORMULÁRIO DE CADASTRO -->
        <div id="secao-form" class="view-section d-none">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 fw-semibold border-start border-4 border-primary ps-2">Nova Reserva</h1>
                <button class="btn btn-outline-secondary rounded-0 fw-semibold" onclick="trocarTela('secao-lista')">&larr; Voltar para Lista</button>
            </div>

            <!-- CARD BRANCO COM BORDA SUAVE -->
            <div class="card bg-white border-secondary-subtle rounded-0 p-4 shadow-sm">
                <form id="formCadastro" method="POST">
                    <div class="mb-3">
                        <label class="form-label text-uppercase small text-dark fw-bold">Nome do Solicitante:</label>
                        <input type="text" class="form-control rounded-0" name="nome_solicitante" id="nome_solicitante" value="<?php echo $_SESSION['usuario_nome']; ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-uppercase small text-dark fw-bold">Categoria do Solicitante:</label>
                        <select class="form-select rounded-0" name="categoria" id="categoria" required>
                            <option value="">-- Selecione a categoria --</option>
                            <option value="Professor">Professor</option>
                            
                            <?php if ($perfil === 'admin'): ?>
                                <option value="Equipe de Manutenção">Equipe de Manutenção</option>
                                <option value="Coordenação / Direção">Coordenação / Direção</option>
                            <?php endif; ?>

                            <option value="Aluno / Monitor">Aluno / Monitor</option>
                            <option value="Outro">Outro</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-uppercase small text-dark fw-bold">Sala / Laboratório:</label>
                        <select class="form-select rounded-0" name="sala_laboratorio" id="sala_laboratorio" required>
                            <option value="">-- Selecione o local --</option>
                            <option value="Sala de Aula 101">Sala de Aula 101</option>
                            <option value="Sala de Aula 102">Sala de Aula 102</option>
                            <option value="Sala de Aula 103">Sala de Aula 103</option>
                            <option value="Sala de Aula 104">Sala de Aula 104</option>
                            <option value="Sala de Aula 105">Sala de Aula 105</option>
                            <option value="Sala de Aula 106">Sala de Aula 106</option>
                            <option value="Lab. Informática 202">Lab. Informática 202</option>
                            <option value="Lab. Informática 203">Lab. Informática 203</option>
                            <option value="Lab. Automação 302">Lab. Automação 302</option>
                            <option value="Lab. Alimentos 303">Lab. Alimentos 303</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-uppercase small text-dark fw-bold">Motivo / Finalidade:</label>
                        <select class="form-select rounded-0" name="motivo" id="motivo" required>
                            <option value="">-- Selecione a finalidade --</option>
                            <option value="Aula Prática / Teórica">Aula Prática / Teórica</option>
                            
                            <?php if ($perfil === 'admin'): ?>
                                <option value="Manutenção / Reparos">Manutenção / Reparos</option>
                            <?php endif; ?>

                            <option value="Reunião Rápida">Reunião Rápida</option>
                            <option value="Evento / Palestra">Evento / Palestra</option>
                            <option value="Estudo / Monitoria">Estudo / Monitoria</option>
                            <option value="Outro">Outro</option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label text-uppercase small text-dark fw-bold">Data da Reserva:</label>
                            <input type="date" class="form-control rounded-0" name="data_reserva" id="data_reserva" min="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label text-uppercase small text-dark fw-bold">Hora de Início:</label>
                            <input type="time" class="form-control rounded-0" name="hora_inicio" id="hora_inicio" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label text-uppercase small text-dark fw-bold">Hora de Fim:</label>
                            <input type="time" class="form-control rounded-0" name="hora_fim" id="hora_fim" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary rounded-0 fw-semibold text-uppercase mt-2">Cadastrar Reserva</button>
                </form>
            </div>
        </div>

    </div>

    <!-- CDN Bootstrap 5 (JS) -->
    <script href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Alternar telas usando classes padrão do Bootstrap (d-none)
        function trocarTela(idSecao) {
            document.getElementById('secao-lista').classList.add('d-none');
            document.getElementById('secao-form').classList.add('d-none');
            document.getElementById(idSecao).classList.remove('d-none');
        }
    </script>
    <script src="script.js"></script>
</body>
</html>