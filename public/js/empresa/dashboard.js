/**
 * ==============================================================================
 * RUSTIC IRON - CORE ENGINE COMPLETO COM FICHA DE TREINO & IMPRESSAO
 * ==============================================================================
 */

/* ==============================================================================
   1. TEMA & INTERFACE GLOBAL
   ============================================================================== */
let sidebarCollapsed = false;
let currentView = "overview";

function sincronizarBotoesTema(t) {
  document.querySelectorAll(".theme-option").forEach((opt) => {
    opt.classList.toggle("active", opt.getAttribute("data-theme") === t);
  });
}

function definirTema(t) {
  document.documentElement.setAttribute("data-theme", t);
  localStorage.setItem("dashboard-theme", t);
  sincronizarBotoesTema(t);
}

function inicializarTema() {
  const temaSalvo = localStorage.getItem("dashboard-theme") || "dark";
  document.documentElement.setAttribute("data-theme", temaSalvo);
  sincronizarBotoesTema(temaSalvo);

  document.querySelectorAll(".theme-option").forEach((btn) => {
    btn.addEventListener("click", function (e) {
      e.stopPropagation();
      const novoTema = this.getAttribute("data-theme");
      definirTema(novoTema);
    });
  });
}

function alternarSidebar() {
  const dashboardSidebar = document.getElementById("dashboardSidebar");
  const dashboardSidebarOverlay = document.getElementById("dashboardSidebarOverlay");
  if (!dashboardSidebar) return;

  sidebarCollapsed = !sidebarCollapsed;
  const isMobile = window.innerWidth <= 1024;

  if (isMobile) {
    const isOpen = dashboardSidebar.classList.contains("collapsed");
    dashboardSidebar.classList.toggle("collapsed", !isOpen);
    dashboardSidebarOverlay?.classList.toggle("active", !isOpen);
  } else {
    dashboardSidebar.classList.toggle("collapsed", sidebarCollapsed);
  }

  localStorage.setItem("dashboard-sidebar-collapsed", sidebarCollapsed.toString());
}

function fecharSidebarMobile() {
  const dashboardSidebar = document.getElementById("dashboardSidebar");
  const dashboardSidebarOverlay = document.getElementById("dashboardSidebarOverlay");
  if (window.innerWidth <= 1024 && dashboardSidebar) {
    dashboardSidebar.classList.remove("collapsed");
    dashboardSidebarOverlay?.classList.remove("active");
  }
}

function alternarMenuUtilizador(e) {
  if (e) e.stopPropagation();
  const userMenu = document.getElementById("userMenu");
  if (userMenu) {
    userMenu.classList.toggle("active");
  }
}

function alternarPesquisaMobile() {
  const searchContainer = document.getElementById("searchContainer");
  const searchInput = document.getElementById("searchInput") || document.getElementById("searchInputGlobal");
  if (searchContainer) {
    searchContainer.classList.add("mobile-active");
    searchInput?.focus();
  }
}

function fecharPesquisaMobile() {
  const searchContainer = document.getElementById("searchContainer");
  const searchInput = document.getElementById("searchInput") || document.getElementById("searchInputGlobal");
  if (searchContainer) {
    searchContainer.classList.remove("mobile-active");
    if (searchInput) searchInput.value = "";
  }
}

/* ==============================================================================
   2. CATRACA & CONTROLO DE ACESSO
   ============================================================================== */
let presentes = 0;
let entradas = 0;
let saidas = 0;
let recusados = 0;
let timeoutResetDisplay = null;

const dadosSimulados = {
  liberado: {
    identificador: "044.821.992-10",
    nome: "Carlos Eduardo Mendes",
    documento: "CPF 044.821.***-10",
    plano: "Plano Iron Anual Livre",
    status: "autorizado",
    msg: "Acesso Liberado - Bom treino!",
  },
  bloqueado: {
    identificador: "712.943.111-45",
    nome: "Mariana Silva Freitas",
    documento: "CPF 712.943.***-45",
    plano: "Plano Mensal Forca",
    status: "inadimplente",
    msg: "Mensalidade Vencida ha 6 dias - Dirija-se a Recepcao",
  },
  nao_encontrado: {
    identificador: "999.000.111-22",
    nome: "Desconhecido / Nao Encontrado",
    documento: "ID: 999.000.111-22",
    plano: "Sem Vinculo",
    status: "recusado",
    msg: "Cartao ou CPF nao registrado no sistema",
  },
};

function preencherDemo(tipo) {
  const cenario = dadosSimulados[tipo];
  if (cenario) {
    const input = document.getElementById("campoIdentificador");
    if (input) input.value = cenario.identificador;
  }
}

function processarIdentificacao(event) {
  event.preventDefault();
  const input = document.getElementById("campoIdentificador");
  const sentidoEl = document.getElementById("tipoSentido");
  const sentido = sentidoEl ? sentidoEl.value : "Entrada";
  const valorDigitado = input ? input.value.trim() : "";

  if (!valorDigitado) return;

  let aluno = Object.values(dadosSimulados).find((item) => item.identificador === valorDigitado);

  if (!aluno) {
    aluno = {
      identificador: valorDigitado,
      nome: "Aluno Avulso / Nao Identificado",
      documento: valorDigitado,
      plano: "Desconhecido",
      status: "recusado",
      msg: "Registro nao encontrado no banco de dados",
    };
  }

  const horaAtual = new Date().toLocaleTimeString("pt-BR", { hour: "2-digit", minute: "2-digit", second: "2-digit" });

  if (aluno.status === "autorizado") {
    atualizarDisplay(true, "ACESSO LIBERADO", aluno.msg, aluno.nome, aluno.plano);

    if (sentido === "Entrada") {
      entradas++;
      presentes++;
    } else {
      saidas++;
      if (presentes > 0) presentes--;
    }

    adicionarLinhaLog(horaAtual, aluno.nome, aluno.documento, sentido, aluno.plano, true, aluno.msg);
  } else {
    recusados++;
    atualizarDisplay(false, "ACESSO NEGADO", aluno.msg, aluno.nome, aluno.plano);
    adicionarLinhaLog(horaAtual, aluno.nome, aluno.documento, sentido, aluno.plano, false, aluno.msg);
  }

  atualizarMetricasCatraca();
  if (input) {
    input.value = "";
    input.focus();
  }
}

function liberarGiroManual(sentidoOperacao) {
  const horaAtual = new Date().toLocaleTimeString("pt-BR", { hour: "2-digit", minute: "2-digit", second: "2-digit" });
  const sentidoFormatado = sentidoOperacao === "entrada" ? "Entrada" : "Saida";

  atualizarDisplay(true, "GIRO MANUAL AUTORIZADO", "Comando acionado diretamente pela Recepcao", "Operador do Sistema", "Acesso Operacional");

  if (sentidoOperacao === "entrada") {
    entradas++;
    presentes++;
  } else {
    saidas++;
    if (presentes > 0) presentes--;
  }

  adicionarLinhaLog(horaAtual, "Liberacao Manual", "Recepcao / Gestao", sentidoFormatado, "Acesso Administrativo", true, "Giro liberado pelo atendente");
  atualizarMetricasCatraca();
}

function bloquearEmergencia() {
  const dot = document.getElementById("catracaStatusDot");
  const badge = document.getElementById("badgeConexao");
  if (!dot) return;

  dot.classList.toggle("travado");

  if (dot.classList.contains("travado")) {
    if (badge) {
      badge.className = "status-badge error";
      badge.textContent = "Travada (Emergencia)";
    }
    atualizarDisplay(false, "TRAVA ACIONADA", "Catraca bloqueada contra giros", "Seguranca", "Modo Panico Ativo");
  } else {
    if (badge) {
      badge.className = "status-badge success";
      badge.textContent = "Online";
    }
    resetarDisplay();
  }
}

function atualizarDisplay(sucesso, titulo, msg, nome, plano) {
  const display = document.getElementById("displayLeitor");
  const icon = document.getElementById("displayIcon");
  const tituloEl = document.getElementById("displayTitulo");
  const msgEl = document.getElementById("displayMsg");
  const detalhes = document.getElementById("displayDetalhes");
  const detalheNome = document.getElementById("detalheNome");
  const detalhePlano = document.getElementById("detalhePlano");

  if (!display) return;

  display.className = "display-leitor " + (sucesso ? "liberado" : "bloqueado");
  if (icon) icon.textContent = sucesso ? "check_circle" : "cancel";
  if (tituloEl) tituloEl.textContent = titulo;
  if (msgEl) msgEl.textContent = msg;

  if (detalhes) detalhes.style.display = "flex";
  if (detalheNome) detalheNome.textContent = nome;
  if (detalhePlano) detalhePlano.textContent = plano;

  clearTimeout(timeoutResetDisplay);
  timeoutResetDisplay = setTimeout(resetarDisplay, 4000);
}

function resetarDisplay() {
  const display = document.getElementById("displayLeitor");
  const icon = document.getElementById("displayIcon");
  const tituloEl = document.getElementById("displayTitulo");
  const msgEl = document.getElementById("displayMsg");
  const detalhes = document.getElementById("displayDetalhes");

  if (!display) return;
  display.className = "display-leitor";
  if (icon) icon.textContent = "fingerprint";
  if (tituloEl) tituloEl.textContent = "AGUARDANDO LEITURA";
  if (msgEl) msgEl.textContent = "Aproxime o cartao RFID, biometria ou digite o CPF";
  if (detalhes) detalhes.style.display = "none";
}

function adicionarLinhaLog(hora, nome, doc, sentido, plano, autorizado, motivo) {
  const tbody = document.getElementById("tabelaLogsCatraca");
  const linhaVazia = document.getElementById("linhaVazia");

  if (linhaVazia) linhaVazia.remove();
  if (!tbody) return;

  const tr = document.createElement("tr");
  tr.innerHTML = `
    <td><strong>${hora}</strong></td>
    <td>
      <div class="project-title-cell">
        <div class="project-icon">
          <span class="material-symbols-rounded">${autorizado ? "person" : "person_off"}</span>
        </div>
        <div class="project-info">
          <div class="project-title-text">${nome}</div>
          <div class="project-meta-text">${doc}</div>
        </div>
      </div>
    </td>
    <td>${doc}</td>
    <td><span class="status-badge ${sentido === "Entrada" ? "info" : "warning"}">${sentido}</span></td>
    <td>${plano}</td>
    <td><span class="status-badge ${autorizado ? "success" : "error"}">${autorizado ? "Liberado" : "Bloqueado"}</span></td>
    <td>${motivo}</td>
  `;

  tbody.insertBefore(tr, tbody.firstChild);
}

function limparHistorico() {
  const tbody = document.getElementById("tabelaLogsCatraca");
  if (!tbody) return;
  tbody.innerHTML = `
    <tr id="linhaVazia">
      <td colspan="7" class="tabela-sem-dados">Nenhum evento registrado ainda. As passagens aparecerao aqui automaticamente.</td>
    </tr>
  `;
}

function atualizarMetricasCatraca() {
  const elPres = document.getElementById("totalPresentes");
  const elEntr = document.getElementById("totalEntradas");
  const elSaid = document.getElementById("totalSaidas");
  const elRecu = document.getElementById("totalRecusados");
  const elBadge = document.getElementById("alertaBadge");

  if (elPres) elPres.textContent = presentes;
  if (elEntr) elEntr.textContent = entradas;
  if (elSaid) elSaid.textContent = saidas;
  if (elRecu) elRecu.textContent = recusados;
  if (elBadge) elBadge.textContent = recusados;
}

/* ==============================================================================
   3. GESTAO DE ALUNOS & AS 53 MODALIDADES COMPLETAS
   ============================================================================== */
