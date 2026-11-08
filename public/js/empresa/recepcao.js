/**
 * ==============================================================================
 * RUSTIC IRON - JAVASCRIPT: MOTOR DA RECEPÇÃO & CATRACA (INTEGRADO AO BACKEND)
 * ==============================================================================
 */

let alunos = [];
let logsCatraca = [];
let faturas = [];

let viewAtualRecepcao = "catraca-terminal";
let filtroAlunosAtual = "todos";
let filtroFaturasAtual = "todos";
let faturaEmBaixa = null;
let modoPanico = false;
let timeoutDisplay = null;

document.addEventListener("DOMContentLoaded", () => {
  const avatarSalvo = localStorage.getItem("rustic_recepcao_avatar");
  if (avatarSalvo) {
    aplicarAvatarRecepcao(avatarSalvo);
  }

  iniciarRelogio();
  carregarDadosRecepcaoServidor();

  const btnSide = document.getElementById("btnToggleSidebar");
  const btnSideHeader = document.getElementById("btnToggleSidebarHeader");
  const sidebar = document.getElementById("dashboardSidebar");
  const overlay = document.getElementById("dashboardSidebarOverlay");

  const alternar = () => {
    if (window.innerWidth <= 1024) {
      sidebar?.classList.toggle("open");
      overlay?.classList.toggle("active");
    } else {
      sidebar?.classList.toggle("collapsed");
    }
  };

  btnSide?.addEventListener("click", alternar);
  btnSideHeader?.addEventListener("click", alternar);
  overlay?.addEventListener("click", () => {
    sidebar?.classList.remove("open");
    overlay?.classList.remove("active");
  });
});

function carregarDadosRecepcaoServidor() {
  fetch('../../app/controllers/RecepcaoController.php?action=carregar_painel_recepcao')
    .then(res => res.json())
    .then(resposta => {
      if (!resposta.sucesso) {
        console.error("Erro ao carregar painel da recepção:", resposta.erro);
        return;
      }
      alunos = resposta.alunos || [];
      faturas = resposta.faturas || [];
      logsCatraca = resposta.logs || [];

      atualizarKpisRecepcao();
      renderizarLogsCatraca();
      renderizarAlunos();
      renderizarFaturas();
    })
    .catch(err => console.error("Erro de comunicação:", err));
}

function iniciarRelogio() {
  const el = document.getElementById("relogioRecepcao");
  if (!el) return;
  setInterval(() => {
    el.textContent = new Date().toLocaleTimeString("pt-BR");
  }, 1000);
  el.textContent = new Date().toLocaleTimeString("pt-BR");
}

function navegarRecepcao(viewId) {
  viewAtualRecepcao = viewId;
  document.querySelectorAll(".view-section").forEach(sec => sec.classList.remove("active-view"));
  document.getElementById(`view-${viewId}`)?.classList.add("active-view");

  document.querySelectorAll(".dashboard-nav-item").forEach(item => {
    item.classList.toggle("active", item.getAttribute("data-target") === viewId);
  });

  const titles = {
    "catraca-terminal": "Catraca & Terminal de Acesso",
    "alunos": "Quadro Geral de Alunos",
    "cobrancas": "Balcão & Cobranças"
  };

  const titleEl = document.getElementById("pageMainTitle");
  if (titleEl) titleEl.textContent = titles[viewId] || "Recepção";

  document.getElementById("dashboardSidebar")?.classList.remove("open");
  document.getElementById("dashboardSidebarOverlay")?.classList.remove("active");
}

function aplicarAvatarRecepcao(base64Img) {
  const img1 = document.getElementById("avatarPerfilRecepcao");
  const img2 = document.getElementById("avatarPerfilRecepcaoDropdown");
  
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

function atualizarFotoPerfilRec(event) {
  const file = event.target.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = function(e) {
      const base64Img = e.target.result;
      aplicarAvatarRecepcao(base64Img);
      localStorage.setItem("rustic_recepcao_avatar", base64Img);
      mostrarToast("Foto de perfil atualizada com sucesso!");
    };
    reader.readAsDataURL(file);
  }
}

function fazerLogoutRecepcao() {
  if (confirm("Deseja realmente encerrar a sessão?")) {
    window.location.href = "../auth/logout.php";
  }
}

