<?php
/**
 * RUSTIC IRON - CONTROLLER DO SUPER ADMIN (Criação, Edição, Exclusão e Status de Academias)
 */
session_start();
require_once __DIR__ . '/../../config/database.php';

// Garante que apenas o Super Admin global pode executar esta ação
if (!isset($_SESSION['usuario_cargo']) || $_SESSION['usuario_cargo'] !== 'superadmin') {
    http_response_code(403);
    echo json_encode(['sucesso' => false, 'erro' => 'acesso_negado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = trim($_POST['action'] ?? '');
    $pdoMaster = Database::getMasterConnection();

    // ==========================================
    // AÇÃO DE ATUALIZAR STATUS (BLOQUEAR / ATIVAR)
    // ==========================================
    if ($action === 'atualizar_status') {
        $id = trim($_POST['id'] ?? '');
        $status = trim($_POST['status'] ?? '');

        if (empty($id) || empty($status)) {
            echo json_encode(['sucesso' => false, 'erro' => 'campos_vazios']);
            exit;
        }

        try {
            $stmt = $pdoMaster->prepare("UPDATE tenants SET status = ? WHERE id = ?");
            $stmt->execute([$status, $id]);

            echo json_encode(['sucesso' => true, 'mensagem' => 'status_atualizado']);
            exit;
        } catch (PDOException $e) {
            echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
            exit;
        }
    }

    // ==========================================
    // AÇÃO DE EXCLUIR ACADEMIA E BANCO DE DADOS
    // ==========================================
    if ($action === 'excluir') {
        $id = trim($_POST['id'] ?? '');
        $dbName = trim($_POST['db_name'] ?? '');

        if (empty($id) || empty($dbName)) {
            echo json_encode(['sucesso' => false, 'erro' => 'campos_vazios']);
            exit;
        }

        // Validação de segurança para o nome do banco de dados (evitar SQL Injection)
        $dbNameSanitized = preg_replace('/[^a-zA-Z0-9_]/', '', $dbName);

        try {
            // 1. Apaga fisicamente o banco de dados do tenant no MySQL
            $pdoMaster->exec("DROP DATABASE IF EXISTS `$dbNameSanitized`");

            // 2. Apaga os registos de lookup de autenticação associados ao tenant
            $stmtLookup = $pdoMaster->prepare("DELETE FROM auth_lookup WHERE tenant_id = ?");
            $stmtLookup->execute([$id]);

            // 3. Apaga o registo da academia na tabela `tenants` do Banco Mestre
            $stmtTenant = $pdoMaster->prepare("DELETE FROM tenants WHERE id = ?");
            $stmtTenant->execute([$id]);

            echo json_encode(['sucesso' => true, 'mensagem' => 'academia_excluida']);
            exit;
        } catch (PDOException $e) {
            echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
            exit;
        }
    }

    // ==========================================
    // AÇÕES DE CRIAÇÃO (INSERT) E EDIÇÃO (UPDATE)
    // ==========================================
    $idEdit = trim($_POST['tenant_id_edit'] ?? '');
    $nomeAcademia = trim($_POST['nome_academia'] ?? '');
    $cnpj = trim($_POST['cnpj'] ?? '');
    
    // GERAÇÃO AUTOMÁTICA DO NOME DO BANCO (Evita conflitos e nomes fixos)
    $dbSlug = strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', $nomeAcademia));
    $dbName = 'rustic_tenant_' . ($dbSlug ?: 'nova_unidade');

    $valorPlano = trim($_POST['valor_plano'] ?? 249.90);
    $vencimento = trim($_POST['vencimento'] ?? date('Y-m-d', strtotime('+30 days')));
    
    $adminNome = trim($_POST['admin_nome'] ?? '');
    $adminCpf = trim($_POST['admin_cpf'] ?? '');
    $adminEmail = trim($_POST['admin_email'] ?? '');
    $adminTelefone = trim($_POST['admin_telefone'] ?? ''); 
    $adminSenha = trim($_POST['admin_senha'] ?? '');

    if (empty($nomeAcademia) || empty($cnpj)) {
        echo json_encode(['sucesso' => false, 'erro' => 'campos_vazios']);
        exit;
    }

    try {
        // SE FOR EDIÇÃO (UPDATE)
        if (!empty($idEdit)) {
            $stmtUpdate = $pdoMaster->prepare("
                UPDATE tenants 
                SET nome_academia = ?, cnpj = ?, valor_plano = ?, vencimento = ?, admin_nome = ?, admin_cpf = ?, admin_email = ?, admin_telefone = ?
                WHERE id = ?
            ");
            $stmtUpdate->execute([$nomeAcademia, $cnpj, $valorPlano, $vencimento, $adminNome, $adminCpf, $adminEmail, $adminTelefone, $idEdit]);

            echo json_encode(['sucesso' => true, 'mensagem' => 'academia_atualizada']);
            exit;
        }

        // SE FOR CRIAÇÃO (INSERT)
        // 1. Verifica se o CNPJ ou o nome do banco já existem
        $stmtCheck = $pdoMaster->prepare("SELECT id FROM tenants WHERE cnpj = ? OR db_name = ?");
        $stmtCheck->execute([$cnpj, $dbName]);
        if ($stmtCheck->fetch()) {
            echo json_encode(['sucesso' => false, 'erro' => 'academia_ja_existe']);
            exit;
        }

        // 2. Insere a nova academia na tabela `tenants` do Banco Mestre (sem o campo admin_senha)
        $stmtTenant = $pdoMaster->prepare("
            INSERT INTO tenants (nome_academia, cnpj, db_name, valor_plano, vencimento, status, admin_nome, admin_cpf, admin_email, admin_telefone) 
            VALUES (?, ?, ?, ?, ?, 'ativo', ?, ?, ?, ?)
        ");
        $stmtTenant->execute([$nomeAcademia, $cnpj, $dbName, $valorPlano, $vencimento, $adminNome, $adminCpf, $adminEmail, $adminTelefone]);
        $tenantId = $pdoMaster->lastInsertId();

        // 3. Cria fisicamente o banco da unidade no MySQL com o nome dinâmico
        $pdoMaster->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        // 4. Conecta e cria a estrutura completa e detalhada no banco do tenant
        $pdoNewTenant = Database::getTenantConnection($dbName);
        $pdoNewTenant->exec("
            -- A) Usuários internos da academia (Admin, Gestores, Professores, Recepção)
            CREATE TABLE IF NOT EXISTS usuarios (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(150) NOT NULL,
                matricula VARCHAR(50) UNIQUE,
                email VARCHAR(100) UNIQUE NOT NULL,
                cpf VARCHAR(20) UNIQUE NOT NULL,
                senha VARCHAR(255) NOT NULL,
                cargo VARCHAR(50) NOT NULL DEFAULT 'admin',
                unidade VARCHAR(100) DEFAULT 'Matriz',
                sexo VARCHAR(20),
                ano_nascimento INT,
                data_admissao DATE DEFAULT (CURRENT_DATE),
                ativo TINYINT(1) DEFAULT 1,
                criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );

            -- B) Planos da academia
            CREATE TABLE IF NOT EXISTS planos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(100) NOT NULL,
                descricao TEXT,
                beneficios TEXT,
                valor DECIMAL(10,2) NOT NULL,
                duracao_dias INT NOT NULL DEFAULT 30,
                ativo TINYINT(1) DEFAULT 1,
                criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );

            -- C) Alunos (Clientes finais)
            CREATE TABLE IF NOT EXISTS alunos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                matricula VARCHAR(50) UNIQUE NOT NULL,
                nome VARCHAR(150) NOT NULL,
                cpf VARCHAR(20) UNIQUE NOT NULL,
                email VARCHAR(100),
                telefone VARCHAR(20),
                contato_emergencia VARCHAR(100),
                ano_nascimento INT,
                sexo VARCHAR(20),
                area_treino_especifico VARCHAR(150),
                status_pagamento ENUM('PAGO', 'PENDENTE', 'ATRASADO') DEFAULT 'PENDENTE',
                status_aluno ENUM('ATIVO', 'INATIVO', 'BLOQUEADO') DEFAULT 'ATIVO',
                criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );

            -- D) Matrículas
            CREATE TABLE IF NOT EXISTS matriculas (
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

            -- E) Pagamentos
            CREATE TABLE IF NOT EXISTS pagamentos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                aluno_id INT NOT NULL,
                matricula_id INT,
                valor DECIMAL(10,2) NOT NULL,
                forma_pagamento VARCHAR(50) DEFAULT 'Pix',
                status VARCHAR(50) DEFAULT 'Pago',
                data_pagamento DATE NOT NULL,
                criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (aluno_id) REFERENCES alunos(id) ON DELETE CASCADE,
                FOREIGN KEY (matricula_id) REFERENCES matriculas(id) ON DELETE SET NULL
            );

            -- F) Check-ins (Catraca - Entrada)
            CREATE TABLE IF NOT EXISTS checkins (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nome_aluno VARCHAR(150) NOT NULL,
                matricula VARCHAR(50) NOT NULL,
                data_hora_entrada TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );

            -- G) Check-outs (Catraca - Saída)
            CREATE TABLE IF NOT EXISTS checkouts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nome_aluno VARCHAR(150) NOT NULL,
                matricula VARCHAR(50) NOT NULL,
                data_hora_saida TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );
        ");

        // 5. Insere o administrador inicial na nova unidade
        $senhaHashCriacao = !empty($adminSenha) ? password_hash($adminSenha, PASSWORD_DEFAULT) : password_hash('123456', PASSWORD_DEFAULT);
        $stmtAdmin = $pdoNewTenant->prepare("
            INSERT INTO usuarios (nome, email, cpf, senha, cargo, unidade, ativo) 
            VALUES (?, ?, ?, ?, 'admin', 'Matriz', 1)
        ");
        $stmtAdmin->execute([$adminNome, $adminEmail, $adminCpf, $senhaHashCriacao]);

        // 6. Regista o acesso global no `auth_lookup` do Master
        $stmtLookup = $pdoMaster->prepare("
            INSERT INTO auth_lookup (cpf, email, db_name, tenant_id) 
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
                email = VALUES(email), 
                db_name = VALUES(db_name), 
                tenant_id = VALUES(tenant_id)
        ");
        $stmtLookup->execute([$adminCpf, $adminEmail, $dbName, $tenantId]);

        echo json_encode(['sucesso' => true, 'mensagem' => 'academia_criada']);
        exit;

    } catch (PDOException $e) {
        echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
        exit;
    }
}