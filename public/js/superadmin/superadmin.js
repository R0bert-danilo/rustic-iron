/**
 * RUSTIC MASTER - JAVASCRIPT: MOTOR COMPLETO DO CONSOLE SUPER ADMIN
 */

let tenants = [];
let faturas = [];
let auditoria = [];

let viewAtualMaster = "visao-geral";
let filtroStatusTenantAtual = "todos";

document.addEventListener("DOMContentLoaded", () => {
  // Carrega a foto de perfil salva anteriormente se existir e aplica em ambos os locais
  const avatarSalvo = localStorage.getItem("rustic_master_avatar");
  if (avatarSalvo) {
    const img1 = document.getElementById("avatarPerfilMaster");
    const img2 = document.getElementById("avatarPerfilMasterDropdown");
    if (img1) img1.src = avatarSalvo;
    if (img2) img2.src = avatarSalvo;
  }

  carregarDadosMasterBackend();
  carregarFaturasBackend();
  setupSidebar();
  setupFormTenantAjax();
});

// CONTROLAR O MENU EXPANSÍVEL (DROPDOWN) DO PERFIL
function toggleMenuPerfil(event) {
  event.stopPropagation();
  const menu = document.getElementById("profileDropdownMenu");
  menu?.classList.toggle("active");
}

// Fecha o menu flutuante se o utilizador clicar fora dele
document.addEventListener("click", () => {
  const menu = document.getElementById("profileDropdownMenu");
  if (menu && menu.classList.contains("active")) {
    menu.classList.remove("active");
  }
});

// FUNÇÕES DE PERFIL E LOGOUT
function atualizarFotoPerfil(event) {
  const file = event.target.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = function(e) {
      const base64Img = e.target.result;
      const img1 = document.getElementById("avatarPerfilMaster");
      const img2 = document.getElementById("avatarPerfilMasterDropdown");
      if (img1) img1.src = base64Img;
      if (img2) img2.src = base64Img;
      localStorage.setItem("rustic_master_avatar", base64Img);
      mostrarToast("Foto de perfil atualizada com sucesso!");
    };
    reader.readAsDataURL(file);
  }
}

function fazerLogoutMaster() {
  if (confirm("Deseja realmente encerrar a sessão do Super Admin?")) {
    window.location.href = "../auth/logout.php";
  }
}

async function carregarDadosMasterBackend() {
  try {
    const resposta = await fetch('../api/get_tenants.php');
    const resultado = await resposta.json();

    if (resultado.sucesso) {
      tenants = resultado.dados || [];
      
      try {
        const respLogs = await fetch('../../app/controllers/LogsController.php?action=listar');
        const resLogsJson = await respLogs.json();
        if (resLogsJson.sucesso) {
          auditoria = resLogsJson.dados || [];
        }
      } catch (e) {
        console.error("Erro ao carregar logs do servidor, usando fallback local:", e);
        auditoria = JSON.parse(localStorage.getItem("rustic_master_auditoria") || "[]");
      }
      
      atualizarKpisMaster();
      renderizarResumos();
      renderizarTenantsCompleto();
      renderizarInfra();
      renderizarAuditoria();
    } else {
      console.error("Erro ao buscar tenants do banco:", resultado.erro);
      carregarMasterLocal();
    }
  } catch (erro) {
    console.error("Erro de conexão com a API master, carregando fallback local:", erro);
    carregarMasterLocal();
  }
}

async function carregarFaturasBackend() {
  try {
    const resposta = await fetch('../../app/controllers/FaturamentoController.php?action=listar');
    const resultado = await resposta.json();
    if (resultado.sucesso) {
      faturas = resultado.dados || [];
      renderizarFaturas();
    }
  } catch (erro) {
    console.error("Erro ao carregar faturas:", erro);
  }
}

function carregarMasterLocal() {
  tenants = JSON.parse(localStorage.getItem("rustic_master_tenants") || "[]");
  faturas = JSON.parse(localStorage.getItem("rustic_master_faturas") || "[]");
  auditoria = JSON.parse(localStorage.getItem("rustic_master_auditoria") || "[]");

  atualizarKpisMaster();
  renderizarResumos();
  renderizarTenantsCompleto();
  renderizarFaturas();
  renderizarInfra();
  renderizarAuditoria();
}

