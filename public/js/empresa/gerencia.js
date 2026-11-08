/**
 * RUSTIC IRON - JAVASCRIPT DA GERÊNCIA & GESTÃO ESTRATÉGICA (COMPLETO E DINÂMICO)
 */

document.addEventListener('DOMContentLoaded', () => {
    const avatarSalvo = localStorage.getItem("rustic_gerencia_avatar");
    if (avatarSalvo) {
        aplicarAvatarGerencia(avatarSalvo);
    }

    carregarEquipe('todos');
    carregarSetores();
    carregarCargosSelect(); 
    carregarTurnosSelect();
    popularSelectsESetoresDinamicos(); 
    carregarEquipamentos('todos');
    iniciarRelogio();
    carregarFluxoLotacaoGerencia(); 
    carregarVisaoTatica();
});

// Tratamento seguro do avatar para exibir imagem ou ícone padrão sem quebrar
function aplicarAvatarGerencia(base64Img) {
    const img1 = document.getElementById("avatarPerfilDiretoria");
    const img2 = document.getElementById("avatarPerfilDiretoriaDropdown");
    
    [img1, img2].forEach(img => {
        if (img) {
            if (base64Img) {
                img.src = base64Img;
                img.style.display = "block";
                const icone = img.previousElementSibling;
                if (icone && icone.classList.contains('fallback-avatar-icon')) {
                    icone.style.display = "none";
                }
            } else {
                img.style.display = "none";
            }
        }
    });
}

// Controlar o menu flutuante de perfil (Dropdown)
function toggleMenuPerfil(event) {
    event.stopPropagation();
    const menu = document.getElementById("profileDropdownMenu");
    document.getElementById("dropdownHorariosCargos")?.classList.remove("active");
    menu?.classList.toggle("active");
}

// Controlar o menu flutuante de Horários & Cargos
function toggleMenuHorariosCargos(event) {
    event.stopPropagation();
    const menu = document.getElementById("dropdownHorariosCargos");
    document.getElementById("profileDropdownMenu")?.classList.remove("active");
    menu?.classList.toggle("active");
}

document.addEventListener("click", () => {
    document.getElementById("dropdownHorariosCargos")?.classList.remove("active");
    document.getElementById("profileDropdownMenu")?.classList.remove("active");
});

function atualizarFotoPerfil(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const base64Img = e.target.result;
            aplicarAvatarGerencia(base64Img);
            localStorage.setItem("rustic_gerencia_avatar", base64Img);
            mostrarToast("Foto de perfil atualizada com sucesso!");
        };
        reader.readAsDataURL(file);
    }
}

function fazerLogoutDiretoria() {
    if (confirm("Deseja realmente encerrar a sessão da Gerência?")) {
        window.location.href = "../auth/logout.php";
    }
}

// Relógio do cabeçalho
function iniciarRelogio() {
    const relogio = document.getElementById('relogioDiretoria');
    if (!relogio) return;
    setInterval(() => {
        const agora = new Date();
        relogio.textContent = agora.toLocaleTimeString('pt-BR');
    }, 1000);
}

// Navegação entre as abas da sidebar com atualização de título
function navegarDiretoria(destino) {
    document.querySelectorAll('.dashboard-nav-item').forEach(item => item.classList.remove('active'));
    document.querySelectorAll('.view-section').forEach(sec => sec.classList.remove('active-view'));

    const navItem = document.querySelector(`[data-target="${destino}"]`);
    const section = document.getElementById(`view-${destino}`);

    if (navItem) navItem.classList.add('active');
    if (section) section.classList.add('active-view');

    const titulosAbas = {
        'visao-operacional': 'Visão Estratégica & DRE',
        'escalas': 'Escala Mensal & Plantões',
        'equipe': 'Quadro da Equipe & Colaboradores',
        'equipamentos': 'Inventário Técnico & Parque de Máquinas',
        'setores': 'Gestão de Setores & Áreas do Ginásio',
        'lotacao': 'Fluxo & Lotação em Tempo Real'
    };

    const mainTitleEl = document.getElementById('pageMainTitle');
    if (mainTitleEl && titulosAbas[destino]) {
        mainTitleEl.textContent = titulosAbas[destino];
    }

    if (destino === 'lotacao') {
        carregarFluxoLotacaoGerencia();
    }
    if (destino === 'visao-operacional') {
        carregarVisaoTatica();
    }
}

// ==========================================
// 1. GESTÃO DE COLABORADORES (EQUIPE)
// ==========================================

function carregarEquipe(categoria = 'todos') {
    fetch(`../../app/controllers/ColaboradoresController.php?action=listar&categoria=${categoria}`)
        .then(res => res.json())
        .then(resposta => {
            if (!resposta.sucesso) return;

            const containerChips = document.querySelector('#view-equipe .filtros-chips');
            if (containerChips && resposta.contagens) {
                let htmlChips = `<button class="btn-chip ${categoria === 'todos' ? 'active' : ''}" data-filtro="todos" onclick="filtrarEquipe('todos', this)">Todos (${resposta.contagens.todos || 0})</button>`;
                
                Object.keys(resposta.contagens).forEach(cargo => {
                    if (cargo === 'todos') return;
                    const ativoClass = (categoria === cargo) ? 'active' : '';
                    htmlChips += `<button class="btn-chip ${ativoClass}" data-filtro="${cargo}" onclick="filtrarEquipe('${cargo}', this)">${cargo} (${resposta.contagens[cargo]})</button>`;
                });
                containerChips.innerHTML = htmlChips;
            }

            const tbody = document.getElementById('tabelaEquipeCorpo');
            if (!tbody) return;
            tbody.innerHTML = '';

            if (!resposta.dados || resposta.dados.length === 0) {
                tbody.innerHTML = `<tr><td colspan="7" style="text-align: center; color: var(--color-text-muted); padding: 24px;">Nenhum colaborador nesta categoria.</td></tr>`;
                return;
            }

            resposta.dados.forEach(collab => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>
                        <strong>${collab.nome}</strong><br>
                        <small style="color: var(--color-text-muted);">${collab.cref || collab.cpf}</small>
                    </td>
                    <td><span class="status-badge info">${collab.cargo}</span></td>
                    <td>${collab.turno || 'Não definido'}</td>
                    <td>${collab.telefone || collab.email}</td>
                    <td><strong>${collab.atletas_alocados || 0}</strong> atletas</td>
                    <td><span class="status-badge success">ATIVO</span></td>
                    <td style="text-align: right;">
                        <button class="btn btn-secondary btn-sm" onclick='editarColaborador(${JSON.stringify(collab)})' title="Editar">
                            <span class="material-symbols-rounded" style="font-size: 16px;">edit</span>
                        </button>
                        <button class="btn btn-secondary btn-sm" onclick="excluirColaborador(${collab.id})" title="Excluir" style="color: #ef4444;">
                            <span class="material-symbols-rounded" style="font-size: 16px;">delete</span>
                        </button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        })
        .catch(err => console.error("Erro na requisição da equipa:", err));
}

function filtrarEquipe(categoria, botaoEl) {
    document.querySelectorAll('.view-section#view-equipe .btn-chip').forEach(btn => btn.classList.remove('active'));
    botaoEl.classList.add('active');
    carregarEquipe(categoria);
}

function abrirModalNovoColaborador() {
    const modal = document.getElementById('modalEquipe');
    if (!modal) return;
    document.getElementById('modalTituloEquipe').textContent = "Cadastrar Colaborador";
    document.getElementById('formEquipe').reset();
    document.getElementById('colaboradorIdEdit').value = "";
    gerarSenhaAleatoriaColaborador();
    carregarCargosSelect();
    carregarTurnosSelect();
    modal.classList.add('active');
}

function fecharModalColaborador() {
    const modal = document.getElementById('modalEquipe');
    if (modal) modal.classList.remove('active');
}

function editarColaborador(collab) {
    const modal = document.getElementById('modalEquipe');
    if (!modal) return;
    document.getElementById('modalTituloEquipe').textContent = "Editar Colaborador";
    document.getElementById('colaboradorIdEdit').value = collab.id;
    document.getElementById('eqNome').value = collab.nome || '';
    document.getElementById('eqCpf').value = collab.cpf || '';
    carregarCargosSelect(collab.cargo); 
    carregarTurnosSelect(collab.turno);
    document.getElementById('eqCref').value = collab.cref || '';
    document.getElementById('eqTelefone').value = collab.telefone || '';
    document.getElementById('eqEmail').value = collab.email || '';
    document.getElementById('eqAlunos').value = collab.atletas_alocados || 0;
    document.getElementById('eqEspecialidade').value = collab.especialidade || '';
    document.getElementById('eqSenhaProvisoria').value = '';
    modal.classList.add('active');
}

function salvarColaborador(event) {
    event.preventDefault();

    const formData = new FormData();
    formData.append('colaborador_id_edit', document.getElementById('colaboradorIdEdit').value);
    formData.append('eqNome', document.getElementById('eqNome').value);
    formData.append('eqCpf', document.getElementById('eqCpf').value);
    formData.append('eqCargo', document.getElementById('eqCargo').value);
    formData.append('eqCref', document.getElementById('eqCref').value);
    formData.append('eqTurno', document.getElementById('eqTurno').value);
    formData.append('eqTelefone', document.getElementById('eqTelefone').value);
    formData.append('eqEmail', document.getElementById('eqEmail').value);
    formData.append('eqAlunos', document.getElementById('eqAlunos').value);
    formData.append('eqEspecialidade', document.getElementById('eqEspecialidade').value);
    formData.append('eqSenhaProvisoria', document.getElementById('eqSenhaProvisoria').value);

    fetch('../../app/controllers/ColaboradoresController.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(resposta => {
        if (resposta.sucesso) {
            mostrarToast("Colaborador salvo com sucesso!");
            fecharModalColaborador();
            carregarEquipe('todos');
        } else {
            alert("Erro ao salvar: " + resposta.erro);
        }
    });
}

