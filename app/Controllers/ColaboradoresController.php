<?php
header('Content-Type: application/json; charset=utf-8');

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/database.php';

try {
    $pdoMaster = Database::getMasterConnection();
    
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $tenantDbName = $_SESSION['tenant_db'] ?? 'rustic_tenant_p';
    $tenantId = $_SESSION['tenant_id'] ?? 1;

    $pdoTenant = Database::getTenantConnection($tenantDbName);

    $action = $_GET['action'] ?? $_POST['action'] ?? '';

    // 1. SALVAR (CADASTRAR OU EDITAR)
    if (empty($action) && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $idEdit           = $_POST['colaborador_id_edit'] ?? '';
        $nome             = trim($_POST['eqNome'] ?? '');
        $cpf              = preg_replace('/\D/', '', $_POST['eqCpf'] ?? '');
        $cargo            = $_POST['eqCargo'] ?? '';
        $cref             = $_POST['eqCref'] ?? '';
        $turno            = $_POST['eqTurno'] ?? '';
        $telefone         = $_POST['eqTelefone'] ?? '';
        $email            = $_POST['eqEmail'] ?? '';
        $alunos           = $_POST['eqAlunos'] ?? 0;
        $especialidade    = $_POST['eqEspecialidade'] ?? '';
        $senhaProvisoria  = $_POST['eqSenhaProvisoria'] ?? '';

        if (empty($nome)) {
            echo json_encode(['sucesso' => false, 'erro' => 'O nome do colaborador não pode estar vazio.']);
            exit;
        }

        if (!empty($idEdit)) {
            $stmt = $pdoTenant->prepare("UPDATE colaboradores SET nome = ?, cpf = ?, cref = ?, cargo = ?, turno = ?, telefone = ?, email = ?, atletas_alocados = ?, especialidade = ? WHERE id = ?");
            $stmt->execute([$nome, $cpf, $cref, $cargo, $turno, $telefone, $email, $alunos, $especialidade, $idEdit]);

            if (!empty($cpf)) {
                if (!empty(trim($senhaProvisoria))) {
                    $senhaHash = password_hash(trim($senhaProvisoria), PASSWORD_DEFAULT);
                    $stmtUpUser = $pdoTenant->prepare("UPDATE usuarios SET nome = ?, email = ?, senha = ?, cargo = ? WHERE matricula = ? OR cpf = ? OR email = ?");
                    $stmtUpUser->execute([$nome, $email, $senhaHash, $cargo, $cpf, $cpf, $email]);
                } else {
                    $stmtUpUser = $pdoTenant->prepare("UPDATE usuarios SET nome = ?, email = ?, cargo = ? WHERE matricula = ? OR cpf = ? OR email = ?");
                    $stmtUpUser->execute([$nome, $email, $cargo, $cpf, $cpf, $email]);
                }
            }
        } else {
            $stmt = $pdoTenant->prepare("INSERT INTO colaboradores (nome, cpf, cref, cargo, turno, telefone, email, atletas_alocados, especialidade, criado_em) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$nome, $cpf, $cref, $cargo, $turno, $telefone, $email, $alunos, $especialidade]);

            if (!empty($cpf)) {
                $stmtCheck = $pdoTenant->prepare("SELECT id FROM usuarios WHERE matricula = ? OR cpf = ?");
                $stmtCheck->execute([$cpf, $cpf]);
                
                if (!$stmtCheck->fetch()) {
                    $senhaHash = !empty(trim($senhaProvisoria)) ? password_hash(trim($senhaProvisoria), PASSWORD_DEFAULT) : password_hash('123456', PASSWORD_DEFAULT);

                    $stmtUser = $pdoTenant->prepare("INSERT INTO usuarios (nome, matricula, email, cpf, cargo, senha, unidade, ativo, criado_em) VALUES (?, ?, ?, ?, ?, ?, 'Matriz', 1, NOW())");
                    $stmtUser->execute([$nome, $cpf, $email, $cpf, $cargo, $senhaHash]);
                }
            }

            if (!empty($cpf) || !empty($email)) {
                $stmtAuthCheck = $pdoMaster->prepare("SELECT id FROM auth_lookup WHERE cpf = ? OR email = ?");
                $stmtAuthCheck->execute([$cpf, $email]);
                
                if (!$stmtAuthCheck->fetch()) {
                    $stmtAuth = $pdoMaster->prepare("INSERT INTO auth_lookup (tenant_id, db_name, cpf, email) VALUES (?, ?, ?, ?)");
                    $stmtAuth->execute([$tenantId, $tenantDbName, $cpf, $email]);
                }
            }
        }

        echo json_encode(['sucesso' => true, 'mensagem' => 'Colaborador salvo com sucesso!']);
        exit;
    }

    // 2. LISTAR COLABORADORES
    if ($action === 'listar') {
        $categoriaFiltro = $_GET['categoria'] ?? 'todos';

        $sqlColab = "SELECT id, nome, cpf, cref, cargo, turno, telefone, email, atletas_alocados, especialidade, 'ATIVO' as situacao FROM colaboradores";
        if ($categoriaFiltro !== 'todos') {
            $sqlColab .= " WHERE cargo = :cat";
            $stmt = $pdoTenant->prepare($sqlColab . " ORDER BY id DESC");
            $stmt->execute(['cat' => $categoriaFiltro]);
        } else {
            $stmt = $pdoTenant->query($sqlColab . " ORDER BY id DESC");
        }
        $dadosColab = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $sqlUser = "SELECT id, nome, cpf, '' as cref, cargo, 'Manhã (06h às 14h)' as turno, '' as telefone, email, 0 as atletas_alocados, '' as especialidade, 'ATIVO' as situacao FROM usuarios WHERE cargo = 'CEO' OR cargo LIKE '%Sócio%' OR cargo LIKE '%Socio%' OR cargo LIKE '%Admin%'";
        if ($categoriaFiltro !== 'todos') {
            $sqlUser .= " AND cargo = :catUser";
            $stmtUser = $pdoTenant->prepare($sqlUser . " ORDER BY id DESC");
            $stmtUser->execute(['catUser' => $categoriaFiltro]);
        } else {
            $stmtUser = $pdoTenant->query($sqlUser . " ORDER BY id DESC");
        }
        $dadosUser = $stmtUser->fetchAll(PDO::FETCH_ASSOC);

        $cpfsExistentes = array_column($dadosColab, 'cpf');
        $dados = $dadosColab;
        foreach ($dadosUser as $u) {
            if (!empty($u['cpf']) && !in_array($u['cpf'], $cpfsExistentes)) {
                $dados[] = $u;
            }
        }

        $stmtCargosColab = $pdoTenant->query("SELECT cargo, COUNT(*) as qtd FROM colaboradores GROUP BY cargo");
        $cargosColab = $stmtCargosColab->fetchAll(PDO::FETCH_ASSOC);

        $contagensMap = [];
        foreach ($cargosColab as $c) {
            if (!empty($c['cargo'])) {
                $contagensMap[$c['cargo']] = ($contagensMap[$c['cargo']] ?? 0) + (int)$c['qtd'];
            }
        }

        foreach ($dadosUser as $du) {
            $cargoU = $du['cargo'];
            if (!empty($cargoU)) {
                $contagensMap[$cargoU] = ($contagensMap[$cargoU] ?? 0) + 1;
            }
        }

        $totalGeral = count($dados);
        $contagens = ['todos' => $totalGeral];
        foreach ($contagensMap as $cargoNome => $qtdCargo) {
            $contagens[$cargoNome] = $qtdCargo;
        }

        echo json_encode([
            'sucesso' => true,
            'tenant_usado' => $tenantDbName,
            'contagens' => $contagens,
            'dados' => $dados
        ]);
        exit;
    }

    // 3. EXCLUIR COLABORADOR
    if ($action === 'excluir') {
        $id = $_POST['id'] ?? '';
        if (!empty($id)) {
            $stmtGet = $pdoTenant->prepare("SELECT cpf, email FROM colaboradores WHERE id = ?");
            $stmtGet->execute([$id]);
            $colab = $stmtGet->fetch();

            if ($colab) {
                if (!empty($colab['cpf'])) {
                    $stmtDelAuth = $pdoMaster->prepare("DELETE FROM auth_lookup WHERE cpf = ?");
                    $stmtDelAuth->execute([$colab['cpf']]);

                    $stmtDelUser = $pdoTenant->prepare("DELETE FROM usuarios WHERE matricula = ? OR cpf = ?");
                    $stmtDelUser->execute([$colab['cpf'], $colab['cpf']]);
                }
            }

            $stmt = $pdoTenant->prepare("DELETE FROM colaboradores WHERE id = ?");
            $stmt->execute([$id]);
        }

        echo json_encode(['sucesso' => true, 'mensagem' => 'Colaborador removido com sucesso!']);
        exit;
    }

    echo json_encode(['sucesso' => false, 'erro' => 'Ação inválida.']);

} catch (Exception $e) {
    echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
}