const mapaModalidades = {
  forca: "Forca",
  hipertrofia: "Hipertrofia",
  ganho_massa: "Ganho de massa muscular",
  emagrecimento: "Emagrecimento",
  perda_gordura: "Perda de gordura",
  definicao: "Definicao muscular",
  resistencia_muscular: "Resistencia muscular",
  resistencia_fisica: "Resistencia fisica",
  condicionamento: "Condicionamento fisico",
  cardiorrespiratorio: "Condicionamento cardiorrespiratorio",
  potencia: "Potencia",
  explosao: "Explosao muscular",
  velocidade: "Velocidade",
  agilidade: "Agilidade",
  coordenacao: "Coordenacao motora",
  equilibrio: "Equilibrio",
  mobilidade: "Mobilidade",
  flexibilidade: "Flexibilidade",
  amplitude_movimento: "Amplitude de movimento",
  postura: "Correcao postural",
  estabilidade: "Estabilidade corporal",
  core: "Fortalecimento do core",
  saude: "Saude e qualidade de vida",
  longevidade: "Longevidade",
  bem_estar: "Bem-estar",
  qualidade_vida: "Qualidade de vida",
  preparacao_fisica: "Preparacao fisica",
  performance: "Performance esportiva",
  competicao: "Preparacao para competicao",
  reabilitacao: "Reabilitacao",
  prevencao_lesoes: "Prevencao de lesoes",
  retorno_esporte: "Retorno ao esporte",
  recuperacao: "Recuperacao fisica",
  musculacao: "Musculacao",
  powerlifting: "Powerlifting",
  levantamento_olimpico: "Levantamento olimpico",
  fisiculturismo: "Fisiculturismo",
  cross_training: "Cross Training",
  funcional: "Treinamento funcional",
  calistenia: "Calistenia",
  corrida: "Corrida",
  ciclismo: "Ciclismo",
  natacao: "Natacao",
  luta: "Luta",
  jiu_jitsu: "Jiu-Jitsu",
  boxe: "Boxe",
  muay_thai: "Muay Thai",
  mma: "MMA",
  futebol: "Futebol",
  basquete: "Basquete",
  volei: "Volei",
  handebol: "Handebol",
  tenis: "Tenis",
  outro: "Outro",
};

// Banco de Exercicios por Musculo
const bancoExercicios = {
  peito: {
    nome: "Peito / Peitoral",
    exercicios: ["Supino Reto com Barra", "Supino Inclinado com Halteres", "Crucifixo Reto", "Crossover na Polia Alta", "Paralelas / Dips", "Supino Declinado"]
  },
  costas: {
    nome: "Costas / Dorsal",
    exercicios: ["Puxada Alta Frontal", "Remada Curvada com Barra", "Remada Baixa no Triangulo", "Levantamento Terra Convencional", "Pull-down na Polia", "Barra Fixa (Pronada)"]
  },
  quadriceps: {
    nome: "Pernas / Quadriceps",
    exercicios: ["Agachamento Livre com Barra", "Leg Press 45", "Cadeira Extensora", "Agachamento Bulgaro", "Hack Machine", "Passada / Afundo com Halteres"]
  },
  posteriores: {
    nome: "Posteriores & Gluteos",
    exercicios: ["Mesa Flexora", "Stiff com Barra", "Cadeira Flexora", "Elevacao Pelvica com Barra", "Gluteo no Cabo", "Bom Dia (Good Morning)"]
  },
  ombros: {
    nome: "Ombros / Deltoides",
    exercicios: ["Desenvolvimento com Halteres", "Elevacao Lateral com Halteres", "Desenvolvimento Militar Barra", "Elevacao Frontal", "Crucifixo Invertido", "Face Pull no Cabo"]
  },
  biceps: {
    nome: "Biceps",
    exercicios: ["Rosca Direta Barra W", "Rosca Alternada com Halteres", "Rosca Martelo", "Rosca Scott", "Rosca Concentrada"]
  },
  triceps: {
    nome: "Triceps",
    exercicios: ["Triceps Corda na Polia", "Triceps Testa com Barra W", "Triceps Frances com Halter", "Triceps Barra Reta", "Mergulho no Banco"]
  },
  trapezio: {
    nome: "Trapezio",
    exercicios: ["Encolhimento com Barra", "Encolhimento com Halteres", "Remada Alta com Barra W"]
  },
  antebraco: {
    nome: "Antebraco",
    exercicios: ["Rosca Punho Inversa", "Rosca Inversa com Barra", "Flexao de Punho"]
  },
  panturrilhas: {
    nome: "Panturrilhas",
    exercicios: ["Gemeos Sentado (Cavalinho)", "Panturrilha no Leg Press", "Gemeos em Pe no Degrau"]
  },
  abdomen: {
    nome: "Abdomen & Core",
    exercicios: ["Abdominal na Polia (Crunch)", "Prancha Isometrica", "Elevacao de Pernas na Barra", "Abdominal Infra no Solo", "Roda Abdominal"]
  },
  cardio: {
    nome: "Cardio & Resistencia",
    exercicios: ["Esteira Curva Mecanica (HIIT)", "Bicicleta Ergometrica", "Simulador de Escada", "Remo Indoor"]
  }
};

let alunos = [
  {
    id: 1,
    nome: "Carlos Eduardo Mendes",
    data_nasc: "1998-04-12",
    cpf: "044.821.992-10",
    telefone: "(79) 99823-1122",
    telefone_emergencia: "(79) 99120-9988",
    endereco: "Av. Beira Mar, 1420",
    cidade: "Aracaju",
    peso: 88.5,
    altura: 178,
    tipo_treino: "powerlifting",
    outra_modalidade: "",
    email: "carlos.mendes@email.com",
    catraca_liberada: true,
    treino: {
      segunda: {
        musculosOrdem: ["peito", "triceps", "ombros"],
        exercicios: {
          peito: [
            { nome: "Supino Reto com Barra", series: 4, reps: "6 a 8", carga: "110 kg" },
            { nome: "Supino Inclinado com Halteres", series: 3, reps: "8 a 10", carga: "36 kg cada" }
          ],
          triceps: [
            { nome: "Triceps Corda na Polia", series: 4, reps: "10 a 12", carga: "35 kg" },
            { nome: "Triceps Testa com Barra W", series: 3, reps: "10", carga: "30 kg" }
          ],
          ombros: [
            { nome: "Elevacao Lateral com Halteres", series: 4, reps: "12 a 15", carga: "14 kg cada" }
          ]
        }
      }
    }
  },
  {
    id: 2,
    nome: "Mariana Silva Freitas",
    data_nasc: "2001-11-25",
    cpf: "712.943.111-45",
    telefone: "(79) 98110-4490",
    telefone_emergencia: "(79) 98877-2233",
    endereco: "Rua das Flores, 88",
    cidade: "Aracaju",
    peso: 63.2,
    altura: 165,
    tipo_treino: "hipertrofia",
    outra_modalidade: "",
    email: "mariana.silva@email.com",
    catraca_liberada: true,
    treino: {}
  },
  {
    id: 3,
    nome: "Rodrigo Santos Oliveira",
    data_nasc: "1995-07-09",
    cpf: "890.312.441-01",
    telefone: "(79) 99122-3344",
    telefone_emergencia: "",
    endereco: "Rua Projetada, 12",
    cidade: "Nossa Senhora do Socorro",
    peso: 94.0,
    altura: 182,
    tipo_treino: "emagrecimento",
    outra_modalidade: "",
    email: "rodrigo.santos@email.com",
    catraca_liberada: false,
    treino: {}
  },
];

