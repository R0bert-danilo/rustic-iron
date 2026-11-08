<!DOCTYPE html>
<html lang="pt-BR" data-theme="dark">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Rustic Master - Console Central SaaS</title>

  <!-- Google Fonts: Inter -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />

  <!-- Google Material Symbols Rounded -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
  <link rel="stylesheet" href="../css/superadmin/superadmin.css" />
</head>
<body>
  <div class="dashboard-container">
    <!-- SIDEBAR MASTER -->
    <aside class="dashboard-sidebar" id="dashboardSidebar">
      <div class="dashboard-brand">
        <button class="dashboard-sidebar-toggle" id="btnToggleSidebar">
          <span class="material-symbols-rounded">menu</span>
        </button>
        <a class="logo" href="javascript:void(0)" onclick="navegarMaster('visao-geral')">
          <span class="brand-badge">RUSTIC</span> MASTER
        </a>
      </div>

      <nav class="dashboard-nav">
        <div class="dashboard-nav-section">
          <span class="nav-section-title">GESTÃO SAAS</span>
          <a href="javascript:void(0)" class="dashboard-nav-item active" data-target="visao-geral" onclick="navegarMaster('visao-geral')">
            <span class="nav-icon material-symbols-rounded">monitoring</span>
            <span class="nav-label">Métricas Globais</span>
          </a>
          <a href="javascript:void(0)" class="dashboard-nav-item" data-target="clientes" onclick="navegarMaster('clientes')">
            <span class="nav-icon material-symbols-rounded">domain</span>
            <span class="nav-label">Academias (Tenants)</span>
          </a>
          <a href="javascript:void(0)" class="dashboard-nav-item" data-target="cobrancas" onclick="navegarMaster('cobrancas')">
            <span class="nav-icon material-symbols-rounded">payments</span>
            <span class="nav-label">Cobranças & Faturas</span>
          </a>
          <a href="javascript:void(0)" class="dashboard-nav-item" data-target="infra" onclick="navegarMaster('infra')">
            <span class="nav-icon material-symbols-rounded">dns</span>
            <span class="nav-label">Bancos & Servidores</span>
          </a>
          <a href="javascript:void(0)" class="dashboard-nav-item" data-target="auditoria" onclick="navegarMaster('auditoria')">
            <span class="nav-icon material-symbols-rounded">policy</span>
            <span class="nav-label">Auditoria & Logs</span>
          </a>
        </div>

        <div class="dashboard-nav-section">
          <span class="nav-section-title">PRODUÇÃO & DEPLOY</span>
          <a href="javascript:void(0)" class="dashboard-nav-item" onclick="executarHealthCheck()">
            <span class="nav-icon material-symbols-rounded">health_and_safety</span>
            <span class="nav-label">Health Check DB</span>
          </a>
          <a href="javascript:void(0)" class="dashboard-nav-item" onclick="exportarMasterData()">
            <span class="nav-icon material-symbols-rounded">file_download</span>
            <span class="nav-label">Exportar Base JSON</span>
          </a>
        </div>
      </nav>
    </aside>

    <div class="dashboard-sidebar-overlay" id="dashboardSidebarOverlay"></div>

    <!-- MAIN WRAPPER -->
    <main class="dashboard-main">
      <header class="dashboard-header">
        <div class="dashboard-header-content">
          <button class="dashboard-sidebar-toggle" id="btnToggleSidebarHeader">
            <span class="material-symbols-rounded">menu</span>
          </button>
          <h1 class="dashboard-header-title" id="pageMainTitle">Painel de Métricas do SaaS</h1>
        </div>

        <div class="search-container">
          <span class="search-icon material-symbols-rounded">search</span>
          <input type="search" class="search-input" id="buscaMaster" placeholder="Filtrar por nome, CNPJ, banco ou responsável..." oninput="filtrarMasterGlobal(this.value)" />
        </div>

        <div class="dashboard-header-actions">
          <!-- BOLHA DE PERFIL EXPANSÍVEL NO TOPO -->
          <div class="header-profile-wrapper">
            <div class="profile-avatar-trigger" onclick="toggleMenuPerfil(event)" title="Perfil de Super Admin">
              <img src="../../assets/img/default-avatar.png" alt="Avatar do Dono" id="avatarPerfilMaster" class="profile-avatar-img">
              <div class="avatar-status-dot"></div>
            </div>

            <!-- Menu Dropdown que expande ao clicar -->
            <div class="profile-dropdown-menu" id="profileDropdownMenu">
              <div class="dropdown-user-info">
                <div class="dropdown-avatar-container">
                  <img src="../../assets/img/default-avatar.png" alt="Avatar" id="avatarPerfilMasterDropdown" class="profile-avatar-img">
                  <label for="inputAlterarFoto" class="avatar-edit-badge" title="Alterar foto">
                    <span class="material-symbols-rounded" style="font-size: 11px;">photo_camera</span>
                  </label>
                  <input type="file" id="inputAlterarFoto" style="display: none;" accept="image/*" onchange="atualizarFotoPerfil(event)">
                </div>
                <div class="dropdown-text-meta">
                  <span class="dropdown-name" id="nomeUsuarioLogado">Robert Danilo</span>
                  <span class="dropdown-role">Super Admin &bull; <small>Console do Dono</small></span>
                </div>
              </div>
              
              <div class="dropdown-divider"></div>

              <button type="button" class="dropdown-logout-btn" onclick="fazerLogoutMaster()">
                <span class="material-symbols-rounded" style="font-size: 18px;">logout</span>
                <span>Encerrar Sessão</span>
              </button>
            </div>
          </div>
        </div>
      </header>

      <div class="dashboard-content">
        <!-- 1. SEÇÃO: VISÃO GERAL / MÉTRICAS EXECUTIVAS -->
        <section id="view-visao-geral" class="view-section active-view">
          <div class="stats-grid">
            <div class="stat-card">
              <div class="stat-card-header">
                <span class="stat-card-title">MRR (Receita Recorrente)</span>
                <div class="stat-card-icon success"><span class="material-symbols-rounded">paid</span></div>
              </div>
              <div class="stat-card-value" id="kpiMrr">R$ 0,00</div>
              <div class="stat-card-change positive"><span class="material-symbols-rounded">trending_up</span><span id="kpiArr">ARR anual: R$ 0,00</span></div>
            </div>

            <div class="stat-card">
              <div class="stat-card-header">
                <span class="stat-card-title">Clientes Ativos</span>
                <div class="stat-card-icon primary"><span class="material-symbols-rounded">domain</span></div>
              </div>
              <div class="stat-card-value" id="kpiTotalTenants">0</div>
              <div class="stat-card-change" id="kpiTenantsDetalhados">0 pagantes | 0 trial</div>
            </div>

            <div class="stat-card">
              <div class="stat-card-header">
                <span class="stat-card-title">Inadimplência Aberta</span>
                <div class="stat-card-icon error"><span class="material-symbols-rounded">warning</span></div>
              </div>
              <div class="stat-card-value" id="kpiInadimplencia">R$ 0,00</div>
              <div class="stat-card-change negative"><span id="kpiQtdAtrasadasMaster">0 academias vencidas</span></div>
            </div>

            <div class="stat-card">
              <div class="stat-card-header">
                <span class="stat-card-title">Bancos Provisionados</span>
                <div class="stat-card-icon info"><span class="material-symbols-rounded">database</span></div>
              </div>
              <div class="stat-card-value" id="kpiBancosIsolados">0</div>
              <div class="stat-card-change positive">100% de isolamento físico</div>
            </div>
          </div>

          <!-- GRÁFICO RESUMO & ATIVIDADES CRÍTICAS -->
          <div class="dual-overview-grid">
            <div class="dashboard-table-container">
              <div class="dashboard-table-header">
                <h3 class="dashboard-table-title">Vencimentos Iminentes (Próximos 7 dias)</h3>
                <button class="btn btn-secondary btn-sm" onclick="navegarMaster('cobrancas')">Todas as Faturas</button>
              </div>
              <table class="dashboard-table">
                <thead>
                  <tr>
                    <th>Academia Cliente</th>
                    <th>Valor do Plano</th>
                    <th>Vencimento</th>
                    <th>Situação</th>
                    <th style="text-align: right;">Ação</th>
                  </tr>
                </thead>
                <tbody id="tabelaVencimentosIminentes"></tbody>
              </table>
            </div>

            <div class="dashboard-table-container">
              <div class="dashboard-table-header">
                <h3 class="dashboard-table-title">Últimas Ações Registradas no Console</h3>
                <button class="btn btn-secondary btn-sm" onclick="navegarMaster('auditoria')">Ver Auditoria</button>
              </div>
              <table class="dashboard-table">
                <thead>
                  <tr>
                    <th>Data/Hora</th>
                    <th>Ação Executada</th>
                    <th>Tenant Alvo</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody id="tabelaResumoAuditoria"></tbody>
              </table>
            </div>
          </div>
        </section>

        <!-- 2. SEÇÃO: GESTÃO DE TENANTS (ACADEMIAS) -->
        <section id="view-clientes" class="view-section">
          <div class="toolbar-filtros">
            <div class="filtros-chips">
              <button class="btn-chip active" data-status="todos" onclick="filtrarStatusTenant('todos', this)">Todos (<span id="cntFiltroTodos">0</span>)</button>
              <button class="btn-chip" data-status="ativo" onclick="filtrarStatusTenant('ativo', this)">Ativos (<span id="cntFiltroAtivos">0</span>)</button>
              <button class="btn-chip" data-status="trial" onclick="filtrarStatusTenant('trial', this)">Trial (Teste) (<span id="cntFiltroTrial">0</span>)</button>
              <button class="btn-chip" data-status="atrasado" onclick="filtrarStatusTenant('atrasado', this)">Atrasados (<span id="cntFiltroAtrasados">0</span>)</button>
              <button class="btn-chip" data-status="bloqueado" onclick="filtrarStatusTenant('bloqueado', this)">Bloqueados (<span id="cntFiltroBloqueados">0</span>)</button>
            </div>
          </div>

          <div class="dashboard-table-container">
            <div class="dashboard-table-header">
              <h3 class="dashboard-table-title">Catálogo Geral de Academias Contratantes</h3>
              <button class="btn btn-primary btn-sm" onclick="abrirModalTenant()">
                <span class="material-symbols-rounded">domain_add</span> Cadastrar Academia
              </button>
            </div>
            <table class="dashboard-table">
              <thead>
                <tr>
                  <th>Academia / Razão Social</th>
                  <th>Responsável & Telefone</th>
                  <th>Valor Plano</th>
                  <th>Dia Venc.</th>
                  <th>Banco de Dados</th>
                  <th>Status Assinatura</th>
                  <th style="text-align: right;">Controles de Gestão</th>
                </tr>
              </thead>
              <tbody id="tabelaTenantsCorpo"></tbody>
            </table>
          </div>
        </section>

        <!-- 3. SEÇÃO: COBRANÇAS & FATURAMENTO SAAS -->
        <section id="view-cobrancas" class="view-section">
          <div class="dashboard-table-container">
            <div class="dashboard-table-header">
              <h3 class="dashboard-table-title">Gestão de Mensalidades Cobradas pelo Sistema</h3>
              <div style="display:flex; gap:10px;">
                <button class="btn btn-secondary btn-sm" onclick="gerarCicloFaturamento()">
                  <span class="material-symbols-rounded">autorenew</span> Rodar Lote do Mês
                </button>
              </div>
            </div>
            <table class="dashboard-table">
              <thead>
                <tr>
                  <th>Fatura ID</th>
                  <th>Academia Pagante</th>
                  <th>Valor</th>
                  <th>Vencimento</th>
                  <th>Estado</th>
                  <th>Data de Liquidação</th>
                  <th style="text-align: right;">Ação</th>
                </tr>
              </thead>
              <tbody id="tabelaFaturasCorpo"></tbody>
            </table>
          </div>
        </section>

        <!-- 4. SEÇÃO: INFRAESTRUTURA & BANCOS INDIVIDUAIS -->
        <section id="view-infra" class="view-section">
          <div class="dashboard-table-container">
            <div class="dashboard-table-header">
              <h3 class="dashboard-table-title">Servidores MySQL & Bancos de Dados Isolados (Tenants)</h3>
              <button class="btn btn-secondary btn-sm" onclick="executarHealthCheck()">
                <span class="material-symbols-rounded">sync</span> Atualizar Conexões
              </button>
            </div>
            <table class="dashboard-table">
              <thead>
                <tr>
                  <th>Identificador DB</th>
                  <th>Unidade Proprietária</th>
                  <th>Engine / Charset</th>
                  <th>Porta / Host</th>
                  <th>Status Conexão</th>
                  <th>Tabelas Instaladas</th>
                  <th style="text-align: right;">Ações de DBA</th>
                </tr>
              </thead>
              <tbody id="tabelaInfraCorpo"></tbody>
            </table>
          </div>
        </section>

        <!-- 5. SEÇÃO: AUDITORIA & LOGS -->
        <section id="view-auditoria" class="view-section">
          <div class="dashboard-table-container">
            <div class="dashboard-table-header">
              <h3 class="dashboard-table-title">Logs de Segurança e Acessos ao Sistema</h3>
              <button class="btn btn-secondary btn-sm" onclick="limparLogsAntigos()">
                <span class="material-symbols-rounded">delete_sweep</span> Limpar Logs Antigos
              </button>
            </div>
            <table class="dashboard-table">
              <thead>
                <tr>
                  <th>Timestamp</th>
                  <th>IP de Origem</th>
                  <th>Evento / Ação</th>
                  <th>Tenant / Alvo</th>
                  <th>Operador</th>
                  <th>Resultado</th>
                </tr>
              </thead>
              <tbody id="tabelaAuditoriaCorpo"></tbody>
            </table>
          </div>
        </section>
      </div>
    </main>
  </div>

  <!-- MODAL PROVISIONAMENTO DE TENANT -->
  <div class="modal-overlay" id="modalTenant">
    <div class="modal-card modal-tenant-largura">
      <div class="modal-header">
        <h3 id="modalTituloTenant">Provisionar Nova Academia</h3>
        <button type="button" class="modal-close" onclick="fecharModalTenant()">&times;</button>
      </div>

      <form id="formTenant" onsubmit="salvarTenant(event)">
        <input type="hidden" id="tenantIdEdit" name="tenant_id_edit" value="" />

        <div class="modal-body form-grid-tenant">
          <div class="form-row full">
            <label for="tnNomeFantasia">Nome da Academia / Fantasia</label>
            <input type="text" id="tnNomeFantasia" name="nome_academia" required placeholder="Ex: Viking Iron Gym" class="input-t" />
          </div>

          <div class="form-row">
            <label for="tnCnpj">CNPJ</label>
            <input type="text" id="tnCnpj" name="cnpj" required placeholder="00.000.000/0001-00" maxlength="18" class="input-t" />
          </div>

          <div class="form-row">
            <label for="tnDbName">Nome do Banco de Dados (ex: rustic_tenant_2)</label>
            <input type="text" id="tnDbName" name="db_name" required placeholder="rustic_tenant_2" class="input-t" />
          </div>

          <div class="form-row">
            <label for="tnValorMensalidade">Valor do Software (R$/mês)</label>
            <input type="number" step="0.01" min="0" id="tnValorMensalidade" name="valor_plano" required value="249.90" class="input-t" />
          </div>

          <div class="form-row">
            <label for="tnDataExpiracao">Data de Vencimento / Expiração</label>
            <input type="date" id="tnDataExpiracao" name="vencimento" required class="input-t" />
          </div>

          <div class="form-row full">
            <hr style="border: 0; border-top: 1px solid rgba(255,255,255,0.1); margin: 10px 0;">
            <h4 style="color: var(--text-color); margin-bottom: 5px;">Credenciais do Administrador Inicial</h4>
          </div>

          <div class="form-row">
            <label for="tnResponsavel">Nome do Admin</label>
            <input type="text" id="tnResponsavel" name="admin_nome" required placeholder="Nome completo" class="input-t" />
          </div>

          <div class="form-row">
            <label for="tnCpfAdmin">CPF do Admin</label>
            <input type="text" id="tnCpfAdmin" name="admin_cpf" required placeholder="000.000.000-00" class="input-t" />
          </div>

          <div class="form-row">
            <label for="tnEmail">E-mail de Acesso</label>
            <input type="email" id="tnEmail" name="admin_email" placeholder="diretoria@academia.com" class="input-t" />
          </div>

          <div class="form-row">
            <label for="tnTelefone">WhatsApp / Telefone do Admin</label>
            <input type="text" id="tnTelefone" name="admin_telefone" placeholder="(00) 00000-0000" class="input-t" />
          </div>

          <div class="form-row full">
            <label for="tnSenhaAdmin">Senha Provisória</label>
            <input type="password" id="tnSenhaAdmin" name="admin_senha" required placeholder="Senha de acesso" class="input-t" />
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" onclick="fecharModalTenant()">Cancelar</button>
          <button type="submit" class="btn btn-primary" id="btnSalvarTenant">
            <span class="material-symbols-rounded">save</span> Salvar & Provisionar
          </button>
        </div>
      </form>
    </div>
  </div>

  <div class="toast-aviso" id="toastMaster">Ação executada com sucesso!</div>

  <script src="../js/superadmin/superadmin.js"></script>
</body>
</html>