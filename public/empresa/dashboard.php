<!DOCTYPE html>
<html lang="pt-BR" data-theme="dark">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Rustic Iron - Sistema Integrado de Gestão</title>

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <link rel="stylesheet" href="../css/empresa/dashboard.css" />
    <link rel="stylesheet" href="../css/empresa/catraca.css" />
    <link rel="stylesheet" href="../css/empresa/financeiro.css" />
    <link rel="stylesheet" href="../css/empresa/planos.css" />
    <link rel="stylesheet" href="../css/empresa/alunos.css" />
    <link rel="stylesheet" href="../css/empresa/equipe.css" />
    <link rel="stylesheet" href="../css/empresa/equipamentos.css" />
    <link rel="stylesheet" href="../css/empresa/configuracoes.css" />
    <style>
      .view-section {
        display: none;
      }
      .view-section.active-view {
        display: block;
      }
    </style>
  </head>
  <body>
    <div class="dashboard-container">
      <!-- SIDEBAR UNIFICADA -->
      <aside class="dashboard-sidebar" id="dashboardSidebar">
        <div class="dashboard-brand">
          <button class="dashboard-sidebar-toggle" id="btnToggleSidebar">
            <span class="material-symbols-rounded">menu</span>
          </button>
          <a class="logo" href="#" onclick="navegarPara('visao-geral')">Rustic Iron</a>
        </div>

        <nav class="dashboard-nav">
          <div class="dashboard-nav-section">
            <a href="javascript:void(0)" class="dashboard-nav-item active" data-target="visao-geral" onclick="navegarPara('visao-geral')">
              <span class="nav-icon material-symbols-rounded">dashboard</span>
              <span class="nav-label">Visão Geral</span>
            </a>
            <a href="javascript:void(0)" class="dashboard-nav-item" data-target="catraca" onclick="navegarPara('catraca')">
              <span class="nav-icon material-symbols-rounded">sensor_door</span>
              <span class="nav-label">Catraca & Acesso</span>
            </a>
            <a href="javascript:void(0)" class="dashboard-nav-item" data-target="financeiro" onclick="navegarPara('financeiro')">
              <span class="nav-icon material-symbols-rounded">payments</span>
              <span class="nav-label">Financeiro</span>
            </a>
            <a href="javascript:void(0)" class="dashboard-nav-item" data-target="planos" onclick="navegarPara('planos')">
              <span class="nav-icon material-symbols-rounded">card_membership</span>
              <span class="nav-label">Planos</span>
            </a>
            <a href="javascript:void(0)" class="dashboard-nav-item" data-target="alunos" onclick="navegarPara('alunos')">
              <span class="nav-icon material-symbols-rounded">groups</span>
              <span class="nav-label">Alunos</span>
            </a>
            <a href="javascript:void(0)" class="dashboard-nav-item" data-target="equipe" onclick="navegarPara('equipe')">
              <span class="nav-icon material-symbols-rounded">badge</span>
              <span class="nav-label">Equipe</span>
            </a>
            <a href="javascript:void(0)" class="dashboard-nav-item" data-target="equipamentos" onclick="navegarPara('equipamentos')">
              <span class="nav-icon material-symbols-rounded">fitness_center</span>
              <span class="nav-label">Equipamentos</span>
            </a>
            <a href="javascript:void(0)" class="dashboard-nav-item" data-target="configuracoes" onclick="navegarPara('configuracoes')">
              <span class="nav-icon material-symbols-rounded">settings</span>
              <span class="nav-label">Configurações</span>
            </a>
          </div>
        </nav>

        <div class="sidebar-footer">
          <a href="index.php" class="btn btn-secondary sidebar-back-button">
            <span class="material-symbols-rounded">logout</span>
            <span class="btn-label">Sair da Conta</span>
          </a>
        </div>
      </aside>

      <div class="dashboard-sidebar-overlay" id="dashboardSidebarOverlay"></div>

      <!-- MAIN CONTAINER -->
      <main class="dashboard-main">
        <header class="dashboard-header">
          <div class="dashboard-header-content">
            <button class="dashboard-sidebar-toggle" id="btnToggleSidebarHeader">
              <span class="material-symbols-rounded">menu</span>
            </button>
            <h1 class="dashboard-header-title" id="pageMainTitle">Visão Geral</h1>
          </div>

          <div class="search-container" id="searchContainer">
            <span class="search-icon material-symbols-rounded">search</span>
            <input type="search" class="search-input form-input" placeholder="Pesquisar no sistema..." id="searchInputGlobal" />
            <button class="search-close btn" id="searchClose">
              <span class="material-symbols-rounded">close</span>
            </button>
          </div>

          <div class="dashboard-header-actions" id="headerActions">
            <button class="btn btn-primary" id="btnHeaderActionContextual" style="display: none;">
              <span class="material-symbols-rounded" id="iconHeaderAction">add_circle</span>
              <span id="labelHeaderAction">Novo</span>
            </button>

            <div class="user-menu" id="userMenu">
              <div class="user-menu-trigger" id="user-menu-trigger" onclick="event.stopPropagation(); this.closest('.user-menu').classList.toggle('active');">
                <div class="user-avatar-small">
                  <span class="material-symbols-rounded">corporate_fare</span>
                </div>
              </div>
              <div class="user-menu-dropdown">
                <a href="javascript:void(0)" onclick="navegarPara('configuracoes')" class="user-menu-item">
                  <span class="icon material-symbols-rounded">domain</span>
                  <span>Configurações</span>
                </a>
                <div class="user-menu-item theme-item">
                  <span class="icon material-symbols-rounded">palette</span>
                  <div class="theme-toggle" id="theme-toggle">
                    <div class="theme-option" data-theme="light">Light</div>
                    <div class="theme-option active" data-theme="dark">Dark</div>
                  </div>
                </div>
                <a href="index.php" class="user-menu-item">
                  <span class="icon material-symbols-rounded">logout</span>
                  <span>Sair</span>
                </a>
              </div>
            </div>
          </div>
        </header>

        <div class="dashboard-content">
          <!-- 1. SEÇÃO: VISÃO GERAL -->
          <section id="view-visao-geral" class="view-section active-view">
            <div class="stats-grid">
              <div class="stat-card">
                <div class="stat-card-header">
                  <div class="stat-card-title">Presentes Agora</div>
                  <div class="stat-card-icon primary"><span class="material-symbols-rounded">sensor_occupied</span></div>
                </div>
                <div class="stat-card-value">0</div>
                <div class="stat-card-change"><span>Catraca sincronizada</span></div>
              </div>

              <div class="stat-card">
                <div class="stat-card-header">
                  <div class="stat-card-title">Faturamento Mês</div>
                  <div class="stat-card-icon success"><span class="material-symbols-rounded">attach_money</span></div>
                </div>
                <div class="stat-card-value">R$ 0,00</div>
                <div class="stat-card-change"><span>Sem lançamentos</span></div>
              </div>

              <div class="stat-card">
                <div class="stat-card-header">
                  <div class="stat-card-title">Alunos Ativos</div>
                  <div class="stat-card-icon warning"><span class="material-symbols-rounded">how_to_reg</span></div>
                </div>
                <div class="stat-card-value">0</div>
                <div class="stat-card-change"><span>Aguardando cadastros</span></div>
              </div>

              <div class="stat-card">
                <div class="stat-card-header">
                  <div class="stat-card-title">Inadimplência</div>
                  <div class="stat-card-icon error"><span class="material-symbols-rounded">warning</span></div>
                </div>
                <div class="stat-card-value">0</div>
                <div class="stat-card-change"><span>Nenhuma pendência</span></div>
              </div>
            </div>

            <div class="dashboard-table-container">
              <div class="empty-state">
                <div class="empty-state-icon"><span class="material-symbols-rounded">dashboard</span></div>
                <h3 class="empty-state-title">Painel Geral Pronto</h3>
                <p class="empty-state-description">Navegue pelas abas do menu lateral para gerenciar alunos, fichas de treino, catraca e finanças.</p>
              </div>
            </div>
          </section>

          <!-- 2. SEÇÃO: CATRACA & ACESSO -->
          <section id="view-catraca" class="view-section">
            <div class="stats-grid">
              <div class="stat-card">
                <div class="stat-card-header">
                  <div class="stat-card-title">Presentes no Ginásio</div>
                  <div class="stat-card-icon primary"><span class="material-symbols-rounded">sensor_occupied</span></div>
                </div>
                <div class="stat-card-value" id="totalPresentes">0</div>
                <div class="stat-card-change positive"><span class="material-symbols-rounded">sync</span><span>Contagem em tempo real</span></div>
              </div>
              <div class="stat-card">
                <div class="stat-card-header">
                  <div class="stat-card-title">Entradas Hoje</div>
                  <div class="stat-card-icon success"><span class="material-symbols-rounded">login</span></div>
                </div>
                <div class="stat-card-value" id="totalEntradas">0</div>
                <div class="stat-card-change positive"><span class="material-symbols-rounded">check_circle</span><span>Giros concluídos</span></div>
              </div>
              <div class="stat-card">
                <div class="stat-card-header">
                  <div class="stat-card-title">Saídas Hoje</div>
                  <div class="stat-card-icon info"><span class="material-symbols-rounded">logout</span></div>
                </div>
                <div class="stat-card-value" id="totalSaidas">0</div>
                <div class="stat-card-change"><span>Registros de saída</span></div>
              </div>
              <div class="stat-card">
                <div class="stat-card-header">
                  <div class="stat-card-title">Acessos Recusados</div>
                  <div class="stat-card-icon error"><span class="material-symbols-rounded">block</span></div>
                </div>
                <div class="stat-card-value" id="totalRecusados">0</div>
                <div class="stat-card-change negative"><span class="material-symbols-rounded">warning</span><span>Bloqueios por fatura/inadimplência</span></div>
              </div>
            </div>

            <div class="catraca-grid">
              <div class="terminal-card">
                <div class="terminal-header">
                  <div class="terminal-status-info">
                    <div class="status-pulse-dot" id="catracaStatusDot"></div>
                    <div>
                      <h3 class="terminal-title">Torniquete Principal #01</h3>
                      <p class="terminal-sub">Hardware: Henry / TopData (IP: 192.168.1.200)</p>
                    </div>
                  </div>
                  <span class="status-badge success" id="badgeConexao">Online</span>
                </div>

                <div class="display-leitor" id="displayLeitor">
                  <span class="material-symbols-rounded display-icon" id="displayIcon">fingerprint</span>
                  <h2 id="displayTitulo">AGUARDANDO LEITURA</h2>
                  <p id="displayMsg">Aproxime o cartão RFID, biometria ou digite o CPF</p>
                  <div class="display-aluno-detalhes" id="displayDetalhes" style="display: none;">
                    <strong id="detalheNome">-</strong>
                    <span id="detalhePlano">-</span>
                  </div>
                </div>

                <div class="terminal-actions">
                  <button type="button" class="btn btn-primary" onclick="liberarGiroManual('entrada')">
                    <span class="material-symbols-rounded">lock_open</span> Liberar Entrada
                  </button>
                  <button type="button" class="btn btn-secondary" onclick="liberarGiroManual('saida')">
                    <span class="material-symbols-rounded">output</span> Liberar Saída
                  </button>
                  <button type="button" class="btn btn-danger" onclick="bloquearEmergencia()">
                    <span class="material-symbols-rounded">emergency_home</span> Trava de Pânico
                  </button>
                </div>
              </div>

              <div class="simulador-card">
                <div class="chart-card-header">
                  <h3 class="chart-card-title">Identificação de Acesso</h3>
                  <p class="chart-card-subtitle">Entrada manual via recepção ou leitor USB de bancada</p>
                </div>

                <form id="formAcessoRapido" onsubmit="processarIdentificacao(event)">
                  <div class="form-group-catraca">
                    <label for="campoIdentificador">CPF, Cartão RFID ou Matrícula</label>
                    <div class="input-com-icone">
                      <span class="material-symbols-rounded">badge</span>
                      <input type="text" id="campoIdentificador" placeholder="Digite o CPF ou passe o cartão..." autocomplete="off" />
                    </div>
                  </div>

                  <div class="form-group-catraca">
                    <label for="tipoSentido">Sentido do Giro</label>
                    <select id="tipoSentido" class="select-catraca">
                      <option value="Entrada">Entrada (Giro Horário)</option>
                      <option value="Saída">Saída (Giro Anti-horário)</option>
                    </select>
                  </div>

                  <div class="simulador-botoes-teste">
                    <button type="submit" class="btn btn-primary btn-full">
                      <span class="material-symbols-rounded">sensor_door</span> Validar & Liberar Giro
                    </button>
                  </div>
                </form>

                <div class="atalhos-demo">
                  <span>Testar cenários:</span>
                  <div class="botoes-demo-tags">
                    <button type="button" class="tag-demo ok" onclick="preencherDemo('liberado')">Aluno Válido</button>
                    <button type="button" class="tag-demo bloqueio" onclick="preencherDemo('bloqueado')">Inadimplente</button>
                    <button type="button" class="tag-demo nao-encontrado" onclick="preencherDemo('nao_encontrado')">Não Cadastrado</button>
                  </div>
                </div>
              </div>
            </div>

            <div class="dashboard-table-container">
              <div class="dashboard-table-header">
                <h3 class="dashboard-table-title">Histórico de Passagens em Tempo Real</h3>
                <button class="btn btn-secondary btn-sm" onclick="limparHistorico()">
                  <span class="material-symbols-rounded">delete_sweep</span> Limpar Tabela
                </button>
              </div>
              <table class="dashboard-table">
                <thead>
                  <tr>
                    <th>Horário</th>
                    <th>Aluno / Visitante</th>
                    <th>Documento / ID</th>
                    <th>Sentido</th>
                    <th>Plano / Vínculo</th>
                    <th>Status</th>
                    <th>Mensagem da Catraca</th>
                  </tr>
                </thead>
                <tbody id="tabelaLogsCatraca">
                  <tr id="linhaVazia">
                    <td colspan="7" class="tabela-sem-dados">Nenhum evento registrado ainda. As passagens aparecerão aqui automaticamente.</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </section>

          <!-- 3. SEÇÃO: FINANCEIRO -->
          <section id="view-financeiro" class="view-section">
            <div class="stats-grid">
              <div class="stat-card">
                <div class="stat-card-header">
                  <div class="stat-card-title">Receita Recebida (Mês)</div>
                  <div class="stat-card-icon success"><span class="material-symbols-rounded">payments</span></div>
                </div>
                <div class="stat-card-value" id="cardTotalRecebido">R$ 0,00</div>
                <div class="stat-card-change positive"><span class="material-symbols-rounded">trending_up</span><span id="cardQtdRecebidos">0 faturas</span></div>
              </div>
              <div class="stat-card">
                <div class="stat-card-header">
                  <div class="stat-card-title">Pendente a Receber</div>
                  <div class="stat-card-icon warning"><span class="material-symbols-rounded">hourglass_top</span></div>
                </div>
                <div class="stat-card-value" id="cardTotalPendente">R$ 0,00</div>
                <div class="stat-card-change"><span id="cardQtdPendentes">0 aguardando</span></div>
              </div>
              <div class="stat-card">
                <div class="stat-card-header">
                  <div class="stat-card-title">Inadimplência Crítica</div>
                  <div class="stat-card-icon error"><span class="material-symbols-rounded">warning</span></div>
                </div>
                <div class="stat-card-value" id="cardTotalAtrasado">R$ 0,00</div>
                <div class="stat-card-change negative"><span id="cardQtdAtrasados">0 vencidas</span></div>
              </div>
              <div class="stat-card">
                <div class="stat-card-header">
                  <div class="stat-card-title">Acessos Bloqueados na Catraca</div>
                  <div class="stat-card-icon primary"><span class="material-symbols-rounded">sensor_door</span></div>
                </div>
                <div class="stat-card-value" id="cardBloqueadosCatraca">0</div>
                <div class="stat-card-change"><span>Bloqueios por débito</span></div>
              </div>
            </div>

            <div class="financeiro-toolbar">
              <div class="filtros-status">
                <button class="btn-filtro active" data-status="todos" onclick="filtrarStatus('todos', this)">Todos (<span id="countTodos">0</span>)</button>
                <button class="btn-filtro" data-status="pago" onclick="filtrarStatus('pago', this)">Em Dia (<span id="countPago">0</span>)</button>
                <button class="btn-filtro" data-status="pendente" onclick="filtrarStatus('pendente', this)">Pendentes (<span id="countPendente">0</span>)</button>
                <button class="btn-filtro" data-status="atrasado" onclick="filtrarStatus('atrasado', this)">Atrasados (<span id="countAtrasado">0</span>)</button>
              </div>
              <div class="acoes-lote">
                <button class="btn btn-secondary btn-sm" onclick="exportarRelatorio()">
                  <span class="material-symbols-rounded">file_download</span> Exportar
                </button>
              </div>
            </div>

            <div class="dashboard-table-container">
              <div class="dashboard-table-header">
                <h3 class="dashboard-table-title">Controle Geral de Mensalidades</h3>
              </div>
              <table class="dashboard-table">
                <thead>
                  <tr>
                    <th>Fatura #</th>
                    <th>Aluno / Atleta</th>
                    <th>Plano Contratado</th>
                    <th>Vencimento</th>
                    <th>Valor</th>
                    <th>Status Pagamento</th>
                    <th>Catraca</th>
                    <th>Ações</th>
                  </tr>
                </thead>
                <tbody id="tabelaFaturas"></tbody>
              </table>
            </div>
          </section>

          <!-- 4. SEÇÃO: PLANOS -->
          <section id="view-planos" class="view-section">
            <div class="stats-grid">
              <div class="stat-card">
                <div class="stat-card-header">
                  <div class="stat-card-title">Total de Planos Ativos</div>
                  <div class="stat-card-icon primary"><span class="material-symbols-rounded">verified</span></div>
                </div>
                <div class="stat-card-value" id="cardPlanosAtivos">0</div>
                <div class="stat-card-change positive"><span>Disponíveis</span></div>
              </div>
              <div class="stat-card">
                <div class="stat-card-header">
                  <div class="stat-card-title">Atletas Matriculados</div>
                  <div class="stat-card-icon success"><span class="material-symbols-rounded">groups</span></div>
                </div>
                <div class="stat-card-value" id="cardTotalMatriculados">0</div>
                <div class="stat-card-change positive"><span>Contratos</span></div>
              </div>
              <div class="stat-card">
                <div class="stat-card-header">
                  <div class="stat-card-title">Receita Média por Aluno (Ticket)</div>
                  <div class="stat-card-icon info"><span class="material-symbols-rounded">trending_up</span></div>
                </div>
                <div class="stat-card-value" id="cardTicketMedio">R$ 0,00</div>
                <div class="stat-card-change"><span>Média ponderada</span></div>
              </div>
              <div class="stat-card">
                <div class="stat-card-header">
                  <div class="stat-card-title">Mais Popular</div>
                  <div class="stat-card-icon warning"><span class="material-symbols-rounded">star</span></div>
                </div>
                <div class="stat-card-value" id="cardPlanoPopular">-</div>
                <div class="stat-card-change positive"><span id="cardPopularQtd">0 assinaturas</span></div>
              </div>
            </div>

            <div class="dashboard-table-container">
              <div class="dashboard-table-header">
                <h3 class="dashboard-table-title">Catálogo de Planos & Regras de Acesso</h3>
              </div>
              <table class="dashboard-table">
                <thead>
                  <tr>
                    <th>Código</th>
                    <th>Nome do Pacote</th>
                    <th>Período / Vigência</th>
                    <th>Valor Base</th>
                    <th>Modalidades Permitidas</th>
                    <th>Alunos Ativos</th>
                    <th>Status</th>
                    <th>Ações</th>
                  </tr>
                </thead>
                <tbody id="tabelaPlanos"></tbody>
              </table>
            </div>
          </section>

          <!-- 5. SEÇÃO: ALUNOS & FICHAS DE TREINO -->
          <section id="view-alunos" class="view-section">
            <div class="stats-grid">
              <div class="stat-card">
                <div class="stat-card-header">
                  <div class="stat-card-title">Total de Alunos</div>
                  <div class="stat-card-icon primary"><span class="material-symbols-rounded">groups</span></div>
                </div>
                <div class="stat-card-value" id="cardTotalAlunos">0</div>
                <div class="stat-card-change positive"><span>Registros ativos</span></div>
              </div>
              <div class="stat-card">
                <div class="stat-card-header">
                  <div class="stat-card-title">Média de Peso</div>
                  <div class="stat-card-icon success"><span class="material-symbols-rounded">scale</span></div>
                </div>
                <div class="stat-card-value" id="cardMediaPeso">0.0 kg</div>
                <div class="stat-card-change"><span>Cálculo corporal</span></div>
              </div>
              <div class="stat-card">
                <div class="stat-card-header">
                  <div class="stat-card-title">Média de Altura</div>
                  <div class="stat-card-icon info"><span class="material-symbols-rounded">height</span></div>
                </div>
                <div class="stat-card-value" id="cardMediaAltura">0 cm</div>
                <div class="stat-card-change"><span>Estatística física</span></div>
              </div>
              <div class="stat-card">
                <div class="stat-card-header">
                  <div class="stat-card-title">Acesso na Catraca</div>
                  <div class="stat-card-icon warning"><span class="material-symbols-rounded">sensor_door</span></div>
                </div>
                <div class="stat-card-value" id="cardCatracaLiberados">0</div>
                <div class="stat-card-change positive"><span>Aptos para giro automático</span></div>
              </div>
            </div>

            <div class="dashboard-table-container">
              <div class="dashboard-table-header">
                <h3 class="dashboard-table-title">Lista de Alunos & Fichas de Treino</h3>
              </div>
              <table class="dashboard-table">
                <thead>
                  <tr>
                    <th>Aluno</th>
                    <th>CPF</th>
                    <th>Telefone</th>
                    <th>Cidade</th>
                    <th>Métricas</th>
                    <th>Modalidade / Treino</th>
                    <th>Catraca</th>
                    <th>Ações & Ficha</th>
                  </tr>
                </thead>
                <tbody id="tabelaAlunos"></tbody>
              </table>
            </div>
          </section>

          <!-- 6. SEÇÃO: EQUIPE -->
          <section id="view-equipe" class="view-section">
            <div class="stats-grid">
              <div class="stat-card">
                <div class="stat-card-header">
                  <div class="stat-card-title">Total de Colaboradores</div>
                  <div class="stat-card-icon primary"><span class="material-symbols-rounded">badge</span></div>
                </div>
                <div class="stat-card-value" id="cardTotalColaboradores">0</div>
                <div class="stat-card-change positive"><span>Quadro ativo</span></div>
              </div>
              <div class="stat-card">
                <div class="stat-card-header">
                  <div class="stat-card-title">Personal Trainers / Coaches</div>
                  <div class="stat-card-icon success"><span class="material-symbols-rounded">sports</span></div>
                </div>
                <div class="stat-card-value" id="cardTotalPersonais">0</div>
                <div class="stat-card-change positive"><span id="cardTotalAlunosAtendidos">0 atletas</span></div>
              </div>
              <div class="stat-card">
                <div class="stat-card-header">
                  <div class="stat-card-title">Folha Mensal Estimada</div>
                  <div class="stat-card-icon warning"><span class="material-symbols-rounded">payments</span></div>
                </div>
                <div class="stat-card-value" id="cardFolhaPagamento">R$ 0,00</div>
                <div class="stat-card-change"><span>Salários base</span></div>
              </div>
              <div class="stat-card">
                <div class="stat-card-header">
                  <div class="stat-card-title">Em Turno Agora</div>
                  <div class="stat-card-icon info"><span class="material-symbols-rounded">schedule</span></div>
                </div>
                <div class="stat-card-value" id="cardEmTurno">0</div>
                <div class="stat-card-change positive"><span>Presença ativa</span></div>
              </div>
            </div>

            <div class="equipe-toolbar">
              <div class="filtros-cargo">
                <button class="btn-filtro-cargo active" data-cargo="todos">Todos (<span id="countTodosCargos">0</span>)</button>
                <button class="btn-filtro-cargo" data-cargo="Personal Trainer">Personais (<span id="countPersonais">0</span>)</button>
                <button class="btn-filtro-cargo" data-cargo="Instrutor de Musculação">Instrutores (<span id="countInstrutores">0</span>)</button>
                <button class="btn-filtro-cargo" data-cargo="Recepção">Recepção (<span id="countRecepcao">0</span>)</button>
                <button class="btn-filtro-cargo" data-cargo="Manutenção & Cargas">Manutenção (<span id="countManutencao">0</span>)</button>
              </div>
            </div>

            <div class="dashboard-table-container">
              <div class="dashboard-table-header">
                <h3 class="dashboard-table-title">Quadro de Profissionais</h3>
              </div>
              <table class="dashboard-table">
                <thead>
                  <tr>
                    <th>Profissional</th>
                    <th>Cargo / Função</th>
                    <th>CREF / Registo</th>
                    <th>Turno de Trabalho</th>
                    <th>Contato</th>
                    <th>Alunos Atendidos</th>
                    <th>Status</th>
                    <th>Ações</th>
                  </tr>
                </thead>
                <tbody id="tabelaEquipe"></tbody>
              </table>
            </div>
          </section>

          <!-- 7. SEÇÃO: EQUIPAMENTOS -->
          <section id="view-equipamentos" class="view-section">
            <div class="stats-grid">
              <div class="stat-card">
                <div class="stat-card-header">
                  <div class="stat-card-title">Total de Maquinários / Itens</div>
                  <div class="stat-card-icon primary"><span class="material-symbols-rounded">precision_manufacturing</span></div>
                </div>
                <div class="stat-card-value" id="cardTotalItens">0</div>
                <div class="stat-card-change positive"><span id="cardQtdTipos">0 tipos</span></div>
              </div>
              <div class="stat-card">
                <div class="stat-card-header">
                  <div class="stat-card-title">Operação Normal</div>
                  <div class="stat-card-icon success"><span class="material-symbols-rounded">check_circle</span></div>
                </div>
                <div class="stat-card-value" id="cardOperacionais">0</div>
                <div class="stat-card-change positive"><span>100% aptos</span></div>
              </div>
              <div class="stat-card">
                <div class="stat-card-header">
                  <div class="stat-card-title">Revisão Pendente / Próxima</div>
                  <div class="stat-card-icon warning"><span class="material-symbols-rounded">build_circle</span></div>
                </div>
                <div class="stat-card-value" id="cardRevisaoProxima">0</div>
                <div class="stat-card-change"><span>Lubrificação ou ajustes</span></div>
              </div>
              <div class="stat-card">
                <div class="stat-card-header">
                  <div class="stat-card-title">Interditados / Manutenção</div>
                  <div class="stat-card-icon error"><span class="material-symbols-rounded">report</span></div>
                </div>
                <div class="stat-card-value" id="cardInterditados">0</div>
                <div class="stat-card-change negative"><span>Bloqueados temporariamente</span></div>
              </div>
            </div>

            <div class="equipamentos-toolbar">
              <div class="filtros-setor">
                <button class="btn-filtro-setor active" data-setor="todos">Todos (<span id="countTodosSetores">0</span>)</button>
                <button class="btn-filtro-setor" data-setor="Powerlifting">Powerlifting (<span id="countPowerlifting">0</span>)</button>
                <button class="btn-filtro-setor" data-setor="Pesos Livres">Pesos Livres (<span id="countPesosLivres">0</span>)</button>
                <button class="btn-filtro-setor" data-setor="Musculação Pesada">Musculação Pesada (<span id="countMusculacao">0</span>)</button>
                <button class="btn-filtro-setor" data-setor="Cardio & Resistência">Cardio (<span id="countCardio">0</span>)</button>
              </div>
            </div>

            <div class="dashboard-table-container">
              <div class="dashboard-table-header">
                <h3 class="dashboard-table-title">Parque de Aparelhos, Barras e Estruturas</h3>
              </div>
              <table class="dashboard-table">
                <thead>
                  <tr>
                    <th>Código</th>
                    <th>Equipamento / Descrição</th>
                    <th>Setor</th>
                    <th>Quantidade</th>
                    <th>Última Lubrificação</th>
                    <th>Próxima Revisão</th>
                    <th>Estado</th>
                    <th>Ações</th>
                  </tr>
                </thead>
                <tbody id="tabelaEquipamentos"></tbody>
              </table>
            </div>
          </section>

          <!-- 8. SEÇÃO: CONFIGURAÇÕES -->
          <section id="view-configuracoes" class="view-section">
            <div class="config-layout">
              <div class="config-menu-lateral">
                <button class="config-tab-btn active" data-tab="tab-unidade">
                  <span class="material-symbols-rounded">domain</span>
                  <span>Dados da Unidade</span>
                </button>
                <button class="config-tab-btn" data-tab="tab-catraca">
                  <span class="material-symbols-rounded">router</span>
                  <span>Catraca & Hardware</span>
                </button>
                <button class="config-tab-btn" data-tab="tab-financeiro">
                  <span class="material-symbols-rounded">account_balance</span>
                  <span>Parâmetros Financeiros</span>
                </button>
                <button class="config-tab-btn" data-tab="tab-notificacoes">
                  <span class="material-symbols-rounded">notifications_active</span>
                  <span>Notificações & Lembretes</span>
                </button>
                <button class="config-tab-btn" data-tab="tab-seguranca">
                  <span class="material-symbols-rounded">security</span>
                  <span>Segurança & Backup</span>
                </button>
              </div>

              <div class="config-paineis">
                <!-- Tab Unidade -->
                <div class="config-panel active" id="tab-unidade">
                  <div class="panel-header">
                    <h3>Dados Institucionais do Ginásio</h3>
                    <p>Informações de registro da academia e capacidade operacional máxima.</p>
                  </div>
                  <div class="form-grid-cfg">
                    <div class="form-group-cfg full">
                      <label>Razão Social / Nome da Empresa</label>
                      <input type="text" id="cfgRazaoSocial" value="Rustic Iron Centro de Treinamento Pesado LTDA" class="input-cfg" />
                    </div>
                    <div class="form-group-cfg">
                      <label>Nome Fantasia</label>
                      <input type="text" id="cfgNomeFantasia" value="Rustic Iron Gym" class="input-cfg" />
                    </div>
                    <div class="form-group-cfg">
                      <label>CNPJ</label>
                      <input type="text" id="cfgCnpj" value="48.192.831/0001-92" class="input-cfg" />
                    </div>
                    <div class="form-group-cfg">
                      <label>Telefone / WhatsApp Comercial</label>
                      <input type="text" id="cfgTelefone" value="(79) 3211-9000" class="input-cfg" />
                    </div>
                    <div class="form-group-cfg">
                      <label>E-mail da Administração</label>
                      <input type="email" id="cfgEmail" value="contato@rusticiron.com" class="input-cfg" />
                    </div>
                    <div class="form-group-cfg full">
                      <label>Endereço Completo</label>
                      <input type="text" id="cfgEndereco" value="Av. Heráclito Rollemberg, 450 - Aracaju/SE" class="input-cfg" />
                    </div>
                    <div class="form-group-cfg">
                      <label>Capacidade Máxima Simultânea</label>
                      <input type="number" id="cfgCapacidade" value="120" class="input-cfg" />
                    </div>
                    <div class="form-group-cfg">
                      <label>Horário de Funcionamento</label>
                      <input type="text" id="cfgHorario" value="Seg a Sex: 05h às 23h | Sáb: 08h às 18h" class="input-cfg" />
                    </div>
                  </div>
                </div>

                <!-- Tab Catraca Hardware -->
                <div class="config-panel" id="tab-catraca">
                  <div class="panel-header">
                    <h3>Integração com Catracas & Torniquetes</h3>
                    <p>Configuração de comunicação local com controladoras Henry, TopData ou Control iD.</p>
                  </div>
                  <div class="form-grid-cfg">
                    <div class="form-group-cfg">
                      <label>Protocolo do Equipamento</label>
                      <select id="cfgProtocoloCatraca" class="select-cfg">
                        <option value="henry">Henry (Prisma / Lumina / Primme)</option>
                        <option value="topdata">TopData (Inner Rep Plus / Revolution)</option>
                        <option value="controlid" selected>Control iD (iDFit / iDBlock)</option>
                        <option value="tcp_custom">Comunicação TCP/IP Socket Direta</option>
                      </select>
                    </div>
                    <div class="form-group-cfg">
                      <label>Endereço IP da Catraca Principal</label>
                      <input type="text" id="cfgIpCatraca" value="192.168.1.200" class="input-cfg" />
                    </div>
                    <div class="form-group-cfg">
                      <label>Porta de Comunicação</label>
                      <input type="number" id="cfgPortaCatraca" value="3000" class="input-cfg" />
                    </div>
                    <div class="form-group-cfg">
                      <label>Timeout para Giro (Segundos)</label>
                      <input type="number" id="cfgTimeoutGiro" value="6" class="input-cfg" />
                    </div>
                    <div class="form-group-cfg">
                      <label>Tolerância de Atraso de Pagamento</label>
                      <select id="cfgToleranciaAtraso" class="select-cfg">
                        <option value="0">Bloquear no dia do vencimento (0 dias)</option>
                        <option value="3">3 dias de carência</option>
                        <option value="5" selected>5 dias de carência (Recomendado)</option>
                        <option value="7">7 dias de carência</option>
                      </select>
                    </div>
                    <div class="form-group-cfg">
                      <label>Aviso Sonoro no Torniquete</label>
                      <select id="cfgBeep" class="select-cfg">
                        <option value="1" selected>Habilitado (Beep único)</option>
                        <option value="0">Silencioso</option>
                      </select>
                    </div>
                    <div class="form-group-cfg full">
                      <label class="toggle-container">
                        <input type="checkbox" id="cfgLiberarSaidaLivre" checked />
                        <div>
                          <strong>Giro de Saída Livre (Sem Bloqueio de Mensalidade)</strong>
                          <span>Permite que alunos com débitos saiam normalmente sem travar a catraca de saída.</span>
                        </div>
                      </label>
                    </div>
                  </div>
                </div>

                <!-- Tab Financeiro Config -->
                <div class="config-panel" id="tab-financeiro">
                  <div class="panel-header">
                    <h3>Recebimentos, PIX & Faturamento</h3>
                    <p>Defina as chaves para emissão de cobranças automáticas e parâmetros de juros.</p>
                  </div>
                  <div class="form-grid-cfg">
                    <div class="form-group-cfg">
                      <label>Tipo de Chave PIX</label>
                      <select id="cfgTipoPix" class="select-cfg">
                        <option value="cnpj" selected>CNPJ</option>
                        <option value="email">E-mail</option>
                        <option value="telefone">Telefone</option>
                        <option value="aleatoria">Chave Aleatória (EVP)</option>
                      </select>
                    </div>
                    <div class="form-group-cfg">
                      <label>Chave PIX Cadastrada</label>
                      <input type="text" id="cfgChavePix" value="48192831000192" class="input-cfg" />
                    </div>
                    <div class="form-group-cfg">
                      <label>Instituição Financeira / Banco</label>
                      <input type="text" id="cfgBancoPix" value="Banco Cora / Itaú Empresas" class="input-cfg" />
                    </div>
                    <div class="form-group-cfg">
                      <label>Dias de Antecedência para Cobrança</label>
                      <input type="number" id="cfgDiasAntecedencia" value="5" class="input-cfg" />
                    </div>
                    <div class="form-group-cfg">
                      <label>Multa por Atraso (%)</label>
                      <input type="number" step="0.1" id="cfgMulta" value="2.0" class="input-cfg" />
                    </div>
                    <div class="form-group-cfg">
                      <label>Juros Diários de Mora (%)</label>
                      <input type="number" step="0.01" id="cfgJuros" value="0.03" class="input-cfg" />
                    </div>
                  </div>
                </div>

                <!-- Tab Notificações -->
                <div class="config-panel" id="tab-notificacoes">
                  <div class="panel-header">
                    <h3>Alertas & Automação de Mensagens</h3>
                    <p>Configuração dos disparos de cobrança, boas-vindas e avisos da unidade.</p>
                  </div>
                  <div class="form-grid-cfg full">
                    <label class="toggle-container">
                      <input type="checkbox" id="cfgNotifWhatsapp" checked />
                      <div>
                        <strong>Disparar Lembrete via WhatsApp</strong>
                        <span>Envia mensagem automática com código PIX 3 dias antes do vencimento do plano.</span>
                      </div>
                    </label>
                  </div>
                  <div class="form-grid-cfg full">
                    <label class="toggle-container">
                      <input type="checkbox" id="cfgNotifEmail" checked />
                      <div>
                        <strong>Recibo por E-mail</strong>
                        <span>Dispara comprovante digital de quitação assim que a mensalidade for paga.</span>
                      </div>
                    </label>
                  </div>
                  <div class="form-grid-cfg full">
                    <label class="toggle-container">
                      <input type="checkbox" id="cfgNotifManutencao" checked />
                      <div>
                        <strong>Alerta de Manutenção Preventiva da Equipe</strong>
                        <span>Avisa os gestores quando um equipamento ultrapassar a data de lubrificação programada.</span>
                      </div>
                    </label>
                  </div>
                </div>

                <!-- Tab Segurança -->
                <div class="config-panel" id="tab-seguranca">
                  <div class="panel-header">
                    <h3>Manutenção da Base & Exportação</h3>
                    <p>Controles de dados e opções de backup para segurança operacional.</p>
                  </div>
                  <div class="seguranca-acoes">
                    <div class="seguranca-item">
                      <div>
                        <strong>Exportar Base de Alunos & Mensalidades (JSON/CSV)</strong>
                        <p>Gere uma cópia completa dos registros locais para arquivo seguro ou auditoria.</p>
                      </div>
                      <button type="button" class="btn btn-secondary" onclick="exportarBackup()">
                        <span class="material-symbols-rounded">cloud_download</span> Exportar Dados
                      </button>
                    </div>

                    <div class="seguranca-item perigo">
                      <div>
                        <strong>Restaurar Padrões de Fábrica</strong>
                        <p>Limpa os parâmetros de configuração mantendo apenas os cadastros de alunos e planos.</p>
                      </div>
                      <button type="button" class="btn btn-danger" onclick="restaurarPadroes()">
                        <span class="material-symbols-rounded">restart_alt</span> Restaurar Parâmetros
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </section>
        </div>
      </main>
    </div>

    <!-- ==================== MODAIS UNIFICADOS ==================== -->

    <!-- 1. MODAL ALUNO (COM TODAS AS 53 MODALIDADES COMPLETAS) -->
    <div class="modal-overlay" id="modalAluno">
      <div class="modal-card modal-aluno-container">
        <div class="modal-header">
          <h3 id="modalTituloAluno">Cadastrar Aluno</h3>
          <button class="modal-close" onclick="fecharModalAluno()">&times;</button>
        </div>

        <form id="formAluno" onsubmit="salvarAluno(event)">
          <input type="hidden" id="alunoIdEdit" value="" />

          <div class="modal-body form-aluno-corpo">
            <div class="form-row full">
              <input type="text" id="nome_completo" name="nome_completo" placeholder="Nome completo" required class="input-form-aluno" />
            </div>

            <div class="form-row split-2">
              <input type="date" id="data_nasc" name="data_nasc" required class="input-form-aluno" />
              <input type="text" id="cpf" name="cpf" placeholder="CPF (000.000.000-00)" required maxlength="14" class="input-form-aluno" />
            </div>

            <div class="form-row split-2">
              <input type="tel" id="telefone" name="telefone" placeholder="Telefone" required class="input-form-aluno" />
              <input type="tel" id="telefone_emergencia" name="telefone_emergencia" placeholder="Telefone de emergência" class="input-form-aluno" />
            </div>

            <div class="form-row full">
              <input type="text" id="endereco" name="endereco" placeholder="Endereço" class="input-form-aluno" />
            </div>

            <div class="form-row full">
              <input type="text" id="cidade" name="cidade" placeholder="Cidade" class="input-form-aluno" />
            </div>

            <div class="form-row split-2">
              <input type="number" step="0.1" id="peso" name="peso" placeholder="Peso (kg)" required class="input-form-aluno" />
              <input type="number" id="altura" name="altura" placeholder="Altura (cm)" required class="input-form-aluno" />
            </div>

            <div class="form-row full">
              <select class="full-width select-form-aluno" id="tipo_treino" name="tipo_treino" required onchange="verificarOutraModalidade(this.value)">
                <option value="" selected disabled>Selecione o objetivo ou modalidade</option>
                <option value="forca">Força</option>
                <option value="hipertrofia">Hipertrofia</option>
                <option value="ganho_massa">Ganho de massa muscular</option>
                <option value="emagrecimento">Emagrecimento</option>
                <option value="perda_gordura">Perda de gordura</option>
                <option value="definicao">Definição muscular</option>
                <option value="resistencia_muscular">Resistência muscular</option>
                <option value="resistencia_fisica">Resistência física</option>
                <option value="condicionamento">Condicionamento físico</option>
                <option value="cardiorrespiratorio">Condicionamento cardiorrespiratório</option>
                <option value="potencia">Potência</option>
                <option value="explosao">Explosão muscular</option>
                <option value="velocidade">Velocidade</option>
                <option value="agilidade">Agilidade</option>
                <option value="coordenacao">Coordenação motora</option>
                <option value="equilibrio">Equilíbrio</option>
                <option value="mobilidade">Mobilidade</option>
                <option value="flexibilidade">Flexibilidade</option>
                <option value="amplitude_movimento">Amplitude de movimento</option>
                <option value="postura">Correção postural</option>
                <option value="estabilidade">Estabilidade corporal</option>
                <option value="core">Fortalecimento do core</option>
                <option value="saude">Saúde e qualidade de vida</option>
                <option value="longevidade">Longevidade</option>
                <option value="bem_estar">Bem-estar</option>
                <option value="qualidade_vida">Qualidade de vida</option>
                <option value="preparacao_fisica">Preparação física</option>
                <option value="performance">Performance esportiva</option>
                <option value="competicao">Preparação para competição</option>
                <option value="reabilitacao">Reabilitação</option>
                <option value="prevencao_lesoes">Prevenção de lesões</option>
                <option value="retorno_esporte">Retorno ao esporte</option>
                <option value="recuperacao">Recuperação física</option>
                <option value="musculacao">Musculação</option>
                <option value="powerlifting">Powerlifting</option>
                <option value="levantamento_olimpico">Levantamento olímpico</option>
                <option value="fisiculturismo">Fisiculturismo</option>
                <option value="cross_training">Cross Training</option>
                <option value="funcional">Treinamento funcional</option>
                <option value="calistenia">Calistenia</option>
                <option value="corrida">Corrida</option>
                <option value="ciclismo">Ciclismo</option>
                <option value="natacao">Natação</option>
                <option value="luta">Luta</option>
                <option value="jiu_jitsu">Jiu-Jitsu</option>
                <option value="boxe">Boxe</option>
                <option value="muay_thai">Muay Thai</option>
                <option value="mma">MMA</option>
                <option value="futebol">Futebol</option>
                <option value="basquete">Basquete</option>
                <option value="volei">Vôlei</option>
                <option value="handebol">Handebol</option>
                <option value="tenis">Tênis</option>
                <option value="outro">Outro</option>
              </select>
            </div>

            <div class="form-row full">
              <input type="text" id="outra_modalidade" name="outra_modalidade" placeholder="Outra modalidade (se aplicável)" class="input-form-aluno" />
            </div>

            <div class="form-row split-2">
              <input type="email" id="email" name="email" placeholder="E-mail" required class="input-form-aluno" autocomplete="email" />
              <input type="password" id="senha" name="senha" placeholder="Senha" class="input-form-aluno" autocomplete="new-password" />
            </div>

            <div class="form-row full">
              <button type="submit" class="btn-cadastrar-aluno" id="btnSubmitAluno">
                <span class="material-symbols-rounded">person_add</span>
                <span id="txtBtnSubmit">Cadastrar Aluno</span>
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- 2. NOVO MODAL: FICHA DE TREINO (ORDENAÇÃO DE MÚSCULOS E EXERCÍCIOS) -->
    <div class="modal-overlay" id="modalFichaTreino">
      <div class="modal-card modal-ficha-largura">
        <div class="modal-header">
          <div>
            <h3 id="fichaModalNomeAluno">Ficha de Treino & Prescrição</h3>
            <span class="ficha-subtitulo-aluno" id="fichaModalSubInfo">Carregando dados...</span>
          </div>
          <button class="modal-close" onclick="fecharModalFicha()">&times;</button>
        </div>

        <div class="modal-body ficha-modal-body">
          <!-- Topo: Configuração de Modalidade e Dia da Semana -->
          <div class="ficha-topo-config">
            <div class="form-group-ficha">
              <label for="fichaModalidadeSelect">Modalidade / Objetivo</label>
              <select id="fichaModalidadeSelect" class="select-ficha" onchange="atualizarModalidadeFicha(this.value)">
                <!-- Preenchido dinamicamente via JS com as 53 modalidades -->
              </select>
            </div>
            <div class="form-group-ficha">
              <label for="fichaDiaSemana">Dia da Semana</label>
              <select id="fichaDiaSemana" class="select-ficha" onchange="selecionarDiaSemanaTreino(this.value)">
                <option value="segunda">Segunda-feira</option>
                <option value="terca">Terça-feira</option>
                <option value="quarta">Quarta-feira</option>
                <option value="quinta">Quinta-feira</option>
                <option value="sexta">Sexta-feira</option>
                <option value="sabado">Sábado</option>
              </select>
            </div>
          </div>

          <!-- Caixas de Seleção de Músculos -->
          <div class="ficha-secao-musculos">
            <span class="ficha-label-destaque">1. Selecione os Grupos Musculares para este dia:</span>
            <div class="grid-checkboxes-musculos" id="containerCheckboxesMusculos">
              <!-- Gerado dinamicamente via JS -->
            </div>
          </div>

          <!-- Lista de Ordem com a Alça de 3 Tracinhos (Drag Handle) -->
          <div class="ficha-secao-ordem">
            <div class="ficha-ordem-header">
              <span class="ficha-label-destaque">2. Ordem de Execução no Treino:</span>
              <span class="ficha-dica-ordem">Arraste pelos 3 tracinhos ou use as setas para definir qual músculo será treinado 1º, 2º ou 3º</span>
            </div>
            <div class="lista-musculos-ordenaveis" id="listaMusculosOrdem">
              <div class="ficha-aviso-vazio">Nenhum músculo selecionado para este dia ainda. Marque as caixinhas acima.</div>
            </div>
          </div>

          <!-- Exercícios por Músculo -->
          <div class="ficha-secao-exercicios">
            <div class="ficha-exercicios-header">
              <span class="ficha-label-destaque">3. Exercícios Prescritos:</span>
            </div>
            <div id="containerExerciciosPorMusculo" class="container-exercicios-blocos">
              <!-- Blocos gerados de acordo com a ordem definida -->
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" onclick="imprimirFichaAtual()">
            <span class="material-symbols-rounded">print</span> Imprimir Ficha
          </button>
          <button type="button" class="btn btn-secondary" onclick="fecharModalFicha()">Cancelar</button>
          <button type="button" class="btn btn-primary" onclick="salvarFichaTreinoAtual()">
            <span class="material-symbols-rounded">save</span> Salvar Ficha de Treino
          </button>
        </div>
      </div>
    </div>

    <!-- ÁREA EXCLUSIVA DE IMPRESSÃO (Oculta na tela, ativada no Ctrl+P / window.print) -->
    <div id="areaImpressaoFicha" class="area-impressao-ficha">
      <div class="print-ficha-header">
        <div class="print-logo-bloco">
          <h1>RUSTIC IRON GYM</h1>
          <p>Centro de Treinamento Pesado & Performance</p>
        </div>
        <div class="print-aluno-bloco">
          <h2 id="printNomeAluno">Nome do Aluno</h2>
          <p id="printDadosFisicos">Métricas: 00 kg | 00 cm | Modalidade: Força</p>
          <p id="printDataEmissao">Emitido em: 00/00/0000</p>
        </div>
      </div>
      <hr class="print-divisor" />
      <div id="printCorpoTreinos" class="print-corpo-treinos">
        <!-- Renderizado dinamicamente -->
      </div>
      <div class="print-rodape">
        <p>Rustic Iron - Força Bruta & Disciplina • Mantenha o foco e hidrate-se durante o treino.</p>
      </div>
    </div>

    <!-- 3. MODAL PAGAMENTO (FINANCEIRO) -->
    <div class="modal-overlay" id="modalPagamento">
      <div class="modal-card">
        <div class="modal-header">
          <h3>Confirmar Recebimento de Mensalidade</h3>
          <button class="modal-close" onclick="fecharModalPagamento()">&times;</button>
        </div>
        <div class="modal-body">
          <p id="modalAlunoNome" class="modal-destaque">-</p>
          <p id="modalPlanoValor" class="modal-sub">-</p>

          <div class="form-group-fin">
            <label for="metodoPagamento">Forma de Pagamento</label>
            <select id="metodoPagamento" class="select-fin">
              <option value="PIX">PIX (Instantâneo)</option>
              <option value="Cartão de Crédito">Cartão de Crédito</option>
              <option value="Cartão de Débito">Cartão de Débito</option>
              <option value="Dinheiro">Dinheiro Físico</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" onclick="fecharModalPagamento()">Cancelar</button>
          <button type="button" class="btn btn-primary" onclick="confirmarBaixaPagamento()">
            <span class="material-symbols-rounded">check</span> Confirmar e Liberar Acesso
          </button>
        </div>
      </div>
    </div>

    <!-- 4. MODAL NOVA COBRANÇA (FINANCEIRO) -->
    <div class="modal-overlay" id="modalNovaCobranca">
      <div class="modal-card">
        <div class="modal-header">
          <h3>Lançar Nova Fatura / Mensalidade</h3>
          <button class="modal-close" onclick="fecharModalNovaCobranca()">&times;</button>
        </div>
        <form onsubmit="salvarNovaCobranca(event)">
          <div class="modal-body">
            <div class="form-group-fin">
              <label for="novoAluno">Nome do Aluno</label>
              <input type="text" id="novoAluno" required placeholder="Nome completo" class="input-fin" />
            </div>
            <div class="form-group-fin">
              <label for="novoCpf">CPF do Aluno</label>
              <input type="text" id="novoCpf" required placeholder="000.000.000-00" class="input-fin" />
            </div>
            <div class="form-group-fin">
              <label for="novoPlano">Plano</label>
              <select id="novoPlano" class="select-fin" onchange="atualizarValorPlano(this.value)">
                <option value="Iron Mensal Livre|130">Iron Mensal Livre - R$ 130,00</option>
                <option value="Full Combat & Iron|190">Full Combat & Iron - R$ 190,00</option>
                <option value="Plano Trimestral|350">Plano Trimestral - R$ 350,00</option>
                <option value="Anual Pesado|1150">Anual Pesado - R$ 1.150,00</option>
              </select>
            </div>
            <div class="form-group-fin">
              <label for="novoVencimento">Data de Vencimento</label>
              <input type="date" id="novoVencimento" required class="input-fin" />
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="fecharModalNovaCobranca()">Cancelar</button>
            <button type="submit" class="btn btn-primary">Lançar Fatura</button>
          </div>
        </form>
      </div>
    </div>

    <!-- 5. MODAL PLANO -->
    <div class="modal-overlay" id="modalPlano">
      <div class="modal-card modal-plano-largura">
        <div class="modal-header">
          <h3 id="modalPlanoTitulo">Configurar Plano</h3>
          <button class="modal-close" onclick="fecharModalPlano()">&times;</button>
        </div>
        <form onsubmit="salvarPlano(event)">
          <div class="modal-body">
            <input type="hidden" id="planoIdEdit" value="" />

            <div class="form-grid-plano">
              <div class="form-group-plan full">
                <label for="planoNome">Nome Comercial do Plano</label>
                <input type="text" id="planoNome" required placeholder="Ex: Iron Powerlifting Mensal" class="input-plan" />
              </div>

              <div class="form-group-plan">
                <label for="planoValor">Valor (R$)</label>
                <input type="number" id="planoValor" step="0.01" min="0" required placeholder="130.00" class="input-plan" />
              </div>

              <div class="form-group-plan">
                <label for="planoPeriodo">Duração / Vigência</label>
                <select id="planoPeriodo" class="select-plan">
                  <option value="Diária (1 dia)">Diária (1 dia)</option>
                  <option value="Mensal (30 dias)">Mensal (30 dias)</option>
                  <option value="Bimestral (60 dias)">Bimestral (60 dias)</option>
                  <option value="Trimestral (90 dias)">Trimestral (90 dias)</option>
                  <option value="Semestral (180 dias)">Semestral (180 dias)</option>
                  <option value="Anual (365 dias)">Anual (365 dias)</option>
                </select>
              </div>

              <div class="form-group-plan full">
                <label>Modalidades Incluídas na Catraca</label>
                <div class="checkbox-modalidades-grid">
                  <label class="check-container"><input type="checkbox" name="modalidade" value="Musculação" /><span>Musculação Pesada</span></label>
                  <label class="check-container"><input type="checkbox" name="modalidade" value="Powerlifting" /><span>Powerlifting</span></label>
                  <label class="check-container"><input type="checkbox" name="modalidade" value="Cross Training" /><span>Cross Training</span></label>
                  <label class="check-container"><input type="checkbox" name="modalidade" value="Boxe" /><span>Boxe Tradicional</span></label>
                  <label class="check-container"><input type="checkbox" name="modalidade" value="Jiu-Jitsu" /><span>Jiu-Jitsu</span></label>
                  <label class="check-container"><input type="checkbox" name="modalidade" value="Funcional" /><span>Funcional & Core</span></label>
                </div>
              </div>

              <div class="form-group-plan full">
                <label for="planoDescricao">Descrição ou Benefícios Adicionais</label>
                <input type="text" id="planoDescricao" placeholder="Ex: Livre acesso ao mezanino de levantamento e halteres" class="input-plan" />
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="fecharModalPlano()">Cancelar</button>
            <button type="submit" class="btn btn-primary" id="btnSalvarPlano">
              <span class="material-symbols-rounded">save</span> Salvar Alterações
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- 6. MODAL EQUIPE -->
    <div class="modal-overlay" id="modalEquipe">
      <div class="modal-card modal-equipe-largura">
        <div class="modal-header">
          <h3 id="modalTituloEquipe">Cadastrar Colaborador</h3>
          <button class="modal-close" id="btnFecharModalEquipe" onclick="document.getElementById('modalEquipe').classList.remove('active')">&times;</button>
        </div>

        <form id="formEquipe" onsubmit="salvarColaborador(event)">
          <input type="hidden" id="colaboradorIdEdit" value="" />

          <div class="modal-body form-equipe-grid">
            <div class="form-row-eq full">
              <label for="eqNome">Nome Completo</label>
              <input type="text" id="eqNome" required placeholder="Nome do colaborador" class="input-eq" />
            </div>

            <div class="form-row-eq">
              <label for="eqCpf">CPF</label>
              <input type="text" id="eqCpf" required placeholder="000.000.000-00" maxlength="14" class="input-eq" />
            </div>

            <div class="form-row-eq">
              <label for="eqCargo">Cargo / Função</label>
              <select id="eqCargo" required class="select-eq" onchange="verificarExigenciaCref(this.value)">
                <option value="Personal Trainer">Personal Trainer</option>
                <option value="Instrutor de Musculação">Instrutor de Musculação</option>
                <option value="Coach de Powerlifting">Coach de Powerlifting</option>
                <option value="Instrutor de Lutas">Instrutor de Lutas</option>
                <option value="Recepção">Recepção</option>
                <option value="Gerente Operacional">Gerente Operacional</option>
                <option value="Manutenção & Cargas">Manutenção & Cargas</option>
                <option value="Fisioterapeuta / Avaliador">Fisioterapeuta / Avaliador</option>
              </select>
            </div>

            <div class="form-row-eq">
              <label for="eqCref">Registro CREF (se aplicável)</label>
              <input type="text" id="eqCref" placeholder="000000-G/UF" class="input-eq" />
            </div>

            <div class="form-row-eq">
              <label for="eqTurno">Turno de Atuação</label>
              <select id="eqTurno" required class="select-eq">
                <option value="Manhã (06h às 14h)">Manhã (06h às 14h)</option>
                <option value="Tarde (14h às 22h)">Tarde (14h às 22h)</option>
                <option value="Noite / Fechamento (16h às 23h)">Noite / Fechamento (16h às 23h)</option>
                <option value="Integral / Comercial (08h às 18h)">Integral / Comercial (08h às 18h)</option>
                <option value="Horista / Agendamento">Horista / Agendamento</option>
              </select>
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
              <label for="eqSalario">Salário Base (R$)</label>
              <input type="number" step="0.01" min="0" id="eqSalario" placeholder="2500.00" class="input-eq" />
            </div>

            <div class="form-row-eq">
              <label for="eqAlunos">Alunos Vinculados</label>
              <input type="number" min="0" id="eqAlunos" value="0" class="input-eq" />
            </div>

            <div class="form-row-eq full">
              <label for="eqEspecialidade">Especialidade / Foco Técnico</label>
              <input type="text" id="eqEspecialidade" placeholder="Ex: Levantamento terra, agachamento livre, reabilitação articular" class="input-eq" />
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('modalEquipe').classList.remove('active')">Cancelar</button>
            <button type="submit" class="btn btn-primary" id="btnSalvarEquipe">
              <span class="material-symbols-rounded">save</span> Salvar Colaborador
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- 7. MODAL EQUIPAMENTO -->
    <div class="modal-overlay" id="modalEquipamento">
      <div class="modal-card modal-equipamento-largura">
        <div class="modal-header">
          <h3 id="modalTituloEquipamento">Registrar Equipamento</h3>
          <button class="modal-close" id="btnFecharModalEquipamento" onclick="document.getElementById('modalEquipamento').classList.remove('active')">&times;</button>
        </div>

        <form id="formEquipamento" onsubmit="salvarEquipamento(event)">
          <input type="hidden" id="equipamentoIdEdit" value="" />

          <div class="modal-body form-equip-grid">
            <div class="form-row-eq full">
              <label for="eqNomeAparelho">Nome do Equipamento / Estrutura</label>
              <input type="text" id="eqNomeAparelho" required placeholder="Ex: Gaiola Monolift / Barra Olímpica 20kg" class="input-eq" />
            </div>

            <div class="form-row-eq">
              <label for="eqSetor">Setor / Área de Treino</label>
              <select id="eqSetor" required class="select-eq">
                <option value="Powerlifting">Powerlifting</option>
                <option value="Pesos Livres">Pesos Livres</option>
                <option value="Musculação Pesada">Musculação Pesada</option>
                <option value="Cardio & Resistência">Cardio & Resistência</option>
                <option value="Tatame / Artes Marciais">Tatame / Artes Marciais</option>
                <option value="Cross Training / Funcional">Cross Training / Funcional</option>
              </select>
            </div>

            <div class="form-row-eq">
              <label for="eqQuantidade">Quantidade de Unidades</label>
              <input type="number" min="1" id="eqQuantidade" required value="1" class="input-eq" />
            </div>

            <div class="form-row-eq">
              <label for="eqUltimaManutencao">Última Revisão / Lubrificação</label>
              <input type="date" id="eqUltimaManutencao" required class="input-eq" />
            </div>

            <div class="form-row-eq">
              <label for="eqProximaRevisao">Próxima Revisão Programada</label>
              <input type="date" id="eqProximaRevisao" required class="input-eq" />
            </div>

            <div class="form-row-eq">
              <label for="eqEstado">Estado Operacional</label>
              <select id="eqEstado" required class="select-eq">
                <option value="Excelente">Excelente (Operacional)</option>
                <option value="Revisão Próxima">Revisão Próxima</option>
                <option value="Em Manutenção">Em Manutenção (Interditado)</option>
                <option value="Danificado">Danificado / Descartar</option>
              </select>
            </div>

            <div class="form-row-eq">
              <label for="eqFabricante">Fabricante / Fornecedor</label>
              <input type="text" id="eqFabricante" placeholder="Ex: Hammer Strength / Eleiko / Produção Própria" class="input-eq" />
            </div>

            <div class="form-row-eq full">
              <label for="eqObservacoes">Observações Técnicas / Guia de Lubrificação</label>
              <input type="text" id="eqObservacoes" placeholder="Ex: Utilizar vaselina industrial nas guias; verificar roldanas a cada 30 dias" class="input-eq" />
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('modalEquipamento').classList.remove('active')">Cancelar</button>
            <button type="submit" class="btn btn-primary" id="btnSalvarEquipamento">
              <span class="material-symbols-rounded">save</span> Salvar Registro
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- TOAST DE NOTIFICAÇÃO -->
    <div class="toast-aviso" id="toastAviso">Ação concluída com sucesso!</div>

    <!-- SCRIPTS CORE -->
    <script src="../js/empresa/dashboard.js"></script>

    <!-- NAVEGAÇÃO SPA E CONTROLADORES GLOBAIS -->
    <script>
      const titlesMap = {
        'visao-geral': 'Visão Geral',
        'catraca': 'Catraca & Controle de Acesso',
        'financeiro': 'Gestão Financeira & Cobranças',
        'planos': 'Planos & Mensalidades',
        'alunos': 'Gestão de Alunos & Fichas de Treino',
        'equipe': 'Gestão da Equipe & Treinadores',
        'equipamentos': 'Parque de Equipamentos & Manutenção',
        'configuracoes': 'Configurações & Parâmetros Operacionais'
      };

      function navegarPara(viewId) {
        document.querySelectorAll('.view-section').forEach(el => el.classList.remove('active-view'));
        const activeSection = document.getElementById(`view-${viewId}`);
        if (activeSection) activeSection.classList.add('active-view');

        document.querySelectorAll('.dashboard-nav-item').forEach(link => {
          link.classList.toggle('active', link.getAttribute('data-target') === viewId);
        });

        const mainTitle = document.getElementById('pageMainTitle');
        if (mainTitle && titlesMap[viewId]) {
          mainTitle.textContent = titlesMap[viewId];
        }

        const btnHeaderAction = document.getElementById('btnHeaderActionContextual');
        const iconHeaderAction = document.getElementById('iconHeaderAction');
        const labelHeaderAction = document.getElementById('labelHeaderAction');

        btnHeaderAction.style.display = 'inline-flex';
        btnHeaderAction.onclick = null;

        if (viewId === 'alunos') {
          labelHeaderAction.textContent = 'Novo Aluno';
          iconHeaderAction.textContent = 'person_add';
          btnHeaderAction.onclick = () => typeof abrirModalAluno === 'function' ? abrirModalAluno() : document.getElementById('modalAluno').classList.add('active');
        } else if (viewId === 'financeiro') {
          labelHeaderAction.textContent = 'Nova Cobrança';
          iconHeaderAction.textContent = 'add_circle';
          btnHeaderAction.onclick = () => typeof abrirModalNovaCobranca === 'function' ? abrirModalNovaCobranca() : document.getElementById('modalNovaCobranca').classList.add('active');
        } else if (viewId === 'planos') {
          labelHeaderAction.textContent = 'Novo Plano';
          iconHeaderAction.textContent = 'add_circle';
          btnHeaderAction.onclick = () => typeof abrirModalPlano === 'function' ? abrirModalPlano() : document.getElementById('modalPlano').classList.add('active');
        } else if (viewId === 'equipe') {
          labelHeaderAction.textContent = 'Novo Colaborador';
          iconHeaderAction.textContent = 'person_add';
          btnHeaderAction.onclick = () => document.getElementById('modalEquipe').classList.add('active');
        } else if (viewId === 'equipamentos') {
          labelHeaderAction.textContent = 'Novo Equipamento';
          iconHeaderAction.textContent = 'add_circle';
          btnHeaderAction.onclick = () => document.getElementById('modalEquipamento').classList.add('active');
        } else if (viewId === 'configuracoes') {
          labelHeaderAction.textContent = 'Salvar Alterações';
          iconHeaderAction.textContent = 'save';
          btnHeaderAction.onclick = () => {
            const btnSalvar = document.getElementById('btnSalvarGeral');
            if (btnSalvar) btnSalvar.click();
          };
        } else {
          btnHeaderAction.style.display = 'none';
        }

        const sidebar = document.getElementById('dashboardSidebar');
        const overlay = document.getElementById('dashboardSidebarOverlay');
        if (sidebar) sidebar.classList.remove('open');
        if (overlay) overlay.classList.remove('active');
      }

      // Sidebar Mobile Toggle
      const toggleBtns = [document.getElementById('btnToggleSidebar'), document.getElementById('btnToggleSidebarHeader')];
      toggleBtns.forEach(btn => {
        if (btn) {
          btn.addEventListener('click', () => {
            document.getElementById('dashboardSidebar').classList.toggle('open');
            document.getElementById('dashboardSidebarOverlay').classList.toggle('active');
          });
        }
      });

      const overlay = document.getElementById('dashboardSidebarOverlay');
      if (overlay) {
        overlay.addEventListener('click', () => {
          document.getElementById('dashboardSidebar').classList.remove('open');
          overlay.classList.remove('active');
        });
      }

      // Abas de Configuração
      document.querySelectorAll('.config-tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
          document.querySelectorAll('.config-tab-btn').forEach(b => b.classList.remove('active'));
          document.querySelectorAll('.config-panel').forEach(p => p.classList.remove('active'));
          btn.classList.add('active');
          const target = document.getElementById(btn.getAttribute('data-tab'));
          if (target) target.classList.add('active');
        });
      });
    </script>
  </body>
</html>