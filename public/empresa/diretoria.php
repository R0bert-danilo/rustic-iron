<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$cargoBruto = $_SESSION['usuario_cargo'] ?? '';
$cargoAtual = mb_strtoupper(trim($cargoBruto), 'UTF-8');
$cargoAtual = str_replace(['Ó', 'Ô', 'Õ'], 'O', $cargoAtual);

$permitidos = ['CEO', 'SOCIO', 'CEO / SOCIO', 'ADMIN', 'DIRETORIA'];

if (!isset($_SESSION['usuario_id']) || !in_array($cargoAtual, $permitidos)) {
    header("Location: /rustic_iron/auth/auth.php?erro=acesso_negado");
    exit;
}

// Dados dinâmicos do utilizador logado na sessão
$nomeUsuario = $_SESSION['usuario_nome'] ?? 'Diretoria Geral';
$cargoUsuario = $_SESSION['usuario_cargo'] ?? 'Gestão Estratégica';
?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="dark">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Rustic Iron - Diretoria Geral & Gestão Estratégica</title>

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
  <link rel="stylesheet" href="../css/empresa/diretoria.css" />
</head>
<body>
  <div class="dashboard-container">
    <!-- SIDEBAR -->
    <aside class="dashboard-sidebar" id="dashboardSidebar">
      <div class="dashboard-brand">
        <button class="dashboard-sidebar-toggle" id="btnToggleSidebar">
          <span class="material-symbols-rounded">menu</span>
        </button>
        <a class="logo" href="javascript:void(0)" onclick="navegarDiretoria('visao-operacional')">Rustic Iron</a>
      </div>

      <nav class="dashboard-nav">
        <div class="dashboard-nav-section">
          <a href="javascript:void(0)" class="dashboard-nav-item active" data-target="visao-operacional" onclick="navegarDiretoria('visao-operacional')">
            <span class="nav-icon material-symbols-rounded">monitoring</span>
            <span class="nav-label">Visão Tática</span>
          </a>
          <a href="javascript:void(0)" class="dashboard-nav-item" data-target="escalas" onclick="navegarDiretoria('escalas')">
            <span class="nav-icon material-symbols-rounded">event_note</span>
            <span class="nav-label">Escala Mensal & Plantões</span>
          </a>
          <a href="javascript:void(0)" class="dashboard-nav-item" data-target="equipe" onclick="navegarDiretoria('equipe')">
            <span class="nav-icon material-symbols-rounded">badge</span>
            <span class="nav-label">Quadro da Equipe</span>
          </a>
          <a href="javascript:void(0)" class="dashboard-nav-item" data-target="equipamentos" onclick="navegarDiretoria('equipamentos')">
            <span class="nav-icon material-symbols-rounded">precision_manufacturing</span>
            <span class="nav-label">Equipamentos & Parque</span>
          </a>
          <a href="javascript:void(0)" class="dashboard-nav-item" data-target="setores" onclick="navegarDiretoria('setores')">
            <span class="nav-icon material-symbols-rounded">domain</span>
            <span class="nav-label">Setores & Áreas</span>
          </a>
          <a href="javascript:void(0)" class="dashboard-nav-item" data-target="lotacao" onclick="navegarDiretoria('lotacao')">
            <span class="nav-icon material-symbols-rounded">sensor_occupied</span>
            <span class="nav-label">Fluxo & Lotação</span>
          </a>
        </div>
      </nav>
    </aside>

    <div class="dashboard-sidebar-overlay" id="dashboardSidebarOverlay"></div>

    <!-- MAIN CONTENT -->
    <main class="dashboard-main">
      <header class="dashboard-header">
        <div class="dashboard-header-content">
          <button class="dashboard-sidebar-toggle" id="btnToggleSidebarHeader">
            <span class="material-symbols-rounded">menu</span>
          </button>
          <h1 class="dashboard-header-title" id="pageMainTitle">Visão Estratégica & DRE</h1>
        </div>

        <div class="search-container" id="searchContainer">
          <span class="search-icon material-symbols-rounded">search</span>
          <input type="search" class="search-input" placeholder="Pesquisar colaborador ou equipamento..." id="inputBuscaGlobalDiretoria" />
        </div>

        <div class="dashboard-header-actions">
          <!-- CABEÇALHO LIMPO (APENAS PERFIL NO TOPO) -->
          <div class="header-profile-wrapper">
            <div class="profile-avatar-trigger" onclick="toggleMenuPerfil(event)" title="Perfil de Diretoria">
              <span class="material-symbols-rounded fallback-avatar-icon">account_circle</span>
              <img src="" alt="Avatar" id="avatarPerfilDiretoria" class="profile-avatar-img" style="display: none;" />
              <div class="avatar-status-dot"></div>
            </div>

            <!-- Menu Dropdown que expande ao clicar -->
            <div class="profile-dropdown-menu" id="profileDropdownMenu">
              <div class="dropdown-user-info">
                <div class="dropdown-avatar-container">
                  <span class="material-symbols-rounded fallback-avatar-icon" style="font-size: 36px;">account_circle</span>
                  <img src="" alt="Avatar" id="avatarPerfilDiretoriaDropdown" class="profile-avatar-img" style="display: none;" />
                  <label for="inputAlterarFoto" class="avatar-edit-badge" title="Alterar foto">
                    <span class="material-symbols-rounded" style="font-size: 11px;">photo_camera</span>
                  </label>
                  <input type="file" id="inputAlterarFoto" style="display: none;" accept="image/*" onchange="atualizarFotoPerfil(event)">
                </div>
                <div class="dropdown-text-meta">
                  <span class="dropdown-name"><?= htmlspecialchars($nomeUsuario) ?></span>
                  <span class="dropdown-role"><?= htmlspecialchars($cargoUsuario) ?> &bull; <small>Rustic Iron</small></span>
                </div>
              </div>
              
              <div class="dropdown-divider"></div>

              <button type="button" class="dropdown-action-btn" onclick="abrirModalTrocarSenhaDiretoria()">
                <span class="material-symbols-rounded" style="font-size: 16px;">key</span>
                <span>Alterar Senha</span>
              </button>

              <button type="button" class="dropdown-logout-btn" onclick="fazerLogoutDiretoria()">
                <span class="material-symbols-rounded" style="font-size: 16px;">logout</span>
                <span>Encerrar Sessão</span>
              </button>
            </div>
          </div>
        </div>
      </header>

      <div class="dashboard-content">
        <!-- 1. VISÃO TÁTICA -->
        <section id="view-visao-operacional" class="view-section active-view">
          <div class="stats-grid">
            <div class="stat-card">
              <div class="stat-card-header">
                <span class="stat-card-title">Equipe no Turno Agora</span>
                <div class="stat-card-icon primary"><span class="material-symbols-rounded">group</span></div>
              </div>
              <div class="stat-card-value" id="kpiEquipePresente">6</div>
              <div class="stat-card-change positive"><span class="material-symbols-rounded">check</span> 100% da escala cumprida</div>
            </div>

            <div class="stat-card">
              <div class="stat-card-header">
                <span class="stat-card-title">Lotação do Ginásio</span>
                <div class="stat-card-icon info"><span class="material-symbols-rounded">speed</span></div>
              </div>
              <div class="stat-card-value" id="kpiOcupacaoPercent">48%</div>
              <div class="stat-card-change" id="kpiOcupacaoDesc">58 de 120 alunos (Fluxo Confortável)</div>
            </div>

            <div class="stat-card">
              <div class="stat-card-header">
                <span class="stat-card-title">Parque de Máquinas</span>
                <div class="stat-card-icon success"><span class="material-symbols-rounded">build_circle</span></div>
              </div>
              <div class="stat-card-value" id="kpiMaquinasOperacionais">94%</div>
              <div class="stat-card-change" id="kpiMaquinasDesc">32 operacionais | 2 preventivas</div>
            </div>

            <div class="stat-card">
              <div class="stat-card-header">
                <span class="stat-card-title">Alertas Operacionais</span>
                <div class="stat-card-icon warning"><span class="material-symbols-rounded">warning</span></div>
              </div>
              <div class="stat-card-value" id="kpiAlertasQtd">1</div>
              <div class="stat-card-change negative" id="kpiAlertasDesc">Revisão cabo Crossover 1 pendente</div>
            </div>
          </div>

          <div class="operacional-dual-grid">
            <div class="dashboard-table-container">
              <div class="dashboard-table-header">
                <h3 class="dashboard-table-title">Escala de Professores & Recepção Hoje</h3>
                <button class="btn btn-secondary btn-sm" onclick="navegarDiretoria('equipe')">Ver Todos</button>
              </div>
              <table class="dashboard-table">
                <thead>
                  <tr>
                    <th>Profissional</th>
                    <th>Função</th>
                    <th>Turno</th>
                    <th>Presença</th>
                  </tr>
                </thead>
                <tbody id="tabelaResumoEscala"></tbody>
              </table>
            </div>

            <div class="dashboard-table-container">
              <div class="dashboard-table-header">
                <h3 class="dashboard-table-title">Preventivas & Manutenções Próximas</h3>
                <button class="btn btn-secondary btn-sm" onclick="navegarDiretoria('equipamentos')">Ver Parque</button>
              </div>
              <table class="dashboard-table">
                <thead>
                  <tr>
                    <th>Equipamento</th>
                    <th>Setor</th>
                    <th>Previsão</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody id="tabelaResumoManutencao"></tbody>
              </table>
            </div>
          </div>
        </section>

        <!-- 2. ESCALAS MENSAIS & PLANTÕES (MATRIZ INLINE) -->
        <section id="view-escalas" class="view-section">
          <div class="dashboard-table-container" style="padding: 24px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
              <div>
                <h3 style="font-size: 1.15rem; font-weight: 700;">Escala Geral da Equipe (Mês Inteiro)</h3>
                <p style="color: var(--color-text-secondary); font-size: 0.85rem;">Visualize e gerencie os plantões automáticos da equipe. Clique em qualquer dia para alterar turnos, atestados ou dedetização.</p>
              </div>
              <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                <input type="month" id="inputMesGeralInline" value="2026-11" onchange="mudarMesGeralInline(this.value)" style="background: #27272a; border: 1px solid #3f3f46; color: #fff; padding: 6px 12px; border-radius: 6px; cursor: pointer;">
                
                <!-- BOTÃO DE PLANTÕES FDS / FERIADOS -->
                <button class="btn btn-secondary btn-sm" onclick="abrirRelatorioPlantoesFDS()" style="background: #065f46; border: 1px solid #059669; color: #fff;">
                  <span class="material-symbols-rounded">list_alt</span> Plantões FDS / Feriados
                </button>
              </div>
            </div>

            <!-- Tabela Matriz Integrada Diretamente na Tela -->
            <div id="tabelaGeralContainerInline" style="width: 100%; min-height: 500px; overflow: auto; border: 1px solid #27272a; border-radius: 8px; background: #121214;">
              <!-- Tabela gerada via JS -->
            </div>
          </div>
        </section>

        <!-- 3. QUADRO DA EQUIPE -->
        <section id="view-equipe" class="view-section">
          <div class="toolbar-filtros"><div class="filtros-chips"></div></div>
          <div class="dashboard-table-container">
            <div class="dashboard-table-header">
              <h3 class="dashboard-table-title">Quadro de Colaboradores & Escalas</h3>
              
              <div style="display: flex; align-items: center; gap: 10px;">
                <!-- BOTÃO EXPANSÍVEL: HORÁRIOS & CARGOS -->
                <div class="header-profile-wrapper" id="wrapperHorariosCargos" style="position: relative;">
                  <button type="button" class="btn btn-secondary" onclick="toggleMenuHorariosCargos(event)">
                    <span class="material-symbols-rounded">schedule</span>
                    <span>Horários & Cargos</span>
                    <span class="material-symbols-rounded" style="font-size: 16px;">expand_more</span>
                  </button>

                  <div class="profile-dropdown-menu" id="dropdownHorariosCargos" style="width: 200px; right: 0; left: auto;">
                    <button type="button" class="dropdown-action-btn" onclick="abrirModalNovoCargo()">
                      <span class="material-symbols-rounded" style="font-size: 16px;">badge</span>
                      <span>Gerenciar Cargos</span>
                    </button>
                    <div class="dropdown-divider" style="margin: 6px 0;"></div>
                    <button type="button" class="dropdown-action-btn" onclick="abrirModalNovoTurno()">
                      <span class="material-symbols-rounded" style="font-size: 16px;">schedule</span>
                      <span>Gerenciar Turnos</span>
                    </button>
                  </div>
                </div>

                <!-- BOTÃO CADASTRAR COLABORADOR -->
                <button class="btn btn-primary" onclick="abrirModalNovoColaborador()">
                  <span class="material-symbols-rounded">person_add</span>
                  <span>Cadastrar Colaborador</span>
                </button>
              </div>
            </div>

            <table class="dashboard-table">
              <thead>
                <tr>
                  <th>Profissional / CREF</th>
                  <th>Função</th>
                  <th>Turno & Horário</th>
                  <th>Contato</th>
                  <th>Atletas Alocados</th>
                  <th>Situação</th>
                  <th style="text-align: right;">Ações</th>
                </tr>
              </thead>
              <tbody id="tabelaEquipeCorpo"></tbody>
            </table>
          </div>
        </section>

        <!-- 4. EQUIPAMENTOS -->
        <section id="view-equipamentos" class="view-section">
          <div class="toolbar-filtros"><div class="filtros-chips"></div></div>
          <div class="dashboard-table-container">
            <div class="dashboard-table-header">
              <h3 class="dashboard-table-title">Inventário Técnico do Ginásio</h3>
              <button class="btn btn-primary btn-sm" onclick="abrirModalNovoEquipamento()">
                <span class="material-symbols-rounded">add_circle</span> Cadastrar Máquina
              </button>
            </div>
            <table class="dashboard-table">
              <thead>
                <tr>
                  <th>Código / Equipamento</th>
                  <th>Setor</th>
                  <th>Qtd</th>
                  <th>Última Revisão</th>
                  <th>Próxima Preventiva</th>
                  <th>Condição Operacional</th>
                  <th style="text-align: right;">Ações</th>
                </tr>
              </thead>
              <tbody id="tabelaEquipamentosCorpo"></tbody>
            </table>
          </div>
        </section>

        <!-- 5. SETORES -->
        <section id="view-setores" class="view-section">
          <div class="dashboard-table-container">
            <div class="dashboard-table-header">
              <h3 class="dashboard-table-title">Gestão de Setores & Áreas do Ginásio</h3>
              <button class="btn btn-primary btn-sm" onclick="abrirModalNovoSetor()">
                <span class="material-symbols-rounded">add_business</span> Cadastrar Setor
              </button>
            </div>
            <table class="dashboard-table">
              <thead>
                <tr>
                  <th>Nome do Setor / Área</th>
                  <th>Responsável</th>
                  <th>Capacidade Máxima</th>
                  <th>Status</th>
                  <th style="text-align: right;">Ações</th>
                </tr>
              </thead>
              <tbody id="tabelaSetoresCorpo"></tbody>
            </table>
          </div>
        </section>

        <!-- 6. FLUXO & LOTAÇÃO -->
        <section id="view-lotacao" class="view-section">
          <div class="dashboard-table-container" style="padding: 24px;">
            <div class="lotacao-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
              <div>
                <h3 style="font-size:1.15rem; font-weight:700;">Capacidade Operacional & Zonas do Ginásio</h3>
                <p style="color:var(--color-text-secondary); font-size:0.85rem;">Distribuição de atletas por zona de treino em tempo real (funcionários excluídos).</p>
              </div>
              <div class="gauge-badge" style="background: #222; border: 1px solid #444; padding: 6px 12px; border-radius: 6px; display: flex; align-items: center; gap: 8px;">
                <span class="material-symbols-rounded" style="color:var(--color-primary);">sensors</span>
                <strong>0 / 0 Limite Técnico</strong>
              </div>
            </div>
            <div class="zonas-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px;"></div>
          </div>
        </section>
      </div>
    </main>
  </div>

  <!-- MODAL: COLABORADOR -->
  <div class="modal-overlay" id="modalEquipe">
    <div class="modal-card modal-equipe-largura">
      <div class="modal-header">
        <h3 id="modalTituloEquipe">Cadastrar Colaborador</h3>
        <button type="button" class="modal-close" onclick="fecharModalColaborador()">&times;</button>
      </div>
      <form id="formEquipe" onsubmit="salvarColaborador(event)">
        <input type="hidden" id="colaboradorIdEdit" value="" />
        <div class="modal-body form-equipe-grid">
          <div class="form-row-eq full">
            <label for="eqNome">Nome Completo</label>
            <input type="text" id="eqNome" required placeholder="Nome do profissional" class="input-eq" />
          </div>
          <div class="form-row-eq">
            <label for="eqCpf">CPF</label>
            <input type="text" id="eqCpf" required placeholder="000.000.000-00" maxlength="14" class="input-eq" />
          </div>
          <div class="form-row-eq">
            <label for="eqCargo">Cargo / Função</label>
            <select id="eqCargo" required class="select-eq"><option value="">Selecione...</option></select>
          </div>
          <div class="form-row-eq">
            <label for="eqCref">Registro CREF</label>
            <input type="text" id="eqCref" placeholder="000000-G/SE" class="input-eq" />
          </div>
          <div class="form-row-eq">
            <label for="eqTurno">Turno de Trabalho</label>
            <select id="eqTurno" required class="select-eq"><option value="">Selecione...</option></select>
          </div>
          <div class="form-row-eq">
            <label for="eqTelefone">Telefone / WhatsApp</label>
            <input type="tel" id="eqTelefone" required placeholder="(79) 99999-9999" class="input-eq" />
          </div>
          <div class="form-row-eq">
            <label for="eqEmail">E-mail Profissional</label>
            <input type="email" id="eqEmail" required placeholder="colaborador@rusticiron.com" class="input-eq" />
          </div>
          <div class="form-row-eq">
            <label for="eqAlunos">Atletas Alocados</label>
            <input type="number" min="0" id="eqAlunos" value="0" class="input-eq" />
          </div>
          <div class="form-row-eq full">
            <label for="eqEspecialidade">Especialidade / Foco Técnico</label>
            <input type="text" id="eqEspecialidade" placeholder="Ex: Levantamento terra" class="input-eq" />
          </div>
          <div class="form-row-eq full">
            <label for="eqSenhaProvisoria">Senha Provisória (Digite ou gere a senha)</label>
            <input type="text" id="eqSenhaProvisoria" class="input-eq" placeholder="Insira a senha provisória do colaborador" />
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" onclick="fecharModalColaborador()">Cancelar</button>
          <button type="submit" class="btn btn-primary">Salvar Colaborador</button>
        </div>
      </form>
    </div>
  </div>

  <!-- MODAL: GERENCIAR CARGOS (COM TABELA) -->
  <div class="modal-overlay" id="modalCargo">
    <div class="modal-card" style="max-width: 500px;">
      <div class="modal-header">
        <h3 id="modalTituloCargo">Gerenciar Cargos</h3>
        <button type="button" class="modal-close" onclick="fecharModalCargo()">&times;</button>
      </div>
      <form id="formCargo" onsubmit="salvarNovoCargo(event)">
        <input type="hidden" id="cargoIdEdit" value="" />
        <div class="modal-body" style="padding: 20px; display: flex; flex-direction: column; gap: 16px;">
          <div class="form-row-eq full">
            <label for="nomeNovoCargo">Nome do Cargo</label>
            <div style="display: flex; gap: 8px;">
              <input type="text" id="nomeNovoCargo" required placeholder="Ex: Personal Trainer" class="input-eq" />
              <button type="submit" class="btn btn-primary" id="btnSalvarCargo">Salvar</button>
            </div>
          </div>

          <div class="dashboard-table-container" style="max-height: 220px; overflow-y: auto; margin-bottom: 0;">
            <table class="dashboard-table" style="font-size: 0.8rem;">
              <thead>
                <tr>
                  <th>Cargo</th>
                  <th style="text-align: right;">Ações</th>
                </tr>
              </thead>
              <tbody id="tabelaCargosModalCorpo"></tbody>
            </table>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" onclick="fecharModalCargo()">Fechar</button>
        </div>
      </form>
    </div>
  </div>

  <!-- MODAL: GERENCIAR TURNOS (COM TABELA) -->
  <div class="modal-overlay" id="modalTurno">
    <div class="modal-card" style="max-width: 500px;">
      <div class="modal-header">
        <h3 id="modalTituloTurno">Gerenciar Turnos</h3>
        <button type="button" class="modal-close" onclick="fecharModalTurno()">&times;</button>
      </div>
      <form id="formTurno" onsubmit="salvarNovoTurno(event)">
        <input type="hidden" id="turnoIdEdit" value="" />
        <div class="modal-body" style="padding: 20px; display: flex; flex-direction: column; gap: 16px;">
          <div class="form-row-eq full">
            <label for="nomeNovoTurno">Descrição do Turno / Horário</label>
            <div style="display: flex; gap: 8px;">
              <input type="text" id="nomeNovoTurno" required placeholder="Ex: Manhã (06h às 14h)" class="input-eq" />
              <button type="submit" class="btn btn-primary" id="btnSalvarTurno">Salvar</button>
            </div>
          </div>

          <div class="dashboard-table-container" style="max-height: 220px; overflow-y: auto; margin-bottom: 0;">
            <table class="dashboard-table" style="font-size: 0.8rem;">
              <thead>
                <tr>
                  <th>Turno</th>
                  <th style="text-align: right;">Ações</th>
                </tr>
              </thead>
              <tbody id="tabelaTurnosModalCorpo"></tbody>
            </table>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" onclick="fecharModalTurno()">Fechar</button>
        </div>
      </form>
    </div>
  </div>

  <!-- OUTROS MODAIS NECESSÁRIOS -->
  <div class="modal-overlay" id="modalEquipamento">
    <div class="modal-card modal-equipamento-largura">
      <div class="modal-header">
        <h3 id="modalTituloEquipamento">Cadastrar Equipamento</h3>
        <button type="button" class="modal-close" onclick="fecharModalEquipamento()">&times;</button>
      </div>
      <form id="formEquipamento" onsubmit="salvarEquipamento(event)">
        <input type="hidden" id="equipamentoIdEdit" value="" />
        <div class="modal-body form-equipe-grid">
          <div class="form-row-eq full">
            <label for="eqpNome">Nome do Equipamento</label>
            <input type="text" id="eqpNome" required class="input-eq" />
          </div>
          <div class="form-row-eq">
            <label for="eqpCodigo">Código Patrimonial</label>
            <input type="text" id="eqpCodigo" required class="input-eq" />
          </div>
          <div class="form-row-eq">
            <label for="eqpSetor">Setor</label>
            <select id="eqpSetor" required class="select-eq"></select>
          </div>
          <div class="form-row-eq">
            <label for="eqpQuantidade">Quantidade</label>
            <input type="number" min="1" id="eqpQuantidade" value="1" required class="input-eq" />
          </div>
          <div class="form-row-eq">
            <label for="eqpEstado">Estado Operacional</label>
            <select id="eqpEstado" required class="select-eq">
              <option value="Excelente">Excelente</option>
              <option value="Revisão Próxima">Revisão Próxima</option>
              <option value="Em Manutenção">Em Manutenção</option>
            </select>
          </div>
          <div class="form-row-eq">
            <label for="eqpUltimaRevisao">Última Revisão</label>
            <input type="date" id="eqpUltimaRevisao" required class="input-eq" />
          </div>
          <div class="form-row-eq">
            <label for="eqpProximaRevisao">Próxima Revisão</label>
            <input type="date" id="eqpProximaRevisao" required class="input-eq" />
          </div>
          <div class="form-row-eq full">
            <label for="eqpObservacoes">Observações</label>
            <input type="text" id="eqpObservacoes" class="input-eq" />
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" onclick="fecharModalEquipamento()">Cancelar</button>
          <button type="submit" class="btn btn-primary">Salvar Equipamento</button>
        </div>
      </form>
    </div>
  </div>

  <div class="modal-overlay" id="modalSetor">
    <div class="modal-card modal-equipamento-largura">
      <div class="modal-header">
        <h3 id="modalTituloSetor">Cadastrar Setor</h3>
        <button type="button" class="modal-close" onclick="fecharModalSetor()">&times;</button>
      </div>
      <form id="formSetor" onsubmit="salvarSetor(event)">
        <input type="hidden" id="setorIdEdit" value="" />
        <div class="modal-body form-equipe-grid">
          <div class="form-row-eq full">
            <label for="setorNome">Nome do Setor</label>
            <input type="text" id="setorNome" required class="input-eq" />
          </div>
          <div class="form-row-eq">
            <label for="setorResponsavel">Responsável</label>
            <input type="text" id="setorResponsavel" required class="input-eq" />
          </div>
          <div class="form-row-eq">
            <label for="setorCapacidade">Capacidade</label>
            <input type="number" min="1" id="setorCapacidade" value="20" required class="input-eq" />
          </div>
          <div class="form-row-eq full">
            <label for="setorStatus">Status</label>
            <select id="setorStatus" required class="select-eq">
              <option value="ATIVO">Ativo</option>
              <option value="MANUTENCAO">Manutenção</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" onclick="fecharModalSetor()">Cancelar</button>
          <button type="submit" class="btn btn-primary">Salvar Setor</button>
        </div>
      </form>
    </div>
  </div>

  <div class="modal-overlay" id="modalEscalaMensal">
    <div class="modal-card modal-equipe-largura">
      <div class="modal-header">
        <h3 id="modalTituloEscalaMensal">Configurar Escala Mensal</h3>
        <button type="button" class="modal-close" onclick="fecharModalEscalaMensal()">&times;</button>
      </div>
      <form id="formEscalaMensal" onsubmit="salvarEscalaMensal(event)">
        <input type="hidden" id="escalaMensalIdEdit" value="" />
        <div class="modal-body form-equipe-grid">
          <div class="form-row-eq">
            <label for="emColaborador">Colaborador</label>
            <select id="emColaborador" required class="select-eq"><option value="">Selecione...</option></select>
          </div>
          <div class="form-row-eq">
            <label for="emMesAno">Mês/Ano</label>
            <input type="month" id="emMesAno" required class="input-eq" />
          </div>
          <div class="form-row-eq">
            <label for="emSegSex">Segunda a Sexta</label>
            <input type="text" id="emSegSex" required value="06:00 - 14:00" class="input-eq" />
          </div>
          <div class="form-row-eq">
            <label for="emSabado">Sábado</label>
            <select id="emSabado" class="select-eq">
              <option value="Folga">Folga</option>
              <option value="Feriado">Feriado</option>
              <option value="08:00 - 12:00">Trabalha (08h-12h)</option>
            </select>
          </div>
          <div class="form-row-eq full">
            <label for="emDomingo">Domingo</label>
            <select id="emDomingo" class="select-eq">
              <option value="Folga">Folga</option>
              <option value="Feriado">Feriado</option>
              <option value="08:00 - 12:00">Trabalha (Plantão 08h-12h)</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" onclick="fecharModalEscalaMensal()">Cancelar</button>
          <button type="submit" class="btn btn-primary">Salvar Escala</button>
        </div>
      </form>
    </div>
  </div>

  <div class="modal-overlay" id="modalTrocaEmergencia">
    <div class="modal-card" style="max-width: 480px;">
      <div class="modal-header">
        <h3>Troca de Emergência</h3>
        <button type="button" class="modal-close" onclick="fecharModalTrocaEmergencia()">&times;</button>
      </div>
      <form id="formTrocaEmergencia" onsubmit="salvarTrocaEmergencia(event)">
        <div class="modal-body" style="display: flex; flex-direction: column; gap: 14px;">
          <div class="form-row-eq full">
            <label for="trocaDataOcorrencia">Data da Ocorrência</label>
            <input type="date" id="trocaDataOcorrencia" required class="input-eq" />
          </div>
          <div class="form-row-eq full">
            <label for="trocaTipo">Tipo</label>
            <select id="trocaTipo" class="select-eq">
              <option value="Doença / Atestado">Doença / Atestado</option>
              <option value="Troca Acordada">Troca Acordada</option>
              <option value="Feriado / Plantão Extra">Feriado / Extra</option>
            </select>
          </div>
          <div class="form-row-eq full">
            <label for="trocaAusente">Funcionário Ausente</label>
            <select id="trocaAusente" required class="select-eq"><option value="">Selecione...</option></select>
          </div>
          <div class="form-row-eq full">
            <label for="trocaSubstituto">Funcionário Substituto</label>
            <select id="trocaSubstituto" required class="select-eq"><option value="">Selecione...</option></select>
          </div>
          <div class="form-row-eq full">
            <label for="trocaMotivo">Motivo</label>
            <input type="text" id="trocaMotivo" class="input-eq" />
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" onclick="fecharModalTrocaEmergencia()">Cancelar</button>
          <button type="submit" class="btn btn-primary">Efetivar</button>
        </div>
      </form>
    </div>
  </div>

  <div class="modal-overlay" id="modalTrocarSenhaDiretoria">
    <div class="modal-card modal-senha-diretoria">
      <div class="modal-header">
        <h3>Alterar Senha da Diretoria</h3>
        <button type="button" class="modal-close" onclick="fecharModalTrocarSenhaDiretoria()">&times;</button>
      </div>
      <form id="formTrocarSenhaDiretoria" onsubmit="salvarMinhaSenhaDiretoria(event)">
        <div class="modal-body" style="display: flex; flex-direction: column; gap: 14px; padding: 20px;">
          <div class="form-row-eq full">
            <label for="dirSenhaAtual">Senha Atual</label>
            <input type="password" id="dirSenhaAtual" required class="input-eq" />
          </div>
          <div class="form-row-eq full">
            <label for="dirNovaSenha">Nova Senha</label>
            <input type="password" id="dirNovaSenha" minlength="6" required class="input-eq" />
          </div>
          <div class="form-row-eq full">
            <label for="dirConfirmaSenha">Confirmar Nova Senha</label>
            <input type="password" id="dirConfirmaSenha" minlength="6" required class="input-eq" />
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" onclick="fecharModalTrocarSenhaDiretoria()">Cancelar</button>
          <button type="submit" class="btn btn-primary">Salvar Nova Senha</button>
        </div>
      </form>
    </div>
  </div>

  <div class="toast-aviso" id="toastDiretoria">Ação executada com sucesso!</div>
  <script src="../js/empresa/diretoria.js"></script>
</body>
</html>