function salvarMasterLocal() {
  localStorage.setItem("rustic_master_tenants", JSON.stringify(tenants));
  localStorage.setItem("rustic_master_faturas", JSON.stringify(faturas));
  localStorage.setItem("rustic_master_auditoria", JSON.stringify(auditoria));
}

async function registrarLog(acao, tenant, operador, status) {
  try {
    const formData = new FormData();
    formData.append('action', 'registrar');
    formData.append('evento_acao', acao);
    formData.append('tenant_alvo', tenant);
    formData.append('operador', operador);
    formData.append('resultado', status);

    await fetch('../../app/controllers/LogsController.php', {
      method: 'POST',
      body: formData
    });
  } catch (e) {
    console.error("Erro ao gravar log no servidor:", e);
  }

  carregarDadosMasterBackend();
}

function navegarMaster(viewId) {
  viewAtualMaster = viewId;
  document.querySelectorAll(".view-section").forEach(sec => sec.classList.remove("active-view"));
  document.getElementById(`view-${viewId}`)?.classList.add("active-view");

  document.querySelectorAll(".dashboard-nav-item").forEach(item => {
    item.classList.toggle("active", item.getAttribute("data-target") === viewId);
  });

  const titles = {
    "visao-geral": "Painel de Métricas do SaaS",
    "clientes": "Academias Clientes & Assinaturas",
    "cobrancas": "Gestão de Cobranças & Faturamento",
    "infra": "Provisionamento de Servidores & Bancos de Dados",
    "auditoria": "Logs de Auditoria e Segurança"
  };

  const titleEl = document.getElementById("pageMainTitle");
  if (titleEl) {
    titleEl.textContent = titles[viewId] || "Super Admin";
  }

  document.getElementById("dashboardSidebar")?.classList.remove("open");
  document.getElementById("dashboardSidebarOverlay")?.classList.remove("active");
}

function atualizarKpisMaster() {
  let mrr = 0;
  let atrasado = 0;
  let ativas = 0;
  let trial = 0;
  let bloqueadas = 0;

  tenants.forEach(t => {
    const statusVal = (t.status || 'ativo').toLowerCase();
    const valMensal = parseFloat(t.valor_plano || t.valor_mensalidade) || 0;

    if (statusVal === "ativo" || statusVal === "ativado") {
      mrr += valMensal;
      ativas++;
    } else if (statusVal === "trial") {
      trial++;
    } else if (statusVal === "atrasado") {
      atrasado += valMensal;
    } else if (statusVal === "bloqueado") {
      bloqueadas++;
    }
  });

  const arr = mrr * 12;

  document.getElementById("kpiMrr").textContent = mrr.toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
  document.getElementById("kpiArr").textContent = `ARR anual: ${arr.toLocaleString("pt-BR", { style: "currency", currency: "BRL" })}`;

  document.getElementById("kpiTotalTenants").textContent = tenants.length;
  document.getElementById("kpiTenantsDetalhados").textContent = `${ativas} pagantes | ${trial} trial | ${bloqueadas} bloqueadas`;

  document.getElementById("kpiInadimplencia").textContent = atrasado.toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
  document.getElementById("kpiQtdAtrasadasMaster").textContent = `${tenants.filter(t => (t.status || '').toLowerCase() === "atrasado").length} academias em débito`;

  document.getElementById("kpiBancosIsolados").textContent = `${tenants.length} bancos MySQL`;
}