function atualizarKpisRecepcao() {
  const cntTotal = document.getElementById("cntAlunosTotal");
  const cntLiberados = document.getElementById("cntAlunosLiberados");
  const cntBloqueados = document.getElementById("cntAlunosBloqueados");
  const kpiPresentes = document.getElementById("kpiPresentes");
  const kpiEntradas = document.getElementById("kpiEntradasHoje");
  const kpiRecusados = document.getElementById("kpiRecusadosHoje");
  const kpiCaixa = document.getElementById("kpiCaixaTurno");

  if (cntTotal) cntTotal.textContent = alunos.length;
  if (cntLiberados) cntLiberados.textContent = alunos.filter(a => a.catraca_liberada === 1).length;
  if (cntBloqueados) cntBloqueados.textContent = alunos.filter(a => a.catraca_liberada === 0).length;

  if (kpiPresentes) kpiPresentes.textContent = alunos.filter(a => a.catraca_liberada === 1).length;
  
  const entradasHoje = logsCatraca.filter(l => l.sentido === 'Entrada' && l.autorizado === 1).length;
  const recusadosHoje = logsCatraca.filter(l => l.autorizado === 0).length;

  if (kpiEntradas) kpiEntradas.textContent = entradasHoje;
  if (kpiRecusados) kpiRecusados.textContent = recusadosHoje;

  const totalPago = faturas.filter(f => f.status === 'pago').reduce((acc, f) => acc + f.valor, 0);
  if (kpiCaixa) kpiCaixa.textContent = totalPago.toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
}

function handleValidarPassagem(e) {
  e.preventDefault();
  const input = document.getElementById("inputCpfCatraca");
  const termo = input.value.trim();
  const sentido = document.getElementById("selectSentidoCatraca").value;

  if (!termo) return;

  const formData = new FormData();
  formData.append('action', 'validar_catraca');
  formData.append('termo', termo);
  formData.append('sentido', sentido);

  fetch('../../app/controllers/RecepcaoController.php', {
    method: 'POST',
    body: formData
  })
    .then(res => res.json())
    .then(resposta => {
      if (!resposta.sucesso) {
        mostrarToast("Erro ao validar acesso.");
        return;
      }

      if (sentido === "Saída") {
        setDisplayCatraca("liberado", "SAÍDA LIBERADA", "Até o próximo treino!", resposta.nome, resposta.plano);
        mostrarToast("Saída registrada com sucesso.");
      } else if (resposta.autorizado === 1) {
        setDisplayCatraca("liberado", "ACESSO LIBERADO", "Bom treino na Rustic Iron!", resposta.nome, resposta.plano);
        mostrarToast(`Catraca Liberada para ${resposta.nome}!`);
      } else {
        setDisplayCatraca("bloqueado", "ACESSO BLOQUEADO", "Mensalidade em aberto - Dirija-se ao balcão", resposta.nome, resposta.plano);
        mostrarToast(`Catraca Travada para ${resposta.nome}: Pendência financeira!`);
      }

      input.value = "";
      carregarDadosRecepcaoServidor();
    })
    .catch(err => console.error("Erro na validação:", err));
}

function setDisplayCatraca(tipo, titulo, msg, nome = "", plano = "") {
  const box = document.getElementById("displayBoxCatraca");
  const icon = document.getElementById("displayIcon");
  const tEl = document.getElementById("displayTitle");
  const mEl = document.getElementById("displayMsg");
  const userBox = document.getElementById("displayUserBox");
  const uNome = document.getElementById("displayUserName");
  const uPlano = document.getElementById("displayUserPlan");

  if (!box) return;

  box.className = "turnstile-display-box " + tipo;
  if (tEl) tEl.textContent = titulo;
  if (mEl) mEl.textContent = msg;

  if (icon) {
    if (tipo === "liberado") icon.textContent = "check_circle";
    else if (tipo === "bloqueado") icon.textContent = "cancel";
    else icon.textContent = "fingerprint";
  }

  if (nome && userBox) {
    userBox.style.display = "flex";
    if (uNome) uNome.textContent = nome;
    if (uPlano) uPlano.textContent = plano;
  } else if (userBox) {
    userBox.style.display = "none";
  }

  clearTimeout(timeoutDisplay);
  timeoutDisplay = setTimeout(resetDisplayCatraca, 4000);
}

