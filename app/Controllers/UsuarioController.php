<?php
/**
 * RUSTIC IRON - BACKEND DE CADASTRO DE UTILIZADORES
 */
session_start();
require_once __DIR__ . '/../config/database.php';

// Garante que apenas quem tem permissão (Admin/Diretoria) pode criar utilizadores
if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['usuario_cargo'], ['admin', 'diretoria'])) {
    header("Location: /auth/auth.php?erro=acesso_negado");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $cpf = trim($_POST['cpf'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $cargo = trim($_POST['cargo'] ?? 'professor'); // diretoria, gerente, professor, recepcao
    
    // Pega o tenant atual da sessão (ou do POST se for o Super Admin a criar para outra unidade)
    $tenantId = $_SESSION['tenant_id'] ?? 1;
    $tenantDbName = $_SESSION['tenant_db'] ?? 'rustic_tenant_1';

    if (empty($nome) || empty($cpf) || empty($senha)) {
        header("Location: /empresa/cadastrar_usuario.php?erro=campos_vazios");
        exit;
    }

    try {
        // 1. Conecta ao Banco do Tenant e insere o utilizador
        $pdoTenant = Database::getTenantConnection($tenantDbName);
        
        // Verifica se o CPF já existe no tenant
        $stmtCheck = $pdoTenant->prepare("SELECT id FROM usuarios WHERE cpf = ? OR email = ?");
        $stmtCheck->execute([$cpf, $email]);
        if ($stmtCheck->fetch()) {
            header("Location: /empresa/cadastrar_usuario.php?erro=usuario_ja_existe");
            exit;
        }

        // Gera o hash seguro da senha
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

        $stmtInsertUser = $pdoTenant->prepare("
            INSERT INTO usuarios (nome, email, cpf, senha, cargo, ativo) 
            VALUES (?, ?, ?, ?, ?, 1)
        ");
        $stmtInsertUser->execute([$nome, $email, $cpf, $senhaHash, $cargo]);

        // 2. Regista o acesso global no Banco Mestre (auth_lookup) para o login funcionar
        $pdoMaster = Database::getMasterConnection();
        
        // Insere o CPF como identificador principal
        $stmtMasterCpf = $pdoMaster->prepare("
            INSERT INTO auth_lookup (identificador, db_name, tenant_id) 
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE db_name = VALUES(db_name), tenant_id = VALUES(tenant_id)
        ");
        $stmtMasterCpf->execute([$cpf, $tenantDbName, $tenantId]);

        // Se informou e-mail, insere também para permitir login por e-mail
        if (!empty($email)) {
            $stmtMasterEmail = $pdoMaster->prepare("
                INSERT INTO auth_lookup (identificador, db_name, tenant_id) 
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE db_name = VALUES(db_name), tenant_id = VALUES(tenant_id)
            ");
            $stmtMasterEmail->execute([$email, $tenantDbName, $tenantId]);
        }

        // Sucesso! Redireciona de volta com mensagem de sucesso
        header("Location: /empresa/diretoria.php?sucesso=usuario_criado");
        exit;

    } catch (PDOException $e) {
        die("Erro ao cadastrar utilizador: " . $e->getMessage());
    }
}