function renderizarAlunos() {
  const tbody = document.getElementById("tabelaAlunos");
  const inputBusca = document.getElementById("inputBuscaAluno");
  const termo = inputBusca ? inputBusca.value.toLowerCase().trim() : "";

  if (!tbody) return;
  tbody.innerHTML = "";

  const filtrados = alunos.filter((a) => {
    const nomeMod = mapaModalidades[a.tipo_treino] || a.tipo_treino;
    return (
      a.nome.toLowerCase().includes(termo) ||
      a.cpf.includes(termo) ||
      a.cidade.toLowerCase().includes(termo) ||
      nomeMod.toLowerCase().includes(termo) ||
      (a.outra_modalidade && a.outra_modalidade.toLowerCase().includes(termo))
    );
  });

  if (filtrados.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="8" class="tabela-sem-dados">Nenhum aluno encontrado.</td>
      </tr>
    `;
    return;
  }

  filtrados.forEach((aluno) => {
    const tr = document.createElement("tr");
    const labelModalidade = aluno.tipo_treino === "outro" && aluno.outra_modalidade
      ? aluno.outra_modalidade
      : mapaModalidades[aluno.tipo_treino] || aluno.tipo_treino;

    const catracaBadge = aluno.catraca_liberada
      ? `<span class="status-badge success">Liberado</span>`
      : `<span class="status-badge error">Bloqueado</span>`;

    tr.innerHTML = `
      <td>
        <div class="project-title-cell">
          <div class="project-icon">
            <span class="material-symbols-rounded">person</span>
          </div>
          <div class="project-info">
            <div class="project-title-text">${aluno.nome}</div>
            <div class="project-meta-text">${aluno.email}</div>
          </div>
        </div>
      </td>
      <td>${aluno.cpf}</td>
      <td>${aluno.telefone}</td>
      <td>${aluno.cidade || "-"}</td>
      <td>${aluno.peso} kg &bull; ${aluno.altura} cm</td>
      <td><span class="status-badge info">${labelModalidade}</span></td>
      <td>${catracaBadge}</td>
      <td>
        <div class="tabela-acoes">
          <button class="btn-acao btn-acao-treino" title="Abrir e Editar Ficha de Treino" onclick="abrirModalFichaTreino(${aluno.id})">
            <span class="material-symbols-rounded" style="font-size:18px">assignment</span>
          </button>
          <button class="btn-acao btn-acao-print" title="Imprimir Ficha do Aluno" onclick="prepararEImprimirFicha(${aluno.id})">
            <span class="material-symbols-rounded" style="font-size:18px">print</span>
          </button>
          <button class="btn-acao" title="Editar dados cadastrais" onclick="editarAluno(${aluno.id})">
            <span class="material-symbols-rounded" style="font-size:18px">edit</span>
          </button>
          <button class="btn-acao excluir" title="Excluir aluno" onclick="excluirAluno(${aluno.id})">
            <span class="material-symbols-rounded" style="font-size:18px">delete</span>
          </button>
        </div>
      </td>
    `;
    tbody.appendChild(tr);
  });
}

function atualizarCardsAlunos() {
  const total = alunos.length;
  const pesoTotal = alunos.reduce((acc, a) => acc + Number(a.peso), 0);
  const alturaTotal = alunos.reduce((acc, a) => acc + Number(a.altura), 0);
  const liberados = alunos.filter((a) => a.catraca_liberada).length;

  const cardTot = document.getElementById("cardTotalAlunos");
  const cardPeso = document.getElementById("cardMediaPeso");
  const cardAlt = document.getElementById("cardMediaAltura");
  const cardLib = document.getElementById("cardCatracaLiberados");

  if (cardTot) cardTot.textContent = total;
  if (cardPeso) cardPeso.textContent = total > 0 ? (pesoTotal / total).toFixed(1) + " kg" : "0.0 kg";
  if (cardAlt) cardAlt.textContent = total > 0 ? Math.round(alturaTotal / total) + " cm" : "0 cm";
  if (cardLib) cardLib.textContent = liberados;
}

function filtrarAlunos() {
  renderizarAlunos();
}

function abrirModalAluno() {
  const form = document.getElementById("formAluno");
  if (form) form.reset();
  const idEdit = document.getElementById("alunoIdEdit");
  if (idEdit) idEdit.value = "";
  const titulo = document.getElementById("modalTituloAluno");
  if (titulo) titulo.textContent = "Cadastrar Aluno";
  const btnSubmit = document.getElementById("txtBtnSubmit");
  if (btnSubmit) btnSubmit.textContent = "Cadastrar Aluno";
  const pass = document.getElementById("senha");
  if (pass) pass.required = true;

  document.getElementById("modalAluno")?.classList.add("active");
}

function fecharModalAluno() {
  document.getElementById("modalAluno")?.classList.remove("active");
}

function verificarOutraModalidade(valor) {
  const campoOutra = document.getElementById("outra_modalidade");
  if (!campoOutra) return;
  if (valor === "outro") {
    campoOutra.focus();
    campoOutra.required = true;
  } else {
    campoOutra.required = false;
  }
}

function editarAluno(id) {
  const aluno = alunos.find((a) => a.id === id);
  if (!aluno) return;

  document.getElementById("alunoIdEdit").value = aluno.id;
  document.getElementById("modalTituloAluno").textContent = "Editar Aluno";
  document.getElementById("txtBtnSubmit").textContent = "Salvar Alteracoes";

  document.getElementById("nome_completo").value = aluno.nome;
  document.getElementById("data_nasc").value = aluno.data_nasc;
  document.getElementById("cpf").value = aluno.cpf;
  document.getElementById("telefone").value = aluno.telefone;
  document.getElementById("telefone_emergencia").value = aluno.telefone_emergencia || "";
  document.getElementById("endereco").value = aluno.endereco || "";
  document.getElementById("cidade").value = aluno.cidade || "";
  document.getElementById("peso").value = aluno.peso;
  document.getElementById("altura").value = aluno.altura;
  document.getElementById("tipo_treino").value = aluno.tipo_treino;
  document.getElementById("outra_modalidade").value = aluno.outra_modalidade || "";
  document.getElementById("email").value = aluno.email;

  const pass = document.getElementById("senha");
  if (pass) pass.required = false;

  document.getElementById("modalAluno")?.classList.add("active");
}

function salvarAluno(e) {
  e.preventDefault();

  const idEdit = document.getElementById("alunoIdEdit").value;
  const nome = document.getElementById("nome_completo").value.trim();
  const dataNasc = document.getElementById("data_nasc").value;
  const cpf = document.getElementById("cpf").value.trim();
  const tel = document.getElementById("telefone").value.trim();
  const telEmerg = document.getElementById("telefone_emergencia").value.trim();
  const end = document.getElementById("endereco").value.trim();
  const cid = document.getElementById("cidade").value.trim();
  const peso = parseFloat(document.getElementById("peso").value);
  const alt = parseInt(document.getElementById("altura").value, 10);
  const tipoTreino = document.getElementById("tipo_treino").value;
  const outraMod = document.getElementById("outra_modalidade").value.trim();
  const email = document.getElementById("email").value.trim();

  if (idEdit) {
    const aluno = alunos.find((a) => a.id === parseInt(idEdit, 10));
    if (aluno) {
      aluno.nome = nome;
      aluno.data_nasc = dataNasc;
      aluno.cpf = cpf;
      aluno.telefone = tel;
      aluno.telefone_emergencia = telEmerg;
      aluno.endereco = end;
      aluno.cidade = cid;
      aluno.peso = peso;
      aluno.altura = alt;
      aluno.tipo_treino = tipoTreino;
      aluno.outra_modalidade = outraMod;
      aluno.email = email;
    }
  } else {
    const novoId = alunos.length ? Math.max(...alunos.map((a) => a.id)) + 1 : 1;
    alunos.unshift({
      id: novoId,
      nome: nome,
      data_nasc: dataNasc,
      cpf: cpf,
      telefone: tel,
      telefone_emergencia: telEmerg,
      endereco: end,
      cidade: cid,
      peso: peso,
      altura: alt,
      tipo_treino: tipoTreino,
      outra_modalidade: outraMod,
      email: email,
      catraca_liberada: true,
      treino: {}
    });
  }

  fecharModalAluno();
  renderizarAlunos();
  atualizarCardsAlunos();
}

function excluirAluno(id) {
  const aluno = alunos.find((a) => a.id === id);
  if (!aluno) return;

  if (confirm(`Deseja realmente remover o aluno "${aluno.nome}"?`)) {
    alunos = alunos.filter((a) => a.id !== id);
    renderizarAlunos();
    atualizarCardsAlunos();
  }
}

/* ==============================================================================
   FICHA DE TREINO, ORDENACAO DE MUSCULOS E IMPRESSAO
   ============================================================================== */
let alunoFichaAtual = null;
let diaTreinoAtual = "segunda";
let musculosOrdemAtual = [];
let dragStartIndex = null;

function inicializarSelectModalidadesFicha() {
  const select = document.getElementById("fichaModalidadeSelect");
  if (!select) return;
  select.innerHTML = "";
  Object.keys(mapaModalidades).forEach((key) => {
    const opt = document.createElement("option");
    opt.value = key;
    opt.textContent = mapaModalidades[key];
    select.appendChild(opt);
  });
}

function abrirModalFichaTreino(id) {
  const aluno = alunos.find((a) => a.id === id);
  if (!aluno) return;

  alunoFichaAtual = aluno;
  if (!alunoFichaAtual.treino) {
    alunoFichaAtual.treino = {};
  }

  inicializarSelectModalidadesFicha();

  document.getElementById("fichaModalNomeAluno").textContent = `Ficha de Treino - ${aluno.nome}`;
  document.getElementById("fichaModalSubInfo").textContent = `${aluno.peso} kg | ${aluno.altura} cm | CPF: ${aluno.cpf}`;

  const selectMod = document.getElementById("fichaModalidadeSelect");
  if (selectMod) selectMod.value = aluno.tipo_treino || "forca";

  diaTreinoAtual = "segunda";
  document.getElementById("fichaDiaSemana").value = "segunda";

  carregarDiaTreino(diaTreinoAtual);
  document.getElementById("modalFichaTreino")?.classList.add("active");
}

function fecharModalFicha() {
  document.getElementById("modalFichaTreino")?.classList.remove("active");
  alunoFichaAtual = null;
}

function atualizarModalidadeFicha(novaModalidade) {
  if (alunoFichaAtual) {
    alunoFichaAtual.tipo_treino = novaModalidade;
    renderizarAlunos();
  }
}

function selecionarDiaSemanaTreino(dia) {
  salvarEstadoDiaAtualEmMemoria();
  diaTreinoAtual = dia;
  carregarDiaTreino(diaTreinoAtual);
}

function carregarDiaTreino(dia) {
  const containerChecks = document.getElementById("containerCheckboxesMusculos");
  if (!containerChecks) return;
  containerChecks.innerHTML = "";

  const treinoDoDia = alunoFichaAtual.treino[dia] || { musculosOrdem: [], exercicios: {} };
  musculosOrdemAtual = [...(treinoDoDia.musculosOrdem || [])];

  Object.keys(bancoExercicios).forEach((chaveMusculo) => {
    const item = bancoExercicios[chaveMusculo];
    const isChecked = musculosOrdemAtual.includes(chaveMusculo);

    const label = document.createElement("label");
    label.className = `checkbox-musculo-card ${isChecked ? "selecionado" : ""}`;
    label.innerHTML = `
      <input type="checkbox" value="${chaveMusculo}" ${isChecked ? "checked" : ""} onchange="alternarSelecaoMusculo('${chaveMusculo}', this.checked)" />
      <span>${item.nome}</span>
    `;
    containerChecks.appendChild(label);
  });

  renderizarListaOrdemMusculos();
  renderizarBlocosExercicios();
}

function alternarSelecaoMusculo(chaveMusculo, isSelected) {
  if (isSelected) {
    if (!musculosOrdemAtual.includes(chaveMusculo)) {
      musculosOrdemAtual.push(chaveMusculo);
    }
  } else {
    musculosOrdemAtual = musculosOrdemAtual.filter((m) => m !== chaveMusculo);
  }

  document.querySelectorAll(".checkbox-musculo-card input").forEach((input) => {
    input.closest(".checkbox-musculo-card").classList.toggle("selecionado", input.checked);
  });

  renderizarListaOrdemMusculos();
  renderizarBlocosExercicios();
}

function renderizarListaOrdemMusculos() {
  const container = document.getElementById("listaMusculosOrdem");
  if (!container) return;
  container.innerHTML = "";

  if (musculosOrdemAtual.length === 0) {
    container.innerHTML = `<div class="ficha-aviso-vazio">Nenhum musculo selecionado para este dia. Marque as caixinhas acima.</div>`;
    return;
  }

  musculosOrdemAtual.forEach((chaveMusculo, index) => {
    const musculoInfo = bancoExercicios[chaveMusculo];
    const card = document.createElement("div");
    card.className = "item-musculo-ordem";
    card.setAttribute("draggable", "true");
    card.dataset.index = index;

    card.innerHTML = `
      <div class="item-ordem-drag-handle" title="Arraste para reordenar">
        <span class="material-symbols-rounded">drag_handle</span>
      </div>
      <div class="item-ordem-numero">${index + 1}o</div>
      <div class="item-ordem-nome">
        <strong>${musculoInfo.nome}</strong>
      </div>
      <div class="item-ordem-controles">
        <button type="button" class="btn-ordem" title="Mover para cima" onclick="moverMusculoOrdem(${index}, -1)" ${index === 0 ? "disabled" : ""}>
          <span class="material-symbols-rounded">arrow_upward</span>
        </button>
        <button type="button" class="btn-ordem" title="Mover para baixo" onclick="moverMusculoOrdem(${index}, 1)" ${index === musculosOrdemAtual.length - 1 ? "disabled" : ""}>
          <span class="material-symbols-rounded">arrow_downward</span>
        </button>
      </div>
    `;

    card.addEventListener("dragstart", () => {
      dragStartIndex = index;
      card.classList.add("arrastando");
    });

    card.addEventListener("dragend", () => {
      card.classList.remove("arrastando");
    });

    card.addEventListener("dragover", (e) => {
      e.preventDefault();
    });

    card.addEventListener("drop", (e) => {
      e.preventDefault();
      trocarPosicaoMusculos(dragStartIndex, index);
    });

    container.appendChild(card);
  });
}

function moverMusculoOrdem(index, direcao) {
  const novoIndex = index + direcao;
  if (novoIndex < 0 || novoIndex >= musculosOrdemAtual.length) return;
  salvarInputsExerciciosEmMemoria();
  trocarPosicaoMusculos(index, novoIndex);
}

function trocarPosicaoMusculos(de, para) {
  salvarInputsExerciciosEmMemoria();
  const elemento = musculosOrdemAtual.splice(de, 1)[0];
  musculosOrdemAtual.splice(para, 0, elemento);
  renderizarListaOrdemMusculos();
  renderizarBlocosExercicios();
}

function renderizarBlocosExercicios() {
  const container = document.getElementById("containerExerciciosPorMusculo");
  if (!container) return;
  container.innerHTML = "";

  if (musculosOrdemAtual.length === 0) {
    container.innerHTML = `<div class="ficha-aviso-vazio">Nenhum bloco de exercicio ativo. Selecione grupos musculares acima.</div>`;
    return;
  }

  const treinoDoDia = (alunoFichaAtual.treino && alunoFichaAtual.treino[diaTreinoAtual]) || { exercicios: {} };

  musculosOrdemAtual.forEach((chaveMusculo, idx) => {
    const musculoInfo = bancoExercicios[chaveMusculo];
    const listaGravada = (treinoDoDia.exercicios && treinoDoDia.exercicios[chaveMusculo]) || [
      { nome: musculoInfo.exercicios[0] || "", series: 3, reps: "10 a 12", carga: "20 kg" }
    ];

    const bloco = document.createElement("div");
    bloco.className = "bloco-exercicio-musculo";
    bloco.dataset.musculo = chaveMusculo;

    bloco.innerHTML = `
      <div class="bloco-exercicio-header">
        <span class="bloco-posicao-tag">${idx + 1}o Foco</span>
        <h4>${musculoInfo.nome}</h4>
        <button type="button" class="btn btn-secondary btn-sm" onclick="adicionarLinhaExercicio('${chaveMusculo}')">
          <span class="material-symbols-rounded">add</span> Adicionar Exercicio
        </button>
      </div>
      <table class="tabela-exercicios-dia">
        <thead>
          <tr>
            <th>Exercicio</th>
            <th style="width: 80px;">Series</th>
            <th style="width: 100px;">Reps</th>
            <th style="width: 120px;">Carga / Obs</th>
            <th style="width: 50px;">Acao</th>
          </tr>
        </thead>
        <tbody id="tbody_exercicios_${chaveMusculo}">
        </tbody>
      </table>
    `;

    container.appendChild(bloco);

    const tbody = bloco.querySelector(`#tbody_exercicios_${chaveMusculo}`);
    listaGravada.forEach((ex) => {
      tbody.appendChild(criarTrExercicio(chaveMusculo, ex));
    });
  });
}