function excluirColaborador(id) {
    if (!confirm("Tens a certeza que queres remover este colaborador?")) return;

    const formData = new FormData();
    formData.append('action', 'excluir');
    formData.append('id', id);

    fetch('../../app/controllers/ColaboradoresController.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(resposta => {
        if (resposta.sucesso) {
            mostrarToast("Colaborador removido!");
            carregarEquipe('todos');
        }
    });
}

function carregarCargosSelect(cargoSelecionado = '') {
    fetch('../../app/controllers/CargosController.php?action=listar')
        .then(res => res.json())
        .then(resposta => {
            if (!resposta.sucesso || !resposta.dados) return;
            const select = document.getElementById('eqCargo');
            if (!select) return;

            let html = '<option value="">Selecione o cargo...</option>';
            resposta.dados.forEach(c => {
                const sel = (c.nome === cargoSelecionado) ? 'selected' : '';
                html += `<option value="${c.nome}" ${sel}>${c.nome}</option>`;
            });
            select.innerHTML = html;
        });
}

// ==========================================
// GESTÃO DE CARGOS NO MODAL
// ==========================================
function abrirModalNovoCargo() {
    const modal = document.getElementById('modalCargo');
    if (!modal) return;
    document.getElementById('formCargo').reset();
    document.getElementById('cargoIdEdit').value = "";
    document.getElementById('modalTituloCargo').textContent = "Gerenciar Cargos";
    carregarTabelaCargosModal();
    modal.classList.add('active');
}

function fecharModalCargo() {
    const modal = document.getElementById('modalCargo');
    if (modal) modal.classList.remove('active');
    carregarCargosSelect();
}

function carregarTabelaCargosModal() {
    fetch('../../app/controllers/CargosController.php?action=listar')
        .then(res => res.json())
        .then(resposta => {
            if (!resposta.sucesso || !resposta.dados) return;
            const tbody = document.getElementById('tabelaCargosModalCorpo');
            if (!tbody) return;
            tbody.innerHTML = '';

            if (resposta.dados.length === 0) {
                tbody.innerHTML = `<tr><td colspan="2" style="text-align: center; color: var(--color-text-muted); padding: 12px;">Nenhum cargo registado.</td></tr>`;
                return;
            }

            resposta.dados.forEach(c => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td><strong>${c.nome}</strong></td>
                    <td style="text-align: right;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick='editarCargoModal(${JSON.stringify(c)})' title="Editar">
                            <span class="material-symbols-rounded" style="font-size: 14px;">edit</span>
                        </button>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="excluirCargoModal(${c.id})" title="Excluir" style="color: #ef4444;">
                            <span class="material-symbols-rounded" style="font-size: 14px;">delete</span>
                        </button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        });
}

function editarCargoModal(cargo) {
    document.getElementById('cargoIdEdit').value = cargo.id;
    document.getElementById('nomeNovoCargo').value = cargo.nome;
    document.getElementById('modalTituloCargo').textContent = "Editar Cargo";
}

function salvarNovoCargo(event) {
    event.preventDefault();
    const idEdit = document.getElementById('cargoIdEdit').value;
    const nomeCargo = document.getElementById('nomeNovoCargo').value;

    const formData = new FormData();
    formData.append('action', 'salvar');
    formData.append('id_edit', idEdit);
    formData.append('nome_cargo', nomeCargo);

    fetch('../../app/controllers/CargosController.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(resposta => {
        if (resposta.sucesso) {
            mostrarToast(resposta.mensagem || "Cargo salvo com sucesso!");
            document.getElementById('cargoIdEdit').value = "";
            document.getElementById('nomeNovoCargo').value = "";
            document.getElementById('modalTituloCargo').textContent = "Gerenciar Cargos";
            carregarTabelaCargosModal();
            carregarCargosSelect();
        } else {
            alert("Erro: " + resposta.erro);
        }
    });
}

function excluirCargoModal(id) {
    if (!confirm("Tens a certeza que queres remover este cargo?")) return;

    const formData = new FormData();
    formData.append('action', 'excluir');
    formData.append('id', id);

    fetch('../../app/controllers/CargosController.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(resposta => {
        if (resposta.sucesso) {
            mostrarToast("Cargo removido!");
            carregarTabelaCargosModal();
            carregarCargosSelect();
        } else {
            alert("Erro: " + resposta.erro);
        }
    });
}

function carregarTurnosSelect(turnoSelecionado = '') {
    fetch('../../app/controllers/TurnosController.php?action=listar')
        .then(res => res.json())
        .then(resposta => {
            if (!resposta.sucesso || !resposta.dados) return;
            const select = document.getElementById('eqTurno');
            if (!select) return;

            let html = '<option value="">Selecione o turno...</option>';
            resposta.dados.forEach(t => {
                const sel = (t.nome === turnoSelecionado) ? 'selected' : '';
                html += `<option value="${t.nome}" ${sel}>${t.nome}</option>`;
            });
            select.innerHTML = html;
        });
}