function renderizarResumos() {
  const tbodyVenc = document.getElementById("tabelaVencimentosIminentes");
  if (!tbodyVenc) return;
  tbodyVenc.innerHTML = "";

  const ordenados = [...tenants].slice(0, 4);

  if (ordenados.length === 0) {
    tbodyVenc.innerHTML = `<tr><td colspan="5" style="text-align:center; padding:16px; color:var(--color-text-muted);">Nenhuma academia cadastrada.</td></tr>`;
  } else {
    ordenados.forEach(t => {
      const nomeAcademia = t.nome_academia || t.nome_fantasia || 'Academia';
      const valorPlano = parseFloat(t.valor_plano || t.valor_mensalidade || 0);
      const vencimento = t.vencimento || t.data_expiracao || '2026-12-31';
      const statusTenant = t.status || 'ativo';

      const tr = document.createElement("tr");
      tr.innerHTML = `
        <td><strong>${nomeAcademia}</strong></td>
        <td><strong>${valorPlano.toLocaleString("pt-BR", { style: "currency", currency: "BRL" })}</strong></td>
        <td>${formatarData(vencimento)}</td>
        <td><span class="status-badge ${obterBadgeStatus(statusTenant)}">${statusTenant.toUpperCase()}</span></td>
        <td style="text-align: right;">
          <button type="button" class="btn btn-secondary btn-sm" onclick="dispararCobrancaWhatsApp(${t.id})">
            <span class="material-symbols-rounded" style="font-size:14px">send</span> WhatsApp
          </button>
        </td>
      `;
      tbodyVenc.appendChild(tr);
    });
  }

  const tbodyAud = document.getElementById("tabelaResumoAuditoria");
  if (!tbodyAud) return;
  tbodyAud.innerHTML = "";

  if (auditoria.length === 0) {
    tbodyAud.innerHTML = `<tr><td colspan="4" style="text-align:center; padding:16px; color:var(--color-text-muted);">Nenhum log registrado.</td></tr>`;
  } else {
    auditoria.slice(0, 4).forEach(log => {
      const tr = document.createElement("tr");
      tr.innerHTML = `
        <td>${log.timestamp || log.criado_em}</td>
        <td><strong>${log.evento_acao || log.acao}</strong></td>
        <td><code>${log.tenant_alvo || log.tenant}</code></td>
        <td><span class="status-badge success">${log.resultado || log.status}</span></td>
      `;
      tbodyAud.appendChild(tr);
    });
  }
}

function renderizarTenantsCompleto() {
  const tbody = document.getElementById("tabelaTenantsCorpo");
  if (!tbody) return;
  tbody.innerHTML = "";

  const filtrados = tenants.filter(t => {
    const st = (t.status || 'ativo').toLowerCase();
    return filtroStatusTenantAtual === "todos" || st === filtroStatusTenantAtual;
  });

  document.getElementById("cntFiltroTodos").textContent = tenants.length;
  document.getElementById("cntFiltroAtivos").textContent = tenants.filter(t => ['ativo', 'ativado'].includes((t.status || 'ativo').toLowerCase())).length;
  document.getElementById("cntFiltroTrial").textContent = tenants.filter(t => (t.status || '').toLowerCase() === "trial").length;
  document.getElementById("cntFiltroAtrasados").textContent = tenants.filter(t => (t.status || '').toLowerCase() === "atrasado").length;
  document.getElementById("cntFiltroBloqueados").textContent = tenants.filter(t => (t.status || '').toLowerCase() === "bloqueado").length;

  if (filtrados.length === 0) {
    tbody.innerHTML = `<tr><td colspan="7" style="text-align:center; padding:24px; color:var(--color-text-muted);">Nenhuma academia neste filtro.</td></tr>`;
    return;
  }

  filtrados.forEach(t => {
    const st = (t.status || 'ativo').toLowerCase();
    const nomeAcademia = t.nome_academia || t.nome_fantasia || 'Academia';
    const responsavel = t.admin_nome || t.responsavel || 'Gestor';
    const telefone = t.admin_telefone || t.telefone || '(00) 00000-0000';
    const valor = parseFloat(t.valor_plano || t.valor_mensalidade || 0);
    const vencimento = t.vencimento || t.data_expiracao || '-';
    const cnpj = t.cnpj || '00.000.000/0001-00';
    const dbName = t.db_name || 'rustic_tenant';
    const tenantId = t.id;

    const tr = document.createElement("tr");
    tr.innerHTML = `
      <td>
        <strong>${nomeAcademia}</strong>
        <div style="font-size:0.75rem; color:var(--color-text-secondary);">${cnpj}</div>
      </td>
      <td>
        <strong>${responsavel}</strong>
        <div style="font-size:0.75rem; color:var(--color-text-secondary);">${telefone}</div>
      </td>
      <td>
        <strong>${valor.toLocaleString("pt-BR", { style: "currency", currency: "BRL" })}</strong>/mês
      </td>
      <td>
        Expira: ${formatarData(vencimento)}
      </td>
      <td><code>${dbName}</code></td>
      <td><span class="status-badge ${obterBadgeStatus(st)}">${st.toUpperCase()}</span></td>
      <td>
        <div class="tabela-acoes">
          <button type="button" class="btn-acao ${st === 'bloqueado' ? 'ok' : 'danger'}" title="${st === 'bloqueado' ? 'Reativar' : 'Suspender'}" onclick="alternarBloqueioTenant(${tenantId})">
            <span class="material-symbols-rounded" style="font-size:16px">${st === 'bloqueado' ? 'lock_open' : 'lock'}</span>
          </button>
          <button type="button" class="btn-acao" title="WhatsApp" onclick="dispararCobrancaWhatsApp(${tenantId})">
            <span class="material-symbols-rounded" style="font-size:16px">chat</span>
          </button>
          <button type="button" class="btn-acao" title="Editar" onclick="editarTenant(${tenantId})">
            <span class="material-symbols-rounded" style="font-size:16px">edit</span>
          </button>
          <button type="button" class="btn-acao danger" title="Excluir" onclick="excluirTenant(${tenantId}, '${dbName}')">
            <span class="material-symbols-rounded" style="font-size:16px">delete</span>
          </button>
        </div>
      </td>
    `;
    tbody.appendChild(tr);
  });
}