function criarTrExercicio(chaveMusculo, dados = { nome: "", series: 3, reps: "10 a 12", carga: "" }) {
  const tr = document.createElement("tr");
  const musculoInfo = bancoExercicios[chaveMusculo];

  let optionsEx = musculoInfo.exercicios.map((ex) => `<option value="${ex}" ${ex === dados.nome ? "selected" : ""}>${ex}</option>`).join("");
  if (!musculoInfo.exercicios.includes(dados.nome) && dados.nome) {
    optionsEx += `<option value="${dados.nome}" selected>${dados.nome}</option>`;
  }

  tr.innerHTML = `
    <td>
      <select class="select-ex-tabela" onchange="if(this.value==='outro'){ const custom = prompt('Digite o nome do exercicio:'); if(custom){ const opt = document.createElement('option'); opt.value = custom; opt.textContent = custom; opt.selected = true; this.appendChild(opt); } }">
        ${optionsEx}
        <option value="outro">+ Outro Exercicio...</option>
      </select>
    </td>
    <td><input type="number" min="1" max="10" value="${dados.series || 3}" class="input-ex-series" /></td>
    <td><input type="text" value="${dados.reps || '10 a 12'}" class="input-ex-reps" /></td>
    <td><input type="text" value="${dados.carga || 'Livre'}" placeholder="Ex: 20kg" class="input-ex-carga" /></td>
    <td style="text-align: center;">
      <button type="button" class="btn-del-linha" title="Remover" onclick="this.closest('tr').remove()">
        <span class="material-symbols-rounded">delete</span>
      </button>
    </td>
  `;
  return tr;
}

function adicionarLinhaExercicio(chaveMusculo) {
  const tbody = document.getElementById(`tbody_exercicios_${chaveMusculo}`);
  if (tbody) {
    tbody.appendChild(criarTrExercicio(chaveMusculo));
  }
}

function salvarInputsExerciciosEmMemoria() {
  if (!alunoFichaAtual) return;
  if (!alunoFichaAtual.treino) alunoFichaAtual.treino = {};
  if (!alunoFichaAtual.treino[diaTreinoAtual]) alunoFichaAtual.treino[diaTreinoAtual] = {};

  const treinoDia = {
    musculosOrdem: [...musculosOrdemAtual],
    exercicios: {}
  };

  musculosOrdemAtual.forEach((chaveMusculo) => {
    const tbody = document.getElementById(`tbody_exercicios_${chaveMusculo}`);
    treinoDia.exercicios[chaveMusculo] = [];
    if (tbody) {
      tbody.querySelectorAll("tr").forEach((tr) => {
        const select = tr.querySelector(".select-ex-tabela");
        const series = tr.querySelector(".input-ex-series")?.value || 3;
        const reps = tr.querySelector(".input-ex-reps")?.value || "10";
        const carga = tr.querySelector(".input-ex-carga")?.value || "";
        if (select) {
          treinoDia.exercicios[chaveMusculo].push({
            nome: select.value,
            series: parseInt(series, 10),
            reps: reps,
            carga: carga
          });
        }
      });
    }
  });

  alunoFichaAtual.treino[diaTreinoAtual] = treinoDia;
}

function salvarEstadoDiaAtualEmMemoria() {
  salvarInputsExerciciosEmMemoria();
}

function salvarFichaTreinoAtual() {
  if (!alunoFichaAtual) return;
  salvarEstadoDiaAtualEmMemoria();
  mostrarToast(`Ficha de treino de ${alunoFichaAtual.nome} salva com sucesso!`);
  fecharModalFicha();
  renderizarAlunos();
}

function prepararEImprimirFicha(alunoId) {
  const aluno = alunos.find((a) => a.id === alunoId);
  if (!aluno) return;

  const printNome = document.getElementById("printNomeAluno");
  const printDados = document.getElementById("printDadosFisicos");
  const printData = document.getElementById("printDataEmissao");
  const printCorpo = document.getElementById("printCorpoTreinos");

  printNome.textContent = aluno.nome;
  const modFormatada = mapaModalidades[aluno.tipo_treino] || aluno.tipo_treino;
  printDados.textContent = `Peso: ${aluno.peso} kg | Altura: ${aluno.altura} cm | Foco: ${modFormatada} | Telefone: ${aluno.telefone}`;
  printData.textContent = `Data de Emissao: ${new Date().toLocaleDateString("pt-BR")}`;

  printCorpo.innerHTML = "";

  const diasOrdem = ["segunda", "terca", "quarta", "quinta", "sexta", "sabado"];
  const nomesDias = {
    segunda: "Segunda-feira",
    terca: "Terca-feira",
    quarta: "Quarta-feira",
    quinta: "Quinta-feira",
    sexta: "Sexta-feira",
    sabado: "Sabado"
  };

  let temAlgumTreino = false;

  diasOrdem.forEach((dia) => {
    const diaInfo = aluno.treino && aluno.treino[dia];
    if (diaInfo && diaInfo.musculosOrdem && diaInfo.musculosOrdem.length > 0) {
      temAlgumTreino = true;
      const blocoDia = document.createElement("div");
      blocoDia.className = "print-bloco-dia";

      let htmlTabelas = "";
      diaInfo.musculosOrdem.forEach((chaveMusculo, i) => {
        const infoMusc = bancoExercicios[chaveMusculo];
        const exs = diaInfo.exercicios[chaveMusculo] || [];

        let rows = "";
        exs.forEach((e) => {
          rows += `
            <tr>
              <td>${e.nome}</td>
              <td style="text-align:center;">${e.series}</td>
              <td style="text-align:center;">${e.reps}</td>
              <td>${e.carga || "-"}</td>
            </tr>
          `;
        });

        htmlTabelas += `
          <div class="print-subgrupo-musculo">
            <strong>${i + 1}o Bloco: ${infoMusc.nome}</strong>
            <table class="print-tabela">
              <thead>
                <tr>
                  <th>Exercicio</th>
                  <th style="width: 50px; text-align:center;">Series</th>
                  <th style="width: 80px; text-align:center;">Reps</th>
                  <th style="width: 100px;">Carga / Notas</th>
                </tr>
              </thead>
              <tbody>
                ${rows || '<tr><td colspan="4">Nenhum exercicio especificado.</td></tr>'}
              </tbody>
            </table>
          </div>
        `;
      });

      blocoDia.innerHTML = `
        <h3 class="print-dia-titulo">${nomesDias[dia]}</h3>
        ${htmlTabelas}
      `;
      printCorpo.appendChild(blocoDia);
    }
  });

  if (!temAlgumTreino) {
    printCorpo.innerHTML = `<p style="padding: 20px; font-style: italic;">Nenhum treino cadastrado nesta ficha ainda. Acesse a ficha para adicionar exercicios aos dias da semana.</p>`;
  }

  window.print();
}

function imprimirFichaAtual() {
  if (alunoFichaAtual) {
    salvarEstadoDiaAtualEmMemoria();
    prepararEImprimirFicha(alunoFichaAtual.id);
  }
}

/* ==============================================================================
   4. GESTAO FINANCEIRA
   ============================================================================== */
let faturas = [
  { id: 101, aluno: "Carlos Eduardo Mendes", cpf: "044.821.992-10", plano: "Plano Iron Anual Livre", valor: 130.0, vencimento: "2026-10-05", status: "pago", metodo: "PIX" },
  { id: 102, aluno: "Mariana Silva Freitas", cpf: "712.943.111-45", plano: "Plano Mensal Forca", valor: 130.0, vencimento: "2026-09-22", status: "atrasado", metodo: null },
  { id: 103, aluno: "Rodrigo Santos Oliveira", cpf: "890.312.441-01", plano: "Full Combat & Iron", valor: 190.0, vencimento: "2026-10-02", status: "pendente", metodo: null },
  { id: 104, aluno: "Fernando Souza Costa", cpf: "334.221.789-89", plano: "Plano Trimestral", valor: 350.0, vencimento: "2026-09-15", status: "atrasado", metodo: null },
  { id: 105, aluno: "Juliana Correia Lima", cpf: "551.442.880-33", plano: "Iron Mensal Livre", valor: 130.0, vencimento: "2026-09-29", status: "pago", metodo: "Cartao de Credito" },
];

let faturaSelecionadaId = null;
let filtroFinanceiroAtual = "todos";

function renderizarTabela() {
  const tbody = document.getElementById("tabelaFaturas");
  const inputBusca = document.getElementById("inputBuscaFinanceiro");
  const termo = inputBusca ? inputBusca.value.toLowerCase().trim() : "";

  if (!tbody) return;
  tbody.innerHTML = "";

  const faturasFiltradas = faturas.filter((fat) => {
    const matchStatus = filtroFinanceiroAtual === "todos" || fat.status === filtroFinanceiroAtual;
    const matchBusca = fat.aluno.toLowerCase().includes(termo) || fat.cpf.includes(termo) || fat.id.toString().includes(termo);
    return matchStatus && matchBusca;
  });

  if (faturasFiltradas.length === 0) {
    tbody.innerHTML = `<tr><td colspan="8" class="tabela-sem-dados">Nenhuma fatura encontrada para este filtro.</td></tr>`;
    return;
  }

  faturasFiltradas.forEach((fat) => {
    const tr = document.createElement("tr");
    let statusHtml = "";
    let catracaHtml = "";

    if (fat.status === "pago") {
      statusHtml = `<span class="badge-fin pago"><span class="material-symbols-rounded" style="font-size:14px">check_circle</span> Pago (${fat.metodo})</span>`;
      catracaHtml = `<span class="status-catraca ok"><span class="material-symbols-rounded" style="font-size:16px">lock_open</span> Liberada</span>`;
    } else if (fat.status === "pendente") {
      statusHtml = `<span class="badge-fin pendente"><span class="material-symbols-rounded" style="font-size:14px">schedule</span> A vencer</span>`;
      catracaHtml = `<span class="status-catraca ok"><span class="material-symbols-rounded" style="font-size:16px">lock_open</span> Liberada</span>`;
    } else {
      statusHtml = `<span class="badge-fin atrasado"><span class="material-symbols-rounded" style="font-size:14px">error</span> Vencida</span>`;
      catracaHtml = `<span class="status-catraca bloqueado"><span class="material-symbols-rounded" style="font-size:16px">block</span> Bloqueada</span>`;
    }

    const valorFormatado = fat.valor.toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
    const dataPartes = fat.vencimento.split("-");
    const dataFormatada = `${dataPartes[2]}/${dataPartes[1]}/${dataPartes[0]}`;

    tr.innerHTML = `
      <td>#${fat.id}</td>
      <td>
        <div class="project-title-cell">
          <div class="project-icon"><span class="material-symbols-rounded">person</span></div>
          <div class="project-info">
            <div class="project-title-text">${fat.aluno}</div>
            <div class="project-meta-text">CPF: ${fat.cpf}</div>
          </div>
        </div>
      </td>
      <td>${fat.plano}</td>
      <td>${dataFormatada}</td>
      <td><strong>${valorFormatado}</strong></td>
      <td>${statusHtml}</td>
      <td>${catracaHtml}</td>
      <td>
        <div class="tabela-acoes">
          ${
            fat.status !== "pago"
              ? `<button class="btn-acao receber" title="Dar baixa no pagamento" onclick="abrirModalPagamento(${fat.id})">
                   <span class="material-symbols-rounded" style="font-size:18px">check</span>
                 </button>`
              : ""
          }
          <button class="btn-acao" title="Enviar lembrete WhatsApp/Email" onclick="enviarLembrete(${fat.id})">
            <span class="material-symbols-rounded" style="font-size:18px">chat</span>
          </button>
          <button class="btn-acao excluir" title="Excluir fatura" onclick="excluirFatura(${fat.id})">
            <span class="material-symbols-rounded" style="font-size:18px">delete</span>
          </button>
        </div>
      </td>
    `;
    tbody.appendChild(tr);
  });
}

