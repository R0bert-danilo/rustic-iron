<?php
/**
 * RUSTIC IRON - PROCESSAMENTO DO SUPER ADMIN (Criação de Novas Academias / Tenants)
 */
session_start();
require_once __DIR__ . '/../config/database.php';

// Garante que apenas o Super Admin global pode executar esta ação
if (!isset($_SESSION['usuario_cargo']) || $_SESSION['usuario_cargo'] !== 'admin') {
    header("Location: /auth/auth.php?erro=acesso_negado");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomeAcademia = trim($_POST['nome_academia'] ?? '');
    $cnpj = trim($_POST['cnpj'] ?? '');
    $dbName = trim($_POST['db_name'] ?? ''); // Ex: rustic_tenant_2
    $valorPlano = trim($_POST['valor_plano'] ?? 249.90);
    $vencimento = trim($_POST['vencimento'] ?? date('Y-m-d', strtotime('+30 days')));
    
    // Dados do Admin inicial da nova academia
    $adminNome = trim($_POST['admin_nome'] ?? '');
    $adminCpf = trim($_POST['admin_cpf'] ?? '');
    $adminEmail = trim($_POST['admin_email'] ?? '');
    $adminSenha = trim($_POST['admin_senha'] ?? '');

    if (empty($nomeAcademia) || empty($cnpj) || empty($dbName) || empty($adminCpf) || empty($adminSenha)) {
        header("Location: /superadmin.php?erro=campos_vazios");
        exit;
    }

    try {
        $pdoMaster = Database::getMasterConnection();

        // 1. Verifica se o CNPJ ou o nome do banco já existem no master
        $stmtCheck = $pdoMaster->prepare("SELECT id FROM tenants WHERE cnpj = ? OR db_name = ?");
        $stmtCheck->execute([$cnpj, $dbName]);
        if ($stmtCheck->fetch()) {
            header("Location: /superadmin.php?erro=academia_ja_existe");
            exit;
        }

        // 2. Insere a nova academia na tabela `tenants` do Banco Mestre
        $stmtTenant = $pdoMaster->prepare("
            INSERT INTO tenants (nome_academia, cnpj, db_name, valor_plano, vencimento, status, ativo) 
            VALUES (?, ?, ?, ?, ?, 'ATIVO', 1)
        ");
        $stmtTenant->execute([$nomeAcademia, $cnpj, $dbName, $valorPlano, $vencimento]);
        $tenantId = $pdoMaster->lastInsertId();

        // 3. Cria fisicamente o novo banco de dados da unidade no MySQL
        $pdoMaster->exec("CREATE DATABASE `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        // 4. Conecta ao novo banco da unidade e cria a estrutura padrão das tabelas
        $pdoNewTenant = Database::getTenantConnection($dbName);
        
        $pdoNewTenant->exec("
            CREATE TABLE usuarios (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(100) NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                cpf VARCHAR(20) UNIQUE NOT NULL,
                senha VARCHAR(255) NOT NULL,
                cargo VARCHAR(50) DEFAULT 'admin',
                ativo TINYINT(1) DEFAULT 1,
                criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE planos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(100) NOT NULL,
                descricao TEXT,
                valor DECIMAL(10,2) NOT NULL,
                duracao_dias INT NOT NULL DEFAULT 30,
                ativo TINYINT(1) DEFAULT 1,
                criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE alunos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(150) NOT NULL,
                cpf VARCHAR(20) UNIQUE NOT NULL,
                email VARCHAR(100),
                telefone VARCHAR(20),
                data_nascimento DATE,
                genero VARCHAR(20),
                status ENUM('ATIVO', 'INATIVO', 'PENDENTE') DEFAULT 'ATIVO',
                criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE matriculas (
                id INT AUTO_INCREMENT PRIMARY KEY,
                aluno_id INT NOT NULL,
                plano_id INT NOT NULL,
                data_inicio DATE NOT NULL,
                data_fim DATE NOT NULL,
                status ENUM('ATIVA', 'EXPIRADA', 'CANCELADA') DEFAULT 'ATIVA',
                criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (aluno_id) REFERENCES alunos(id) ON DELETE CASCADE,
                FOREIGN KEY (plano_id) REFERENCES planos(id) ON DELETE RESTRICT
            );

            CREATE TABLE pagamentos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                aluno_id INT NOT NULL,
                matricula_id INT,
                valor DECIMAL(10,2) NOT NULL,
                forma_pagamento VARCHAR(50) DEFAULT 'Dinheiro',
                status VARCHAR(50) DEFAULT 'Pago',
                data_pagamento DATE NOT NULL,
                criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (aluno_id) REFERENCES alunos(id) ON DELETE CASCADE,
                FOREIGN KEY (matricula_id) REFERENCES matriculas(id) ON DELETE SET NULL
            );

            CREATE TABLE checkins (
                id INT AUTO_INCREMENT PRIMARY KEY,
                aluno_id INT NOT NULL,
                data_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (aluno_id) REFERENCES alunos(id) ON DELETE CASCADE
            );
        ");

        // 5. Insere o administrador inicial na nova unidade
        $senhaHash = password_hash($adminSenha, PASSWORD_DEFAULT);
        $stmtAdmin = $pdoNewTenant->prepare("
            INSERT INTO usuarios (nome, email, cpf, senha, cargo, ativo) 
            VALUES (?, ?, ?, ?, 'admin', 1)
        ");
        $stmtAdmin->execute([$adminNome, $adminEmail, $adminCpf, $senhaHash]);

        // 6. Regista o acesso global no `auth_lookup` do Banco Mestre
        $stmtLookup = $pdoMaster->prepare("
            INSERT INTO auth_lookup (identificador, db_name, tenant_id) 
            VALUES (?, ?, ?), (?, ?, ?)
            ON DUPLICATE KEY UPDATE db_name = VALUES(db_name), tenant_id = VALUES(tenant_id)
        ");
        $stmtLookup->execute([$adminCpf, $dbName, $tenantId, $adminEmail, $dbName, $tenantId]);

        // Sucesso total!
        header("Location: /superadmin.php?sucesso=academia_criada");
        exit;

    } catch (PDOException $e) {
        die("Erro ao criar nova academia/tenant: " . $e->getMessage());
    }
}