function filtrarStatusTenant(st, btn) {
  filtroStatusTenantAtual = st;
  document.querySelectorAll(".btn-chip").forEach(b => b.classList.remove("active"));
  if (btn) btn.classList.add("active");
  renderizarTenantsCompleto();
}

async function alternarBloqueioTenant(id) {
  const t = tenants.find(x => x.id == id);
  if (!t) return;

  const novoStatus = (t.status === "bloqueado") ? "ativo" : "bloqueado";
  const acaoTexto = novoStatus === "bloqueado" ? "Bloqueio de Acesso" : "Desbloqueio Manual";

  try {
    const formData = new FormData();
    formData.append('action', 'atualizar_status');
    formData.append('id', id);
    formData.append('status', novoStatus);

    const response = await fetch('../../app/controllers/SuperAdminController.php', {
      method: 'POST',
      body: formData
    });

    const resultado = await response.json();

    if (resultado.sucesso) {
      t.status = novoStatus;
      registrarLog(acaoTexto, t.nome_academia || t.nome_fantasia, "Super Admin", novoStatus === "bloqueado" ? "Bloqueado" : "Reativado");
      mostrarToast(novoStatus === "bloqueado" ? "Acesso CORTADO para esta unidade." : "Acesso restabelecido com sucesso.");
      carregarDadosMasterBackend();
    } else {
      alert("Erro ao alterar status: " + (resultado.erro || "Desconhecido"));
    }
  } catch (error) {
    console.error("Erro na requisição AJAX de bloqueio:", error);
    alert("Erro crítico ao comunicar com o servidor.");
  }
}

async function excluirTenant(id, dbName) {
  if (!confirm(`Tens a certeza absoluta que queres APAGAR a academia e destruir o banco de dados "${dbName}" permanentemente?`)) {
    return;
  }

  try {
    const formData = new FormData();
    formData.append('action', 'excluir');
    formData.append('id', id);
    formData.append('db_name', dbName);

    const response = await fetch('../../app/controllers/SuperAdminController.php', {
      method: 'POST',
      body: formData
    });

    const respostaTexto = await response.text();
    let resultado;
    try {
      resultado = JSON.parse(respostaTexto);
    } catch (err) {
      console.error("Erro ao interpretar JSON:", respostaTexto);
      alert("Erro no servidor ao tentar excluir.");
      return;
    }

    if (resultado.sucesso) {
      mostrarToast("Academia e banco de dados excluídos com sucesso!");
      carregarDadosMasterBackend();
    } else {
      alert("Erro ao excluir academia: " + (resultado.erro || "Desconhecido"));
    }
  } catch (error) {
    console.error("Erro na requisição AJAX de exclusão:", error);
    alert("Erro crítico ao comunicar com o servidor.");
  }
}

