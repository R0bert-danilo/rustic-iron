<?php
/**
 * RUSTIC IRON - CONTROLLER DE ALTERAÇÃO DE SENHA DA DIRETORIA (COMPLETO E CORRIGIDO)
 */
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['sucesso' => false, 'erro' => 'Sessão expirada. Faça login novamente.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $usuarioId = $_SESSION['usuario_id'];
        $tenantDbName = $_SESSION['tenant_db'] ?? 'rustic_tenant_p';
        $pdoTenant = Database::getTenantConnection($tenantDbName);

        $senhaAtual = $_POST['senha_atual'] ?? '';
        $novaSenha = $_POST['nova_senha'] ?? '';

        if (empty($senhaAtual) || empty($novaSenha)) {
            echo json_encode(['sucesso' => false, 'erro' => 'Preencha todos os campos obrigatórios.']);
            exit;
        }

        $user = false;
        $tabelaAlvo = '';

        // 1. Procura primeiro na tabela 'usuarios' (onde fica o CEO e admins principais)
        try {
            $stmt = $pdoTenant->prepare("SELECT id, senha FROM usuarios WHERE id = ?");
            $stmt->execute([$usuarioId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user) {
                $tabelaAlvo = 'usuarios';
            }
        } catch (Exception $ex) {
            // Tabela usuarios pode não existir no tenant, prossegue
        }

        // 2. Se não encontrar em 'usuarios', procura na tabela 'colaboradores'
        if (!$user) {
            try {
                // Garante que a coluna senha existe caso vá usar colaboradores
                $pdoTenant->exec("ALTER TABLE colaboradores ADD COLUMN senha VARCHAR(255) DEFAULT NULL;");
            } catch (Exception $ex) {}

            $stmt = $pdoTenant->prepare("SELECT id, senha FROM colaboradores WHERE id = ?");
            $stmt->execute([$usuarioId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user) {
                $tabelaAlvo = 'colaboradores';
            }
        }

        if (!$user) {
            echo json_encode(['sucesso' => false, 'erro' => 'Utilizador não encontrado na base de dados.']);
            exit;
        }

        // 3. Valida se a senha atual está correta (suporta texto plano ou hash seguro)
        // Se a senha no banco estiver vazia ou nula (comum em seeds iniciais), permite a alteração direta ou valida
        $senhaBanco = $user['senha'] ?? '';
        $senhaValida = empty($senhaBanco) || ($senhaAtual === $senhaBanco) || password_verify($senhaAtual, $senhaBanco);

        if (!$senhaValida) {
            echo json_encode(['sucesso' => false, 'erro' => 'A senha atual está incorreta.']);
            exit;
        }

        // 4. Grava a nova senha encriptada com hash seguro
        $novaSenhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);
        $stmtUpdate = $pdoTenant->prepare("UPDATE {$tabelaAlvo} SET senha = ? WHERE id = ?");
        $stmtUpdate->execute([$novaSenhaHash, $usuarioId]);

        echo json_encode(['sucesso' => true, 'mensagem' => 'Senha alterada com sucesso!']);
        exit;

    } catch (Exception $e) {
        echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
        exit;
    }
}

echo json_encode(['sucesso' => false, 'erro' => 'Método de requisição inválido.']);