function resetDisplayCatraca() {
  const box = document.getElementById("displayBoxCatraca");
  const icon = document.getElementById("displayIcon");
  const tEl = document.getElementById("displayTitle");
  const mEl = document.getElementById("displayMsg");
  const userBox = document.getElementById("displayUserBox");

  if (!box) return;

  box.className = "turnstile-display-box";
  if (icon) icon.textContent = "fingerprint";
  if (tEl) tEl.textContent = "AGUARDANDO LEITURA";
  if (mEl) mEl.textContent = "Aproxime o cartão RFID ou digite o CPF abaixo";
  if (userBox) userBox.style.display = "none";
}

function renderizarLogsCatraca() {
  const tbody = document.getElementById("tabelaLogsCatracaCorpo");
  if (!tbody) return;
  tbody.innerHTML = "";

  logsCatraca.slice(0, 8).forEach(l => {
    const tr = document.createElement("tr");
    tr.innerHTML = `
      <td><code>${l.horario}</code></td>
      <td><strong>${l.aluno}</strong><div style="font-size:0.75rem; color:var(--color-text-secondary);">${l.cpf || ''}</div></td>
      <td><span class="status-badge ${l.sentido === 'Entrada' ? 'info' : 'warning'}">${l.sentido}</span></td>
      <td><span class="status-badge ${l.autorizado ? 'success' : 'error'}">${l.motivo}</span></td>
      <td style="text-align: right;">
        ${!l.autorizado ? `
          <button type="button" class="btn btn-secondary btn-sm" onclick="liberarGiroManual('Entrada')">
            <span class="material-symbols-rounded" style="font-size:14px">lock_open</span> Liberar 1x
          </button>
        ` : '<span style="color:var(--color-text-muted); font-size:0.75rem;">OK</span>'}
      </td>
    `;
    tbody.appendChild(tr);
  });
}

function limparLogsCatraca() {
  logsCatraca = [];
  renderizarLogsCatraca();
  mostrarToast("Logs limpos!");
}

function liberarGiroManual(sentido) {
  const hora = new Date().toLocaleTimeString("pt-BR").slice(0, 5);
  setDisplayCatraca("liberado", `GIRO MANUAL (${sentido.toUpperCase()})`, "Comando disparado pela recepção", "Operador Balcão", "Giro Forçado");
  mostrarToast(`Giro de ${sentido} concedido.`);
}

function toggleTravaPanico() {
  modoPanico = !modoPanico;
  const badge = document.getElementById("badgeCatracaStatus");
  if (modoPanico) {
    if (badge) {
      badge.className = "status-badge error";
      badge.textContent = "Catraca Bloqueada (Emergência)";
    }
    setDisplayCatraca("bloqueado", "TRAVA ATIVADA", "Catraca bloqueada pela recepção");
    mostrarToast("Trava de emergência acionada!");
  } else {
    if (badge) {
      badge.className = "status-badge success";
      badge.textContent = "Catraca Online (TCP/IP)";
    }
    resetDisplayCatraca();
    mostrarToast("Catraca destravada com sucesso.");
  }
}

function renderizarAlunos() {
  const tbody = document.getElementById("tabelaAlunosCorpo");
  if (!tbody) return;
  tbody.innerHTML = "";

  const filtrados = alunos.filter(a => {
    if (filtroAlunosAtual === "liberados") return a.catraca_liberada === 1;
    if (filtroAlunosAtual === "bloqueados") return a.catraca_liberada === 0;
    return true;
  });

  if (filtrados.length === 0) {
    tbody.innerHTML = `<tr><td colspan="6" style="text-align:center; padding:24px; color:var(--color-text-muted);">Nenhum aluno encontrado.</td></tr>`;
    return;
  }

  filtrados.forEach(a => {
    const tr = document.createElement("tr");
    tr.innerHTML = `
      <td>
        <strong>${a.nome}</strong>
        <div style="font-size:0.75rem; color:var(--color-text-secondary);">${a.matricula || 'N/A'}</div>
      </td>
      <td><code>${a.cpf}</code></td>
      <td>${a.telefone || '-'}</td>
      <td>${a.plano || 'Padrão'}</td>
      <td><span class="status-badge ${a.catraca_liberada ? 'success' : 'error'}">${a.catraca_liberada ? 'Liberado' : 'Travado'}</span></td>
      <td style="text-align: right;">
        <button type="button" class="btn btn-secondary btn-sm" onclick="alternarTravaCatraca(${a.id})">
          <span class="material-symbols-rounded" style="font-size:16px">${a.catraca_liberada ? 'lock' : 'lock_open'}</span> ${a.catraca_liberada ? 'Bloquear' : 'Liberar'}
        </button>
      </td>
    `;
    tbody.appendChild(tr);
  });
}