// ==========================================
// GESTÃO DE TURNOS NO MODAL
// ==========================================
function abrirModalNovoTurno() {
    const modal = document.getElementById('modalTurno');
    if (!modal) return;
    document.getElementById('formTurno').reset();
    document.getElementById('turnoIdEdit').value = "";
    document.getElementById('modalTituloTurno').textContent = "Gerenciar Turnos";
    carregarTabelaTurnosModal();
    modal.classList.add('active');
}

function fecharModalTurno() {
    const modal = document.getElementById('modalTurno');
    if (modal) modal.classList.remove('active');
    carregarTurnosSelect();
}

function carregarTabelaTurnosModal() {
    fetch('../../app/controllers/TurnosController.php?action=listar')
        .then(res => res.json())
        .then(resposta => {
            if (!resposta.sucesso || !resposta.dados) return;
            const tbody = document.getElementById('tabelaTurnosModalCorpo');
            if (!tbody) return;
            tbody.innerHTML = '';

            if (resposta.dados.length === 0) {
                tbody.innerHTML = `<tr><td colspan="2" style="text-align: center; color: var(--color-text-muted); padding: 12px;">Nenhum turno registado.</td></tr>`;
                return;
            }

            resposta.dados.forEach(t => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td><strong>${t.nome}</strong></td>
                    <td style="text-align: right;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick='editarTurnoModal(${JSON.stringify(t)})' title="Editar">
                            <span class="material-symbols-rounded" style="font-size: 14px;">edit</span>
                        </button>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="excluirTurnoModal(${t.id})" title="Excluir" style="color: #ef4444;">
                            <span class="material-symbols-rounded" style="font-size: 14px;">delete</span>
                        </button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        });
}

function editarTurnoModal(turno) {
    document.getElementById('turnoIdEdit').value = turno.id;
    document.getElementById('nomeNovoTurno').value = turno.nome;
    document.getElementById('modalTituloTurno').textContent = "Editar Turno";
}

function salvarNovoTurno(event) {
    event.preventDefault();
    const idEdit = document.getElementById('turnoIdEdit').value;
    const nomeTurno = document.getElementById('nomeNovoTurno').value;

    const formData = new FormData();
    formData.append('action', 'salvar');
    formData.append('id_edit', idEdit);
    formData.append('nome_turno', nomeTurno);

    fetch('../../app/controllers/TurnosController.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(resposta => {
        if (resposta.sucesso) {
            mostrarToast(resposta.mensagem || "Turno salvo com sucesso!");
            document.getElementById('turnoIdEdit').value = "";
            document.getElementById('nomeNovoTurno').value = "";
            document.getElementById('modalTituloTurno').textContent = "Gerenciar Turnos";
            carregarTabelaTurnosModal();
            carregarTurnosSelect();
        } else {
            alert("Erro: " + resposta.erro);
        }
    });
}

function excluirTurnoModal(id) {
    if (!confirm("Tens a certeza que queres remover este turno?")) return;

    const formData = new FormData();
    formData.append('action', 'excluir');
    formData.append('id', id);

    fetch('../../app/controllers/TurnosController.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(resposta => {
        if (resposta.sucesso) {
            mostrarToast("Turno removido!");
            carregarTabelaTurnosModal();
            carregarTurnosSelect();
        } else {
            alert("Erro: " + resposta.erro);
        }
    });
}

function carregarEquipamentos(setor = 'todos') {
    fetch(`../../app/controllers/EquipamentosController.php?action=listar&setor=${setor}`)
        .then(res => res.json())
        .then(resposta => {
            if (!resposta.sucesso) return;
            const tbody = document.getElementById('tabelaEquipamentosCorpo');
            if (!tbody) return;
            tbody.innerHTML = '';

            if (!resposta.dados || resposta.dados.length === 0) {
                tbody.innerHTML = `<tr><td colspan="7" style="text-align: center; color: var(--color-text-muted); padding: 24px;">Nenhum equipamento registado.</td></tr>`;
                return;
            }

            resposta.dados.forEach(eqp => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>
                        <strong>${eqp.nome}</strong><br>
                        <small style="color: var(--color-text-muted);">${eqp.codigo}</small>
                    </td>
                    <td><span class="status-badge info">${eqp.setor}</span></td>
                    <td><strong>${eqp.quantidade}</strong> un.</td>
                    <td>${eqp.ultima_revisao || 'Não informada'}</td>
                    <td>${eqp.proxima_revisao || 'Não programada'}</td>
                    <td><span class="status-badge success">${eqp.estado}</span></td>
                    <td style="text-align: right;">
                        <button class="btn btn-secondary btn-sm" onclick='editarEquipamento(${JSON.stringify(eqp)})' title="Editar">
                            <span class="material-symbols-rounded" style="font-size: 16px;">edit</span>
                        </button>
                        <button class="btn btn-secondary btn-sm" onclick="excluirEquipamento(${eqp.id})" title="Excluir" style="color: #ef4444;">
                            <span class="material-symbols-rounded" style="font-size: 16px;">delete</span>
                        </button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        });
}

function filtrarEquipamentos(setor, botaoEl) {
    document.querySelectorAll('#view-equipamentos .filtros-chips .btn-chip').forEach(btn => btn.classList.remove('active'));
    botaoEl.classList.add('active');
    carregarEquipamentos(setor);
}

function abrirModalNovoEquipamento() {
    const modal = document.getElementById('modalEquipamento');
    if (!modal) return;
    document.getElementById('modalTituloEquipamento').textContent = "Cadastrar Equipamento";
    document.getElementById('formEquipamento').reset();
    document.getElementById('equipamentoIdEdit').value = "";
    modal.classList.add('active');
}

function fecharModalEquipamento() {
    const modal = document.getElementById('modalEquipamento');
    if (modal) modal.classList.remove('active');
}

function editarEquipamento(eqp) {
    const modal = document.getElementById('modalEquipamento');
    if (!modal) return;
    document.getElementById('modalTituloEquipamento').textContent = "Editar Equipamento";
    document.getElementById('equipamentoIdEdit').value = eqp.id;
    document.getElementById('eqpNome').value = eqp.nome || '';
    document.getElementById('eqpCodigo').value = eqp.codigo || '';
    document.getElementById('eqpSetor').value = eqp.setor || '';
    document.getElementById('eqpQuantidade').value = eqp.quantidade || 1;
    document.getElementById('eqpEstado').value = eqp.estado || 'Excelente';
    document.getElementById('eqpUltimaRevisao').value = eqp.ultima_revisao || '';
    document.getElementById('eqpProximaRevisao').value = eqp.proxima_revisao || '';
    document.getElementById('eqpObservacoes').value = eqp.observacoes || '';
    modal.classList.add('active');
}

function salvarEquipamento(event) {
    event.preventDefault();
    const formData = new FormData();
    formData.append('action', 'salvar');
    formData.append('equipamento_id_edit', document.getElementById('equipamentoIdEdit').value);
    formData.append('eqpNome', document.getElementById('eqpNome').value);
    formData.append('eqpCodigo', document.getElementById('eqpCodigo').value);
    formData.append('eqpSetor', document.getElementById('eqpSetor').value);
    formData.append('eqpQuantidade', document.getElementById('eqpQuantidade').value);
    formData.append('eqpEstado', document.getElementById('eqpEstado').value);
    formData.append('eqpUltimaRevisao', document.getElementById('eqpUltimaRevisao').value);
    formData.append('eqpProximaRevisao', document.getElementById('eqpProximaRevisao').value);
    formData.append('eqpObservacoes', document.getElementById('eqpObservacoes').value);

    fetch('../../app/controllers/EquipamentosController.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(resposta => {
            if (resposta.sucesso) {
                mostrarToast("Equipamento salvo com sucesso!");
                fecharModalEquipamento();
                carregarEquipamentos('todos');
            } else {
                alert("Erro ao salvar equipamento: " + (resposta.erro || "Erro desconhecido"));
            }
        })
        .catch(err => {
            console.error("Erro na requisição:", err);
            alert("Erro de comunicação com o servidor.");
        });
}

