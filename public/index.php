<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rustic Iron - Sistema de Gestão para Academias</title>
    <!-- Boxicons -->
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Google+Sans:wght@400;500;700;800&family=Oswald:wght@600;700&display=swap" rel="stylesheet">
    <!-- Folha de Estilo Global -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <!-- NAVBAR TOPO -->
    <header class="navbar">
        <div class="nav-container">
            <a href="index.php" class="logo">
                <i class='bx bx-dumbbell'></i>
                <span>Rustic Iron</span>
            </a>

            <nav class="nav-links">
                <a href="#funcionalidades">Funcionalidades</a>
                <a href="#planos">Planos</a>
                <a href="#sobre">Sobre Nós</a>
                <a href="#contato">Contato</a>
            </nav>

            <div class="nav-actions">
                <a href="auth/auth.php" class="btn-login-nav">Login</a>
                <a href="#contato" class="btn-consultor">Falar com Consultor</a>
            </div>
        </div>
    </header>

    <!-- ÁREA HERO -->
    <main class="hero-section">
        <div class="hero-container">
            <!-- Texto e Botões -->
            <div class="hero-content">
                <h1>O controle total da sua academia, em um só lugar.</h1>
                <p>Descubra o sistema que gerencia alunos, finanças, treinos e acessos com máxima eficiência.</p>
                
                <div class="hero-buttons">
                    <!-- Botão alterado para Planos -->
                    <a href="#planos" class="btn-experimentar">Planos</a>
                    <!-- Botão alterado para abrir a Demo do Sistema -->
                    <a href="./demo/demo.php" class="link-demonstracao">
                        <i class='bx bx-play-circle'></i> Ver Demonstração
                    </a>
                </div>
            </div>

            <!-- Imagem Central dos Atletas -->
            <div class="hero-illustration">
                <img src="assets/img/hero/atletas.png" alt="Rustic Iron Atletas" class="img-athletes">
            </div>
        </div>

        <!-- SEÇÃO DE CARDS DE FUNCIONALIDADES (Sem a imagem gigante embaixo!) -->
        <section class="features-summary" id="funcionalidades">
            <h2 class="features-title">Tudo o que você precisa para gerenciar</h2>
            
            <div class="features-grid">
                <!-- 1. Controle de Acesso -->
                <div class="feature-card card-orange" onclick="window.location.href='demo.php?tab=acesso'">
                    <div class="card-text">
                        <i class='bx bx-fingerprint card-mini-icon'></i>
                        <h3>Controle de<br>Acesso</h3>
                    </div>
                    <div class="card-art">
                        <i class='bx bx-door-open main-card-icon'></i>
                    </div>
                </div>

                <!-- 2. Gestão Financeira -->
                <div class="feature-card card-green" onclick="window.location.href='demo.php?tab=financeiro'">
                    <div class="card-text">
                        <i class='bx bx-dollar-circle card-mini-icon'></i>
                        <h3>Gestão<br>Financeira</h3>
                    </div>
                    <div class="card-art">
                        <span class="symbol-art">$</span>
                    </div>
                </div>

                <!-- 3. Fichas de Treino -->
                <div class="feature-card card-olive" onclick="window.location.href='demo.php?tab=treinos'">
                    <div class="card-text">
                        <i class='bx bx-sort-alt-2 card-mini-icon'></i>
                        <h3>Fichas de<br>Treino</h3>
                    </div>
                    <div class="card-art">
                        <i class='bx bx-task main-card-icon'></i>
                    </div>
                </div>

                <!-- 4. Painel do Aluno -->
                <div class="feature-card card-purple" onclick="window.location.href='demo.php?tab=aluno'">
                    <div class="card-text">
                        <i class='bx bx-user card-mini-icon'></i>
                        <h3>Painel do<br>Aluno</h3>
                    </div>
                    <div class="card-art">
                        <i class='bx bxs-user-detail main-card-icon'></i>
                    </div>
                </div>

                <!-- 5. Estatísticas & Gráficos -->
                <div class="feature-card card-dark" onclick="window.location.href='demo.php?tab=relatorios'">
                    <div class="card-text">
                        <i class='bx bx-bar-chart-alt-2 card-mini-icon'></i>
                        <h3>Estatísticas<br>& Gráficos</h3>
                    </div>
                    <div class="card-art">
                        <i class='bx bx-pie-chart-alt-2 main-card-icon pie-color'></i>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script src="../js/main.js?v=3.0"></script>
</body>
</html>