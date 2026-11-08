<?php
/**
 * RUSTIC IRON - TELA DE AUTENTICAÇÃO E CADASTRO DE UNIDADE
 */
$erro = $_GET['erro'] ?? '';
$mensagemErro = '';

if ($erro === 'usuario_nao_encontrado' || $erro === 'usuario_ou_senha_incorretos') {
    $mensagemErro = 'Usuário, CPF ou e-mail/palavra-passe incorretos.';
} elseif ($erro === 'senha_incorreta') {
    $mensagemErro = 'Palavra-passe incorreta. Tente novamente.';
} elseif ($erro === 'campos_vazios') {
    $mensagemErro = 'Preencha todos os campos obrigatórios.';
} elseif ($erro === 'acesso_negado') {
    $mensagemErro = 'Acesso restrito. Faça login com uma credencial autorizada.';
}
?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rustic Iron - Autenticação</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    
    <!-- Caminho correto para o CSS -->
    <link rel="stylesheet" href="../css/auth/auth.css">
</head>
<body>
    <main class="auth-container">
        <div class="auth-toggle">
            <button type="button" class="btn-toggle active" id="btn-toggle-login" onclick="alternarTelaAuth('login')">
                <span class="material-symbols-rounded">login</span> Entrar
            </button>
            <button type="button" class="btn-toggle" id="btn-toggle-registro" onclick="alternarTelaAuth('registro')">
                <span class="material-symbols-rounded">domain</span> Cadastrar Unidade
            </button>
        </div>

        <?php if (!empty($mensagemErro)): ?>
            <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; color: #f87171; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 0.875rem; text-align: center; font-weight: 500;">
                <?= htmlspecialchars($mensagemErro, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <!-- 1. BLOCO LOGIN -->
        <section id="secao-login" class="auth-card active">
            <form id="form-login" action="../../app/controllers/AuthController.php" method="POST">
                <h2>
                    <span class="material-symbols-rounded">fitness_center</span> Rustic Iron
                </h2>
                <p class="auth-subtitle">Acesse sua conta para entrar no sistema</p>

                <div class="login-box">
                    <input type="text" name="identificador" id="username" placeholder="Usuário, CPF ou E-mail" required autocomplete="username">
                    <span class="material-symbols-rounded">person</span>   
                </div>

                <div class="login-box">
                    <input type="password" name="senha" id="password" placeholder="Sua senha" required autocomplete="current-password">
                    <span class="material-symbols-rounded">lock</span>
                </div>

                <div class="opcoes-login">
                    <label for="remember-me">
                        <input type="checkbox" name="remember-me" id="remember-me">
                        Lembrar-me
                    </label>
                    <a href="recuperar.php">Esqueceu a senha?</a>
                </div>
                
                <button type="submit" class="btn-auth">Entrar no Sistema</button>

                <div class="registro-link">
                    <p>Primeira vez? <a href="javascript:void(0)" onclick="alternarTelaAuth('registro')">Cadastrar Academia</a></p>
                </div>
            </form>
        </section>

        <!-- 2. BLOCO REGISTRO EMPRESA -->
        <section id="secao-registro" class="auth-card">
            <form id="form-cadastro" action="../../app/controllers/TenantController.php" method="POST">
                <h2>
                    <span class="material-symbols-rounded">fitness_center</span> Cadastro da Academia
                </h2>
                <p class="auth-subtitle">Preencha os dados da unidade para configurar seu sistema</p>

                <div class="form-grid">
                    <div class="input-wrapper full-width">
                        <label>Razão Social / Nome da Empresa</label>
                        <input type="text" name="razao_social" placeholder="Ex: Rustic Iron Centro de Treinamento LTDA" required>
                    </div>

                    <div class="input-wrapper">
                        <label>Nome Fantasia</label>
                        <input type="text" name="nome_fantasia" placeholder="Ex: Rustic Iron Gym" required>
                    </div>

                    <div class="input-wrapper">
                        <label>CNPJ</label>
                        <input type="text" id="cnpj" name="cnpj" placeholder="00.000.000/0001-00" maxlength="18" required>
                    </div>

                    <div class="input-wrapper">
                        <label>Gestor / Responsável</label>
                        <input type="text" name="responsavel" placeholder="Nome completo do administrador" required>
                    </div>

                    <div class="input-wrapper">
                        <label>Telefone / WhatsApp Comercial</label>
                        <input type="tel" id="telefone_empresa" name="telefone_empresa" placeholder="(00) 00000-0000" required>
                    </div>

                    <div class="input-wrapper full-width">
                        <label>Endereço Completo da Unidade</label>
                        <input type="text" name="endereco_empresa" placeholder="Rua / Avenida, Número, Bairro" required>
                    </div>

                    <div class="input-wrapper">
                        <label>Cidade / UF</label>
                        <input type="text" name="cidade_empresa" placeholder="Cidade / Estado" required>
                    </div>

                    <div class="input-wrapper">
                        <label>E-mail Corporativo (Login Principal)</label>
                        <input type="email" name="email_empresa" placeholder="diretoria@rusticiron.com" required autocomplete="email">
                    </div>

                    <div class="input-wrapper full-width">
                        <label>Senha de Acesso do Administrador</label>
                        <input type="password" name="senha_empresa" placeholder="Crie uma senha forte" required autocomplete="new-password">
                    </div>
                </div>

                <div class="actions">
                    <button type="submit" class="btn-auth">
                        <span class="material-symbols-rounded">verified_user</span> Concluir Cadastro & Iniciar
                    </button>
                </div>

                <div class="registro-link">
                    <p>Já possui cadastro? <a href="javascript:void(0)" onclick="alternarTelaAuth('login')">Acessar Conta</a></p>
                </div>
            </form>
        </section>
    </main>

    <!-- Caminho para o JavaScript -->
    <script src="../js/auth/auth.js"></script>
</body>
</html>