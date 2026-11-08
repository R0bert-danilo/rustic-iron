<!DOCTYPE html>
<html lang="pt-BR" data-theme="dark">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Rustic Iron - Demonstração Interativa do Sistema</title>

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />

  <style>
    /* ==============================================================================
       1. DESIGN SYSTEM COMPLETO & RESET INDUSTRIAL
       ============================================================================== */
    :root {
      --color-primary: #d96200;
      --color-primary-hover: #b85300;
      --color-primary-50: rgba(217, 98, 0, 0.05);
      --color-primary-100: rgba(217, 98, 0, 0.1);
      --color-primary-200: rgba(217, 98, 0, 0.2);

      --color-success: #10b981;
      --color-success-50: rgba(16, 185, 129, 0.05);
      --color-success-100: rgba(16, 185, 129, 0.1);

      --color-warning: #f59e0b;
      --color-warning-50: rgba(245, 158, 11, 0.05);
      --color-warning-100: rgba(245, 158, 11, 0.1);

      --color-error: #ef4444;
      --color-error-50: rgba(239, 68, 68, 0.05);
      --color-error-100: rgba(239, 68, 68, 0.1);

      --color-info: #3b82f6;
      --color-info-100: rgba(59, 130, 246, 0.1);

      --color-text: #f0f0f5;
      --color-text-secondary: #a1a1aa;
      --color-text-muted: #71717a;

      --color-background: #121214;
      --color-surface: #1e1e24;
      --color-surface-hover: #272730;
      --color-border: #2e2e38;

      --font-primary: "Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
      --sidebar-width: 260px;
      --header-height: 64px;
      --radius-sm: 0.25rem;
      --radius-md: 0.5rem;
      --radius-lg: 0.75rem;
      --radius-xl: 1rem;
      --radius-full: 9999px;
      --transition: 0.2s ease;
    }

    *, *::before, *::after {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: var(--font-primary);
      background: var(--color-background);
      color: var(--color-text);
      line-height: 1.6;
      font-size: 14px;
      overflow: hidden;
    }

    a { text-decoration: none; color: inherit; }
    button { font-family: inherit; font-size: inherit; border: none; background: none; cursor: pointer; }

    /* Faixa de Demonstração */
    .demo-banner {
      background: linear-gradient(90deg, #b45309, #d96200, #ea580c);
      color: #fff;
      font-size: 12px;
      font-weight: 600;
      padding: 4px 16px;
      text-align: center;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      letter-spacing: 0.3px;
      z-index: 2001;
    }

    .demo-banner a {
      background: rgba(0,0,0,0.3);
      padding: 2px 8px;
      border-radius: 4px;
      text-decoration: underline;
    }

    /* Layout Geral */
    .dashboard-container {
      display: flex;
      height: calc(100vh - 28px);
      overflow: hidden;
    }

    .dashboard-sidebar {
      width: var(--sidebar-width);
      background: var(--color-background);
      border-right: 1px solid var(--color-border);
      display: flex;
      flex-direction: column;
      position: sticky;
      left: 0;
      top: 0;
      height: 100%;
      z-index: 1000;
      transition: var(--transition);
    }

    .dashboard-brand {
      padding: 16px 20px;
      display: flex;
      align-items: center;
      gap: 12px;
      height: var(--header-height);
    }

    .dashboard-brand .logo {
      font-size: 1.15rem;
      font-weight: 700;
      color: var(--color-primary);
      letter-spacing: 0.5px;
    }

    .dashboard-nav {
      flex: 1;
      padding: 12px 8px;
      overflow-y: auto;
    }

    .dashboard-nav-item {
      display: flex;
      align-items: center;
      padding: 10px 14px;
      color: var(--color-text-secondary);
      border-radius: var(--radius-lg);
      margin: 3px 0;
      transition: var(--transition);
      cursor: pointer;
    }

    .dashboard-nav-item .nav-icon {
      margin-right: 12px;
      font-size: 1.4rem;
    }

    .dashboard-nav-item:hover {
      background: var(--color-surface-hover);
      color: var(--color-text);
    }

    .dashboard-nav-item.active {
      background: var(--color-surface-hover);
      color: var(--color-primary);
      font-weight: 600;
    }

    .sidebar-footer {
      padding: 16px;
      border-top: 1px solid var(--color-border);
    }

    .dashboard-main {
      flex: 1;
      display: flex;
      flex-direction: column;
      height: 100%;
      overflow: hidden;
    }

    .dashboard-header {
      height: var(--header-height);
      background: var(--color-background);
      border-bottom: 1px solid var(--color-border);
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 24px;
      gap: 16px;
    }

    .dashboard-header-title {
      font-size: 1.25rem;
      font-weight: 600;
    }

    .dashboard-content {
      flex: 1;
      overflow-y: auto;
      padding: 24px;
      background: var(--color-surface);
    }

    .view-section { display: none; }
    .view-section.active-view { display: block; }

    /* Botões */
    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      padding: 8px 16px;
      font-size: 13px;
      font-weight: 600;
      border-radius: var(--radius-lg);
      cursor: pointer;
      transition: var(--transition);
    }

    .btn-primary { background: var(--color-primary); color: white; }
    .btn-primary:hover { background: var(--color-primary-hover); transform: translateY(-1px); }
    .btn-secondary { background: var(--color-surface); color: var(--color-text); border: 1px solid var(--color-border); }
    .btn-secondary:hover { background: var(--color-surface-hover); }
    .btn-danger { background: var(--color-error-50); color: var(--color-error); border: 1px solid var(--color-error-100); }
    .btn-danger:hover { background: var(--color-error); color: white; }
    .btn-sm { padding: 4px 10px; font-size: 12px; }
    .btn-full { width: 100%; height: 44px; }

    /* Cards de Estatísticas */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 16px;
      margin-bottom: 24px;
    }

    .stat-card {
      background: var(--color-background);
      border: 1px solid var(--color-border);
      border-radius: var(--radius-xl);
      padding: 18px;
    }

    .stat-card-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 10px;
    }

    .stat-card-title {
      font-size: 12px;
      color: var(--color-text-secondary);
      font-weight: 500;
    }

    .stat-card-icon {
      width: 36px;
      height: 36px;
      border-radius: var(--radius-lg);
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .stat-card-icon.primary { background: var(--color-primary-100); color: var(--color-primary); }
    .stat-card-icon.success { background: var(--color-success-100); color: var(--color-success); }
    .stat-card-icon.warning { background: var(--color-warning-100); color: var(--color-warning); }
    .stat-card-icon.error { background: var(--color-error-100); color: var(--color-error); }

    .stat-card-value {
      font-size: 1.75rem;
      font-weight: 700;
      margin-bottom: 4px;
    }

    .stat-card-change {
      font-size: 12px;
      color: var(--color-text-secondary);
    }
    .stat-card-change.positive { color: var(--color-success); }
    .stat-card-change.negative { color: var(--color-error); }

    /* Tabelas */
    .dashboard-table-container {
      background: var(--color-background);
      border: 1px solid var(--color-border);
      border-radius: var(--radius-xl);
      margin-bottom: 24px;
      overflow: hidden;
    }

    .dashboard-table-header {
      padding: 16px 20px;
      border-bottom: 1px solid var(--color-border);
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .dashboard-table {
      width: 100%;
      border-collapse: collapse;
      text-align: left;
    }

    .dashboard-table th {
      padding: 12px 18px;
      background: var(--color-surface);
      color: var(--color-text-secondary);
      font-size: 11px;
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }

    .dashboard-table td {
      padding: 12px 18px;
      border-bottom: 1px solid var(--color-border);
      font-size: 13px;
    }

    .dashboard-table tbody tr:hover { background: var(--color-surface-hover); }

    .status-badge {
      display: inline-block;
      padding: 3px 8px;
      border-radius: var(--radius-full);
      font-size: 11px;
      font-weight: 600;
    }
    .status-badge.success { background: var(--color-success-100); color: var(--color-success); }
    .status-badge.warning { background: var(--color-warning-100); color: var(--color-warning); }
    .status-badge.error { background: var(--color-error-100); color: var(--color-error); }
    .status-badge.info { background: var(--color-info-100); color: var(--color-info); }

    .tabela-acoes { display: flex; gap: 6px; }
    .btn-acao {
      width: 30px; height: 30px; border-radius: var(--radius-md);
      background: var(--color-surface); border: 1px solid var(--color-border);
      color: var(--color-text-secondary); display: inline-flex; align-items: center; justify-content: center;
      cursor: pointer; transition: var(--transition);
    }
    .btn-acao:hover { color: var(--color-primary); border-color: var(--color-primary); }
    .btn-acao.excluir:hover { color: var(--color-error); border-color: var(--color-error); }

    /* Catraca Especial */
    .catraca-grid { display: grid; grid-template-columns: 1.3fr 1fr; gap: 20px; margin-bottom: 24px; }
    .terminal-card, .simulador-card {
      background: var(--color-background); border: 1px solid var(--color-border);
      border-radius: var(--radius-xl); padding: 20px;
    }
    .terminal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
    .display-leitor {
      min-height: 180px; background: #0a0a0d; border: 2px solid var(--color-border);
      border-radius: var(--radius-lg); display: flex; flex-direction: column;
      align-items: center; justify-content: center; padding: 20px; text-align: center; margin-bottom: 16px;
    }
    .display-leitor.liberado { border-color: var(--color-success); background: rgba(16, 185, 129, 0.08); }
    .display-leitor.bloqueado { border-color: var(--color-error); background: rgba(239, 68, 68, 0.08); }
    .display-icon { font-size: 48px; color: var(--color-primary); margin-bottom: 6px; }
    .display-leitor.liberado .display-icon { color: var(--color-success); }
    .display-leitor.bloqueado .display-icon { color: var(--color-error); }
    .terminal-actions { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px; }

    /* Modais */
    .modal-overlay {
      display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8);
      backdrop-filter: blur(4px); z-index: 3000; align-items: center; justify-content: center; padding: 20px;
    }
    .modal-overlay.active { display: flex; }
    .modal-card {
      width: 100%; max-width: 580px; background: var(--color-background);
      border: 1px solid var(--color-border); border-radius: var(--radius-xl); overflow: hidden;
    }
    .modal-header { padding: 16px 20px; border-bottom: 1px solid var(--color-border); display: flex; justify-content: space-between; align-items: center; }
    .modal-body { padding: 20px; max-height: 75vh; overflow-y: auto; }
    .modal-footer { padding: 14px 20px; background: var(--color-surface); border-top: 1px solid var(--color-border); display: flex; justify-content: flex-end; gap: 10px; }

    .form-row { display: flex; gap: 12px; margin-bottom: 12px; }
    .form-row.split-2 > * { flex: 1; }
    .input-form {
      width: 100%; height: 42px; padding: 0 14px; background: #1c1c21;
      border: 1px solid #2e2e38; border-radius: 8px; color: #fff; font-size: 13px; outline: none;
    }
    .input-form:focus { border-color: var(--color-primary); }

    /* Notificação Toast */
    .toast-aviso {
      position: fixed; bottom: 20px; right: 20px; background: var(--color-primary);
      color: #fff; padding: 12px 20px; border-radius: 8px; font-weight: 600;
      box-shadow: 0 10px 25px rgba(0,0,0,0.8); opacity: 0; transform: translateY(15px);
      transition: all 0.25s ease; z-index: 9999; pointer-events: none;
    }
    .toast-aviso.show { opacity: 1; transform: translateY(0); }
  </style>
</head>
<body>

  <!-- FAIXA SUPERIOR DE DEMO -->
  <div class="demo-banner">
    <span class="material-symbols-rounded" style="font-size:16px;">science</span>
    <span><strong>MODO DEMONSTRAÇÃO ATIVO:</strong> Navegue livremente por todas as telas. Dados gerados localmente no navegador sem banco de dados.</span>
    <a href="../index.php">← Voltar à Página Inicial</a>
  </div>

  <div class="dashboard-container">
    <!-- SIDEBAR -->
    <aside class="dashboard-sidebar" id="dashboardSidebar">
      <div class="dashboard-brand">
        <span class="material-symbols-rounded" style="color:var(--color-primary); font-size: 28px;">fitness_center</span>
        <a class="logo" href="javascript:void(0)" onclick="navegarPara('visao-geral')">Rustic Iron</a>
      </div>

      <nav class="dashboard-nav">
        <a href="javascript:void(0)" class="dashboard-nav-item active" data-target="visao-geral" onclick="navegarPara('visao-geral')">
          <span class="nav-icon material-symbols-rounded">dashboard</span>
          <span>Visão Geral</span>
        </a>
        <a href="javascript:void(0)" class="dashboard-nav-item" data-target="catraca" onclick="navegarPara('catraca')">
          <span class="nav-icon material-symbols-rounded">sensor_door</span>
          <span>Catraca & Acesso</span>
        </a>
        <a href="javascript:void(0)" class="dashboard-nav-item" data-target="financeiro" onclick="navegarPara('financeiro')">
          <span class="nav-icon material-symbols-rounded">payments</span>
          <span>Financeiro</span>
        </a>
        <a href="javascript:void(0)" class="dashboard-nav-item" data-target="planos" onclick="navegarPara('planos')">
          <span class="nav-icon material-symbols-rounded">card_membership</span>
          <span>Planos</span>
        </a>
        <a href="javascript:void(0)" class="dashboard-nav-item" data-target="alunos" onclick="navegarPara('alunos')">
          <span class="nav-icon material-symbols-rounded">groups</span>
          <span>Alunos & Fichas</span>
        </a>
        <a href="javascript:void(0)" class="dashboard-nav-item" data-target="equipe" onclick="navegarPara('equipe')">
          <span class="nav-icon material-symbols-rounded">badge</span>
          <span>Equipe & Coaches</span>
        </a>
        <a href="javascript:void(0)" class="dashboard-nav-item" data-target="equipamentos" onclick="navegarPara('equipamentos')">
          <span class="nav-icon material-symbols-rounded">precision_manufacturing</span>
          <span>Equipamentos</span>
        </a>
        <a href="javascript:void(0)" class="dashboard-nav-item" data-target="configuracoes" onclick="navegarPara('configuracoes')">
          <span class="nav-icon material-symbols-rounded">settings</span>
          <span>Configurações</span>
        </a>
      </nav>

      <div class="sidebar-footer">
        <a href="../index.php" class="btn btn-secondary btn-full">
          <span class="material-symbols-rounded">logout</span>
          <span>Sair da Demo</span>
        </a>
      </div>
    </aside>

    <!-- ÁREA PRINCIPAL -->
    <main class="dashboard-main">
      <header class="dashboard-header">
        <h1 class="dashboard-header-title" id="pageMainTitle">Visão Geral</h1>
        <div style="display: flex; gap: 10px; align-items: center;">
          <button class="btn btn-primary" id="btnHeaderAction" style="display:none;" onclick="executarAcaoContextual()">
            <span class="material-symbols-rounded" id="iconHeaderAction">add</span>
            <span id="labelHeaderAction">Novo</span>
          </button>
          <a href="../auth/auth.php" class="btn btn-secondary btn-sm">
            <span class="material-symbols-rounded" style="font-size:16px;">lock</span> Criar Conta Real
          </a>
        </div>
      </header>

      <div class="dashboard-content">
        <!-- 1. VISÃO GERAL -->
        <section id="view-visao-geral" class="view-section active-view">
          <div class="stats-grid">
            <div class="stat-card">
              <div class="stat-card-header">
                <span class="stat-card-title">Presentes no Ginásio</span>
                <div class="stat-card-icon primary"><span class="material-symbols-rounded">sensor_occupied</span></div>
              </div>
              <div class="stat-card-value" id="dashTotalPresentes">4</div>
              <div class="stat-card-change positive">● Catraca Integrada</div>
            </div>
            <div class="stat-card">
              <div class="stat-card-header">
                <span class="stat-card-title">Faturamento Previsto</span>
                <div class="stat-card-icon success"><span class="material-symbols-rounded">payments</span></div>
              </div>
              <div class="stat-card-value">R$ 1.950,00</div>
              <div class="stat-card-change positive">↑ 12% vs mês anterior</div>
            </div>
            <div class="stat-card">
              <div class="stat-card-header">
                <span class="stat-card-title">Alunos Matriculados</span>
                <div class="stat-card-icon warning"><span class="material-symbols-rounded">how_to_reg</span></div>
              </div>
              <div class="stat-card-value" id="dashTotalAlunos">3</div>
              <div class="stat-card-change">Ativos no sistema</div>
            </div>
            <div class="stat-card">
              <div class="stat-card-header">
                <span class="stat-card-title">Inadimplentes Bloqueados</span>
                <div class="stat-card-icon error"><span class="material-symbols-rounded">warning</span></div>
              </div>
              <div class="stat-card-value">1</div>
              <div class="stat-card-change negative">Catraca em trava automática</div>
            </div>
          </div>

          <div class="dashboard-table-container">
            <div class="dashboard-table-header">
              <h3>Demonstração Interativa Pronta</h3>
              <span class="status-badge success">Online</span>
            </div>
            <div style="padding: 24px; text-align: center; color: var(--color-text-secondary);">
              <p style="font-size: 15px; margin-bottom: 12px; color: #fff;">
                Bem-vindo à área de testes do <strong>Rustic Iron</strong>!
              </p>
              <p style="max-width: 600px; margin: 0 auto 16px;">
                Use o menu à esquerda para simular acessos na catraca, cadastrar alunos com fichas de treino, baixar parcelas no financeiro e configurar o hardware.
              </p>
              <button class="btn btn-primary" onclick="navegarPara('catraca')">
                <span class="material-symbols-rounded">sensor_door</span> Testar Catraca Agora
              </button>
            </div>
          </div>
        </section>

        <!-- 2. CATRACA & ACESSO -->
        <section id="view-catraca" class="view-section">
          <div class="stats-grid">
            <div class="stat-card">
              <div class="stat-card-header"><span class="stat-card-title">Presentes Agora</span><div class="stat-card-icon primary"><span class="material-symbols-rounded">sensor_occupied</span></div></div>
              <div class="stat-card-value" id="catTotalPresentes">4</div>
            </div>
            <div class="stat-card">
              <div class="stat-card-header"><span class="stat-card-title">Entradas Concluídas</span><div class="stat-card-icon success"><span class="material-symbols-rounded">login</span></div></div>
              <div class="stat-card-value" id="catTotalEntradas">12</div>
            </div>
            <div class="stat-card">
              <div class="stat-card-header"><span class="stat-card-title">Acessos Recusados</span><div class="stat-card-icon error"><span class="material-symbols-rounded">block</span></div></div>
              <div class="stat-card-value" id="catTotalRecusados">2</div>
            </div>
          </div>

          <div class="catraca-grid">
            <div class="terminal-card">
              <div class="terminal-header">
                <div>
                  <h3>Torniquete #01 (Principal)</h3>
                  <p style="font-size: 11px; color: var(--color-text-muted);">Control iD / TopData (IP: 192.168.1.200)</p>
                </div>
                <span class="status-badge success" id="badgeConexao">Online</span>
              </div>
              <div class="display-leitor" id="displayLeitor">
                <span class="material-symbols-rounded display-icon" id="displayIcon">fingerprint</span>
                <h2 id="displayTitulo" style="font-size: 18px; margin-bottom: 4px;">AGUARDANDO LEITURA</h2>
                <p id="displayMsg" style="font-size: 12px; color: var(--color-text-secondary);">Passe o cartão RFID ou digite o CPF abaixo</p>
              </div>
              <div class="terminal-actions">
                <button type="button" class="btn btn-primary" onclick="liberarGiroManual('entrada')">Liberar Entrada</button>
                <button type="button" class="btn btn-secondary" onclick="liberarGiroManual('saida')">Liberar Saída</button>
                <button type="button" class="btn btn-danger" onclick="bloquearEmergencia()">Pânico</button>
              </div>
            </div>

            <div class="simulador-card">
              <h3 style="margin-bottom: 12px;">Identificação Manual</h3>
              <form onsubmit="processarIdentificacao(event)">
                <div style="margin-bottom: 12px;">
                  <label style="font-size: 11px; text-transform: uppercase;">CPF ou Cartão RFID</label>
                  <input type="text" id="campoIdentificador" class="input-form" placeholder="Digite 044.821.992-10..." required />
                </div>
                <div style="margin-bottom: 16px;">
                  <label style="font-size: 11px; text-transform: uppercase;">Sentido</label>
                  <select id="tipoSentido" class="input-form">
                    <option value="Entrada">Entrada (Giro Horário)</option>
                    <option value="Saída">Saída (Giro Anti-horário)</option>
                  </select>
                </div>
                <button type="submit" class="btn btn-primary btn-full">Validar Acesso</button>
              </form>
              <div style="margin-top: 16px; border-top: 1px solid var(--color-border); padding-top: 12px;">
                <p style="font-size: 11px; margin-bottom: 8px;">Cenários de Teste Rápidos:</p>
                <div style="display: flex; gap: 6px;">
                  <button class="btn btn-secondary btn-sm" onclick="preencherDemo('liberado')">Aluno Válido</button>
                  <button class="btn btn-secondary btn-sm" onclick="preencherDemo('bloqueado')">Inadimplente</button>
                </div>
              </div>
            </div>
          </div>

          <div class="dashboard-table-container">
            <div class="dashboard-table-header">
              <h3>Histórico de Passagens</h3>
              <button class="btn btn-secondary btn-sm" onclick="limparHistorico()">Limpar</button>
            </div>
            <table class="dashboard-table">
              <thead>
                <tr>
                  <th>Horário</th>
                  <th>Aluno</th>
                  <th>Documento</th>
                  <th>Sentido</th>
                  <th>Status</th>
                  <th>Resposta</th>
                </tr>
              </thead>
              <tbody id="tabelaLogsCatraca"></tbody>
            </table>
          </div>
        </section>

        <!-- 3. FINANCEIRO -->
        <section id="view-financeiro" class="view-section">
          <div class="stats-grid">
            <div class="stat-card">
              <div class="stat-card-header"><span class="stat-card-title">Recebido (Mês)</span><div class="stat-card-icon success"><span class="material-symbols-rounded">payments</span></div></div>
              <div class="stat-card-value" id="cardTotalRecebido">R$ 260,00</div>
            </div>
            <div class="stat-card">
              <div class="stat-card-header"><span class="stat-card-title">Pendente</span><div class="stat-card-icon warning"><span class="material-symbols-rounded">hourglass_top</span></div></div>
              <div class="stat-card-value" id="cardTotalPendente">R$ 190,00</div>
            </div>
            <div class="stat-card">
              <div class="stat-card-header"><span class="stat-card-title">Vencido</span><div class="stat-card-icon error"><span class="material-symbols-rounded">warning</span></div></div>
              <div class="stat-card-value" id="cardTotalAtrasado">R$ 480,00</div>
            </div>
          </div>

          <div class="dashboard-table-container">
            <div class="dashboard-table-header">
              <h3>Controle de Mensalidades</h3>
            </div>
            <table class="dashboard-table">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Aluno</th>
                  <th>Plano</th>
                  <th>Vencimento</th>
                  <th>Valor</th>
                  <th>Status</th>
                  <th>Ações</th>
                </tr>
              </thead>
              <tbody id="tabelaFaturas"></tbody>
            </table>
          </div>
        </section>

        <!-- 4. PLANOS -->
        <section id="view-planos" class="view-section">
          <div class="dashboard-table-container">
            <div class="dashboard-table-header">
              <h3>Catálogo de Planos</h3>
            </div>
            <table class="dashboard-table">
              <thead>
                <tr>
                  <th>Código</th>
                  <th>Plano</th>
                  <th>Vigência</th>
                  <th>Valor</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody id="tabelaPlanos"></tbody>
            </table>
          </div>
        </section>

        <!-- 5. ALUNOS & FICHAS -->
        <section id="view-alunos" class="view-section">
          <div class="dashboard-table-container">
            <div class="dashboard-table-header">
              <h3>Alunos Cadastrados</h3>
            </div>
            <table class="dashboard-table">
              <thead>
                <tr>
                  <th>Nome</th>
                  <th>CPF</th>
                  <th>Telefone</th>
                  <th>Métricas</th>
                  <th>Modalidade</th>
                  <th>Catraca</th>
                  <th>Ações</th>
                </tr>
              </thead>
              <tbody id="tabelaAlunos"></tbody>
            </table>
          </div>
        </section>

        <!-- 6. EQUIPE -->
        <section id="view-equipe" class="view-section">
          <div class="dashboard-table-container">
            <div class="dashboard-table-header">
              <h3>Quadro de Profissionais</h3>
            </div>
            <table class="dashboard-table">
              <thead>
                <tr>
                  <th>Colaborador</th>
                  <th>Cargo</th>
                  <th>CREF</th>
                  <th>Turno</th>
                  <th>Contato</th>
                </tr>
              </thead>
              <tbody id="tabelaEquipe"></tbody>
            </table>
          </div>
        </section>

        <!-- 7. EQUIPAMENTOS -->
        <section id="view-equipamentos" class="view-section">
          <div class="dashboard-table-container">
            <div class="dashboard-table-header">
              <h3>Inventário de Maquinários</h3>
            </div>
            <table class="dashboard-table">
              <thead>
                <tr>
                  <th>Código</th>
                  <th>Aparelho</th>
                  <th>Setor</th>
                  <th>Qtd</th>
                  <th>Estado</th>
                </tr>
              </thead>
              <tbody id="tabelaEquipamentos"></tbody>
            </table>
          </div>
        </section>

        <!-- 8. CONFIGURAÇÕES -->
        <section id="view-configuracoes" class="view-section">
          <div class="dashboard-table-container" style="padding: 24px;">
            <h3 style="margin-bottom: 16px;">Parâmetros da Unidade</h3>
            <div class="form-row split-2">
              <div>
                <label style="font-size:11px;">NOME DA ACADEMIA</label>
                <input type="text" class="input-form" value="Rustic Iron Gym" />
              </div>
              <div>
                <label style="font-size:11px;">IP DA CATRACA</label>
                <input type="text" class="input-form" value="192.168.1.200" />
              </div>
            </div>
            <button class="btn btn-primary" onclick="mostrarToast('Configurações salvas na sessão!')">Salvar Parâmetros</button>
          </div>
        </section>
      </div>
    </main>
  </div>

  <!-- MODAL NOVO ALUNO -->
  <div class="modal-overlay" id="modalAluno">
    <div class="modal-card">
      <div class="modal-header">
        <h3>Cadastrar Novo Aluno (Demo)</h3>
        <button class="modal-close" onclick="fecharModal('modalAluno')">&times;</button>
      </div>
      <form onsubmit="salvarNovoAlunoDemo(event)">
        <div class="modal-body">
          <div class="form-row">
            <input type="text" id="demoNome" class="input-form" placeholder="Nome Completo" required />
          </div>
          <div class="form-row split-2">
            <input type="text" id="demoCpf" class="input-form" placeholder="CPF" required />
            <input type="tel" id="demoTel" class="input-form" placeholder="Telefone" required />
          </div>
          <div class="form-row split-2">
            <input type="number" id="demoPeso" class="input-form" placeholder="Peso (kg)" required />
            <input type="number" id="demoAltura" class="input-form" placeholder="Altura (cm)" required />
          </div>
          <div class="form-row">
            <select id="demoModalidade" class="input-form" required>
              <option value="Powerlifting">Powerlifting</option>
              <option value="Musculação">Musculação</option>
              <option value="Hipertrofia">Hipertrofia</option>
              <option value="Cross Training">Cross Training</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" onclick="fecharModal('modalAluno')">Cancelar</button>
          <button type="submit" class="btn btn-primary">Cadastrar</button>
        </div>
      </form>
    </div>
  </div>

  <div class="toast-aviso" id="toastAviso">Operação concluída!</div>

  <!-- SCRIPT UNIFICADO DA DEMO -->
  <script>
    // ESTADO LOCAL MOCKADO
    let alunosMock = [
      { id: 1, nome: "Carlos Eduardo Mendes", cpf: "044.821.992-10", tel: "(79) 99823-1122", peso: 88.5, altura: 178, mod: "Powerlifting", liberado: true },
      { id: 2, nome: "Mariana Silva Freitas", cpf: "712.943.111-45", tel: "(79) 98110-4490", peso: 63.2, altura: 165, mod: "Hipertrofia", liberado: false },
      { id: 3, nome: "Rodrigo Santos Oliveira", cpf: "890.312.441-01", tel: "(79) 99122-3344", peso: 94.0, altura: 182, mod: "Musculação", liberado: true }
    ];

    let faturasMock = [
      { id: 101, aluno: "Carlos Eduardo Mendes", plano: "Anual Pesado", venc: "05/10/2026", valor: "R$ 130,00", status: "pago" },
      { id: 102, aluno: "Mariana Silva Freitas", plano: "Mensal Livre", venc: "22/09/2026", valor: "R$ 130,00", status: "atrasado" },
      { id: 103, aluno: "Rodrigo Santos Oliveira", plano: "Full Combat", venc: "15/10/2026", valor: "R$ 190,00", status: "pendente" }
    ];

    let equipeMock = [
      { nome: "Marcos Valério Santos", cargo: "Personal Trainer", cref: "014523-G/SE", turno: "Manhã", tel: "(79) 99812-4411" },
      { nome: "Camila Duarte Lima", cargo: "Coach Powerlifting", cref: "009832-G/SE", turno: "Tarde", tel: "(79) 99120-7733" }
    ];

    let planosMock = [
      { cod: "PLN-01", nome: "Iron Mensal Livre", vig: "30 dias", val: "R$ 130,00", status: "Ativo" },
      { cod: "PLN-02", nome: "Full Combat & Iron", vig: "30 dias", val: "R$ 190,00", status: "Ativo" },
      { cod: "PLN-03", nome: "Anual Pesado", vig: "365 dias", val: "R$ 1.150,00", status: "Ativo" }
    ];

    let equipMock = [
      { cod: "EQP-01", nome: "Gaiola Monolift / Squat Rack", setor: "Powerlifting", qtd: 3, estado: "Excelente" },
      { cod: "EQP-02", nome: "Barras Olímpicas Eleiko 20kg", setor: "Pesos Livres", qtd: 12, estado: "Excelente" },
      { cod: "EQP-03", nome: "Leg Press 45° Blindado", setor: "Musculação", qtd: 2, estado: "Revisão Próxima" }
    ];

    let presentes = 4;
    let entradas = 12;
    let recusados = 2;

    // RENDERIZADORES
    function renderizarTudo() {
      // Alunos
      const tbAlunos = document.getElementById("tabelaAlunos");
      if (tbAlunos) {
        tbAlunos.innerHTML = alunosMock.map(a => `
          <tr>
            <td><strong>${a.nome}</strong></td>
            <td>${a.cpf}</td>
            <td>${a.tel}</td>
            <td>${a.peso}kg • ${a.altura}cm</td>
            <td><span class="status-badge info">${a.mod}</span></td>
            <td><span class="status-badge ${a.liberado ? 'success' : 'error'}">${a.liberado ? 'Liberado' : 'Bloqueado'}</span></td>
            <td>
              <button class="btn-acao" title="Alternar Catraca" onclick="alternarCatracaAluno(${a.id})">
                <span class="material-symbols-rounded" style="font-size:16px;">lock_reset</span>
              </button>
            </td>
          </tr>
        `).join("");
      }

      // Faturas
      const tbFaturas = document.getElementById("tabelaFaturas");
      if (tbFaturas) {
        tbFaturas.innerHTML = faturasMock.map(f => `
          <tr>
            <td>#${f.id}</td>
            <td>${f.aluno}</td>
            <td>${f.plano}</td>
            <td>${f.venc}</td>
            <td><strong>${f.valor}</strong></td>
            <td><span class="status-badge ${f.status === 'pago' ? 'success' : (f.status === 'atrasado' ? 'error' : 'warning')}">${f.status.toUpperCase()}</span></td>
            <td>
              ${f.status !== 'pago' ? `<button class="btn btn-secondary btn-sm" onclick="darBaixaFatura(${f.id})">Baixar</button>` : '<span>Quitado</span>'}
            </td>
          </tr>
        `).join("");
      }

      // Equipe
      const tbEq = document.getElementById("tabelaEquipe");
      if (tbEq) {
        tbEq.innerHTML = equipeMock.map(e => `
          <tr>
            <td><strong>${e.nome}</strong></td>
            <td>${e.cargo}</td>
            <td>${e.cref}</td>
            <td>${e.turno}</td>
            <td>${e.tel}</td>
          </tr>
        `).join("");
      }

      // Planos
      const tbPl = document.getElementById("tabelaPlanos");
      if (tbPl) {
        tbPl.innerHTML = planosMock.map(p => `
          <tr>
            <td>${p.cod}</td>
            <td><strong>${p.nome}</strong></td>
            <td>${p.vig}</td>
            <td>${p.val}</td>
            <td><span class="status-badge success">${p.status}</span></td>
          </tr>
        `).join("");
      }

      // Equipamentos
      const tbEqp = document.getElementById("tabelaEquipamentos");
      if (tbEqp) {
        tbEqp.innerHTML = equipMock.map(e => `
          <tr>
            <td>${e.cod}</td>
            <td><strong>${e.nome}</strong></td>
            <td>${e.setor}</td>
            <td>${e.qtd}</td>
            <td><span class="status-badge ${e.estado === 'Excelente' ? 'success' : 'warning'}">${e.estado}</span></td>
          </tr>
        `).join("");
      }

      document.getElementById("dashTotalAlunos").textContent = alunosMock.length;
    }

    // NAVEGAÇÃO SPA
    const titles = {
      'visao-geral': 'Visão Geral',
      'catraca': 'Catraca & Controle de Acesso',
      'financeiro': 'Financeiro & Cobranças',
      'planos': 'Planos de Adesão',
      'alunos': 'Alunos & Fichas de Treino',
      'equipe': 'Equipe & Treinadores',
      'equipamentos': 'Inventário de Equipamentos',
      'configuracoes': 'Configurações da Unidade'
    };

    function navegarPara(view) {
      document.querySelectorAll('.view-section').forEach(el => el.classList.remove('active-view'));
      document.querySelectorAll('.dashboard-nav-item').forEach(el => el.classList.remove('active'));

      const sec = document.getElementById('view-' + view);
      if (sec) sec.classList.add('active-view');

      const navItem = document.querySelector(`[data-target="${view}"]`);
      if (navItem) navItem.classList.add('active');

      document.getElementById('pageMainTitle').textContent = titles[view] || 'Dashboard';

      const btnAcao = document.getElementById('btnHeaderAction');
      const label = document.getElementById('labelHeaderAction');
      if (view === 'alunos') {
        btnAcao.style.display = 'inline-flex';
        label.textContent = 'Novo Aluno';
      } else {
        btnAcao.style.display = 'none';
      }
    }

    function executarAcaoContextual() {
      document.getElementById('modalAluno').classList.add('active');
    }

    function fecharModal(id) {
      document.getElementById(id).classList.remove('active');
    }

    // SIMULADOR CATRACA
    function processarIdentificacao(e) {
      e.preventDefault();
      const val = document.getElementById("campoIdentificador").value.trim();
      const sentido = document.getElementById("tipoSentido").value;
      const aluno = alunosMock.find(a => a.cpf === val || a.nome.toLowerCase().includes(val.toLowerCase()));

      const hora = new Date().toLocaleTimeString();

      if (aluno && aluno.liberado) {
        atualizarDisplayCatraca(true, "ACESSO LIBERADO", `Bem-vindo(a), ${aluno.nome}!`);
        if (sentido === 'Entrada') { presentes++; entradas++; } else { if (presentes > 0) presentes--; }
        adicionarLog(hora, aluno.nome, aluno.cpf, sentido, true, "Giro Autorizado");
      } else if (aluno && !aluno.liberado) {
        recusados++;
        atualizarDisplayCatraca(false, "ACESSO BLOQUEADO", "Mensalidade em aberto - Dirija-se à Recepção");
        adicionarLog(hora, aluno.nome, aluno.cpf, sentido, false, "Inadimplência");
      } else {
        recusados++;
        atualizarDisplayCatraca(false, "NÃO ENCONTRADO", "ID ou CPF não localizado");
        adicionarLog(hora, "Desconhecido", val, sentido, false, "Cadastro Inexistente");
      }

      document.getElementById("catTotalPresentes").textContent = presentes;
      document.getElementById("dashTotalPresentes").textContent = presentes;
      document.getElementById("catTotalEntradas").textContent = entradas;
      document.getElementById("catTotalRecusados").textContent = recusados;
      document.getElementById("campoIdentificador").value = "";
    }

    function preencherDemo(tipo) {
      if (tipo === 'liberado') document.getElementById('campoIdentificador').value = "044.821.992-10";
      if (tipo === 'bloqueado') document.getElementById('campoIdentificador').value = "712.943.111-45";
    }

    function atualizarDisplayCatraca(sucesso, tit, msg) {
      const d = document.getElementById("displayLeitor");
      const icon = document.getElementById("displayIcon");
      d.className = "display-leitor " + (sucesso ? "liberado" : "bloqueado");
      icon.textContent = sucesso ? "check_circle" : "cancel";
      document.getElementById("displayTitulo").textContent = tit;
      document.getElementById("displayMsg").textContent = msg;

      setTimeout(() => {
        d.className = "display-leitor";
        icon.textContent = "fingerprint";
        document.getElementById("displayTitulo").textContent = "AGUARDANDO LEITURA";
        document.getElementById("displayMsg").textContent = "Passe o cartão RFID ou digite o CPF abaixo";
      }, 3500);
    }

    function liberarGiroManual(sentido) {
      atualizarDisplayCatraca(true, "LIBERAÇÃO MANUAL", "Comando acionado pelo operador");
      if (sentido === 'entrada') presentes++;
      adicionarLog(new Date().toLocaleTimeString(), "Operador da Recepção", "Admin", sentido, true, "Giro Avulso");
    }

    function bloquearEmergencia() {
      atualizarDisplayCatraca(false, "MODO PÂNICO", "Catracas travadas por segurança");
    }

    function adicionarLog(hora, nome, doc, sentido, ok, msg) {
      const tb = document.getElementById("tabelaLogsCatraca");
      const tr = document.createElement("tr");
      tr.innerHTML = `
        <td>${hora}</td>
        <td><strong>${nome}</strong></td>
        <td>${doc}</td>
        <td>${sentido}</td>
        <td><span class="status-badge ${ok ? 'success' : 'error'}">${ok ? 'Liberado' : 'Recusado'}</span></td>
        <td>${msg}</td>
      `;
      tb.insertBefore(tr, tb.firstChild);
    }

    function limparHistorico() {
      document.getElementById("tabelaLogsCatraca").innerHTML = "";
    }

    function salvarNovoAlunoDemo(e) {
      e.preventDefault();
      const novo = {
        id: alunosMock.length + 1,
        nome: document.getElementById("demoNome").value,
        cpf: document.getElementById("demoCpf").value,
        tel: document.getElementById("demoTel").value,
        peso: document.getElementById("demoPeso").value,
        altura: document.getElementById("demoAltura").value,
        mod: document.getElementById("demoModalidade").value,
        liberado: true
      };
      alunosMock.unshift(novo);
      renderizarTudo();
      fecharModal('modalAluno');
      mostrarToast(`Aluno "${novo.nome}" cadastrado com sucesso!`);
      e.target.reset();
    }

    function alternarCatracaAluno(id) {
      const a = alunosMock.find(x => x.id === id);
      if (a) {
        a.liberado = !a.liberado;
        renderizarTudo();
        mostrarToast(`Status da catraca de ${a.nome} alterado para ${a.liberado ? 'Liberado' : 'Bloqueado'}`);
      }
    }

    function darBaixaFatura(id) {
      const f = faturasMock.find(x => x.id === id);
      if (f) {
        f.status = "pago";
        renderizarTudo();
        mostrarToast(`Mensalidade #${id} quitada e registrada no caixa!`);
      }
    }

    function mostrarToast(msg) {
      const t = document.getElementById("toastAviso");
      t.textContent = msg;
      t.classList.add("show");
      setTimeout(() => t.classList.remove("show"), 3000);
    }

    document.addEventListener("DOMContentLoaded", () => {
      renderizarTudo();
      
      const urlParams = new URLSearchParams(window.location.search);
      const tab = urlParams.get('tab');
      if (tab) {
        navegarPara(tab);
      }
    });
  </script>
</body>
</html>