function excluirEquipamento(id) {
    if (!confirm("Remover este equipamento?")) return;
    const formData = new FormData();
    formData.append('action', 'excluir');
    formData.append('id', id);
    fetch('../../app/controllers/EquipamentosController.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(resposta => {
            if (resposta.sucesso) {
                mostrarToast("Removido!");
                carregarEquipamentos('todos');
            }
        });
}

function carregarSetores() {
    fetch('../../app/controllers/SetoresController.php?action=listar')
        .then(res => res.json())
        .then(resposta => {
            if (!resposta.sucesso) return;
            const tbody = document.getElementById('tabelaSetoresCorpo');
            if (!tbody) return;
            tbody.innerHTML = '';

            if (!resposta.dados || resposta.dados.length === 0) {
                tbody.innerHTML = `<tr><td colspan="5" style="text-align: center; color: var(--color-text-muted); padding: 24px;">Nenhum setor registado.</td></tr>`;
                return;
            }

            resposta.dados.forEach(setor => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td><strong>${setor.nome}</strong></td>
                    <td>${setor.responsavel}</td>
                    <td><strong>${setor.capacidade}</strong> atletas</td>
                    <td><span class="status-badge ${setor.status === 'ATIVO' ? 'success' : 'warning'}">${setor.status}</span></td>
                    <td style="text-align: right;">
                        <button class="btn btn-secondary btn-sm" onclick='editarSetor(${JSON.stringify(setor)})' title="Editar">
                            <span class="material-symbols-rounded" style="font-size: 16px;">edit</span>
                        </button>
                        <button class="btn btn-secondary btn-sm" onclick="excluirSetor(${setor.id})" title="Excluir" style="color: #ef4444;">
                            <span class="material-symbols-rounded" style="font-size: 16px;">delete</span>
                        </button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        });
}

function popularSelectsESetoresDinamicos() {
    fetch('../../app/controllers/SetoresController.php?action=listar')
        .then(res => res.json())
        .then(resposta => {
            if (!resposta.sucesso || !resposta.dados) return;
            const setores = resposta.dados;

            const containerFiltros = document.querySelector('#view-equipamentos .filtros-chips');
            if (containerFiltros) {
                let htmlFiltros = `<button class="btn-chip active" data-setor="todos" onclick="filtrarEquipamentos('todos', this)">Todos Setores</button>`;
                setores.forEach(s => {
                    htmlFiltros += `<button class="btn-chip" data-setor="${s.nome}" onclick="filtrarEquipamentos('${s.nome}', this)">${s.nome}</button>`;
                });
                containerFiltros.innerHTML = htmlFiltros;
            }

            const selectModal = document.getElementById('eqpSetor');
            if (selectModal) {
                let htmlOptions = '';
                setores.forEach(s => {
                    htmlOptions += `<option value="${s.nome}">${s.nome}</option>`;
                });
                selectModal.innerHTML = htmlOptions;
            }
        });
}

function abrirModalNovoSetor() {
    const modal = document.getElementById('modalSetor');
    if (!modal) return;
    document.getElementById('modalTituloSetor').textContent = "Cadastrar Setor";
    document.getElementById('formSetor').reset();
    document.getElementById('setorIdEdit').value = "";
    modal.classList.add('active');
}

function fecharModalSetor() {
    const modal = document.getElementById('modalSetor');
    if (modal) modal.classList.remove('active');
}

function editarSetor(setor) {
    const modal = document.getElementById('modalSetor');
    if (!modal) return;
    document.getElementById('modalTituloSetor').textContent = "Editar Setor";
    document.getElementById('setorIdEdit').value = setor.id;
    document.getElementById('setorNome').value = setor.nome || '';
    document.getElementById('setorResponsavel').value = setor.responsavel || '';
    document.getElementById('setorCapacidade').value = setor.capacidade || 20;
    document.getElementById('setorStatus').value = setor.status || 'ATIVO';
    modal.classList.add('active');
}

function salvarSetor(event) {
    event.preventDefault();
    const formData = new FormData();
    formData.append('action', 'salvar');
    formData.append('id_edit', document.getElementById('setorIdEdit').value);
    formData.append('nome', document.getElementById('setorNome').value);
    formData.append('responsavel', document.getElementById('setorResponsavel').value);
    formData.append('capacidade', document.getElementById('setorCapacidade').value);
    formData.append('status', document.getElementById('setorStatus').value);

    fetch('../../app/controllers/SetoresController.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(resposta => {
            if (resposta.sucesso) {
                mostrarToast("Setor salvo com sucesso!");
                fecharModalSetor();
                carregarSetores();
                popularSelectsESetoresDinamicos(); 
            } else {
                alert("Erro: " + resposta.erro);
            }
        });
}

function excluirSetor(id) {
    if (!confirm("Remover este setor?")) return;
    const formData = new FormData();
    formData.append('action', 'excluir');
    formData.append('id', id);
    fetch('../../app/controllers/SetoresController.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(resposta => {
            if (resposta.sucesso) {
                mostrarToast("Setor removido!");
                carregarSetores();
                popularSelectsESetoresDinamicos(); 
            }
        });
}

async function carregarFluxoLotacaoGerencia() {
    try {
        const res = await fetch('../../app/controllers/FluxoController.php?action=dados_lotacao');
        const data = await res.json();

        if (data.sucesso) {
            const gaugeBadge = document.querySelector('.gauge-badge');
            if (gaugeBadge) {
                gaugeBadge.innerHTML = `
                    <span class="material-symbols-rounded" style="color:var(--color-primary);">sensors</span>
                    <strong>${data.total_geral_ocupacao} / ${data.capacidade_total_geral} Limite Técnico</strong>
                `;
            }

            const zonasGrid = document.querySelector('#view-lotacao .zonas-grid');
            if (!zonasGrid) return;
            zonasGrid.innerHTML = '';

            if (!data.zonas || data.zonas.length === 0) {
                zonasGrid.innerHTML = `<p style="color: var(--color-text-muted); padding: 20px;">Nenhum setor registado na tabela setores.</p>`;
                return;
            }

            data.zonas.forEach(zona => {
                const desligado = zona.monitoramento_ativo === 0;

                const card = document.createElement('div');
                card.className = `zona-card ${desligado ? 'desativado' : ''}`;
                card.style = "background: #1e1e1e; border: 1px solid #333; padding: 16px; border-radius: 8px; color: #fff;";

                card.innerHTML = `
                    <div class="zona-top" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <strong style="font-size: 0.95rem;">${zona.nome}</strong>
                        <span class="status-badge ${desligado ? 'warning' : (zona.porcentagem > 80 ? 'warning' : 'success')}">
                            ${desligado ? 'Desativado' : `${zona.ocupados} / ${zona.capacidade} vagas`}
                        </span>
                    </div>

                    ${!desligado ? `
                        <div class="progress-bar-bg" style="background: #333; border-radius: 4px; height: 8px; margin: 10px 0; overflow: hidden;">
                            <div class="progress-bar-fill" style="background: ${zona.porcentagem > 80 ? '#ef4444' : 'var(--color-primary)'}; width: ${zona.porcentagem}%; height: 100%;"></div>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 0.78rem; color: var(--color-text-secondary);">
                            <span>${zona.porcentagem}% ocupação</span>
                            <span>${zona.vagas_disponiveis} vagas livres</span>
                        </div>
                    ` : `<p style="font-size: 0.78rem; color: #666; margin: 10px 0 0 0;">Controle de lotação desligado para esta área.</p>`}

                    <div style="margin-top: 14px; border-top: 1px solid #333; padding-top: 10px; display: flex; align-items: center; justify-content: space-between; font-size: 0.82rem;">
                        <span>Monitorar Lotação:</span>
                        <input type="checkbox" ${zona.monitoramento_ativo === 1 ? 'checked' : ''} onchange="alternarMonitoramentoArea(${zona.id}, ${zona.monitoramento_ativo})" style="cursor: pointer; accent-color: var(--color-primary); width: 16px; height: 16px;">
                    </div>
                `;
                zonasGrid.appendChild(card);
            });
        }
    } catch (e) {
        console.error("Erro ao carregar fluxo e lotação:", e);
    }
}