function atualizarMetricasFinanceiras() {
  let recebido = 0;
  let pendente = 0;
  let atrasado = 0;
  let qtdPago = 0;
  let qtdPendente = 0;
  let qtdAtrasado = 0;

  faturas.forEach((fat) => {
    if (fat.status === "pago") {
      recebido += fat.valor;
      qtdPago++;
    } else if (fat.status === "pendente") {
      pendente += fat.valor;
      qtdPendente++;
    } else if (fat.status === "atrasado") {
      atrasado += fat.valor;
      qtdAtrasado++;
    }
  });

  const elRec = document.getElementById("cardTotalRecebido");
  const elPen = document.getElementById("cardTotalPendente");
  const elAtr = document.getElementById("cardTotalAtrasado");
  const elQtdRec = document.getElementById("cardQtdRecebidos");
  const elQtdPen = document.getElementById("cardQtdPendentes");
  const elQtdAtr = document.getElementById("cardQtdAtrasados");
  const elBloq = document.getElementById("cardBloqueadosCatraca");

  if (elRec) elRec.textContent = recebido.toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
  if (elPen) elPen.textContent = pendente.toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
  if (elAtr) elAtr.textContent = atrasado.toLocaleString("pt-BR", { style: "currency", currency: "BRL" });

  if (elQtdRec) elQtdRec.textContent = `${qtdPago} mensalidades liquidadas`;
  if (elQtdPen) elQtdPen.textContent = `${qtdPendente} aguardando pagamento`;
  if (elQtdAtr) elQtdAtr.textContent = `${qtdAtrasado} faturas vencidas`;
  if (elBloq) elBloq.textContent = qtdAtrasado;

  const countTodos = document.getElementById("countTodos");
  const countPago = document.getElementById("countPago");
  const countPendente = document.getElementById("countPendente");
  const countAtrasado = document.getElementById("countAtrasado");

  if (countTodos) countTodos.textContent = faturas.length;
  if (countPago) countPago.textContent = qtdPago;
  if (countPendente) countPendente.textContent = qtdPendente;
  if (countAtrasado) countAtrasado.textContent = qtdAtrasado;
}

function filtrarStatus(status, botao) {
  filtroFinanceiroAtual = status;
  document.querySelectorAll(".btn-filtro").forEach((btn) => btn.classList.remove("active"));
  if (botao) botao.classList.add("active");
  renderizarTabela();
}

function filtrarFaturas() {
  renderizarTabela();
}

function abrirModalPagamento(id) {
  faturaSelecionadaId = id;
  const fat = faturas.find((f) => f.id === id);
  if (!fat) return;

  const elAluno = document.getElementById("modalAlunoNome");
  const elPlano = document.getElementById("modalPlanoValor");
  if (elAluno) elAluno.textContent = fat.aluno;
  if (elPlano) elPlano.textContent = `${fat.plano} - ${fat.valor.toLocaleString("pt-BR", { style: "currency", currency: "BRL" })}`;

  document.getElementById("modalPagamento")?.classList.add("active");
}

function fecharModalPagamento() {
  document.getElementById("modalPagamento")?.classList.remove("active");
  faturaSelecionadaId = null;
}

function confirmarBaixaPagamento() {
  if (!faturaSelecionadaId) return;

  const fat = faturas.find((f) => f.id === faturaSelecionadaId);
  const metodo = document.getElementById("metodoPagamento")?.value || "PIX";

  if (fat) {
    fat.status = "pago";
    fat.metodo = metodo;
  }

  fecharModalPagamento();
  renderizarTabela();
  atualizarMetricasFinanceiras();
}

function abrirModalNovaCobranca() {
  document.getElementById("modalNovaCobranca")?.classList.add("active");
}

function fecharModalNovaCobranca() {
  document.getElementById("modalNovaCobranca")?.classList.remove("active");
}

function salvarNovaCobranca(e) {
  e.preventDefault();

  const nome = document.getElementById("novoAluno").value.trim();
  const cpf = document.getElementById("novoCpf").value.trim();
  const planoSelect = document.getElementById("novoPlano").value.split("|");
  const planoNome = planoSelect[0];
  const planoValor = parseFloat(planoSelect[1]);
  const vencimento = document.getElementById("novoVencimento").value;

  const hoje = new Date().toISOString().split("T")[0];
  const statusInicial = vencimento < hoje ? "atrasado" : "pendente";

  const novaFatura = {
    id: faturas.length ? Math.max(...faturas.map((f) => f.id)) + 1 : 101,
    aluno: nome,
    cpf: cpf,
    plano: planoNome,
    valor: planoValor,
    vencimento: vencimento,
    status: statusInicial,
    metodo: null,
  };

  faturas.unshift(novaFatura);
  fecharModalNovaCobranca();
  renderizarTabela();
  atualizarMetricasFinanceiras();
  e.target.reset();
}

function excluirFatura(id) {
  if (confirm(`Deseja remover o lancamento #${id}?`)) {
    faturas = faturas.filter((f) => f.id !== id);
    renderizarTabela();
    atualizarMetricasFinanceiras();
  }
}

function enviarLembrete(id) {
  const fat = faturas.find((f) => f.id === id);
  if (fat) {
    alert(`Lembrete de cobranca disparado para ${fat.aluno} (Fatura #${fat.id} - ${fat.valor.toLocaleString("pt-BR", { style: "currency", currency: "BRL" })})`);
  }
}

function exportarRelatorio() {
  alert("Relatorio consolidado exportado em formato CSV!");
}

/* ==============================================================================
   5. GESTAO DE PLANOS
   ============================================================================== */
let planos = [
  { id: 1, codigo: "PLN-01", nome: "Iron Mensal Livre", periodo: "Mensal (30 dias)", valor: 130.0, modalidades: ["Musculacao", "Powerlifting"], descricao: "Acesso total a sala de forca e anilhas olimpicas", alunos: 62, ativo: true },
  { id: 2, codigo: "PLN-02", nome: "Full Combat & Iron", periodo: "Mensal (30 dias)", valor: 190.0, modalidades: ["Musculacao", "Boxe", "Jiu-Jitsu"], descricao: "Musculacao livre + tatame de artes marciais", alunos: 28, ativo: true },
  { id: 3, codigo: "PLN-03", nome: "Plano Trimestral", periodo: "Trimestral (90 dias)", valor: 350.0, modalidades: ["Musculacao", "Powerlifting", "Funcional"], descricao: "Renovacao a cada 3 meses com desconto especial", alunos: 54, ativo: true },
  { id: 4, codigo: "PLN-04", nome: "Anual Pesado", periodo: "Anual (365 dias)", valor: 1150.0, modalidades: ["Musculacao", "Powerlifting", "Cross Training", "Boxe"], descricao: "Acesso integral com tranca garantida de preco", alunos: 32, ativo: true },
  { id: 5, codigo: "PLN-05", nome: "Day Use (Diaria)", periodo: "Diaria (1 dia)", valor: 30.0, modalidades: ["Musculacao"], descricao: "Passe unico para atletas visitantes", alunos: 6, ativo: true },
];

function renderizarPlanos() {
  const tbody = document.getElementById("tabelaPlanos");
  const inputBusca = document.getElementById("inputBuscaPlano");
  const termo = inputBusca ? inputBusca.value.toLowerCase().trim() : "";

  if (!tbody) return;
  tbody.innerHTML = "";

  const planosFiltrados = planos.filter((p) => {
    return (
      p.nome.toLowerCase().includes(termo) ||
      p.codigo.toLowerCase().includes(termo) ||
      p.modalidades.some((m) => m.toLowerCase().includes(termo))
    );
  });

  if (planosFiltrados.length === 0) {
    tbody.innerHTML = `<tr><td colspan="8" class="tabela-sem-dados">Nenhum plano encontrado.</td></tr>`;
    return;
  }

  planosFiltrados.forEach((plano) => {
    const tr = document.createElement("tr");
    const badgesModalidades = plano.modalidades.map((m) => `<span class="tag-modalidade">${m}</span>`).join("");
    const valorFormatado = plano.valor.toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
    const statusBadge = plano.ativo
      ? `<span class="status-badge success">Ativo</span>`
      : `<span class="status-badge warning">Pausado</span>`;

    tr.innerHTML = `
      <td><strong>${plano.codigo}</strong></td>
      <td>
        <div class="project-title-cell">
          <div class="project-icon"><span class="material-symbols-rounded">fitness_center</span></div>
          <div class="project-info">
            <div class="project-title-text">${plano.nome}</div>
            <div class="project-meta-text">${plano.descricao || "Sem detalhes"}</div>
          </div>
        </div>
      </td>
      <td>${plano.periodo}</td>
      <td><strong>${valorFormatado}</strong></td>
      <td><div class="tag-modalidades-container">${badgesModalidades}</div></td>
      <td>${plano.alunos} atletas</td>
      <td>${statusBadge}</td>
      <td>
        <div class="tabela-acoes">
          <button class="btn-acao" title="Editar plano" onclick="editarPlano(${plano.id})">
            <span class="material-symbols-rounded" style="font-size:18px">edit</span>
          </button>
          <button class="btn-acao pausar" title="${plano.ativo ? "Pausar novas adesoes" : "Reativar plano"}" onclick="alternarStatusPlano(${plano.id})">
            <span class="material-symbols-rounded" style="font-size:18px">${plano.ativo ? "pause" : "play_arrow"}</span>
          </button>
          <button class="btn-acao excluir" title="Excluir plano" onclick="excluirPlano(${plano.id})">
            <span class="material-symbols-rounded" style="font-size:18px">delete</span>
          </button>
        </div>
      </td>
    `;
    tbody.appendChild(tr);
  });
}

function atualizarCardsResumoPlanos() {
  const planosAtivos = planos.filter((p) => p.ativo);
  const totalAlunos = planos.reduce((acc, p) => acc + p.alunos, 0);
  const receitaTotal = planos.reduce((acc, p) => acc + p.valor * p.alunos, 0);
  const ticketMedio = totalAlunos > 0 ? receitaTotal / totalAlunos : 0;

  let planoMaisPopular = { nome: "-", alunos: 0 };
  planos.forEach((p) => {
    if (p.alunos > planoMaisPopular.alunos) planoMaisPopular = p;
  });

  const cardAtv = document.getElementById("cardPlanosAtivos");
  const cardMat = document.getElementById("cardTotalMatriculados");
  const cardTkt = document.getElementById("cardTicketMedio");
  const cardPop = document.getElementById("cardPlanoPopular");
  const cardPopQtd = document.getElementById("cardPopularQtd");

  if (cardAtv) cardAtv.textContent = planosAtivos.length;
  if (cardMat) cardMat.textContent = totalAlunos;
  if (cardTkt) cardTkt.textContent = ticketMedio.toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
  if (cardPop) cardPop.textContent = planoMaisPopular.nome;
  if (cardPopQtd) cardPopQtd.textContent = `${planoMaisPopular.alunos} assinaturas ativas`;
}

function filtrarPlanos() {
  renderizarPlanos();
}

function abrirModalPlano() {
  document.getElementById("planoIdEdit").value = "";
  document.getElementById("modalPlanoTitulo").textContent = "Criar Novo Plano";
  document.getElementById("planoNome").value = "";
  document.getElementById("planoValor").value = "";
  document.getElementById("planoPeriodo").value = "Mensal (30 dias)";
  document.getElementById("planoDescricao").value = "";
  document.querySelectorAll('input[name="modalidade"]').forEach((cb) => (cb.checked = false));
  document.getElementById("modalPlano")?.classList.add("active");
}

function fecharModalPlano() {
  document.getElementById("modalPlano")?.classList.remove("active");
}

function editarPlano(id) {
  const plano = planos.find((p) => p.id === id);
  if (!plano) return;

  document.getElementById("planoIdEdit").value = plano.id;
  document.getElementById("modalPlanoTitulo").textContent = `Editar: ${plano.nome}`;
  document.getElementById("planoNome").value = plano.nome;
  document.getElementById("planoValor").value = plano.valor;
  document.getElementById("planoPeriodo").value = plano.periodo;
  document.getElementById("planoDescricao").value = plano.descricao || "";

  document.querySelectorAll('input[name="modalidade"]').forEach((cb) => {
    cb.checked = plano.modalidades.includes(cb.value);
  });

  document.getElementById("modalPlano")?.classList.add("active");
}