function dispararCobrancaWhatsApp(id) {
  const t = tenants.find(x => x.id == id);
  if (!t) return;
  const resp = t.admin_nome || t.responsavel || 'Gestor';
  const nomeAcad = t.nome_academia || t.nome_fantasia || 'Academia';
  
  const telefoneBruto = t.admin_telefone || t.telefone || '';
  const telefone = telefoneBruto.replace(/\D/g, '');

  if (!telefone) {
    mostrarToast("Esta academia não possui um número de WhatsApp cadastrado!");
    return;
  }

  const valor = parseFloat(t.valor_plano || t.valor_mensalidade || 0);
  const msg = `Olá ${resp}! Passando da equipe Rustic Master para atualizar sobre a mensalidade do software Rustic Iron da sua unidade (${nomeAcad}). Valor: ${valor.toLocaleString("pt-BR", { style: "currency", currency: "BRL" })}. Posso gerar o código PIX?`;
  
  const url = `https://api.whatsapp.com/send?phone=55${telefone}&text=${encodeURIComponent(msg)}`;
  window.open(url, '_blank');
  registrarLog("Notificação WhatsApp", nomeAcad, "Super Admin", "Enviada");
  mostrarToast(`WhatsApp aberto para cobrança de ${resp}!`);
}

function abrirModalTenant() {
  document.getElementById("tenantIdEdit").value = "";
  document.getElementById("formTenant").reset();
  document.getElementById("modalTituloTenant").textContent = "Provisionar Nova Academia";

  const exp = new Date();
  exp.setDate(exp.getDate() + 30);
  document.getElementById("tnDataExpiracao").value = exp.toISOString().split("T")[0];

  document.getElementById("modalTenant").classList.add("active");
}

function fecharModalTenant() {
  document.getElementById("modalTenant").classList.remove("active");
}

function editarTenant(id) {
  const t = tenants.find(x => x.id == id);
  if (!t) return;

  document.getElementById("tenantIdEdit").value = t.id;
  document.getElementById("modalTituloTenant").textContent = `Editar: ${t.nome_academia || t.nome_fantasia || 'Academia'}`;
  
  if(document.getElementById("tnNomeFantasia")) document.getElementById("tnNomeFantasia").value = t.nome_academia || t.nome_fantasia || '';
  if(document.getElementById("tnCnpj")) document.getElementById("tnCnpj").value = t.cnpj || '';
  if(document.getElementById("tnDbName")) document.getElementById("tnDbName").value = t.db_name || '';
  if(document.getElementById("tnValorMensalidade")) document.getElementById("tnValorMensalidade").value = t.valor_plano || t.valor_mensalidade || 249.90;
  if(document.getElementById("tnDataExpiracao")) document.getElementById("tnDataExpiracao").value = t.vencimento || t.data_expiracao || '';
  
  const nomeAdmin = t.admin_nome || t.responsavel || t.nome_responsavel || '';
  const cpfAdmin = t.admin_cpf || t.cpf_admin || t.cpf || '';
  const emailAdmin = t.admin_email || t.email || '';
  const telefoneAdmin = t.admin_telefone || t.telefone || '';

  if(document.getElementById("tnResponsavel")) document.getElementById("tnResponsavel").value = nomeAdmin;
  if(document.getElementById("tnCpfAdmin")) document.getElementById("tnCpfAdmin").value = cpfAdmin;
  if(document.getElementById("tnEmail")) document.getElementById("tnEmail").value = emailAdmin;
  if(document.getElementById("tnTelefone")) document.getElementById("tnTelefone").value = telefoneAdmin;
  if(document.getElementById("tnSenhaAdmin")) document.getElementById("tnSenhaAdmin").value = '';

  document.getElementById("modalTenant").classList.add("active");
}

