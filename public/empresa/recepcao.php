<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$cargoBruto = $_SESSION['usuario_cargo'] ?? '';
$cargoAtual = mb_strtoupper(trim($cargoBruto), 'UTF-8');
$cargoAtual = str_replace(
    ['Ó', 'Ô', 'Õ', 'Á', 'À', 'Â', 'Ã', 'É', 'Ê', 'Í', 'Ú', 'Ç'],
    ['O', 'O', 'O', 'A', 'A', 'A', 'A', 'E', 'E', 'I', 'U', 'C'],
    $cargoAtual
);

// Adicionado RECEC e RECEP para pegar Rececionista (sem P) e Recepcionista (com P)
$permitidos = [
    'RECEP', 
    'RECEC', 
    'ATENDENTE', 
    'BALCAO', 
    'SECRETARIA', 
    'CEO', 
    'SOCIO', 
    'ADMIN', 
    'GERENTE', 
    'DIRETORIA'
];

$autorizado = false;
foreach ($permitidos as $p) {
    if (strpos($cargoAtual, $p) !== false) {
        $autorizado = true;
        break;
    }
}

if (!isset($_SESSION['usuario_id']) || !$autorizado) {
    header("Location: ../auth/auth.php?erro=acesso_negado");
    exit;
}

$nomeUsuario = $_SESSION['usuario_nome'] ?? 'Recepcionista';
$cargoUsuario = $_SESSION['usuario_cargo'] ?? 'Recepção • Balcão';
?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="dark">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Rustic Iron - Recepção & Catraca</title>

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
  <link rel="stylesheet" href="../css/empresa/professor.css" />
  <link rel="stylesheet" href="../css/empresa/recepcao.css" />