setInterval(() => {
    const secLotacao = document.getElementById('view-lotacao');
    if (secLotacao && secLotacao.classList.contains('active-view')) {
        carregarFluxoLotacaoGerencia();
    }
}, 5000);

async function alternarMonitoramentoArea(idSetor, statusAtual) {
    const formData = new FormData();
    formData.append('action', 'toggle_monitoramento');
    formData.append('id', idSetor);
    formData.append('status_atual', statusAtual);

    try {
        const res = await fetch('../../app/controllers/FluxoController.php', {
            method: 'POST',
            body: formData
        });
        const json = await res.json();

        if (json.sucesso) {
            mostrarToast("Monitoramento da área atualizado!");
            carregarFluxoLotacaoGerencia();
        } else {
            alert(json.erro || "Erro ao alterar estado.");
        }
    } catch (err) {
        console.error("Erro AJAX toggle:", err);
    }
}

// ==========================================
// 2. VISÃO TÁTICA (DINÂMICA) - SEM VALORES FIXOS
// ==========================================

function carregarVisaoTatica() {
    const kpiEquipe = document.getElementById('kpiEquipePresente');
    const kpiOcupacao = document.getElementById('kpiOcupacaoPercent');
    const kpiDesc = document.getElementById('kpiOcupacaoDesc');
    const kpiMaquinas = document.getElementById('kpiMaquinasOperacionais');
    const kpiMaquinasDesc = document.getElementById('kpiMaquinasDesc');
    const kpiAlertas = document.getElementById('kpiAlertasQtd');
    const kpiAlertasDesc = document.getElementById('kpiAlertasDesc');

    if (kpiEquipe) kpiEquipe.textContent = "...";
    if (kpiOcupacao) kpiOcupacao.textContent = "...";
    if (kpiDesc) kpiDesc.textContent = "A carregar dados do ginásio...";
    if (kpiMaquinas) kpiMaquinas.textContent = "...";
    if (kpiMaquinasDesc) kpiMaquinasDesc.textContent = "A carregar inventário...";
    if (kpiAlertas) kpiAlertas.textContent = "...";
    if (kpiAlertasDesc) kpiAlertasDesc.textContent = "A verificar alertas...";

    fetch('../../app/controllers/VisaoTaticaController.php?action=dados_taticos')
        .then(res => res.json())
        .then(resposta => {
            if (!resposta.sucesso) return;

            if (resposta.kpis) {
                if (kpiEquipe) kpiEquipe.textContent = resposta.kpis.equipe_presente;
                if (kpiOcupacao) kpiOcupacao.textContent = resposta.kpis.ocupacao_percent + '%';
                if (kpiDesc) {
                    const alunos = resposta.kpis.alunos_presentes || 0;
                    const cap = resposta.kpis.capacidade_total || 120;
                    let statusFluxo = "Fluxo Confortável";
                    if (resposta.kpis.ocupacao_percent > 80) statusFluxo = "Fluxo Intenso";
                    else if (resposta.kpis.ocupacao_percent > 50) statusFluxo = "Fluxo Moderado";
                    kpiDesc.textContent = `${alunos} de ${cap} alunos (${statusFluxo})`;
                }
                if (kpiMaquinas) kpiMaquinas.textContent = resposta.kpis.parque_percent + '%';
                if (kpiMaquinasDesc) {
                    const operacionais = resposta.kpis.maquinas_operacionais || 0;
                    const manutencao = resposta.kpis.maquinas_manutencao || 0;
                    kpiMaquinasDesc.textContent = `${operacionais} operacionais | ${manutencao} preventivas`;
                }
                if (kpiAlertas) kpiAlertas.textContent = resposta.kpis.alertas_qtd;
                if (kpiAlertasDesc) {
                    if (resposta.kpis.alertas_qtd > 0 && resposta.manutencoes && resposta.manutencoes.length > 0) {
                        kpiAlertasDesc.textContent = `Revisão ${resposta.manutencoes[0].nome} pendente`;
                    } else {
                        kpiAlertasDesc.textContent = `Nenhum alerta pendente no momento`;
                    }
                }
            }

            // Tabela: Escala de Professores & Recepção Hoje
            const tbodyEscala = document.getElementById('tabelaResumoEscala');
            if (tbodyEscala) {
                tbodyEscala.innerHTML = '';
                if (!resposta.escala || resposta.escala.length === 0) {
                    tbodyEscala.innerHTML = `<tr><td colspan="4" style="text-align: center; color: var(--color-text-muted); padding: 16px;">Nenhum profissional registado para hoje.</td></tr>`;
                } else {
                    resposta.escala.forEach(item => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td><strong>${item.nome}</strong></td>
                            <td><span class="status-badge info">${item.cargo}</span></td>
                            <td>${item.turno || 'Turno Geral'}</td>
                            <td><span class="status-badge success">${item.status || 'Presente'}</span></td>
                        `;
                        tbodyEscala.appendChild(tr);
                    });
                }
            }

            // Tabela: Preventivas & Manutenções Próximas
            const tbodyManutencao = document.getElementById('tabelaResumoManutencao');
            if (tbodyManutencao) {
                tbodyManutencao.innerHTML = '';
                if (!resposta.manutencoes || resposta.manutencoes.length === 0) {
                    tbodyManutencao.innerHTML = `<tr><td colspan="4" style="text-align: center; color: var(--color-text-muted); padding: 16px;">Nenhuma preventiva programada.</td></tr>`;
                } else {
                    resposta.manutencoes.forEach(eq => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td><strong>${eq.nome}</strong></td>
                            <td>${eq.setor}</td>
                            <td>${eq.proxima_revisao ? eq.proxima_revisao.split('-').reverse().join('/') : 'Não informada'}</td>
                            <td><span class="status-badge warning">${eq.estado || 'Revisão'}</span></td>
                        `;
                        tbodyManutencao.appendChild(tr);
                    });
                }
            }
        })
        .catch(err => {
            console.error("Erro ao carregar visão tática:", err);
            if (kpiDesc) kpiDesc.textContent = "Erro ao comunicar com o servidor.";
        });
}


// ==========================================
// 3. ESCALAS MENSAIS & MATRIZ INLINE
// ==========================================