function salvarPlano(e) {
  e.preventDefault();

  const idEdit = document.getElementById("planoIdEdit").value;
  const nome = document.getElementById("planoNome").value.trim();
  const valor = parseFloat(document.getElementById("planoValor").value);
  const periodo = document.getElementById("planoPeriodo").value;
  const descricao = document.getElementById("planoDescricao").value.trim();

  const modalidadesSelecionadas = [];
  document.querySelectorAll('input[name="modalidade"]:checked').forEach((cb) => {
    modalidadesSelecionadas.push(cb.value);
  });

  if (modalidadesSelecionadas.length === 0) {
    alert("Selecione pelo menos uma modalidade inclusa para a catraca liberar o acesso.");
    return;
  }

  if (idEdit) {
    const plano = planos.find((p) => p.id === parseInt(idEdit, 10));
    if (plano) {
      plano.nome = nome;
      plano.valor = valor;
      plano.periodo = periodo;
      plano.descricao = descricao;
      plano.modalidades = modalidadesSelecionadas;
    }
  } else {
    const novoId = planos.length ? Math.max(...planos.map((p) => p.id)) + 1 : 1;
    const novoCodigo = `PLN-0${novoId}`;

    planos.push({
      id: novoId,
      codigo: novoCodigo,
      nome: nome,
      periodo: periodo,
      valor: valor,
      modalidades: modalidadesSelecionadas,
      descricao: descricao,
      alunos: 0,
      ativo: true,
    });
  }

  fecharModalPlano();
  renderizarPlanos();
  atualizarCardsResumoPlanos();
}

function alternarStatusPlano(id) {
  const plano = planos.find((p) => p.id === id);
  if (plano) {
    plano.ativo = !plano.ativo;
    renderizarPlanos();
    atualizarCardsResumoPlanos();
  }
}

function excluirPlano(id) {
  const plano = planos.find((p) => p.id === id);
  if (!plano) return;

  if (plano.alunos > 0) {
    alert(`Nao e possivel excluir o plano "${plano.nome}" pois existem ${plano.alunos} alunos matriculados nele.`);
    return;
  }

  if (confirm(`Deseja realmente excluir o plano "${plano.nome}"?`)) {
    planos = planos.filter((p) => p.id !== id);
    renderizarPlanos();
    atualizarCardsResumoPlanos();
  }
}

/* ==============================================================================
   6. GESTAO DA EQUIPA
   ============================================================================== */
let colaboradores = [
  { id: 1, nome: "Marcos Valerio Santos", cpf: "014.523.881-20", cargo: "Personal Trainer", cref: "014523-G/SE", turno: "Manha (06h as 14h)", telefone: "(79) 99812-4411", email: "marcos.treinador@rusticiron.com", salario: 3500.0, alunos: 14, especialidade: "Powerlifting & Levantamentos Basicos", ativo: true },
  { id: 2, nome: "Camila Duarte Lima", cpf: "009.832.771-40", cargo: "Personal Trainer", cref: "009832-G/SE", turno: "Tarde (14h as 22h)", telefone: "(79) 99120-7733", email: "camila.coach@rusticiron.com", salario: 3200.0, alunos: 11, especialidade: "Hipertrofia Feminina & Mobilidade", ativo: true },
  { id: 3, nome: "Lucas Andrade Costa", cpf: "721.445.112-90", cargo: "Instrutor de Musculacao", cref: "018221-G/SE", turno: "Noite / Fechamento (16h as 23h)", telefone: "(79) 98833-2211", email: "lucas.musculacao@rusticiron.com", salario: 2200.0, alunos: 28, especialidade: "Acompanhamento de Sala e Postura", ativo: true },
  { id: 4, nome: "Renata Bezerra Fontes", cpf: "554.120.998-33", cargo: "Recepcao", cref: "-", turno: "Manha (06h as 14h)", telefone: "(79) 99911-0022", email: "recepcao@rusticiron.com", salario: 1800.0, alunos: 0, especialidade: "Controle de Acessos & Atendimento", ativo: true },
  { id: 5, nome: "Jorge Maciel Santana", cpf: "331.882.441-05", cargo: "Manutencao & Cargas", cref: "-", turno: "Integral / Comercial (08h as 18h)", telefone: "(79) 98144-5566", email: "manutencao@rusticiron.com", salario: 2000.0, alunos: 0, especialidade: "Lubrificacao de Guias e Equipamentos", ativo: true },
];

let filtroCargoAtual = "todos";

function renderizarEquipe() {
  const tbody = document.getElementById("tabelaEquipe");
  const inputBusca = document.getElementById("inputBuscaEquipe");
  const termo = inputBusca ? inputBusca.value.toLowerCase().trim() : "";

  if (!tbody) return;
  tbody.innerHTML = "";

  const filtrados = colaboradores.filter((colab) => {
    const matchCargo = filtroCargoAtual === "todos" || colab.cargo === filtroCargoAtual;
    const matchBusca =
      colab.nome.toLowerCase().includes(termo) ||
      colab.cargo.toLowerCase().includes(termo) ||
      (colab.cref && colab.cref.toLowerCase().includes(termo)) ||
      colab.telefone.includes(termo);

    return matchCargo && matchBusca;
  });

  if (filtrados.length === 0) {
    tbody.innerHTML = `<tr><td colspan="8" class="tabela-sem-dados">Nenhum colaborador encontrado para este filtro.</td></tr>`;
    return;
  }

  filtrados.forEach((c) => {
    const tr = document.createElement("tr");
    const statusBadge = c.ativo
      ? `<span class="status-badge success">Ativo</span>`
      : `<span class="status-badge warning">Afastado / Ferias</span>`;

    tr.innerHTML = `
      <td>
        <div class="project-title-cell">
          <div class="project-icon"><span class="material-symbols-rounded">badge</span></div>
          <div class="project-info">
            <div class="project-title-text">${c.nome}</div>
            <div class="project-meta-text">${c.email}</div>
          </div>
        </div>
      </td>
      <td><strong>${c.cargo}</strong></td>
      <td>${c.cref || "-"}</td>
      <td>${c.turno}</td>
      <td>${c.telefone}</td>
      <td>${c.alunos > 0 ? `${c.alunos} atletas` : "-"}</td>
      <td>${statusBadge}</td>
      <td>
        <div class="tabela-acoes">
          <button class="btn-acao" title="Editar dados" onclick="editarColaborador(${c.id})">
            <span class="material-symbols-rounded" style="font-size:18px">edit</span>
          </button>
          <button class="btn-acao pausar" title="${c.ativo ? "Definir como afastado" : "Reativar colaborador"}" onclick="alternarStatusColaborador(${c.id})">
            <span class="material-symbols-rounded" style="font-size:18px">${c.ativo ? "pause" : "play_arrow"}</span>
          </button>
          <button class="btn-acao excluir" title="Remover da equipe" onclick="excluirColaborador(${c.id})">
            <span class="material-symbols-rounded" style="font-size:18px">delete</span>
          </button>
        </div>
      </td>
    `;
    tbody.appendChild(tr);
  });
}

function atualizarCardsEquipe() {
  const total = colaboradores.length;
  const personais = colaboradores.filter((c) => c.cargo.includes("Personal") || c.cargo.includes("Coach"));
  const totalAlunos = colaboradores.reduce((acc, c) => acc + (Number(c.alunos) || 0), 0);
  const folhaTotal = colaboradores.reduce((acc, c) => acc + (Number(c.salario) || 0), 0);
  const emTurno = colaboradores.filter((c) => c.ativo).length;

  const elTotal = document.getElementById("cardTotalColaboradores");
  const elPersonais = document.getElementById("cardTotalPersonais");
  const elAlunos = document.getElementById("cardTotalAlunosAtendidos");
  const elFolha = document.getElementById("cardFolhaPagamento");
  const elTurno = document.getElementById("cardEmTurno");

  if (elTotal) elTotal.textContent = total;
  if (elPersonais) elPersonais.textContent = personais.length;
  if (elAlunos) elAlunos.textContent = `${totalAlunos} atletas acompanhados`;
  if (elFolha) elFolha.textContent = folhaTotal.toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
  if (elTurno) elTurno.textContent = emTurno;

  const countTodos = document.getElementById("countTodosCargos");
  const countPersonais = document.getElementById("countPersonais");
  const countInstrutores = document.getElementById("countInstrutores");
  const countRecepcao = document.getElementById("countRecepcao");
  const countManutencao = document.getElementById("countManutencao");

  if (countTodos) countTodos.textContent = total;
  if (countPersonais) countPersonais.textContent = colaboradores.filter((c) => c.cargo === "Personal Trainer").length;
  if (countInstrutores) countInstrutores.textContent = colaboradores.filter((c) => c.cargo === "Instrutor de Musculacao").length;
  if (countRecepcao) countRecepcao.textContent = colaboradores.filter((c) => c.cargo === "Recepcao").length;
  if (countManutencao) countManutencao.textContent = colaboradores.filter((c) => c.cargo === "Manutencao & Cargas").length;
}

function abrirModalEquipe() {
  document.getElementById("colaboradorIdEdit").value = "";
  document.getElementById("formEquipe").reset();
  document.getElementById("modalTituloEquipe").textContent = "Cadastrar Colaborador";
  document.getElementById("modalEquipe")?.classList.add("active");
}

function fecharModalEquipe() {
  document.getElementById("modalEquipe")?.classList.remove("active");
}

function verificarExigenciaCref(cargo) {
  const campoCref = document.getElementById("eqCref");
  if (!campoCref) return;
  if (cargo.includes("Personal") || cargo.includes("Instrutor") || cargo.includes("Coach")) {
    campoCref.placeholder = "000000-G/SE (Obrigatorio)";
    campoCref.required = true;
  } else {
    campoCref.placeholder = "Nao aplicavel";
    campoCref.required = false;
  }
}

function editarColaborador(id) {
  const c = colaboradores.find((item) => item.id === id);
  if (!c) return;

  document.getElementById("colaboradorIdEdit").value = c.id;
  document.getElementById("modalTituloEquipe").textContent = `Editar: ${c.nome}`;
  document.getElementById("eqNome").value = c.nome;
  document.getElementById("eqCpf").value = c.cpf;
  document.getElementById("eqCargo").value = c.cargo;
  document.getElementById("eqCref").value = c.cref === "-" ? "" : c.cref;
  document.getElementById("eqTurno").value = c.turno;
  document.getElementById("eqTelefone").value = c.telefone;
  document.getElementById("eqEmail").value = c.email;
  document.getElementById("eqSalario").value = c.salario || "";
  document.getElementById("eqAlunos").value = c.alunos || 0;
  document.getElementById("eqEspecialidade").value = c.especialidade || "";

  verificarExigenciaCref(c.cargo);
  document.getElementById("modalEquipe")?.classList.add("active");
}

function salvarColaborador(e) {
  e.preventDefault();

  const idEdit = document.getElementById("colaboradorIdEdit").value;
  const nome = document.getElementById("eqNome").value.trim();
  const cpf = document.getElementById("eqCpf").value.trim();
  const cargo = document.getElementById("eqCargo").value;
  const cref = document.getElementById("eqCref").value.trim() || "-";
  const turno = document.getElementById("eqTurno").value;
  const tel = document.getElementById("eqTelefone").value.trim();
  const email = document.getElementById("eqEmail").value.trim();
  const salario = parseFloat(document.getElementById("eqSalario").value) || 0;
  const alunosCount = parseInt(document.getElementById("eqAlunos").value, 10) || 0;
  const esp = document.getElementById("eqEspecialidade").value.trim();

  if (idEdit) {
    const c = colaboradores.find((item) => item.id === parseInt(idEdit, 10));
    if (c) {
      c.nome = nome;
      c.cpf = cpf;
      c.cargo = cargo;
      c.cref = cref;
      c.turno = turno;
      c.telefone = tel;
      c.email = email;
      c.salario = salario;
      c.alunos = alunosCount;
      c.especialidade = esp;
    }
  } else {
    const novoId = colaboradores.length ? Math.max(...colaboradores.map((item) => item.id)) + 1 : 1;
    colaboradores.unshift({
      id: novoId,
      nome: nome,
      cpf: cpf,
      cargo: cargo,
      cref: cref,
      turno: turno,
      telefone: tel,
      email: email,
      salario: salario,
      alunos: alunosCount,
      especialidade: esp,
      ativo: true,
    });
  }

  fecharModalEquipe();
  renderizarEquipe();
  atualizarCardsEquipe();
}