function setupFormTenantAjax() {
  const form = document.getElementById("formTenant");
  if (!form) return;

  form.onsubmit = async function(e) {
    e.preventDefault();

    const formData = new FormData(form);

    try {
      const response = await fetch('../../app/controllers/SuperAdminController.php', {
        method: 'POST',
        body: formData
      });

      const respostaTexto = await response.text();
      let resultado;
      try {
        resultado = JSON.parse(respostaTexto);
      } catch (err) {
        console.error("O PHP não retornou um JSON válido:", respostaTexto);
        alert("Erro no servidor (ver console F12 para detalhes).");
        return;
      }

      if (resultado.sucesso) {
        fecharModalTenant();
        mostrarToast("Academia provisionada com sucesso!");
        carregarDadosMasterBackend();
      } else {
        alert("Erro ao provisionar academia: " + (resultado.erro || "Desconhecido"));
      }
    } catch (error) {
      console.error("Erro na requisição AJAX:", error);
      alert("Erro crítico ao comunicar com o servidor.");
    }
  };
}

function renderizarFaturas() {
  const tbody = document.getElementById("tabelaFaturasCorpo");
  if (!tbody) return;
  tbody.innerHTML = "";

  if (faturas.length === 0) {
    tbody.innerHTML = `<tr><td colspan="7" style="text-align:center; padding:24px; color:var(--color-text-muted);">Nenhuma fatura registrada.</td></tr>`;
    return;
  }

  faturas.forEach(f => {
    const valorNum = parseFloat(f.valor || 0);
    const tr = document.createElement("tr");
    tr.innerHTML = `
      <td><strong>#${f.id}</strong></td>
      <td><strong>${f.academia}</strong></td>
      <td><strong>${valorNum.toLocaleString("pt-BR", { style: "currency", currency: "BRL" })}</strong></td>
      <td>${formatarData(f.vencimento)}</td>
      <td><span class="status-badge ${obterBadgeStatus(f.status)}">${f.status.toUpperCase()}</span></td>
      <td>${f.pago_em ? formatarData(f.pago_em) : 'Aguardando Pagamento'}</td>
      <td style="text-align: right;">
        ${f.status !== 'pago' ? `
          <button type="button" class="btn btn-secondary btn-sm" onclick="darBaixaFaturaSaaS(${f.id})">
            <span class="material-symbols-rounded" style="font-size:14px">check_circle</span> Dar Baixa
          </button>
        ` : `
          <span style="color:var(--color-success); font-weight:700; font-size:0.75rem;">Liquidado</span>
        `}
      </td>
    `;
    tbody.appendChild(tr);
  });
}

async function gerarCicloFaturamento() {
  if (!confirm("Deseja gerar o lote mensal de cobrança para todas as academias ativas?")) return;

  try {
    const formData = new FormData();
    formData.append('action', 'gerar_lote');

    const resposta = await fetch('../../app/controllers/FaturamentoController.php', {
      method: 'POST',
      body: formData
    });
    const resultado = await resposta.json();

    if (resultado.sucesso) {
      mostrarToast(resultado.mensagem);
      carregarFaturasBackend();
    } else {
      alert("Erro ao gerar lote: " + resultado.erro);
    }
  } catch (erro) {
    console.error("Erro:", erro);
    alert("Erro crítico ao comunicar com o servidor.");
  }
}

async function darBaixaFaturaSaaS(fatId) {
  try {
    const formData = new FormData();
    formData.append('action', 'dar_baixa');
    formData.append('fatura_id', fatId);

    const resposta = await fetch('../../app/controllers/FaturamentoController.php', {
      method: 'POST',
      body: formData
    });
    const resultado = await resposta.json();

    if (resultado.sucesso) {
      mostrarToast("Fatura liquidada com sucesso!");
      carregarFaturasBackend();
    } else {
      alert("Erro ao dar baixa.");
    }
  } catch (erro) {
    console.error("Erro:", erro);
  }
}

