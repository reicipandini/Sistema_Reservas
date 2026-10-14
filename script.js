function confirmarExclusao() {
    return confirm("Tem certeza que deseja excluir esta reserva?");
}

document.addEventListener("DOMContentLoaded", function() {
    var selectCategoria = document.getElementById("categoria");
    var selectMotivo = document.getElementById("motivo");

    if (selectCategoria && selectMotivo) {
        selectCategoria.addEventListener("change", function() {
            var categoriaSelecionada = this.value;

            for (var i = 0; i < selectMotivo.options.length; i++) {
                var option = selectMotivo.options[i];

                // Se a categoria for Professor, esconde e desabilita a opção de Manutenção 
                if (categoriaSelecionada === "Professor" && option.value === "Manutenção / Reparos") {
                    option.style.display = "none";
                    option.disabled = true;

                    // Se a opção bloqueada estiver selecionada, reseta o campo motivo
                    if (selectMotivo.value === "Manutenção / Reparos") {
                        selectMotivo.value = "";
                    }
                } else {
                    option.style.display = "block";
                    option.disabled = false;
                }
            }
        });
    }
});

// Validação no envio do formulário
document.getElementById("formCadastro")?.addEventListener("submit", function(event) {
    
    var nome = document.getElementById("nome_solicitante").value;
    var categoria = document.getElementById("categoria").value;
    var sala = document.getElementById("sala_laboratorio").value;
    var motivo = document.getElementById("motivo").value;
    var horaInicio = document.getElementById("hora_inicio").value;
    var horaFim = document.getElementById("hora_fim").value;

    if (nome.trim().length < 3) {
        alert("O nome do solicitante é obrigatório e deve ter pelo menos 3 caracteres.");
        event.preventDefault();
        return;
    }

    if (categoria === "Professor" && motivo === "Manutenção / Reparos") {
        alert("Professores não podem cadastrar reservas com a finalidade de Manutenção / Reparos.");
        event.preventDefault();
        return;
    }

    if (horaInicio >= horaFim) {
        alert("A hora de fim deve ser posterior à hora de início.");
        event.preventDefault();
        return;
    }
});