function filtrarAlunosStatus(st, btn) {
  filtroAlunosAtual = st;
  document.querySelectorAll(".filtros-chips .btn-chip").forEach(b => b.classList.remove("active"));
  if (btn) btn.classList.add("active");
  renderizarAlunos();
}

function alternarTravaCatraca(id) {
  const a = alunos.find(x => x.id === id);
  if (!a) return;
  a.catraca_liberada = a.catraca_liberada === 1 ? 0 : 1;
  renderizarAlunos();
  atualizarKpisRecepcao();
  mostrarToast(`Acesso de ${a.nome}: ${a.catraca_liberada ? 'LIBERADO' : 'TRAVADO'}`);
}

function abrirModalNovoAluno() {
  document.getElementById("formAluno")?.reset();
  document.getElementById("modalAluno")?.classList.add("active");
}

function fecharModalNovoAluno() {
  document.getElementById("modalAluno")?.classList.remove("active");
}

function salvarAluno(e) {
  e.preventDefault();
  const nome = document.getElementById("alNome").value.trim();
  const cpf = document.getElementById("alCpf").value.trim();
  const telefone = document.getElementById("alTelefone").value.trim();
  const plano = document.getElementById("alPlano").value;
  const catraca_liberada = document.getElementById("alCatraca").value;

  const formData = new FormData();
  formData.append('action', 'cadastrar_aluno');
  formData.append('nome', nome);
  formData.append('cpf', cpf);
  formData.append('telefone', telefone);
  formData.append('plano', plano);
  formData.append('catraca_liberada', catraca_liberada);

  fetch('../../app/controllers/RecepcaoController.php', {
    method: 'POST',
    body: formData
  })
    .then(res => res.json())
    .then(resposta => {
      if (resposta.sucesso) {
        mostrarToast("Aluno cadastrado com sucesso!");
        fecharModalNovoAluno();
        carregarDadosRecepcaoServidor();
      } else {
        alert("Erro: " + (resposta.erro || "Desconhecido"));
      }
    })
    .catch(err => console.error("Erro ao salvar aluno:", err));
}

function renderizarFaturas() {
  const tbody = document.getElementById("tabelaFaturasCorpo");
  if (!tbody) return;
  tbody.innerHTML = "";

  const filtradas = faturas.filter(f => {
    if (filtroFaturasAtual !== "todos") return f.status === filtroFaturasAtual;
    return true;
  });

  if (filtradas.length === 0) {
    tbody.innerHTML = `<tr><td colspan="7" style="text-align:center; padding:24px; color:var(--color-text-muted);">Nenhuma mensalidade neste filtro.</td></tr>`;
    return;
  }

  filtradas.forEach(f => {
    const tr = document.createElement("tr");
    tr.innerHTML = `
      <td><strong>${f.id}</strong></td>
      <td><strong>${f.aluno}</strong><div style="font-size:0.75rem; color:var(--color-text-secondary);">${f.cpf || ''}</div></td>
      <td>${f.plano || '-'}</td>
      <td><strong>${f.valor.toLocaleString("pt-BR", { style: "currency", currency: "BRL" })}</strong></td>
      <td>${f.vencimento || '-'}</td>
      <td><span class="status-badge ${f.status === 'pago' ? 'success' : (f.status === 'atrasado' ? 'error' : 'warning')}">${(f.status || 'pendente').toUpperCase()}</span></td>
      <td style="text-align: right;">
        ${f.status !== 'pago' ? `
          <button type="button" class="btn btn-secondary btn-sm" onclick="abrirModalPagamento('${f.id}')">
            <span class="material-symbols-rounded" style="font-size:14px">point_of_sale</span> Receber
          </button>
        ` : '<span style="color:var(--color-success); font-weight:700; font-size:0.75rem;">Liquidado</span>'}
      </td>
    `;
    tbody.appendChild(tr);
  });
}

