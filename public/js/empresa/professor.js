/**
 * ==============================================================================
 * RUSTIC IRON - JAVASCRIPT COMPLETO DO PAINEL DO PROFESSOR (DE SEGUNDA A DOMINGO)
 * ==============================================================================
 */

let MODALIDADES_DISPONIVEIS = [];
let BANCO_EXERCICIOS = {};
let TREINOS_BASE_MODALIDADE = {}; // Armazena os treinos base robustos vindos do banco
let atletasData = [];

let modalidadeFiltroAtual = 'todas';
let atletaEditandoAtual = null;
let diaSemanaEditando = 'segunda';

document.addEventListener('DOMContentLoaded', () => {
    const avatarSalvo = localStorage.getItem("rustic_professor_avatar");
    if (avatarSalvo) {
        aplicarAvatarProfessor(avatarSalvo);
    }

    configurarToggleSidebar();
    carregarDadosDoServidor();
    carregarModalidadesBanco();
});

// ==========================================
// 1. COMUNICAÇÃO COM O SERVIDOR (BACKEND)
// ==========================================

function carregarDadosDoServidor() {
    fetch('../../app/controllers/ProfessorController.php?action=carregar_painel')
        .then(res => res.json())
        .then(resposta => {
            if (!resposta.sucesso) {
                console.error("Erro ao carregar painel:", resposta.erro);
                return;
            }

            BANCO_EXERCICIOS = resposta.banco_exercicios || {};
            TREINOS_BASE_MODALIDADE = resposta.treinos_base_modalidade || {}; // Captura do MySQL
            atletasData = resposta.atletas || [];

            atualizarKPIsProfessor();
            renderizarTabelaAtletas();
            renderizarFichasAtivas();
            renderizarBancoExerciciosView();
        })
        .catch(err => console.error("Erro de comunicação:", err));
}

function carregarModalidadesBanco() {
    fetch('../../app/controllers/ModalidadesController.php?action=listar')
        .then(res => res.json())
        .then(resposta => {
            if (resposta.sucesso && resposta.dados) {
                MODALIDADES_DISPONIVEIS = resposta.dados;
            }
        })
        .catch(err => console.error("Erro ao carregar modalidades:", err));
}

// ==========================================
// 2. SIDEBAR, PERFIL, FOTO & NAVEGAÇÃO
// ==========================================

function configurarToggleSidebar() {
    const sidebar = document.getElementById('dashboardSidebar');
    const overlay = document.getElementById('dashboardSidebarOverlay');
    const btn1 = document.getElementById('btnToggleSidebar');
    const btn2 = document.getElementById('btnToggleSidebarHeader');

    [btn1, btn2].forEach(btn => {
        btn?.addEventListener('click', () => {
            if (window.innerWidth <= 1024) {
                sidebar?.classList.toggle('open');
                overlay?.classList.toggle('active');
            } else {
                sidebar?.classList.toggle('collapsed');
            }
        });
    });

    overlay?.addEventListener('click', () => {
        sidebar?.classList.remove('open');
        overlay?.classList.remove('active');
    });
}

function aplicarAvatarProfessor(base64Img) {
    const img1 = document.getElementById("avatarPerfilProfessor");
    const img2 = document.getElementById("avatarPerfilProfessorDropdown");
    
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

function toggleMenuPerfil(event) {
    event.stopPropagation();
    const menu = document.getElementById("profileDropdownMenu");
    menu?.classList.toggle("active");
}

document.addEventListener("click", () => {
    document.getElementById("profileDropdownMenu")?.classList.remove("active");
});

function atualizarFotoPerfil(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const base64Img = e.target.result;
            aplicarAvatarProfessor(base64Img);
            localStorage.setItem("rustic_professor_avatar", base64Img);
            mostrarToast("Foto de perfil atualizada com sucesso!");
        };
        reader.readAsDataURL(file);
    }
}

function fazerLogoutProfessor() {
    if (confirm("Deseja realmente encerrar a sessão?")) {
        window.location.href = "../auth/logout.php";
    }
}

