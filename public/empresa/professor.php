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

$permitidos = [
    'PROFESSOR', 
    'PERSONAL', 
    'PERSONAL TRAINER', 
    'INSTRUTOR', 
    'INSTRUTOR DE MUSCULACAO', 
    'PROFESSOR DE LPO & POWERLIFTING', 
    'PROFESSOR DE GINASTICA COLETIVA',
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
    header("Location: /rustic_iron/auth/auth.php?erro=acesso_negado");
    exit;
}

$nomeUsuario = $_SESSION['usuario_nome'] ?? 'Treinador';
$cargoUsuario = $_SESSION['usuario_cargo'] ?? 'Coach • Prescrição';
?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="dark">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Rustic Iron - Painel do Treinador & Prescrição</title>

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
  <link rel="stylesheet" href="../css/empresa/professor.css" />
</head>
<body>
  <div class="dashboard-container">
    <!-- SIDEBAR -->
    <aside class="dashboard-sidebar" id="dashboardSidebar">
      <div class="dashboard-brand">
        <button class="dashboard-sidebar-toggle" id="btnToggleSidebar">
          <span class="material-symbols-rounded">menu</span>
        </button>
        <a class="logo" href="javascript:void(0)" onclick="navegarProfessor('visao-treinador')">Rustic Iron</a>
      </div>

      <nav class="dashboard-nav">
        <div class="dashboard-nav-section">
          <a href="javascript:void(0)" class="dashboard-nav-item active" data-target="visao-treinador" onclick="navegarProfessor('visao-treinador')">
            <span class="nav-icon material-symbols-rounded">sports</span>
            <span class="nav-label">Painel de Atletas</span>
          </a>
          <a href="javascript:void(0)" class="dashboard-nav-item" data-target="fichas-ativas" onclick="navegarProfessor('fichas-ativas')">
            <span class="nav-icon material-symbols-rounded">clinical_notes</span>
            <span class="nav-label">Fichas & Prescrições</span>
          </a>
          <a href="javascript:void(0)" class="dashboard-nav-item" data-target="banco-exercicios" onclick="navegarProfessor('banco-exercicios')">
            <span class="nav-icon material-symbols-rounded">fitness_center</span>
            <span class="nav-label">Banco de Exercícios</span>
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
          <h1 class="dashboard-header-title" id="pageMainTitle">Painel de Atletas & Prescrição</h1>
        </div>

        <div class="search-container" id="searchContainer">
          <span class="search-icon material-symbols-rounded">search</span>
          <input type="search" class="search-input" placeholder="Pesquisar atleta por nome, CPF ou matrícula..." id="inputBuscaAtleta" oninput="filtrarAtletas()" />
        </div>

        <div class="dashboard-header-actions">
          <div class="header-profile-wrapper">
            <div class="profile-avatar-trigger" onclick="toggleMenuPerfil(event)" title="Perfil do Treinador">
              <span class="material-symbols-rounded fallback-avatar-icon">account_circle</span>
              <img src="" alt="Avatar" id="avatarPerfilProfessor" class="profile-avatar-img" style="display: none;" />
              <div class="avatar-status-dot"></div>
            </div>

            <div class="profile-dropdown-menu" id="profileDropdownMenu">
              <div class="dropdown-user-info">
                <div class="dropdown-avatar-container">
                  <span class="material-symbols-rounded fallback-avatar-icon" style="font-size: 36px;">account_circle</span>
                  <img src="" alt="Avatar" id="avatarPerfilProfessorDropdown" class="profile-avatar-img" style="display: none;" />
                  <label for="inputAlterarFoto" class="avatar-edit-badge" title="Alterar foto">
                    <span class="material-symbols-rounded" style="font-size: 11px;">photo_camera</span>
                  </label>
                  <input type="file" id="inputAlterarFoto" style="display: none;" accept="image/*" onchange="atualizarFotoPerfil(event)">
                </div>
                <div class="dropdown-text-meta">
                  <span class="dropdown-name"><?= htmlspecialchars($nomeUsuario, ENT_QUOTES, 'UTF-8') ?></span>
                  <span class="dropdown-role"><?= htmlspecialchars($cargoUsuario, ENT_QUOTES, 'UTF-8') ?> &bull; <small>Rustic Iron</small></span>
                </div>
              </div>
              
              <div class="dropdown-divider"></div>

              <button type="button" class="dropdown-action-btn" onclick="abrirModalTrocarSenhaProfessor()">
                <span class="material-symbols-rounded" style="font-size: 16px;">key</span>
                <span>Alterar Senha</span>
              </button>

              <button type="button" class="dropdown-logout-btn" onclick="fazerLogoutProfessor()">
                <span class="material-symbols-rounded" style="font-size: 16px;">logout</span>
                <span>Encerrar Sessão</span>
              </button>
            </div>
          </div>
        </div>
      </header>

      <div class="dashboard-content">
        <!-- 1. PAINEL DE ATLETAS -->
        <section id="view-visao-treinador" class="view-section active-view">
          <div class="stats-grid">
            <div class="stat-card">
              <div class="stat-card-header">
                <span class="stat-card-title">Atletas Atribuídos</span>
                <div class="stat-card-icon primary"><span class="material-symbols-rounded">groups</span></div>
              </div>
              <div class="stat-card-value" id="kpiTotalAtletas">0</div>
              <div class="stat-card-change positive"><span class="material-symbols-rounded">check</span> Registados na base</div>
            </div>

            <div class="stat-card">
              <div class="stat-card-header">
                <span class="stat-card-title">Fichas a Vencer (>45d)</span>
                <div class="stat-card-icon warning"><span class="material-symbols-rounded">pending_actions</span></div>
              </div>
              <div class="stat-card-value" id="kpiFichasVencendo">0</div>
              <div class="stat-card-change negative">Ciclos que precisam de atualização</div>
            </div>

            <div class="stat-card">
              <div class="stat-card-header">
                <span class="stat-card-title">Em Alerta de Evasão</span>
                <div class="stat-card-icon error"><span class="material-symbols-rounded">person_off</span></div>
              </div>
              <div class="stat-card-value" id="kpiEvasao">0</div>
              <div class="stat-card-change negative">+7 dias sem passar na catraca</div>
            </div>

            <div class="stat-card">
              <div class="stat-card-header">
                <span class="stat-card-title">Fichas Prontas Hoje</span>
                <div class="stat-card-icon success"><span class="material-symbols-rounded">fitness_center</span></div>
              </div>
              <div class="stat-card-value" id="kpiFichasProntas">0</div>
              <div class="stat-card-change positive">Com prescrição completa</div>
            </div>
          </div>

          <div class="toolbar-filtros">
            <div class="filtros-chips" id="containerFiltrosModalidade">
              <button class="btn-chip active" data-mod="todas" onclick="filtrarPorModalidade('todas', this)">Todas</button>
              <button class="btn-chip" data-mod="Powerlifting" onclick="filtrarPorModalidade('Powerlifting', this)">Powerlifting</button>
              <button class="btn-chip" data-mod="Hipertrofia" onclick="filtrarPorModalidade('Hipertrofia', this)">Hipertrofia</button>
              <button class="btn-chip" data-mod="Força" onclick="filtrarPorModalidade('Força', this)">Força Pura</button>
              <button class="btn-chip" data-mod="Musculação Pesada" onclick="filtrarPorModalidade('Musculação Pesada', this)">Musculação</button>
            </div>
          </div>

          <div class="dashboard-table-container">
            <div class="dashboard-table-header">
              <h3 class="dashboard-table-title">Quadro de Alunos & Prescrições de Treino</h3>
              <span class="status-badge info" id="lblTotalExibido">0 alunos listados</span>
            </div>
            <table class="dashboard-table">
              <thead>
                <tr>
                  <th>Aluno / Matrícula</th>
                  <th>Métricas Físicas</th>
                  <th>Foco / Objetivo</th>
                  <th>Última Prescrição</th>
                  <th>Status do Treino</th>
                  <th style="text-align: right;">Ações Técnicas</th>
                </tr>
              </thead>
              <tbody id="tabelaAtletasCorpo"></tbody>
            </table>
          </div>
        </section>

        <!-- 2. FICHAS ATIVAS -->
        <section id="view-fichas-ativas" class="view-section">
          <div class="dashboard-table-container">
            <div class="dashboard-table-header">
              <div>
                <h3 class="dashboard-table-title">Resumo de Periodizações Ativas</h3>
                <p style="color: var(--color-text-secondary); font-size: 0.8rem; margin-top: 2px;">Lista de ciclos de força e hipertrofia em andamento na unidade.</p>
              </div>
            </div>
            <table class="dashboard-table">
              <thead>
                <tr>
                  <th>Atleta</th>
                  <th>Foco Principal</th>
                  <th>Dias Prescritos</th>
                  <th>Última Atualização</th>
                  <th style="text-align: right;">Ação</th>
                </tr>
              </thead>
              <tbody id="tabelaFichasAtivasCorpo"></tbody>
            </table>
          </div>
        </section>

        <!-- 3. BANCO DE EXERCÍCIOS -->
        <section id="view-banco-exercicios" class="view-section">
          <div class="dashboard-table-container" style="padding: 24px;">
            <div style="margin-bottom: 20px;">
              <h3 style="font-size: 1.1rem; font-weight: 700;">Banco Padrão de Movimentos & Biomecânica</h3>
              <p style="color: var(--color-text-secondary); font-size: 0.85rem;">Exercícios disponíveis para montagem das fichas no sistema.</p>
            </div>
            <div id="gridBancoExerciciosView" class="exercicios-grid-view"></div>
          </div>
        </section>
      </div>
    </main>
  </div>

  <!-- MODAL: PRESCRIÇÃO DA FICHA -->
  <div class="modal-overlay" id="modalFichaTreino">
    <div class="modal-card modal-ficha-largura">
      <div class="modal-header">
        <div class="atleta-header-summary">
          <div class="atleta-avatar-box">
            <span class="material-symbols-rounded">person</span>
          </div>
          <div>
            <h3 id="fichaModalNomeAluno">Ficha de Treino</h3>
            <p id="fichaModalSubInfo" style="font-size: 0.75rem; color: var(--color-text-secondary);">Métricas • CPF • Foco</p>
          </div>
        </div>
        <button type="button" class="modal-close" onclick="fecharModalFicha()">&times;</button>
      </div>

      <div class="modal-body">
        <div class="ficha-top-config-bar">
          <div class="form-row-eq">
            <label for="fichaSelectModalidade">Objetivo / Modalidade</label>
            <select id="fichaSelectModalidade" class="select-eq" onchange="atualizarModalidadeAtleta(this.value)">
              <option value="" selected disabled>A carregar do banco...</option>
            </select>
          </div>

          <div class="form-row-eq">
            <label>Dia da Semana (Divisão)</label>
            <div class="days-selector-pills">
              <button type="button" class="day-btn active" data-day="segunda" onclick="trocarDiaTreino('segunda', this)">Seg</button>
              <button type="button" class="day-btn" data-day="terca" onclick="trocarDiaTreino('terca', this)">Ter</button>
              <button type="button" class="day-btn" data-day="quarta" onclick="trocarDiaTreino('quarta', this)">Qua</button>
              <button type="button" class="day-btn" data-day="quinta" onclick="trocarDiaTreino('quinta', this)">Qui</button>
              <button type="button" class="day-btn" data-day="sexta" onclick="trocarDiaTreino('sexta', this)">Sex</button>
              <button type="button" class="day-btn" data-day="sabado" onclick="trocarDiaTreino('sabado', this)">Sáb</button>
              <button type="button" class="day-btn" data-day="domingo" onclick="trocarDiaTreino('domingo', this)">Dom</button>
            </div>
          </div>
        </div>

        <!-- 1. GRUPOS MUSCULARES -->
        <section class="ficha-section-box">
          <div class="section-title-tag">
            <span class="num-badge">1</span>
            <h4>Grupos Musculares deste Dia</h4>
          </div>
          <div class="muscles-checkbox-grid" id="containerChecksMusculos"></div>
        </section>

        <!-- 2. ORDEM DE PRIORIDADE -->
        <section class="ficha-section-box">
          <div class="section-title-tag">
            <span class="num-badge">2</span>
            <div>
              <h4>Ordem de Prioridade no Treino</h4>
              <p class="section-hint">Use as setas para ordenar a prioridade (1º, 2º, 3º...)</p>
            </div>
          </div>
          <div class="muscles-order-list" id="listaMusculosOrdem">
            <div class="empty-hint">Nenhum grupo muscular selecionado.</div>
          </div>
        </section>

        <!-- 3. EXERCÍCIOS -->
        <section class="ficha-section-box">
          <div class="section-title-tag">
            <span class="num-badge">3</span>
            <h4>Exercícios, Séries, Repetições & Sobrecarga</h4>
          </div>
          <div class="exercise-blocks-container" id="containerBlocosExercicios"></div>
        </section>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="imprimirFichaAtleta()">
          <span class="material-symbols-rounded" style="font-size: 18px;">print</span> Imprimir Cupom (80mm)
        </button>
        <button type="button" class="btn btn-secondary" onclick="fecharModalFicha()">Cancelar</button>
        <button type="button" class="btn btn-primary" onclick="salvarTreinoEmMemoria()">
          <span class="material-symbols-rounded" style="font-size: 18px;">save</span> Gravar Prescrição
        </button>
      </div>
    </div>
  </div>

  <!-- MODAL: ALTERAR SENHA DO TREINADOR -->
  <div class="modal-overlay" id="modalTrocarSenhaProfessor">
    <div class="modal-card modal-senha-diretoria">
      <div class="modal-header">
        <h3>Alterar Senha do Treinador</h3>
        <button type="button" class="modal-close" onclick="fecharModalTrocarSenhaProfessor()">&times;</button>
      </div>
      <form id="formTrocarSenhaProfessor" onsubmit="salvarMinhaSenhaProfessor(event)">
        <div class="modal-body" style="display: flex; flex-direction: column; gap: 14px; padding: 20px;">
          <div class="form-row-eq full">
            <label for="profSenhaAtual">Senha Atual</label>
            <input type="password" id="profSenhaAtual" required class="input-eq" />
          </div>
          <div class="form-row-eq full">
            <label for="profNovaSenha">Nova Senha (Mínimo 6 caracteres)</label>
            <input type="password" id="profNovaSenha" minlength="6" required class="input-eq" />
          </div>
          <div class="form-row-eq full">
            <label for="profConfirmaSenha">Confirmar Nova Senha</label>
            <input type="password" id="profConfirmaSenha" minlength="6" required class="input-eq" />
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" onclick="fecharModalTrocarSenhaProfessor()">Cancelar</button>
          <button type="submit" class="btn btn-primary">Salvar Nova Senha</button>
        </div>
      </form>
    </div>
  </div>

  <!-- CUPOM TÉRMICO NÃO FISCAL (80mm) -->
  <div id="printSheet" class="print-sheet-area">
    <div class="print-sheet-header">
      <div class="print-gym-brand">
        <h1 class="thermal-logo">RUSTIC IRON GYM</h1>
        <p class="thermal-sub">CENTRO DE TREINAMENTO PESADO</p>
        <p class="thermal-sub">COMPROVANTE DE PRESCRIÇÃO TÉRMICA</p>
      </div>
      <div class="print-athlete-info">
        <h2 class="thermal-athlete-name" id="printAtletaNome">Nome do Aluno</h2>
        <div id="printAtletaMetricas">Peso: 00 kg | Altura: 00 cm</div>
        <div id="printDataPrescricao">Prescrito em: 00/00/0000 • Coach: <?= htmlspecialchars($nomeUsuario, ENT_QUOTES, 'UTF-8') ?></div>
      </div>
    </div>
    <hr class="print-divider" />
    <div id="printSheetContent" class="print-sheet-content"></div>
    <div class="print-sheet-footer">
      <strong>RUSTIC IRON - FORÇA & DISCIPLINA</strong>
      <p style="margin:2px 0;">Hidrate-se e anote suas progressões de carga a cada treino!</p>
      <p style="margin:2px 0;">*** FIM DO COMPROVANTE ***</p>
    </div>
  </div>

  <div class="toast-aviso" id="toastProfessor">Ação executada com sucesso!</div>
  <script src="../js/empresa/professor.js"></script>
</body>
</html>