function filtrarFaturasStatus(st, btn) {
  filtroFaturasAtual = st;
  document.querySelectorAll("#view-cobrancas .btn-chip").forEach(b => b.classList.remove("active"));
  if (btn) btn.classList.add("active");
  renderizarFaturas();
}

function abrirModalPagamento(fatId) {
  const fat = faturas.find(x => String(x.id) === String(fatId));
  if (!fat) return;

  faturaEmBaixa = fat;
  document.getElementById("modalPagAlunoNome").textContent = fat.aluno;
  document.getElementById("modalPagPlanoInfo").textContent = `${fat.plano} • Valor: ${fat.valor.toLocaleString("pt-BR", { style: "currency", currency: "BRL" })}`;
  document.getElementById("modalPagamento")?.classList.add("active");
}

function fecharModalPagamento() {
  document.getElementById("modalPagamento")?.classList.remove("active");
  faturaEmBaixa = null;
}

function confirmarBaixaPagamento() {
  if (!faturaEmBaixa) return;

  const formData = new FormData();
  formData.append('action', 'dar_baixa_fatura');
  formData.append('fatura_id', faturaEmBaixa.id);

  fetch('../../app/controllers/RecepcaoController.php', {
    method: 'POST',
    body: formData
  })
    .then(res => res.json())
    .then(resposta => {
      if (resposta.sucesso) {
        mostrarToast("Mensalidade recebida e catraca liberada!");
        fecharModalPagamento();
        carregarDadosRecepcaoServidor();
      } else {
        alert("Erro: " + (resposta.erro || "Desconhecido"));
      }
    })
    .catch(err => console.error("Erro ao dar baixa:", err));
}

function abrirModalTrocarSenhaRecepcao() {
  document.getElementById("formTrocarSenhaRecepcao")?.reset();
  document.getElementById("modalTrocarSenhaRecepcao")?.classList.add("active");
}

function fecharModalTrocarSenhaRecepcao() {
  document.getElementById("modalTrocarSenhaRecepcao")?.classList.remove("active");
}

function salvarMinhaSenhaRecepcao(e) {
  e.preventDefault();

  const atual = document.getElementById("recSenhaAtual").value;
  const nova = document.getElementById("recNovaSenha").value;
  const confirma = document.getElementById("recConfirmaSenha").value;

  if (nova !== confirma) {
    mostrarToast("A nova senha e a confirmação não coincidem!");
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
        fecharModalTrocarSenhaRecepcao();
        document.getElementById("formTrocarSenhaRecepcao")?.reset();
      } else {
        alert("Erro: " + (resposta.erro || 'Erro ao alterar senha.'));
      }
    })
    .catch(err => console.error("Erro ao alterar senha:", err));
}

function executarBuscaGlobal(termo) {
  const t = termo.toLowerCase().trim();
  if (viewAtualRecepcao === "alunos") {
    const tbody = document.getElementById("tabelaAlunosCorpo");
    if (!tbody) return;
    const matches = alunos.filter(a => a.nome.toLowerCase().includes(t) || a.cpf.includes(t) || (a.matricula && a.matricula.toLowerCase().includes(t)));
    tbody.innerHTML = "";
    matches.forEach(a => {
      const tr = document.createElement("tr");
      tr.innerHTML = `
        <td><strong>${a.nome}</strong><div style="font-size:0.75rem; color:var(--color-text-secondary);">${a.matricula || 'N/A'}</div></td>
        <td><code>${a.cpf}</code></td>
        <td>${a.telefone || '-'}</td>
        <td>${a.plano || 'Padrão'}</td>
        <td><span class="status-badge ${a.catraca_liberada ? 'success' : 'error'}">${a.catraca_liberada ? 'Liberado' : 'Travado'}</span></td>
        <td style="text-align: right;">
          <button type="button" class="btn btn-secondary btn-sm" onclick="alternarTravaCatraca(${a.id})">
            <span class="material-symbols-rounded" style="font-size:16px">${a.catraca_liberada ? 'lock' : 'lock_open'}</span>
          </button>
        </td>
      `;
      tbody.appendChild(tr);
    });
  }
}

function mostrarToast(msg) {
  const t = document.getElementById("toastRecepcao");
  if (!t) return;
  t.textContent = msg;
  t.classList.add("show");
  setTimeout(() => t.classList.remove("show"), 3200);
}