</head>
<body>
  <div class="dashboard-container">
    <!-- SIDEBAR RECEPÇÃO -->
    <aside class="dashboard-sidebar" id="dashboardSidebar">
      <div class="dashboard-brand">
        <button class="dashboard-sidebar-toggle" id="btnToggleSidebar">
          <span class="material-symbols-rounded">menu</span>
        </button>
        <a class="logo" href="javascript:void(0)" onclick="navegarRecepcao('catraca-terminal')">Rustic Iron</a>
      </div>

      <nav class="dashboard-nav">
        <div class="dashboard-nav-section">
          <a href="javascript:void(0)" class="dashboard-nav-item active" data-target="catraca-terminal" onclick="navegarRecepcao('catraca-terminal')">
            <span class="nav-icon material-symbols-rounded">door_front</span>
            <span class="nav-label">Catraca & Terminal</span>
          </a>
          <a href="javascript:void(0)" class="dashboard-nav-item" data-target="alunos" onclick="navegarRecepcao('alunos')">
            <span class="nav-icon material-symbols-rounded">groups</span>
            <span class="nav-label">Quadro de Alunos</span>
          </a>
          <a href="javascript:void(0)" class="dashboard-nav-item" data-target="cobrancas" onclick="navegarRecepcao('cobrancas')">
            <span class="nav-icon material-symbols-rounded">point_of_sale</span>
            <span class="nav-label">Balcão & Cobranças</span>
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
          <h1 class="dashboard-header-title" id="pageMainTitle">Catraca & Terminal de Acesso</h1>
        </div>

        <div class="search-container" id="searchContainer">
          <span class="search-icon material-symbols-rounded">search</span>
          <input type="search" class="search-input" placeholder="Pesquisar aluno por nome, CPF ou matrícula..." id="inputBuscaGlobalRecepcao" oninput="executarBuscaGlobal(this.value)" />
        </div>

        <div class="dashboard-header-actions">
          <button class="btn btn-primary" id="btnContextualHeader" onclick="abrirModalNovoAluno()">
            <span class="material-symbols-rounded">person_add</span>
            <span>Novo Aluno</span>
          </button>

          <div class="clock-chip" id="relogioRecepcao">00:00:00</div>

          <div class="header-profile-wrapper">
            <div class="profile-avatar-trigger" onclick="toggleMenuPerfil(event)" title="Perfil da Recepção">
              <span class="material-symbols-rounded fallback-avatar-icon">account_circle</span>
              <img src="" alt="Avatar" id="avatarPerfilRecepcao" class="profile-avatar-img" style="display: none;" />
              <div class="avatar-status-dot"></div>
            </div>

            <div class="profile-dropdown-menu" id="profileDropdownMenu">
              <div class="dropdown-user-info">
                <div class="dropdown-avatar-container">
                  <span class="material-symbols-rounded fallback-avatar-icon" style="font-size: 36px;">account_circle</span>
                  <img src="" alt="Avatar" id="avatarPerfilRecepcaoDropdown" class="profile-avatar-img" style="display: none;" />
                  <label for="inputAlterarFotoRec" class="avatar-edit-badge" title="Alterar foto">
                    <span class="material-symbols-rounded" style="font-size: 11px;">photo_camera</span>
                  </label>
                  <input type="file" id="inputAlterarFotoRec" style="display: none;" accept="image/*" onchange="atualizarFotoPerfilRec(event)">
                </div>
                <div class="dropdown-text-meta">
                  <span class="dropdown-name"><?= htmlspecialchars($nomeUsuario, ENT_QUOTES, 'UTF-8') ?></span>
                  <span class="dropdown-role"><?= htmlspecialchars($cargoUsuario, ENT_QUOTES, 'UTF-8') ?> &bull; <small>Rustic Iron</small></span>
                </div>
              </div>
              
              <div class="dropdown-divider"></div>

              <button type="button" class="dropdown-action-btn" onclick="abrirModalTrocarSenhaRecepcao()">
                <span class="material-symbols-rounded" style="font-size: 16px;">key</span>
                <span>Alterar Senha</span>
              </button>

              <button type="button" class="dropdown-logout-btn" onclick="fazerLogoutRecepcao()">
                <span class="material-symbols-rounded" style="font-size: 16px;">logout</span>
                <span>Encerrar Sessão</span>
              </button>
            </div>
          </div>
        </div>
      </header>

      <div class="dashboard-content">
        <!-- 1. SEÇÃO: CATRACA & TERMINAL -->
        <section id="view-catraca-terminal" class="view-section active-view">
          <div class="stats-grid">
            <div class="stat-card">
              <div class="stat-card-header">
                <span class="stat-card-title">Alunos no Ginásio</span>
                <div class="stat-card-icon primary"><span class="material-symbols-rounded">fitness_center</span></div>
              </div>
              <div class="stat-card-value" id="kpiPresentes">0</div>
              <div class="stat-card-change positive"><span class="material-symbols-rounded">check</span> Capacidade ideal</div>
            </div>

            <div class="stat-card">
              <div class="stat-card-header">
                <span class="stat-card-title">Acessos Hoje</span>
                <div class="stat-card-icon success"><span class="material-symbols-rounded">login</span></div>
              </div>
              <div class="stat-card-value" id="kpiEntradasHoje">0</div>
              <div class="stat-card-change positive">Validados no torniquete</div>
            </div>

            <div class="stat-card">
              <div class="stat-card-header">
                <span class="stat-card-title">Tentativas Recusadas</span>
                <div class="stat-card-icon error"><span class="material-symbols-rounded">block</span></div>
              </div>
              <div class="stat-card-value" id="kpiRecusadosHoje">0</div>
              <div class="stat-card-change negative">Pendência financeira / Outros</div>
            </div>

            <div class="stat-card">
              <div class="stat-card-header">
                <span class="stat-card-title">Caixa do Turno</span>
                <div class="stat-card-icon warning"><span class="material-symbols-rounded">payments</span></div>
              </div>
              <div class="stat-card-value" id="kpiCaixaTurno">R$ 0,00</div>
              <div class="stat-card-change positive">Mensalidades liquidadas</div>
            </div>
          </div>

          <!-- PAINEL OPERACIONAL DA CATRACA & SIMULADOR -->
          <div class="operacional-dual-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <!-- TERMINAL DO TORNIQUETE -->
            <div class="dashboard-table-container" style="padding: 22px;">
              <div class="dashboard-table-header" style="padding: 0 0 14px 0; margin-bottom: 16px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                  <span class="material-symbols-rounded" style="color: var(--color-primary);">sensor_door</span>
                  <h3 class="dashboard-table-title">Torniquete Principal #01</h3>
                </div>
                <span class="status-badge success" id="badgeCatracaStatus">Catraca Online (TCP/IP)</span>
              </div>

              <!-- DISPLAY DIGITAL DE LED -->
              <div class="turnstile-display-box" id="displayBoxCatraca">
                <span class="material-symbols-rounded display-symbol" id="displayIcon">fingerprint</span>
                <h3 class="display-title" id="displayTitle">AGUARDANDO LEITURA</h3>
                <p class="display-msg" id="displayMsg">Aproxime o cartão RFID ou digite o CPF abaixo</p>
                <div class="display-user-box" id="displayUserBox" style="display: none;">
                  <strong id="displayUserName">-</strong>
                  <span id="displayUserPlan">-</span>
                </div>
              </div>

              <!-- FORMULÁRIO DE ENTRADA MANUAL / LEITOR USB -->
              <form id="formBiparCatraca" onsubmit="handleValidarPassagem(event)" style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 16px;">
                <div class="form-row-eq full">
                  <label for="inputCpfCatraca">CPF, Cartão RFID ou Matrícula</label>
                  <div class="input-icon-wrapper">
                    <span class="material-symbols-rounded">badge</span>
                    <input type="text" id="inputCpfCatraca" placeholder="Digite o CPF ou aproxime a tag..." class="input-eq" autocomplete="off" />
                  </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                  <div>
                    <label style="font-size: 0.72rem; font-weight: 600; color: var(--color-text-secondary); text-transform: uppercase;">Sentido</label>
                    <select id="selectSentidoCatraca" class="select-eq">
                      <option value="Entrada">Entrada (Giro Horário)</option>
                      <option value="Saída">Saída (Giro Livre)</option>
                    </select>
                  </div>
                  <div style="display: flex; align-items: flex-end;">
                    <button type="submit" class="btn btn-primary" style="width: 100%; height: 42px;">
                      <span class="material-symbols-rounded">check</span> Validar Acesso
                    </button>
                  </div>
                </div>
              </form>

              <!-- COMANDOS DE MESA RÁPIDOS -->
              <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="liberarGiroManual('Entrada')">
                  <span class="material-symbols-rounded" style="font-size: 15px;">lock_open</span> Giro Entrada
                </button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="liberarGiroManual('Saída')">
                  <span class="material-symbols-rounded" style="font-size: 15px;">output</span> Giro Saída
                </button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="toggleTravaPanico()" style="color: var(--color-error); border-color: rgba(239, 68, 68, 0.4);">
                  <span class="material-symbols-rounded" style="font-size: 15px;">emergency</span> Trancar Catraca
                </button>
              </div>
            </div>

            <!-- FEED EM TEMPO REAL DA CATRACA -->
            <div class="dashboard-table-container">
              <div class="dashboard-table-header">
                <h3 class="dashboard-table-title">Passagens Recentes no Torniquete</h3>
                <button class="btn btn-secondary btn-sm" onclick="limparLogsCatraca()">Limpar Logs</button>
              </div>
              <table class="dashboard-table">
                <thead>
                  <tr>
                    <th>Horário</th>
                    <th>Aluno / Identificador</th>
                    <th>Sentido</th>
                    <th>Situação</th>
                    <th style="text-align: right;">Ação</th>
                  </tr>
                </thead>
                <tbody id="tabelaLogsCatracaCorpo"></tbody>
              </table>
            </div>
          </div>
        </section>

        <!-- 2. SEÇÃO: QUADRO DE ALUNOS -->
        <section id="view-alunos" class="view-section">
          <div class="toolbar-filtros">
            <div class="filtros-chips">
              <button class="btn-chip active" data-filtro="todos" onclick="filtrarAlunosStatus('todos', this)">Todos (<span id="cntAlunosTotal">0</span>)</button>
              <button class="btn-chip" data-filtro="liberados" onclick="filtrarAlunosStatus('liberados', this)">Liberados (<span id="cntAlunosLiberados">0</span>)</button>
              <button class="btn-chip" data-filtro="bloqueados" onclick="filtrarAlunosStatus('bloqueados', this)">Bloqueados (<span id="cntAlunosBloqueados">0</span>)</button>
            </div>
          </div>

          <div class="dashboard-table-container">
            <div class="dashboard-table-header">
              <h3 class="dashboard-table-title">Quadro Geral de Alunos Matriculados</h3>
              <button class="btn btn-primary btn-sm" onclick="abrirModalNovoAluno()">
                <span class="material-symbols-rounded">person_add</span> Cadastrar Aluno
              </button>
            </div>
            <table class="dashboard-table">
              <thead>
                <tr>
                  <th>Matrícula / Aluno</th>
                  <th>CPF</th>
                  <th>Telefone / WhatsApp</th>
                  <th>Plano Contratado</th>
                  <th>Status Catraca</th>
                  <th style="text-align: right;">Controles</th>
                </tr>
              </thead>
              <tbody id="tabelaAlunosCorpo"></tbody>
            </table>
          </div>
        </section>

        <!-- 3. SEÇÃO: BALCÃO & COBRANÇAS -->
        <section id="view-cobrancas" class="view-section">
          <div class="toolbar-filtros">
            <div class="filtros-chips">
              <button class="btn-chip active" data-fatura="todos" onclick="filtrarFaturasStatus('todos', this)">Todas</button>
              <button class="btn-chip" data-fatura="atrasado" onclick="filtrarFaturasStatus('atrasado', this)">Atrasadas</button>
              <button class="btn-chip" data-fatura="pendente" onclick="filtrarFaturasStatus('pendente', this)">A Vencer</button>
              <button class="btn-chip" data-fatura="pago" onclick="filtrarFaturasStatus('pago', this)">Pagas</button>
            </div>
          </div>

          <div class="dashboard-table-container">
            <div class="dashboard-table-header">
              <h3 class="dashboard-table-title">Mensalidades & Baixa Imediata no Balcão</h3>
            </div>
            <table class="dashboard-table">
              <thead>
                <tr>
                  <th>Fatura #</th>
                  <th>Aluno / CPF</th>
                  <th>Plano</th>
                  <th>Valor</th>
                  <th>Vencimento</th>
                  <th>Situação</th>
                  <th style="text-align: right;">Ação</th>
                </tr>
              </thead>
              <tbody id="tabelaFaturasCorpo"></tbody>
            </table>
          </div>
        </section>
      </div>
    </main>
  </div>

  <!-- MODAL: CADASTRAR ALUNO -->
  <div class="modal-overlay" id="modalAluno">
    <div class="modal-card" style="max-width: 620px;">
      <div class="modal-header">
        <h3 id="modalTituloAluno">Cadastrar Novo Aluno</h3>
        <button type="button" class="modal-close" onclick="fecharModalNovoAluno()">&times;</button>
      </div>

      <form id="formAluno" onsubmit="salvarAluno(event)">
        <div class="modal-body" style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
          <div class="form-row-eq full">
            <label for="alNome">Nome Completo</label>
            <input type="text" id="alNome" required placeholder="Ex: Felipe Ramos Oliveira" class="input-eq" />
          </div>

          <div class="form-row-eq">
            <label for="alCpf">CPF (Acesso na Catraca)</label>
            <input type="text" id="alCpf" required placeholder="000.000.000-00" maxlength="14" class="input-eq" />
          </div>

          <div class="form-row-eq">
            <label for="alTelefone">WhatsApp / Telefone</label>
            <input type="tel" id="alTelefone" required placeholder="(79) 99999-9999" class="input-eq" />
          </div>

          <div class="form-row-eq">
            <label for="alPlano">Plano Inicial</label>
            <select id="alPlano" required class="select-eq">
              <option value="Iron Mensal Livre">Iron Mensal Livre (R$ 130,00)</option>
              <option value="Full Combat & Iron">Full Combat & Iron (R$ 190,00)</option>
              <option value="Trimestral Heavy">Trimestral Heavy (R$ 350,00)</option>
              <option value="Anual Pesado Pro">Anual Pesado Pro (R$ 1.150,00)</option>
            </select>
          </div>

          <div class="form-row-eq">
            <label for="alCatraca">Liberação de Entrada</label>
            <select id="alCatraca" class="select-eq">
              <option value="1" selected>Liberado Imediatamente</option>
              <option value="0">Bloqueado (Pendente Pagamento)</option>
            </select>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" onclick="fecharModalNovoAluno()">Cancelar</button>
          <button type="submit" class="btn btn-primary">
            <span class="material-symbols-rounded">save</span> Salvar Aluno
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- MODAL: BAIXA DE MENSALIDADE NO BALCÃO -->
  <div class="modal-overlay" id="modalPagamento">
    <div class="modal-card modal-senha-diretoria">
      <div class="modal-header">
        <div style="display: flex; align-items: center; gap: 8px;">
          <span class="material-symbols-rounded" style="color: var(--color-primary); font-size: 24px;">point_of_sale</span>
          <h3 style="font-size: 1.05rem; font-weight: 700; color: #fff;">Recebimento de Mensalidade</h3>
        </div>
        <button type="button" class="modal-close" onclick="fecharModalPagamento()">&times;</button>
      </div>

      <div class="modal-body" style="display: flex; flex-direction: column; gap: 14px;">
        <div class="notice-box-senha" style="display: flex; align-items: flex-start; gap: 10px; background: rgba(217, 98, 0, 0.08); border: 1px solid rgba(217, 98, 0, 0.25); border-radius: var(--radius-md); padding: 10px 14px;">
          <span class="material-symbols-rounded" style="color: var(--color-primary);">info</span>
          <div>
            <strong id="modalPagAlunoNome" style="color: #fff; font-size: 0.95rem; display: block;">Nome do Aluno</strong>
            <span id="modalPagPlanoInfo" style="font-size: 0.75rem; color: var(--color-text-muted);">Plano • Valor: R$ 0,00</span>
          </div>
        </div>

        <div class="form-row-eq full">
          <label>Forma de Pagamento</label>
          <select id="modalPagMetodo" class="select-eq">
            <option value="PIX">PIX (Instantâneo no Balcão)</option>
            <option value="Dinheiro">Dinheiro Físico</option>
            <option value="Cartão de Débito">Cartão de Débito</option>
            <option value="Cartão de Crédito">Cartão de Crédito</option>
          </select>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="fecharModalPagamento()">Cancelar</button>
        <button type="button" class="btn btn-primary" onclick="confirmarBaixaPagamento()">
          <span class="material-symbols-rounded">check_circle</span> Dar Baixa & Liberar Catraca
        </button>
      </div>
    </div>
  </div>

  <!-- MODAL: ALTERAR SENHA DA RECEPÇÃO -->
  <div class="modal-overlay" id="modalTrocarSenhaRecepcao">
    <div class="modal-card modal-senha-diretoria">
      <div class="modal-header">
        <div style="display: flex; align-items: center; gap: 8px;">
          <span class="material-symbols-rounded" style="color: var(--color-primary); font-size: 24px;">lock_reset</span>
          <h3 style="font-size: 1.05rem; font-weight: 700; color: #fff;">Alterar Minha Senha</h3>
        </div>
        <button type="button" class="modal-close" onclick="fecharModalTrocarSenhaRecepcao()">&times;</button>
      </div>

      <form id="formTrocarSenhaRecepcao" onsubmit="salvarMinhaSenhaRecepcao(event)">
        <div class="modal-body" style="display: flex; flex-direction: column; gap: 14px;">
          <div class="form-row-eq full">
            <label for="recSenhaAtual">Senha Atual / Provisória</label>
            <input type="password" id="recSenhaAtual" required placeholder="Digite sua senha atual" class="input-eq" autocomplete="current-password" />
          </div>

          <div class="form-row-eq full">
            <label for="recNovaSenha">Nova Senha (Mínimo 6 caracteres)</label>
            <input type="password" id="recNovaSenha" minlength="6" required placeholder="Digite a nova senha" class="input-eq" autocomplete="new-password" />
          </div>

          <div class="form-row-eq full">
            <label for="recConfirmaSenha">Confirmar Nova Senha</label>
            <input type="password" id="recConfirmaSenha" minlength="6" required placeholder="Repita a nova senha" class="input-eq" autocomplete="new-password" />
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" onclick="fecharModalTrocarSenhaRecepcao()">Cancelar</button>
          <button type="submit" class="btn btn-primary">
            <span class="material-symbols-rounded">save</span> Salvar Nova Senha
          </button>
        </div>
      </form>
    </div>
  </div>

  <div class="toast-aviso" id="toastRecepcao">Ação executada com sucesso!</div>

  <script src="../js/empresa/recepcao.js"></script>
</body>
</html>