function alternarStatusColaborador(id) {
  const c = colaboradores.find((item) => item.id === id);
  if (c) {
    c.ativo = !c.ativo;
    renderizarEquipe();
    atualizarCardsEquipe();
  }
}

function excluirColaborador(id) {
  const c = colaboradores.find((item) => item.id === id);
  if (!c) return;

  if (confirm(`Deseja remover ${c.nome} do quadro de colaboradores?`)) {
    colaboradores = colaboradores.filter((item) => item.id !== id);
    renderizarEquipe();
    atualizarCardsEquipe();
  }
}

/* ==============================================================================
   7. PARQUE DE EQUIPAMENTOS
   ============================================================================== */
let equipamentos = [
  { id: 1, codigo: "EQP-01", nome: "Gaiola Monolift / Squat Rack", setor: "Powerlifting", quantidade: 3, ultima_manutencao: "2026-09-12", proxima_revisao: "2026-10-15", estado: "Excelente", fabricante: "Rustic Heavy Steel", observacoes: "Pinos e travas engraxados quinzenalmente." },
  { id: 2, codigo: "EQP-02", nome: "Barras Olimpicas 20kg (Rolamentadas)", setor: "Pesos Livres", quantidade: 12, ultima_manutencao: "2026-09-18", proxima_revisao: "2026-10-20", estado: "Excelente", fabricante: "Eleiko Training", observacoes: "Oleo mineral nos rolamentos." },
  { id: 3, codigo: "EQP-03", nome: "Leg Press 45 Graus Blindado", setor: "Musculacao Pesada", quantidade: 2, ultima_manutencao: "2026-08-02", proxima_revisao: "2026-10-02", estado: "Revisao Proxima", fabricante: "Hammer Iron", observacoes: "Lubrificar guias lineares." },
  { id: 4, codigo: "EQP-04", nome: "Banco de Supino Reto com Plataforma", setor: "Powerlifting", quantidade: 4, ultima_manutencao: "2026-09-01", proxima_revisao: "2026-11-01", estado: "Excelente", fabricante: "Rustic Heavy Steel", observacoes: "Estofamento reforcado." },
  { id: 5, codigo: "EQP-05", nome: "Esteira Curva Mecanica Sem Motor", setor: "Cardio & Resistencia", quantidade: 3, ultima_manutencao: "2026-07-20", proxima_revisao: "2026-09-25", estado: "Em Manutencao", fabricante: "Woodway Curve", observacoes: "Substituicao da correia." },
  { id: 6, codigo: "EQP-06", nome: "Crossover com Polias Ajustaveis Duplas", setor: "Musculacao Pesada", quantidade: 1, ultima_manutencao: "2026-08-15", proxima_revisao: "2026-10-10", estado: "Revisao Proxima", fabricante: "Iron Force", observacoes: "Cabos de aco inspecionados." },
];

let filtroSetorAtual = "todos";

function renderizarEquipamentos() {
  const tbody = document.getElementById("tabelaEquipamentos");
  const termo = document.getElementById("inputBuscaEquipamentos")?.value.toLowerCase().trim() || "";

  if (!tbody) return;
  tbody.innerHTML = "";

  const filtrados = equipamentos.filter((eq) => {
    const matchSetor = filtroSetorAtual === "todos" || eq.setor === filtroSetorAtual;
    const matchBusca =
      eq.nome.toLowerCase().includes(termo) ||
      eq.codigo.toLowerCase().includes(termo) ||
      eq.setor.toLowerCase().includes(termo) ||
      eq.fabricante.toLowerCase().includes(termo);

    return matchSetor && matchBusca;
  });

  if (filtrados.length === 0) {
    tbody.innerHTML = `<tr><td colspan="8" class="tabela-sem-dados">Nenhum equipamento catalogado para este filtro.</td></tr>`;
    return;
  }

  filtrados.forEach((item) => {
    const tr = document.createElement("tr");
    let statusBadge = "";
    if (item.estado === "Excelente") {
      statusBadge = `<span class="status-badge success">Excelente</span>`;
    } else if (item.estado === "Revisao Proxima") {
      statusBadge = `<span class="status-badge warning">Revisao Proxima</span>`;
    } else {
      statusBadge = `<span class="status-badge error">${item.estado}</span>`;
    }

    const formatarData = (dataStr) => {
      if (!dataStr) return "-";
      const p = dataStr.split("-");
      return `${p[2]}/${p[1]}/${p[0]}`;
    };

    tr.innerHTML = `
      <td><strong>${item.codigo}</strong></td>
      <td>
        <div class="project-title-cell">
          <div class="project-icon"><span class="material-symbols-rounded">fitness_center</span></div>
          <div class="project-info">
            <div class="project-title-text">${item.nome}</div>
            <div class="project-meta-text">${item.observacoes || item.fabricante || "Sem notas"}</div>
          </div>
        </div>
      </td>
      <td><span class="status-badge info">${item.setor}</span></td>
      <td><strong>${item.quantidade}</strong> un.</td>
      <td>${formatarData(item.ultima_manutencao)}</td>
      <td>${formatarData(item.proxima_revisao)}</td>
      <td>${statusBadge}</td>
      <td>
        <div class="tabela-acoes">
          <button class="btn-acao manutencao" title="Registrar manutencao efetuada hoje" onclick="concluirManutencaoHoje(${item.id})">
            <span class="material-symbols-rounded" style="font-size:18px">build</span>
          </button>
          <button class="btn-acao" title="Editar equipamento" onclick="editarEquipamento(${item.id})">
            <span class="material-symbols-rounded" style="font-size:18px">edit</span>
          </button>
          <button class="btn-acao excluir" title="Excluir do inventario" onclick="excluirEquipamento(${item.id})">
            <span class="material-symbols-rounded" style="font-size:18px">delete</span>
          </button>
        </div>
      </td>
    `;
    tbody.appendChild(tr);
  });
}

function atualizarCardsEquipamentos() {
  const totalUnidades = equipamentos.reduce((acc, eq) => acc + (Number(eq.quantidade) || 0), 0);
  const operacionais = equipamentos.filter((eq) => eq.estado === "Excelente").reduce((acc, eq) => acc + eq.quantidade, 0);
  const revisaoProxima = equipamentos.filter((eq) => eq.estado === "Revisao Proxima").reduce((acc, eq) => acc + eq.quantidade, 0);
  const interditados = equipamentos.filter((eq) => eq.estado === "Em Manutencao" || eq.estado === "Danificado").reduce((acc, eq) => acc + eq.quantidade, 0);

  const elTotal = document.getElementById("cardTotalItens");
  const elTipos = document.getElementById("cardQtdTipos");
  const elOperacionais = document.getElementById("cardOperacionais");
  const elRevisao = document.getElementById("cardRevisaoProxima");
  const elInterditados = document.getElementById("cardInterditados");

  if (elTotal) elTotal.textContent = totalUnidades;
  if (elTipos) elTipos.textContent = `${equipamentos.length} categorias cadastradas`;
  if (elOperacionais) elOperacionais.textContent = operacionais;
  if (elRevisao) elRevisao.textContent = revisaoProxima;
  if (elInterditados) elInterditados.textContent = interditados;

  const countTodos = document.getElementById("countTodosSetores");
  const countPower = document.getElementById("countPowerlifting");
  const countPesos = document.getElementById("countPesosLivres");
  const countMusc = document.getElementById("countMusculacao");
  const countCardio = document.getElementById("countCardio");

  if (countTodos) countTodos.textContent = equipamentos.length;
  if (countPower) countPower.textContent = equipamentos.filter((eq) => eq.setor === "Powerlifting").length;
  if (countPesos) countPesos.textContent = equipamentos.filter((eq) => eq.setor === "Pesos Livres").length;
  if (countMusc) countMusc.textContent = equipamentos.filter((eq) => eq.setor === "Musculacao Pesada").length;
  if (countCardio) countCardio.textContent = equipamentos.filter((eq) => eq.setor === "Cardio & Resistencia").length;
}

function abrirModalEquipamento() {
  document.getElementById("equipamentoIdEdit").value = "";
  document.getElementById("formEquipamento").reset();
  document.getElementById("modalTituloEquipamento").textContent = "Registrar Equipamento";
  document.getElementById("modalEquipamento")?.classList.add("active");
}

function fecharModalEquipamento() {
  document.getElementById("modalEquipamento")?.classList.remove("active");
}

function editarEquipamento(id) {
  const eq = equipamentos.find((item) => item.id === id);
  if (!eq) return;

  document.getElementById("equipamentoIdEdit").value = eq.id;
  document.getElementById("modalTituloEquipamento").textContent = `Editar: ${eq.nome}`;
  document.getElementById("eqNomeAparelho").value = eq.nome;
  document.getElementById("eqSetor").value = eq.setor;
  document.getElementById("eqQuantidade").value = eq.quantidade;
  document.getElementById("eqUltimaManutencao").value = eq.ultima_manutencao;
  document.getElementById("eqProximaRevisao").value = eq.proxima_revisao;
  document.getElementById("eqEstado").value = eq.estado;
  document.getElementById("eqFabricante").value = eq.fabricante || "";
  document.getElementById("eqObservacoes").value = eq.observacoes || "";

  document.getElementById("modalEquipamento")?.classList.add("active");
}

function salvarEquipamento(e) {
  e.preventDefault();

  const idEdit = document.getElementById("equipamentoIdEdit").value;
  const nome = document.getElementById("eqNomeAparelho").value.trim();
  const setor = document.getElementById("eqSetor").value;
  const quantidade = parseInt(document.getElementById("eqQuantidade").value, 10) || 1;
  const ultimaManutencao = document.getElementById("eqUltimaManutencao").value;
  const proximaRevisao = document.getElementById("eqProximaRevisao").value;
  const estado = document.getElementById("eqEstado").value;
  const fabricante = document.getElementById("eqFabricante").value.trim();
  const observacoes = document.getElementById("eqObservacoes").value.trim();

  if (idEdit) {
    const eq = equipamentos.find((item) => item.id === parseInt(idEdit, 10));
    if (eq) {
      eq.nome = nome;
      eq.setor = setor;
      eq.quantidade = quantidade;
      eq.ultima_manutencao = ultimaManutencao;
      eq.proxima_revisao = proximaRevisao;
      eq.estado = estado;
      eq.fabricante = fabricante;
      eq.observacoes = observacoes;
    }
  } else {
    const novoId = equipamentos.length ? Math.max(...equipamentos.map((item) => item.id)) + 1 : 1;
    const novoCodigo = `EQP-${novoId < 10 ? "0" + novoId : novoId}`;

    equipamentos.unshift({
      id: novoId,
      codigo: novoCodigo,
      nome: nome,
      setor: setor,
      quantidade: quantidade,
      ultima_manutencao: ultimaManutencao,
      proxima_revisao: proximaRevisao,
      estado: estado,
      fabricante: fabricante,
      observacoes: observacoes,
    });
  }

  fecharModalEquipamento();
  renderizarEquipamentos();
  atualizarCardsEquipamentos();
}