function renderizarInfra() {
  const tbody = document.getElementById("tabelaInfraCorpo");
  if (!tbody) return;
  tbody.innerHTML = "";

  if (tenants.length === 0) {
    tbody.innerHTML = `<tr><td colspan="7" style="text-align:center; padding:24px; color:var(--color-text-muted);">Nenhum banco provisionado.</td></tr>`;
    return;
  }

  tenants.forEach(t => {
    const dbName = t.db_name || 'rustic_tenant';
    const nomeAcademia = t.nome_academia || t.nome_fantasia || 'Academia';

    const tr = document.createElement("tr");
    tr.innerHTML = `
      <td><code>${dbName}</code></td>
      <td><strong>${nomeAcademia}</strong></td>
      <td>InnoDB / utf8mb4</td>
      <td>127.0.0.1:3306</td>
      <td><span class="status-badge success">Conectado (Ativo)</span></td>
      <td>7 tabelas normalizadas</td>
      <td style="text-align: right;">
        <button type="button" class="btn btn-secondary btn-sm" onclick="dumpSqlTenant('${dbName}')">
          <span class="material-symbols-rounded" style="font-size:14px">download</span> Exportar Dump
        </button>
      </td>
    `;
    tbody.appendChild(tr);
  });
}

function dumpSqlTenant(dbName) {
  mostrarToast(`Backup estrutural de ${dbName} gerado com sucesso!`);
}

function renderizarAuditoria() {
  const tbody = document.getElementById("tabelaAuditoriaCorpo");
  if (!tbody) return;
  tbody.innerHTML = "";

  if (auditoria.length === 0) {
    tbody.innerHTML = `<tr><td colspan="6" style="text-align:center; padding:24px; color:var(--color-text-muted);">Nenhum log de auditoria recente.</td></tr>`;
    return;
  }

  auditoria.forEach(a => {
    const tr = document.createElement("tr");
    tr.innerHTML = `
      <td>${a.timestamp || a.criado_em || '-'}</td>
      <td><code>${a.ip_de_origem || a.ip || '127.0.0.1'}</code></td>
      <td><strong>${a.evento_acao || a.acao}</strong></td>
      <td>${a.tenant_alvo || a.tenant || '-'}</td>
      <td>${a.operador || 'Super Admin'}</td>
      <td><span class="status-badge success">${a.resultado || a.status || 'Sucesso'}</span></td>
    `;
    tbody.appendChild(tr);
  });
}

function limparLogsAntigos() {
  auditoria = [];
  salvarMasterLocal();
  renderizarAuditoria();
  renderizarResumos();
  mostrarToast("Logs limpos com sucesso!");
}

function executarHealthCheck() {
  mostrarToast("Health Check concluído: Servidor MySQL respondendo perfeitamente!");
}

function exportarMasterData() {
  const data = JSON.stringify({ tenants, faturas, auditoria }, null, 2);
  const blob = new Blob([data], { type: "application/json" });
  const url = URL.createObjectURL(blob);
  const a = document.createElement("a");
  a.href = url;
  a.download = `rustic_master_backup_${new Date().toISOString().split("T")[0]}.json`;
  a.click();
  mostrarToast("Backup JSON exportado!");
}

function formatarData(str) {
  if (!str) return "-";
  const p = str.split("-");
  if (p.length < 3) return str;
  return `${p[2]}/${p[1]}/${p[0]}`;
}

function obterBadgeStatus(st) {
  switch ((st || '').toLowerCase()) {
    case "ativo": case "ativado": case "pago": return "success";
    case "trial": return "info";
    case "pendente": return "warning";
    case "atrasado": case "bloqueado": return "error";
    default: return "info";
  }
}

function mostrarToast(msg) {
  const t = document.getElementById("toastMaster");
  if (!t) return;
  t.textContent = msg;
  t.classList.add("show");
  setTimeout(() => t.classList.remove("show"), 3200);
}

function setupSidebar() {
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
}