let mesAnoGeralInlineAtual = new Date().toISOString().slice(0, 7);

document.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => {
        carregarMatrizEscalaInline(mesAnoGeralInlineAtual);
    }, 300);
});

function mudarMesGeralInline(novoMes) {
    if (!novoMes) return;
    mesAnoGeralInlineAtual = novoMes;
    carregarMatrizEscalaInline(novoMes);
}

function carregarMatrizEscalaInline(mesAno) {
    const container = document.getElementById('tabelaGeralContainerInline');
    if (!container) return;

    container.innerHTML = `<div style="padding: 40px; text-align: center; color: #a1a1aa;">A carregar escala geral do mês...</div>`;

    fetch(`../../app/controllers/VisaoTaticaController.php?action=escala_geral_mes&mes_ano=${mesAno}`)
        .then(res => res.json())
        .then(resposta => {
            if (!resposta.sucesso) {
                container.innerHTML = `<div style="padding: 40px; text-align: center; color: #ef4444;">Erro ao carregar dados do servidor.</div>`;
                return;
            }

            const diasNoMes = resposta.total_dias;
            const colaboradores = resposta.colaboradores;
            const escalasMap = resposta.escalas_map;

            if (!colaboradores || colaboradores.length === 0) {
                container.innerHTML = `<div style="padding: 40px; text-align: center; color: #a1a1aa;">Nenhum colaborador registado na base de dados.</div>`;
                return;
            }

            let html = `
                <table style="width: 100%; border-collapse: collapse; text-align: center; font-size: 0.8rem; white-space: nowrap;">
                    <thead>
                        <tr style="background: #27272a; position: sticky; top: 0; z-index: 5;">
                            <th style="padding: 12px 14px; text-align: left; border-bottom: 2px solid #3f3f46; min-width: 180px; position: sticky; left: 0; background: #27272a; z-index: 6;">Colaborador</th>
            `;

            for (let d = 1; d <= diasNoMes; d++) {
                const dataStr = `${mesAno}-${String(d).padStart(2, '0')}`;
                html += `
                    <th style="padding: 10px 4px; border-bottom: 2px solid #3f3f46; min-width: 60px; position: relative;">
                        <div style="display: flex; align-items: center; justify-content: center; gap: 4px;">
                            <span>${d}</span>
                            <span onclick="toggleOpcoesDiaCabecalho(event, '${dataStr}')" style="cursor: pointer; font-size: 13px; color: #a1a1aa;" title="Opções do Dia (Feriado / Escala Normal)">⚙️</span>
                        </div>
                    </th>`;
            }

            html += `</tr></thead><tbody>`;

            colaboradores.forEach(c => {
                html += `<tr>
                    <td style="padding: 10px 14px; text-align: left; font-weight: bold; background: #18181b; border-bottom: 1px solid #27272a; position: sticky; left: 0; z-index: 4;">${c.nome}</td>`;

                for (let d = 1; d <= diasNoMes; d++) {
                    const dataStr = `${mesAno}-${String(d).padStart(2, '0')}`;
                    const statusDia = escalasMap[c.id]?.[dataStr] || 'Folga';
                    
                    let corBadge = '#27272a';
                    let corTexto = '#a1a1aa';
                    if (statusDia !== 'Folga') {
                        corBadge = '#065f46';
                        corTexto = '#34d399';
                    }
                    if (statusDia.includes('Atestado') || statusDia.includes('Feriado') || statusDia.includes('Dedetização')) {
                        corBadge = '#78350f';
                        corTexto = '#fbbf24';
                    }

                    html += `
                        <td style="padding: 6px 4px; border-bottom: 1px solid #27272a; border-right: 1px solid #27272a;">
                            <div onclick="editarDiaInline(${c.id}, '${dataStr}', '${statusDia}', '${c.nome}')" 
                                 style="background: ${corBadge}; color: ${corTexto}; padding: 6px 6px; border-radius: 4px; cursor: pointer; font-weight: 600; font-size: 0.72rem; overflow: hidden; text-overflow: ellipsis;" 
                                 title="${c.nome} - ${dataStr}: ${statusDia}">
                                ${statusDia}
                            </div>
                        </td>
                    `;
                }
                html += `</tr>`;
            });

            html += `</tbody></table>`;
            container.innerHTML = html;
        })
        .catch(err => {
            console.error("Erro na matriz inline:", err);
            container.innerHTML = `<div style="padding: 40px; text-align: center; color: #ef4444;">Erro de conexão com o servidor.</div>`;
        });
}

function toggleOpcoesDiaCabecalho(event, dataStr) {
    event.stopPropagation();
    let menuAntigo = document.getElementById('menuOpcoesDia');
    if (menuAntigo) menuAntigo.remove();

    const rect = event.target.getBoundingClientRect();
    const menu = document.createElement('div');
    menu.id = 'menuOpcoesDia';
    menu.style.cssText = `position: fixed; top: ${rect.bottom + 5}px; left: ${rect.left}px; background: #18181b; border: 1px solid #3f3f46; border-radius: 6px; padding: 6px; z-index: 99999; box-shadow: 0 4px 12px rgba(0,0,0,0.5); display: flex; flex-direction: column; gap: 4px;`;

    menu.innerHTML = `
        <button type="button" onclick="aplicarOpcaoGlobalDia('${dataStr}', 'Feriado')" style="background: #78350f; color: #fbbf24; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 0.75rem; text-align: left; font-weight: bold;">Marcar Feriado (Geral)</button>
        <button type="button" onclick="resetarDiaGlobal('${dataStr}')" style="background: #065f46; color: #34d399; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 0.75rem; text-align: left; font-weight: bold;">Voltar à Escala Normal</button>
    `;

    document.body.appendChild(menu);

    document.addEventListener('click', function fecharMenu(e) {
        if (!menu.contains(e.target)) {
            menu.remove();
            document.removeEventListener('click', fecharMenu);
        }
    });
}

function aplicarOpcaoGlobalDia(dataStr, novoStatus) {
    let menu = document.getElementById('menuOpcoesDia');
    if (menu) menu.remove();

    fetch(`../../app/controllers/VisaoTaticaController.php?action=escala_geral_mes&mes_ano=${mesAnoGeralInlineAtual}`)
        .then(res => res.json())
        .then(resposta => {
            if (!resposta.sucesso || !resposta.colaboradores) return;

            let promessas = resposta.colaboradores.map(c => {
                const formData = new FormData();
                formData.append('action', 'salvar_excecao_dia');
                formData.append('colaborador_id', c.id);
                formData.append('data_especifica', dataStr);
                formData.append('novo_status', novoStatus);
                formData.append('motivo', 'Ajuste em massa via cabeçalho do dia');

                return fetch('../../app/controllers/VisaoTaticaController.php', { method: 'POST', body: formData });
            });

            Promise.all(promessas).then(() => {
                mostrarToast(`Dia ${dataStr.split('-').reverse().join('/')} atualizado para ${novoStatus}!`);
                carregarMatrizEscalaInline(mesAnoGeralInlineAtual);
            });
        });
}