function concluirManutencaoHoje(id) {
  const eq = equipamentos.find((item) => item.id === id);
  if (!eq) return;

  const hoje = new Date().toISOString().split("T")[0];
  const proxima = new Date();
  proxima.setDate(proxima.getDate() + 45);
  const proximaStr = proxima.toISOString().split("T")[0];

  if (confirm(`Confirmar manutencao preventiva concluida hoje para "${eq.nome}"?\nO estado sera alterado para Excelente e o prazo renovado.`)) {
    eq.ultima_manutencao = hoje;
    eq.proxima_revisao = proximaStr;
    eq.estado = "Excelente";
    renderizarEquipamentos();
    atualizarCardsEquipamentos();
  }
}

function excluirEquipamento(id) {
  const eq = equipamentos.find((item) => item.id === id);
  if (!eq) return;

  if (confirm(`Deseja realmente remover o registro de "${eq.nome}" do inventario?`)) {
    equipamentos = equipamentos.filter((item) => item.id !== id);
    renderizarEquipamentos();
    atualizarCardsEquipamentos();
  }
}

/* ==============================================================================
   8. CONFIGURACOES & PARAMETROS
   ============================================================================== */
function carregarConfiguracoesSalvas() {
  const salvo = localStorage.getItem("rustic_iron_config");
  if (!salvo) return;

  try {
    const cfg = JSON.parse(salvo);

    if (cfg.razaoSocial && document.getElementById("cfgRazaoSocial")) document.getElementById("cfgRazaoSocial").value = cfg.razaoSocial;
    if (cfg.nomeFantasia && document.getElementById("cfgNomeFantasia")) document.getElementById("cfgNomeFantasia").value = cfg.nomeFantasia;
    if (cfg.cnpj && document.getElementById("cfgCnpj")) document.getElementById("cfgCnpj").value = cfg.cnpj;
    if (cfg.telefone && document.getElementById("cfgTelefone")) document.getElementById("cfgTelefone").value = cfg.telefone;
    if (cfg.email && document.getElementById("cfgEmail")) document.getElementById("cfgEmail").value = cfg.email;
    if (cfg.endereco && document.getElementById("cfgEndereco")) document.getElementById("cfgEndereco").value = cfg.endereco;
    if (cfg.capacidade && document.getElementById("cfgCapacidade")) document.getElementById("cfgCapacidade").value = cfg.capacidade;
    if (cfg.horario && document.getElementById("cfgHorario")) document.getElementById("cfgHorario").value = cfg.horario;

    if (cfg.protocoloCatraca && document.getElementById("cfgProtocoloCatraca")) document.getElementById("cfgProtocoloCatraca").value = cfg.protocoloCatraca;
    if (cfg.ipCatraca && document.getElementById("cfgIpCatraca")) document.getElementById("cfgIpCatraca").value = cfg.ipCatraca;
    if (cfg.portaCatraca && document.getElementById("cfgPortaCatraca")) document.getElementById("cfgPortaCatraca").value = cfg.portaCatraca;
    if (cfg.timeoutGiro && document.getElementById("cfgTimeoutGiro")) document.getElementById("cfgTimeoutGiro").value = cfg.timeoutGiro;
    if (cfg.toleranciaAtraso && document.getElementById("cfgToleranciaAtraso")) document.getElementById("cfgToleranciaAtraso").value = cfg.toleranciaAtraso;
    if (cfg.beep && document.getElementById("cfgBeep")) document.getElementById("cfgBeep").value = cfg.beep;
    if (typeof cfg.saidaLivre === "boolean" && document.getElementById("cfgLiberarSaidaLivre")) document.getElementById("cfgLiberarSaidaLivre").checked = cfg.saidaLivre;

    if (cfg.tipoPix && document.getElementById("cfgTipoPix")) document.getElementById("cfgTipoPix").value = cfg.tipoPix;
    if (cfg.chavePix && document.getElementById("cfgChavePix")) document.getElementById("cfgChavePix").value = cfg.chavePix;
    if (cfg.bancoPix && document.getElementById("cfgBancoPix")) document.getElementById("cfgBancoPix").value = cfg.bancoPix;
    if (cfg.diasAntecedencia && document.getElementById("cfgDiasAntecedencia")) document.getElementById("cfgDiasAntecedencia").value = cfg.diasAntecedencia;
    if (cfg.multa && document.getElementById("cfgMulta")) document.getElementById("cfgMulta").value = cfg.multa;
    if (cfg.juros && document.getElementById("cfgJuros")) document.getElementById("cfgJuros").value = cfg.juros;

    if (typeof cfg.notifWhatsapp === "boolean" && document.getElementById("cfgNotifWhatsapp")) document.getElementById("cfgNotifWhatsapp").checked = cfg.notifWhatsapp;
    if (typeof cfg.notifEmail === "boolean" && document.getElementById("cfgNotifEmail")) document.getElementById("cfgNotifEmail").checked = cfg.notifEmail;
    if (typeof cfg.notifManutencao === "boolean" && document.getElementById("cfgNotifManutencao")) document.getElementById("cfgNotifManutencao").checked = cfg.notifManutencao;
  } catch (e) {
    console.error("Erro ao carregar configuracoes salvas:", e);
  }
}

function salvarTodasConfiguracoes() {
  const dadosConfig = {
    razaoSocial: document.getElementById("cfgRazaoSocial")?.value.trim(),
    nomeFantasia: document.getElementById("cfgNomeFantasia")?.value.trim(),
    cnpj: document.getElementById("cfgCnpj")?.value.trim(),
    telefone: document.getElementById("cfgTelefone")?.value.trim(),
    email: document.getElementById("cfgEmail")?.value.trim(),
    endereco: document.getElementById("cfgEndereco")?.value.trim(),
    capacidade: document.getElementById("cfgCapacidade")?.value,
    horario: document.getElementById("cfgHorario")?.value.trim(),

    protocoloCatraca: document.getElementById("cfgProtocoloCatraca")?.value,
    ipCatraca: document.getElementById("cfgIpCatraca")?.value.trim(),
    portaCatraca: document.getElementById("cfgPortaCatraca")?.value,
    timeoutGiro: document.getElementById("cfgTimeoutGiro")?.value,
    toleranciaAtraso: document.getElementById("cfgToleranciaAtraso")?.value,
    beep: document.getElementById("cfgBeep")?.value,
    saidaLivre: document.getElementById("cfgLiberarSaidaLivre")?.checked,

    tipoPix: document.getElementById("cfgTipoPix")?.value,
    chavePix: document.getElementById("cfgChavePix")?.value.trim(),
    bancoPix: document.getElementById("cfgBancoPix")?.value.trim(),
    diasAntecedencia: document.getElementById("cfgDiasAntecedencia")?.value,
    multa: document.getElementById("cfgMulta")?.value,
    juros: document.getElementById("cfgJuros")?.value,

    notifWhatsapp: document.getElementById("cfgNotifWhatsapp")?.checked,
    notifEmail: document.getElementById("cfgNotifEmail")?.checked,
    notifManutencao: document.getElementById("cfgNotifManutencao")?.checked,
  };

  localStorage.setItem("rustic_iron_config", JSON.stringify(dadosConfig));
  mostrarToast("Parametros do sistema salvos com sucesso!");
}

function exportarBackup() {
  const dados = {
    dataExportacao: new Date().toISOString(),
    configuracoes: localStorage.getItem("rustic_iron_config") || {},
  };

  const blob = new Blob([JSON.stringify(dados, null, 2)], { type: "application/json" });
  const url = URL.createObjectURL(blob);
  const a = document.createElement("a");
  a.href = url;
  a.download = `backup_rustic_iron_${new Date().toISOString().split("T")[0]}.json`;
  a.click();
  URL.revokeObjectURL(url);
}

function restaurarPadroes() {
  if (confirm("Deseja restaurar as configuracoes padrao? Essa acao resetara os parametros de catraca e faturamento.")) {
    localStorage.removeItem("rustic_iron_config");
    mostrarToast("Parametros redefinidos. Recarregando pagina...");
    setTimeout(() => location.reload(), 1200);
  }
}

function mostrarToast(mensagem) {
  const toast = document.getElementById("toastAviso");
  if (!toast) return;

  toast.textContent = mensagem;
  toast.classList.add("show");

  setTimeout(() => {
    toast.classList.remove("show");
  }, 3500);
}

/* ==============================================================================
   9. INICIALIZACAO CENTRAL (DOM READY)
   ============================================================================== */
document.addEventListener("DOMContentLoaded", () => {
  inicializarTema();

  const userMenu = document.getElementById("userMenu");
  const userTrigger = document.getElementById("user-menu-trigger");
  if (userTrigger && userMenu) {
    userTrigger.addEventListener("click", alternarMenuUtilizador);
  }

  document.addEventListener("click", (e) => {
    if (userMenu && !userMenu.contains(e.target)) {
      userMenu.classList.remove("active");
    }
  });

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && userMenu && userMenu.classList.contains("active")) {
      userMenu.classList.remove("active");
    }
  });

  const dashboardSidebar = document.getElementById("dashboardSidebar");
  const dashboardSidebarOverlay = document.getElementById("dashboardSidebarOverlay");
  sidebarCollapsed = localStorage.getItem("dashboard-sidebar-collapsed") === "true";
  dashboardSidebar?.classList.toggle("collapsed", sidebarCollapsed);

  document.querySelectorAll(".dashboard-sidebar-toggle").forEach((toggle) => {
    toggle.addEventListener("click", alternarSidebar);
  });
  dashboardSidebarOverlay?.addEventListener("click", fecharSidebarMobile);

  const mobileSearchBtn = document.getElementById("mobileSearchBtn");
  const searchClose = document.getElementById("searchClose");
  mobileSearchBtn?.addEventListener("click", alternarPesquisaMobile);
  searchClose?.addEventListener("click", fecharPesquisaMobile);

  renderizarAlunos();
  atualizarCardsAlunos();

  renderizarTabela();
  atualizarMetricasFinanceiras();

  renderizarPlanos();
  atualizarCardsResumoPlanos();

  renderizarEquipe();
  atualizarCardsEquipe();

  renderizarEquipamentos();
  atualizarCardsEquipamentos();

  carregarConfiguracoesSalvas();

  document.querySelectorAll(".btn-filtro-cargo").forEach((btn) => {
    btn.addEventListener("click", () => {
      document.querySelectorAll(".btn-filtro-cargo").forEach((b) => b.classList.remove("active"));
      btn.classList.add("active");
      filtroCargoAtual = btn.getAttribute("data-cargo");
      renderizarEquipe();
    });
  });

  document.querySelectorAll(".btn-filtro-setor").forEach((btn) => {
    btn.addEventListener("click", () => {
      document.querySelectorAll(".btn-filtro-setor").forEach((b) => b.classList.remove("active"));
      btn.classList.add("active");
      filtroSetorAtual = btn.getAttribute("data-setor");
      renderizarEquipamentos();
    });
  });

  const botoesConfig = document.querySelectorAll(".config-tab-btn");
  const paineisConfig = document.querySelectorAll(".config-panel");
  botoesConfig.forEach((btn) => {
    btn.addEventListener("click", () => {
      botoesConfig.forEach((b) => b.classList.remove("active"));
      paineisConfig.forEach((p) => p.classList.remove("active"));
      btn.classList.add("active");
      const tabAlvo = btn.getAttribute("data-tab");
      const painel = document.getElementById(tabAlvo);
      if (painel) painel.classList.add("active");
    });
  });

  document.getElementById("btnNovoColaborador")?.addEventListener("click", abrirModalEquipe);
  document.getElementById("btnFecharModalEquipe")?.addEventListener("click", fecharModalEquipe);
  document.getElementById("btnCancelarModalEquipe")?.addEventListener("click", fecharModalEquipe);
  document.getElementById("inputBuscaEquipe")?.addEventListener("input", renderizarEquipe);

  document.getElementById("btnNovoEquipamento")?.addEventListener("click", abrirModalEquipamento);
  document.getElementById("btnFecharModalEquipamento")?.addEventListener("click", fecharModalEquipamento);
  document.getElementById("btnCancelarModalEquipamento")?.addEventListener("click", fecharModalEquipamento);
  document.getElementById("inputBuscaEquipamentos")?.addEventListener("input", renderizarEquipamentos);

  document.getElementById("btnSalvarGeral")?.addEventListener("click", salvarTodasConfiguracoes);
});