function navegarProfessor(destino) {
    document.querySelectorAll('.dashboard-nav-item').forEach(item => item.classList.remove('active'));
    document.querySelectorAll('.view-section').forEach(sec => sec.classList.remove('active-view'));

    const navItem = document.querySelector(`[data-target="${destino}"]`);
    const section = document.getElementById(`view-${destino}`);

    if (navItem) navItem.classList.add('active');
    if (section) section.classList.add('active-view');

    const titulosAbas = {
        'visao-treinador': 'Painel de Atletas & Prescrição',
        'fichas-ativas': 'Fichas & Periodizações Ativas',
        'banco-exercicios': 'Banco de Exercícios & Biomecânica'
    };

    const mainTitleEl = document.getElementById('pageMainTitle');
    if (mainTitleEl && titulosAbas[destino]) {
        mainTitleEl.textContent = titulosAbas[destino];
    }
}

// ==========================================
// 3. TABELA, FILTROS E KPIS
// ==========================================

function atualizarKPIsProfessor() {
    const total = atletasData.length;
    const vencendo = atletasData.filter(a => a.statusTreino === "Ficha Vencendo").length;
    const evasao = atletasData.filter(a => a.statusTreino === "Alerta Evasão").length;
    const prontas = atletasData.filter(a => a.statusTreino === "Em Dia").length;

    document.getElementById('kpiTotalAtletas').textContent = total;
    document.getElementById('kpiFichasVencendo').textContent = vencendo;
    document.getElementById('kpiEvasao').textContent = evasao;
    document.getElementById('kpiFichasProntas').textContent = prontas;
}

function filtrarPorModalidade(modalidade, btnEl) {
    modalidadeFiltroAtual = modalidade;
    document.querySelectorAll('#containerFiltrosModalidade .btn-chip').forEach(b => b.classList.remove('active'));
    if (btnEl) btnEl.classList.add('active');
    renderizarTabelaAtletas();
}

function filtrarAtletas() {
    renderizarTabelaAtletas();
}