function resetarDiaGlobal(dataStr) {
    let menu = document.getElementById('menuOpcoesDia');
    if (menu) menu.remove();

    fetch(`../../app/controllers/VisaoTaticaController.php?action=escala_geral_mes&mes_ano=${mesAnoGeralInlineAtual}`)
        .then(res => res.json())
        .then(resposta => {
            if (!resposta.sucesso || !resposta.colaboradores) return;

            let promessas = resposta.colaboradores.map(c => {
                const formData = new FormData();
                formData.append('action', 'remover_excecao_dia');
                formData.append('colaborador_id', c.id);
                formData.append('data_especifica', dataStr);

                return fetch('../../app/controllers/VisaoTaticaController.php', { method: 'POST', body: formData });
            });

            Promise.all(promessas).then(() => {
                mostrarToast(`Dia ${dataStr.split('-').reverse().join('/')} voltou à escala normal!`);
                carregarMatrizEscalaInline(mesAnoGeralInlineAtual);
            });
        });
}

function editarDiaInline(colaboradorId, data, statusAtual, nomeColaborador) {
    let popAntigo = document.getElementById('popoverSelecaoTurno');
    if (popAntigo) popAntigo.remove();

    fetch('../../app/controllers/TurnosController.php?action=listar')
        .then(res => res.json())
        .then(resposta => {
            let listaTurnosDinamicos = ["Folga", "Feriado", "Atestado / Doença", "Dedetização / Manutenção Geral"];
            if (resposta.sucesso && resposta.dados) {
                resposta.dados.forEach(t => {
                    if (!listaTurnosDinamicos.includes(t.nome)) listaTurnosDinamicos.push(t.nome);
                });
            }

            const popover = document.createElement('div');
            popover.id = 'popoverSelecaoTurno';
            popover.className = 'modal-overlay active';
            popover.style.cssText = "display: flex; align-items: center; justify-content: center; z-index: 9999;";

            let botoesHtml = '';
            listaTurnosDinamicos.forEach(opcao => {
                const isAtual = opcao === statusAtual;
                botoesHtml += `
                    <button type="button" onclick="salvarNovoTurnoInline(${colaboradorId}, '${data}', '${opcao}')" 
                        style="background: ${isAtual ? 'var(--color-primary)' : '#27272a'}; color: #fff; border: 1px solid #3f3f46; padding: 10px 14px; border-radius: 6px; cursor: pointer; text-align: left; font-size: 0.85rem; font-weight: 500; transition: 0.2s;"
                        onmouseover="this.style.background='var(--color-primary)'" onmouseout="this.style.background='${isAtual ? 'var(--color-primary)' : '#27272a'}';">
                        ${opcao} ${isAtual ? ' ✓' : ''}
                    </button>
                `;
            });

            popover.innerHTML = `
                <div class="modal-card" style="width: 400px; max-width: 95%; background: #18181b; border: 1px solid #27272a; border-radius: 12px; padding: 20px; color: #fff; box-shadow: 0 10px 25px rgba(0,0,0,0.6);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; border-bottom: 1px solid #27272a; padding-bottom: 8px;">
                        <div>
                            <h4 style="margin: 0; font-size: 1rem; color: #f43f5e;">Selecionar Turno / Estado</h4>
                            <small style="color: #a1a1aa;">${nomeColaborador} &bull; ${data.split('-').reverse().join('/')}</small>
                        </div>
                        <button onclick="document.getElementById('popoverSelecaoTurno').remove()" style="background: none; border: none; color: #a1a1aa; cursor: pointer; font-size: 1.4rem;">&times;</button>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 8px; max-height: 320px; overflow-y: auto; padding-right: 4px;">
                        ${botoesHtml}
                    </div>
                    <div style="margin-top: 14px; text-align: right;">
                        <button type="button" onclick="document.getElementById('popoverSelecaoTurno').remove()" class="btn btn-secondary btn-sm">Cancelar</button>
                    </div>
                </div>
            `;
            document.body.appendChild(popover);
        });
}

function salvarNovoTurnoInline(colaboradorId, data, novoStatus) {
    const formData = new FormData();
    formData.append('action', 'salvar_excecao_dia');
    formData.append('colaborador_id', colaboradorId);
    formData.append('data_especifica', data);
    formData.append('novo_status', novoStatus);
    formData.append('motivo', 'Ajuste manual via visão geral inline');

    fetch('../../app/controllers/VisaoTaticaController.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(resposta => {
            if (resposta.sucesso) {
                mostrarToast("Turno atualizado com sucesso!");
                let pop = document.getElementById('popoverSelecaoTurno');
                if (pop) pop.remove();
                carregarMatrizEscalaInline(mesAnoGeralInlineAtual);
            } else {
                alert("Erro ao atualizar: " + resposta.erro);
            }
        });
}


// ==========================================
// 4. RELATÓRIO APENAS DE QUEM VAI TRABALHAR EM FDS / FERIADOS
// ==========================================

function abrirRelatorioPlantoesFDS() {
    const mesAno = document.getElementById('inputMesGeralInline')?.value || mesAnoGeralInlineAtual;
    
    fetch(`../../app/controllers/VisaoTaticaController.php?action=escala_geral_mes&mes_ano=${mesAno}`)
        .then(res => res.json())
        .then(resposta => {
            if (!resposta.sucesso) {
                alert("Erro ao gerar relatório de plantões.");
                return;
            }

            const diasNoMes = resposta.total_dias;
            const colaboradores = resposta.colaboradores;
            const escalasMap = resposta.escalas_map;

            let plantoesFDS = [];
            let textoWhatsapp = `📋 *ESCALA DE PLANTÕES (FDS E FERIADOS) - ${mesAno.split('-').reverse().join('/')}*\n\n`;

            colaboradores.forEach(c => {
                for (let d = 1; d <= diasNoMes; d++) {
                    const dataStr = `${mesAno}-${String(d).padStart(2, '0')}`;
                    const dataObj = new Date(mesAno.split('-')[0], mesAno.split('-')[1] - 1, d);
                    const diaSemana = dataObj.getDay(); // 0 = Domingo, 6 = Sábado

                    const statusDia = escalasMap[c.id]?.[dataStr] || 'Folga';

                    const ehFDS = (diaSemana === 0 || diaSemana === 6);

                    if (statusDia !== 'Folga' && (ehFDS || statusDia.includes('Feriado'))) {
                        const diaFormatado = String(d).padStart(2, '0') + '/' + mesAno.split('-')[1];
                        plantoesFDS.push({
                            nome: c.nome,
                            dia: diaFormatado,
                            horario: statusDia
                        });
                    }
                }
            });

            plantoesFDS.sort((a, b) => a.dia.localeCompare(b.dia));

            if (plantoesFDS.length === 0) {
                textoWhatsapp += `Nenhum plantão registado para os fins de semana ou feriados deste mês.\n`;
            } else {
                plantoesFDS.forEach(p => {
                    textoWhatsapp += `👤 ${p.nome} - 📅 ${p.dia} - ⏰ ${p.horario}\n`;
                });
            }

            textoWhatsapp += `\n_Gerado automaticamente pelo Sistema Rustic Iron_`;

            let modalRel = document.getElementById('modalRelatorioPlantoes');
            if (!modalRel) {
                modalRel = document.createElement('div');
                modalRel.id = 'modalRelatorioPlantoes';
                modalRel.className = 'modal-overlay';
                document.body.appendChild(modalRel);
            }

            modalRel.innerHTML = `
                <div class="modal-card" style="width: 550px; max-width: 95%; background: #18181b; border: 1px solid #27272a; border-radius: 12px; padding: 24px; color: #fff; box-shadow: 0 10px 30px rgba(0,0,0,0.7);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid #27272a; padding-bottom: 10px;">
                        <h3 style="margin: 0; font-size: 1.1rem; color: #34d399;">Plantões de Fim de Semana & Feriados</h3>
                        <button onclick="document.getElementById('modalRelatorioPlantoes').classList.remove('active')" style="background: none; border: none; color: #a1a1aa; cursor: pointer; font-size: 1.5rem;">&times;</button>
                    </div>
                    <p style="font-size: 0.82rem; color: #a1a1aa; margin-bottom: 12px;">Lista estrita apenas de quem irá trabalhar em fins de semana ou feriados:</p>
                    <textarea id="textoCopiaWhatsapp" rows="12" style="width: 100%; background: #121214; border: 1px solid #3f3f46; color: #34d399; padding: 12px; border-radius: 6px; font-family: monospace; font-size: 0.85rem; resize: none; outline: none;" readonly>${textoWhatsapp}</textarea>
                    <div style="margin-top: 16px; display: flex; justify-content: flex-end; gap: 10px;">
                        <button type="button" class="btn btn-secondary" onclick="document.getElementById('modalRelatorioPlantoes').classList.remove('active')">Fechar</button>
                        <button type="button" class="btn btn-primary" onclick="copiarTextoWhatsapp()" style="background: #059669; border-color: #059669;">
                            <span class="material-symbols-rounded" style="font-size: 16px; vertical-align: middle;">content_copy</span> Copiar para o WhatsApp
                        </button>
                    </div>
                </div>
            `;
            modalRel.classList.add('active');
        });
}

function copiarTextoWhatsapp() {
    const textarea = document.getElementById('textoCopiaWhatsapp');
    if (!textarea) return;
    textarea.select();
    navigator.clipboard.writeText(textarea.value).then(() => {
        mostrarToast("Escala copiada para a área de transferência!");
        document.getElementById('modalRelatorioPlantoes').classList.remove('active');
    }).catch(err => {
        alert("Erro ao copiar texto.");
    });
}


// ==========================================
// 5. OUTRAS FUNÇÕES DE APOIO E MODAIS
// ==========================================

function carregarColaboradoresSelectEscala() {
    fetch('../../app/controllers/ColaboradoresController.php?action=listar&categoria=todos')
        .then(res => res.json())
        .then(resposta => {
            if (!resposta.sucesso || !resposta.dados) return;
            const selectAusente = document.getElementById('trocaAusente');
            const selectSubstituto = document.getElementById('trocaSubstituto');

            let html = '<option value="">Selecione o profissional...</option>';
            resposta.dados.forEach(c => {
                html += `<option value="${c.id}">${c.nome} (${c.cargo})</option>`;
            });

            if (selectAusente) selectAusente.innerHTML = html;
            if (selectSubstituto) selectSubstituto.innerHTML = html;
        });
}

function abrirModalNovaEscalaMensal() {
    const modal = document.getElementById('modalEscalaMensal');
    if (!modal) return;
    document.getElementById('modalTituloEscalaMensal').textContent = "Configurar Escala Mensal Fixa";
    document.getElementById('formEscalaMensal').reset();
    document.getElementById('escalaMensalIdEdit').value = "";
    document.getElementById('emMesAno').value = new Date().toISOString().slice(0, 7);
    carregarColaboradoresSelectEscala();
    modal.classList.add('active');
}

function fecharModalEscalaMensal() {
    const modal = document.getElementById('modalEscalaMensal');
    if (modal) modal.classList.remove('active');
}

function salvarEscalaMensal(event) {
    event.preventDefault();
    const formData = new FormData();
    formData.append('action', 'salvar_escala_mensal');
    formData.append('escala_id_edit', document.getElementById('escalaMensalIdEdit').value);
    formData.append('colaborador_id', document.getElementById('emColaborador')?.value || '');
    formData.append('mes_ano', document.getElementById('emMesAno').value);
    formData.append('seg_sex', document.getElementById('emSegSex').value);
    formData.append('sabado', document.getElementById('emSabado').value);
    formData.append('domingo', document.getElementById('emDomingo').value);

    fetch('../../app/controllers/VisaoTaticaController.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(resposta => {
            if (resposta.sucesso) {
                mostrarToast("Escala mensal guardada com sucesso!");
                fecharModalEscalaMensal();
                carregarMatrizEscalaInline(mesAnoGeralInlineAtual);
            } else {
                alert(resposta.erro || "Erro ao guardar escala.");
            }
        });
}

function abrirModalTrocaEmergencia() {
    const modal = document.getElementById('modalTrocaEmergencia');
    if (!modal) return;
    document.getElementById('formTrocaEmergencia').reset();
    document.getElementById('trocaDataOcorrencia').value = new Date().toISOString().slice(0, 10);
    carregarColaboradoresSelectEscala();
    modal.classList.add('active');
}

function fecharModalTrocaEmergencia() {
    const modal = document.getElementById('modalTrocaEmergencia');
    if (modal) modal.classList.remove('active');
}

function salvarTrocaEmergencia(event) {
    event.preventDefault();
    const formData = new FormData();
    formData.append('action', 'troca_emergencia');
    formData.append('data_ocorrencia', document.getElementById('trocaDataOcorrencia').value);
    formData.append('tipo_ocorrencia', document.getElementById('trocaTipo').value);
    formData.append('colaborador_ausente_id', document.getElementById('trocaAusente').value);
    formData.append('colaborador_substituto_id', document.getElementById('trocaSubstituto').value);
    formData.append('motivo', document.getElementById('trocaMotivo').value);

    fetch('../../app/controllers/VisaoTaticaController.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(resposta => {
            if (resposta.sucesso) {
                mostrarToast(resposta.mensagem || "Alteração de escala efetuada!");
                fecharModalTrocaEmergencia();
                carregarMatrizEscalaInline(mesAnoGeralInlineAtual);
            } else {
                alert(resposta.erro || "Erro ao efetivar troca.");
            }
        });
}

function gerarSenhaAleatoriaColaborador() {
    const chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789@#$!";
    let senha = "";
    for (let i = 0; i < 8; i++) {
        senha += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    const inputSenha = document.getElementById('eqSenhaProvisoria');
    if (inputSenha) inputSenha.value = senha;
}

function abrirModalTrocarSenhaDiretoria() {
    const modal = document.getElementById('modalTrocarSenhaDiretoria');
    if (modal) modal.classList.add('active');
}

function fecharModalTrocarSenhaDiretoria() {
    const modal = document.getElementById('modalTrocarSenhaDiretoria');
    if (modal) modal.classList.remove('active');
}

function salvarMinhaSenhaDiretoria(event) {
    event.preventDefault();
    const atual = document.getElementById('dirSenhaAtual').value;
    const nova = document.getElementById('dirNovaSenha').value;
    const confirma = document.getElementById('dirConfirmaSenha').value;

    if (nova !== confirma) {
        alert("A nova senha e a confirmação não coincidem.");
        return;
    }

    const formData = new FormData();
    formData.append('senha_atual', atual);
    formData.append('nova_senha', nova);

    fetch('../../app/controllers/AuthDiretoriaController.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(resposta => {
            if (resposta.sucesso) {
                mostrarToast("Senha alterada com sucesso!");
                fecharModalTrocarSenhaDiretoria();
                document.getElementById('formTrocarSenhaDiretoria').reset();
            } else {
                alert("Erro: " + (resposta.erro || 'Erro desconhecido'));
            }
        });
}

function mostrarToast(mensagem) {
    const toast = document.getElementById('toastDiretoria');
    if (!toast) return;
    toast.textContent = mensagem;
    toast.classList.add('show');
    setTimeout(() => { toast.classList.remove('show'); }, 3000);
}