function renderizarTabelaAtletas() {
    const tbody = document.getElementById('tabelaAtletasCorpo');
    const lblTotal = document.getElementById('lblTotalExibido');
    const termoBusca = (document.getElementById('inputBuscaAtleta')?.value || '').toLowerCase();

    if (!tbody) return;
    tbody.innerHTML = '';

    const filtrados = atletasData.filter(atleta => {
        const bateModalidade = (modalidadeFiltroAtual === 'todas' || atleta.modalidade.toLowerCase() === modalidadeFiltroAtual.toLowerCase());
        const bateBusca = atleta.nome.toLowerCase().includes(termoBusca) ||
                          atleta.cpf.includes(termoBusca) ||
                          (atleta.matricula && atleta.matricula.toLowerCase().includes(termoBusca));
        return bateModalidade && bateBusca;
    });

    if (lblTotal) lblTotal.textContent = `${filtrados.length} aluno(s) listado(s)`;

    if (filtrados.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" style="text-align: center; color: var(--color-text-muted); padding: 24px;">Nenhum aluno encontrado na base de dados.</td></tr>`;
        return;
    }

    filtrados.forEach(atleta => {
        let badgeClass = 'info';
        if (atleta.statusTreino === 'Em Dia') badgeClass = 'success';
        if (atleta.statusTreino === 'Ficha Vencendo') badgeClass = 'warning';
        if (atleta.statusTreino === 'Alerta Evasão') badgeClass = 'error';

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>
                <strong>${atleta.nome}</strong><br>
                <small style="color: var(--color-text-muted);">Mat: ${atleta.matricula || 'N/A'} &bull; CPF: ${atleta.cpf}</small>
            </td>
            <td>${atleta.peso} kg &bull; ${atleta.altura} cm</td>
            <td><span class="status-badge info">${atleta.modalidade}</span></td>
            <td>${atleta.ultimaPrescricao}</td>
            <td><span class="status-badge ${badgeClass}">${atleta.statusTreino}</span></td>
            <td style="text-align: right;">
                <button class="btn btn-primary btn-sm" onclick="abrirModalPrescricao(${atleta.id})">
                    <span class="material-symbols-rounded" style="font-size: 16px;">edit_note</span> Prescrever
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function renderizarFichasAtivas() {
    const tbody = document.getElementById('tabelaFichasAtivasCorpo');
    if (!tbody) return;
    tbody.innerHTML = '';

    if (atletasData.length === 0) {
        tbody.innerHTML = `<tr><td colspan="5" style="text-align: center; color: var(--color-text-muted); padding: 24px;">Nenhuma ficha registada.</td></tr>`;
        return;
    }

    atletasData.forEach(atleta => {
        const diasCount = Object.keys(atleta.treinos || {}).length;
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><strong>${atleta.nome}</strong></td>
            <td><span class="status-badge info">${atleta.modalidade}</span></td>
            <td><strong>${diasCount}</strong> dia(s) configurado(s)</td>
            <td>${atleta.ultimaPrescricao}</td>
            <td style="text-align: right;">
                <button class="btn btn-secondary btn-sm" onclick="abrirModalPrescricao(${atleta.id})">
                    <span class="material-symbols-rounded" style="font-size: 16px;">visibility</span> Abrir Ficha
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function renderizarBancoExerciciosView() {
    const grid = document.getElementById('gridBancoExerciciosView');
    if (!grid) return;
    grid.innerHTML = '';

    const grupos = Object.keys(BANCO_EXERCICIOS);
    if (grupos.length === 0) {
        grid.innerHTML = `<p style="color: var(--color-text-muted);">Nenhum exercício encontrado.</p>`;
        return;
    }

    grupos.forEach(grupo => {
        const card = document.createElement('div');
        card.className = 'exercicio-banco-card';
        const listaHtml = BANCO_EXERCICIOS[grupo]
            .map(ex => `<li style="margin-bottom: 6px; color: var(--color-text-secondary); font-size: 0.82rem;">&bull; ${ex}</li>`)
            .join('');

        card.innerHTML = `
            <h4 style="color: var(--color-primary); font-size: 0.95rem; margin-bottom: 10px; border-bottom: 1px solid var(--color-border); padding-bottom: 6px;">${grupo}</h4>
            <ul style="list-style: none; padding: 0; max-height: 180px; overflow-y: auto;">${listaHtml}</ul>
        `;
        grid.appendChild(card);
    });
}

// ==========================================
// 4. TEMPLATES AUTOMÁTICOS POR MODALIDADE
// ==========================================

function gerarTemplateAutomatico(modalidade) {
    const tpl = {};
    const modLower = (modalidade || '').toLowerCase();

    if (modLower.includes('powerlifting') || modLower.includes('força')) {
        tpl.segunda = { 
            ordemMusculos: ["Quadríceps & Glúteo", "Core & Abandômen"], 
            exercicios: { 
                "Quadríceps & Glúteo": [{ nome: "Agachamento Livre (Low Bar / High Bar)", series: "5", reps: "3", carga: "@ RPE 8.5" }], 
                "Core & Abandômen": [{ nome: "Prancha Abdominal com Carga", series: "4", reps: "45s", carga: "Anilha 20kg" }] 
            } 
        };
        tpl.quarta = { 
            ordemMusculos: ["Peitoral", "Tríceps"], 
            exercicios: { 
                "Peitoral": [{ nome: "Supino Reto com Barra", series: "5", reps: "3", carga: "Pesado" }], 
                "Tríceps": [{ nome: "Supino Fechado", series: "4", reps: "6", carga: "Pesado" }] 
            } 
        };
        tpl.sexta = { 
            ordemMusculos: ["Costas", "Bíceps & Antebraço"], 
            exercicios: { 
                "Costas": [{ nome: "Levantamento Terra (Deadlift)", series: "3", reps: "2", carga: "Max Effort" }] 
            } 
        };
    } else {
        // Template Padrão Hipertrofia / Musculação (Segunda a Domingo)
        tpl.segunda = { ordemMusculos: ["Peitoral", "Tríceps"], exercicios: { "Peitoral": [{ nome: "Supino Reto com Barra", series: "4", reps: "10", carga: "" }], "Tríceps": [{ nome: "Tríceps Pulley com Corda", series: "3", reps: "12", carga: "" }] } };
        tpl.terca = { ordemMusculos: ["Costas", "Bíceps & Antebraço"], exercicios: { "Costas": [{ nome: "Puxada Frontal Alta (Pronada)", series: "4", reps: "10", carga: "" }], "Bíceps & Antebraço": [{ nome: "Rosca Direta com Barra W", series: "3", reps: "10", carga: "" }] } };
        tpl.quarta = { ordemMusculos: ["Quadríceps & Glúteo"], exercicios: { "Quadríceps & Glúteo": [{ nome: "Leg Press 45° Tradicional", series: "4", reps: "12", carga: "" }, { nome: "Cadeira Extensora", series: "3", reps: "15", carga: "" }] } };
        tpl.quinta = { ordemMusculos: ["Ombros (Deltoides)"], exercicios: { "Ombros (Deltoides)": [{ nome: "Desenvolvimento com Halteres sentado", series: "4", reps: "10", carga: "" }, { nome: "Elevação Lateral com Halteres", series: "4", reps: "12", carga: "" }] } };
        tpl.sexta = { ordemMusculos: ["Posterior de Coxa"], exercicios: { "Posterior de Coxa": [{ nome: "Mesa Flexora", series: "4", reps: "12", carga: "" }, { nome: "Stiff com Barra", series: "4", reps: "10", carga: "" }] } };
        tpl.sabado = { ordemMusculos: ["Aeróbico & Cardio"], exercicios: { "Aeróbico & Cardio": [{ nome: "Esteira (Corrida Contínua / Intervalada)", series: "1", reps: "30min", carga: "Moderado" }] } };
        tpl.domingo = { ordemMusculos: ["Alongamento & Mobilidade"], exercicios: { "Alongamento & Mobilidade": [{ nome: "Mobilidade Torácica com Foam Roller", series: "3", reps: "15s", carga: "Livre" }] } };
    }
    return tpl;
}

// ==========================================
// 5. MODAL DE PRESCRIÇÃO E EDIÇÃO (SEG-DOM)
// ==========================================

function abrirModalPrescricao(atletaId) {
    if (atletasData.length === 0) {
        alert("Nenhum aluno registado.");
        return;
    }

    const atleta = atletasData.find(a => a.id === atletaId) || atletasData[0];
    if (!atleta) return;

    atletaEditandoAtual = atleta;
    if (!atletaEditandoAtual.treinos || Object.keys(atletaEditandoAtual.treinos).length === 0) {
        atletaEditandoAtual.treinos = gerarTemplateAutomatico(atleta.modalidade);
    }
    diaSemanaEditando = 'segunda';

    document.getElementById('fichaModalNomeAluno').textContent = atleta.nome;
    document.getElementById('fichaModalSubInfo').textContent = `${atleta.peso}kg • ${atleta.altura}cm • Matrícula: ${atleta.matricula || 'N/A'}`;

    const selectMod = document.getElementById('fichaSelectModalidade');
    if (selectMod) {
        let htmlOpcoes = `<option value="" disabled>Selecione o objetivo ou modalidade</option>`;
        MODALIDADES_DISPONIVEIS.forEach(mod => {
            const selecionado = (mod.nome.toLowerCase() === atleta.modalidade.toLowerCase()) ? 'selected' : '';
            htmlOpcoes += `<option value="${mod.nome}" ${selecionado}>${mod.nome} (${mod.tipo})</option>`;
        });
        selectMod.innerHTML = htmlOpcoes;
    }

    document.querySelectorAll('.day-btn').forEach(btn => {
        btn.classList.toggle('active', btn.getAttribute('data-day') === 'segunda');
    });

    carregarDadosDiaNoModal();
    document.getElementById('modalFichaTreino')?.classList.add('active');
}

function fecharModalFicha() {
    document.getElementById('modalFichaTreino')?.classList.remove('active');
}

function atualizarModalidadeAtleta(novaModalidade) {
    if (atletaEditandoAtual) {
        atletaEditandoAtual.modalidade = novaModalidade;
        
        // Atualiza os exercícios dos músculos já marcados com base na nova modalidade
        const diaObj = obterEstruturaDiaAtual();
        if (diaObj.ordemMusculos && diaObj.ordemMusculos.length > 0) {
            diaObj.ordemMusculos.forEach(musculo => {
                const modalidadeBruta = novaModalidade;
                const modChave1 = modalidadeBruta.trim();
                const modChave2 = modalidadeBruta.replace(/\s*\([^)]*\)/g, '').trim();

                let exerciciosEncontrados = null;

                if (TREINOS_BASE_MODALIDADE[modChave1] && TREINOS_BASE_MODALIDADE[modChave1][musculo]) {
                    exerciciosEncontrados = TREINOS_BASE_MODALIDADE[modChave1][musculo];
                } else if (TREINOS_BASE_MODALIDADE[modChave2] && TREINOS_BASE_MODALIDADE[modChave2][musculo]) {
                    exerciciosEncontrados = TREINOS_BASE_MODALIDADE[modChave2][musculo];
                } else {
                    const chaveEncontrada = Object.keys(TREINOS_BASE_MODALIDADE).find(k => 
                        k.toLowerCase().includes(modChave2.toLowerCase())
                    );
                    if (chaveEncontrada && TREINOS_BASE_MODALIDADE[chaveEncontrada][musculo]) {
                        exerciciosEncontrados = TREINOS_BASE_MODALIDADE[chaveEncontrada][musculo];
                    }
                }

                if (exerciciosEncontrados && exerciciosEncontrados.length > 0) {
                    diaObj.exercicios[musculo] = JSON.parse(JSON.stringify(exerciciosEncontrados));
                } else {
                    const opBanco = BANCO_EXERCICIOS[musculo] || ["Exercício Padrão"];
                    diaObj.exercicios[musculo] = [
                        { nome: opBanco[0] || "Exercício 1", series: "4", reps: "10", carga: "" },
                        { nome: opBanco[1] || opBanco[0] || "Exercício 2", series: "4", reps: "10", carga: "" },
                        { nome: opBanco[2] || opBanco[0] || "Exercício 3", series: "3", reps: "12", carga: "" },
                        { nome: opBanco[3] || opBanco[0] || "Exercício 4", series: "3", reps: "15", carga: "" }
                    ];
                }
            });
        }

        carregarDadosDiaNoModal();
    }
}

function trocarDiaTreino(dia, btnEl) {
    diaSemanaEditando = dia;
    document.querySelectorAll('.day-btn').forEach(b => b.classList.remove('active'));
    if (btnEl) btnEl.classList.add('active');
    carregarDadosDiaNoModal();
}

function obterEstruturaDiaAtual() {
    if (!atletaEditandoAtual.treinos[diaSemanaEditando]) {
        atletaEditandoAtual.treinos[diaSemanaEditando] = {
            ordemMusculos: [],
            exercicios: {}
        };
    }
    return atletaEditandoAtual.treinos[diaSemanaEditando];
}

function carregarDadosDiaNoModal() {
    const diaObj = obterEstruturaDiaAtual();
    const containerChecks = document.getElementById('containerChecksMusculos');
    if (!containerChecks) return;

    containerChecks.innerHTML = '';
    Object.keys(BANCO_EXERCICIOS).forEach(musculo => {
        const selecionado = diaObj.ordemMusculos.includes(musculo);
        const label = document.createElement('label');
        label.className = `muscle-check-label ${selecionado ? 'checked' : ''}`;
        label.innerHTML = `
            <input type="checkbox" ${selecionado ? 'checked' : ''} onchange="alternarMusculoDia('${musculo}', this.checked)">
            <span>${musculo}</span>
        `;
        containerChecks.appendChild(label);
    });

    renderizarOrdemPrioridade();
    renderizarBlocosExercicios();
}

// FALLBACK UNIVERSAL BLINDADO: Garante sempre 4 exercícios completos por músculo
function alternarMusculoDia(musculo, isChecked) {
    const diaObj = obterEstruturaDiaAtual();
    if (isChecked) {
        if (!diaObj.ordemMusculos.includes(musculo)) {
            diaObj.ordemMusculos.push(musculo);
        }

        const modalidadeBruta = atletaEditandoAtual.modalidade || 'Musculação Pesada';
        const modChave1 = modalidadeBruta.trim();
        const modChave2 = modalidadeBruta.replace(/\s*\([^)]*\)/g, '').trim();

        let exerciciosEncontrados = null;

        if (TREINOS_BASE_MODALIDADE[modChave1] && TREINOS_BASE_MODALIDADE[modChave1][musculo]) {
            exerciciosEncontrados = TREINOS_BASE_MODALIDADE[modChave1][musculo];
        } else if (TREINOS_BASE_MODALIDADE[modChave2] && TREINOS_BASE_MODALIDADE[modChave2][musculo]) {
            exerciciosEncontrados = TREINOS_BASE_MODALIDADE[modChave2][musculo];
        } else {
            const chaveEncontrada = Object.keys(TREINOS_BASE_MODALIDADE).find(k => 
                k.toLowerCase().includes(modChave2.toLowerCase())
            );
            if (chaveEncontrada && TREINOS_BASE_MODALIDADE[chaveEncontrada][musculo]) {
                exerciciosEncontrados = TREINOS_BASE_MODALIDADE[chaveEncontrada][musculo];
            }
        }

        if (exerciciosEncontrados && exerciciosEncontrados.length > 0) {
            diaObj.exercicios[musculo] = JSON.parse(JSON.stringify(exerciciosEncontrados));
        } else {
            const opBanco = BANCO_EXERCICIOS[musculo] || ["Exercício Padrão"];
            diaObj.exercicios[musculo] = [
                { nome: opBanco[0] || "Exercício 1", series: "4", reps: "10", carga: "" },
                { nome: opBanco[1] || opBanco[0] || "Exercício 2", series: "4", reps: "10", carga: "" },
                { nome: opBanco[2] || opBanco[0] || "Exercício 3", series: "3", reps: "12", carga: "" },
                { nome: opBanco[3] || opBanco[0] || "Exercício 4", series: "3", reps: "15", carga: "" }
            ];
        }
    } else {
        diaObj.ordemMusculos = diaObj.ordemMusculos.filter(m => m !== musculo);
        delete diaObj.exercicios[musculo];
    }
    carregarDadosDiaNoModal();
}

function renderizarOrdemPrioridade() {
    const diaObj = obterEstruturaDiaAtual();
    const container = document.getElementById('listaMusculosOrdem');
    if (!container) return;

    if (diaObj.ordemMusculos.length === 0) {
        container.innerHTML = `<div class="empty-hint">Nenhum grupo muscular selecionado para este dia.</div>`;
        return;
    }

    container.innerHTML = '';
    diaObj.ordemMusculos.forEach((musculo, idx) => {
        const item = document.createElement('div');
        item.className = 'muscle-order-item';
        item.innerHTML = `
            <div><strong>${idx + 1}º Prioridade:</strong> ${musculo}</div>
            <div style="display: flex; gap: 6px;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="moverPrioridadeMusculo(${idx}, -1)" ${idx === 0 ? 'disabled' : ''}>&uarr;</button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="moverPrioridadeMusculo(${idx}, 1)" ${idx === diaObj.ordemMusculos.length - 1 ? 'disabled' : ''}>&darr;</button>
            </div>
        `;
        container.appendChild(item);
    });
}

function moverPrioridadeMusculo(index, direcao) {
    const diaObj = obterEstruturaDiaAtual();
    const novoIndex = index + direcao;
    if (novoIndex < 0 || novoIndex >= diaObj.ordemMusculos.length) return;

    const temp = diaObj.ordemMusculos[index];
    diaObj.ordemMusculos[index] = diaObj.ordemMusculos[novoIndex];
    diaObj.ordemMusculos[novoIndex] = temp;

    renderizarOrdemPrioridade();
    renderizarBlocosExercicios();
}

function renderizarBlocosExercicios() {
    const diaObj = obterEstruturaDiaAtual();
    const container = document.getElementById('containerBlocosExercicios');
    if (!container) return;

    if (diaObj.ordemMusculos.length === 0) {
        container.innerHTML = `<div class="empty-hint">Selecione ao menos um grupo muscular acima.</div>`;
        return;
    }

    container.innerHTML = '';
    diaObj.ordemMusculos.forEach(musculo => {
        const listaEx = diaObj.exercicios[musculo] || [];
        const opcoesBanco = BANCO_EXERCICIOS[musculo] || [];

        const bloco = document.createElement('div');
        bloco.className = 'muscle-exercise-group';

        let linhasHtml = '';
        listaEx.forEach((itemEx, i) => {
            const optionsHtml = opcoesBanco.map(op =>
                `<option value="${op}" ${op === itemEx.nome ? 'selected' : ''}>${op}</option>`
            ).join('');

            linhasHtml += `
                <div class="exercise-row-grid">
                    <select class="select-eq" onchange="atualizarCampoExercicio('${musculo}', ${i}, 'nome', this.value)">
                        ${optionsHtml}
                    </select>
                    <input type="text" class="input-eq" placeholder="Séries" value="${itemEx.series}" oninput="atualizarCampoExercicio('${musculo}', ${i}, 'series', this.value)" />
                    <input type="text" class="input-eq" placeholder="Reps" value="${itemEx.reps}" oninput="atualizarCampoExercicio('${musculo}', ${i}, 'reps', this.value)" />
                    <input type="text" class="input-eq" placeholder="Carga / RPE" value="${itemEx.carga}" oninput="atualizarCampoExercicio('${musculo}', ${i}, 'carga', this.value)" />
                    <button type="button" class="btn btn-secondary btn-sm" onclick="removerLinhaExercicio('${musculo}', ${i})" style="color: var(--color-error);">
                        <span class="material-symbols-rounded" style="font-size: 16px;">delete</span>
                    </button>
                </div>
            `;
        });

        bloco.innerHTML = `
            <div class="muscle-exercise-header">
                <strong style="color: var(--color-primary);">${musculo}</strong>
                <button type="button" class="btn btn-secondary btn-sm" onclick="adicionarLinhaExercicio('${musculo}')">
                    <span class="material-symbols-rounded" style="font-size: 15px;">add</span> Exercício
                </button>
            </div>
            <div>${linhasHtml}</div>
        `;
        container.appendChild(bloco);
    });
}

function adicionarLinhaExercicio(musculo) {
    const diaObj = obterEstruturaDiaAtual();
    if (!diaObj.exercicios[musculo]) diaObj.exercicios[musculo] = [];
    diaObj.exercicios[musculo].push({
        nome: BANCO_EXERCICIOS[musculo][0],
        series: "4",
        reps: "10",
        carga: ""
    });
    renderizarBlocosExercicios();
}

function removerLinhaExercicio(musculo, index) {
    const diaObj = obterEstruturaDiaAtual();
    if (!diaObj.exercicios[musculo]) return;
    diaObj.exercicios[musculo].splice(index, 1);
    renderizarBlocosExercicios();
}

function atualizarCampoExercicio(musculo, index, campo, valor) {
    const diaObj = obterEstruturaDiaAtual();
    if (diaObj.exercicios[musculo] && diaObj.exercicios[musculo][index]) {
        diaObj.exercicios[musculo][index][campo] = valor;
    }
}

function salvarTreinoEmMemoria() {
    if (!atletaEditandoAtual) return;

    const formData = new FormData();
    formData.append('action', 'salvar_prescricao');
    formData.append('atleta_id', atletaEditandoAtual.id);
    formData.append('modalidade', atletaEditandoAtual.modalidade);
    formData.append('treinos_json', JSON.stringify(atletaEditandoAtual.treinos));

    fetch('../../app/controllers/ProfessorController.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(resposta => {
        if (resposta.sucesso) {
            mostrarToast("Prescrição gravada com sucesso!");
            fecharModalFicha();
            carregarDadosDoServidor();
        } else {
            alert("Erro ao gravar: " + (resposta.erro || "Desconhecido"));
        }
    })
    .catch(err => {
        console.error("Erro AJAX:", err);
        alert("Erro de comunicação com o servidor.");
    });
}

// ==========================================
// 6. IMPRESSÃO TÉRMICA & SENHA
// ==========================================

function imprimirFichaAtleta() {
    if (!atletaEditandoAtual) return;

    document.getElementById('printAtletaNome').textContent = atletaEditandoAtual.nome;
    document.getElementById('printAtletaMetricas').textContent = `Peso: ${atletaEditandoAtual.peso} kg | Altura: ${atletaEditandoAtual.altura} cm | Objetivo: ${atletaEditandoAtual.modalidade}`;

    const contentDiv = document.getElementById('printSheetContent');
    contentDiv.innerHTML = '';

    const diasMap = {
        segunda: "SEGUNDA-FEIRA",
        terca: "TERÇA-FEIRA",
        quarta: "QUARTA-FEIRA",
        quinta: "QUINTA-FEIRA",
        sexta: "SEXTA-FEIRA",
        sabado: "SÁBADO",
        domingo: "DOMINGO"
    };

    Object.keys(diasMap).forEach(diaKey => {
        const diaInfo = atletaEditandoAtual.treinos[diaKey];
        if (diaInfo && diaInfo.ordemMusculos && diaInfo.ordemMusculos.length > 0) {
            let htmlDia = `<div style="margin-bottom: 10px;"><strong>[${diasMap[diaKey]}]</strong><br>`;
            diaInfo.ordemMusculos.forEach(musc => {
                htmlDia += `<u>* ${musc}:</u><br>`;
                (diaInfo.exercicios[musc] || []).forEach(ex => {
                    htmlDia += `- ${ex.nome}: ${ex.series}x${ex.reps} ${ex.carga ? '(' + ex.carga + ')' : ''}<br>`;
                });
            });
            htmlDia += `</div>`;
            contentDiv.innerHTML += htmlDia;
        }
    });

    if (contentDiv.innerHTML === '') {
        contentDiv.innerHTML = `<p>Nenhum exercício prescrito.</p>`;
    }

    window.print();
}

function abrirModalTrocarSenhaProfessor() {
    document.getElementById('modalTrocarSenhaProfessor')?.classList.add('active');
}

function fecharModalTrocarSenhaProfessor() {
    document.getElementById('modalTrocarSenhaProfessor')?.classList.remove('active');
}

function salvarMinhaSenhaProfessor(event) {
    event.preventDefault();
    const atual = document.getElementById('profSenhaAtual').value;
    const nova = document.getElementById('profNovaSenha').value;
    const confirma = document.getElementById('profConfirmaSenha').value;

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
                fecharModalTrocarSenhaProfessor();
                document.getElementById('formTrocarSenhaProfessor').reset();
            } else {
                alert("Erro: " + (resposta.erro || 'Erro ao alterar senha.'));
            }
        });
}

function mostrarToast(mensagem) {
    const toast = document.getElementById('toastProfessor');
    if (!toast) return;
    toast.textContent = mensagem;
    toast.classList.add('show');
    setTimeout(() => { toast.classList.remove('show'); }, 3000);
}