<?php
/**
 * RUSTIC IRON - CONTROLLER DE AUTENTICAÇÃO MULTI-TENANT (COMPLETO E SEPARADO POR PERFIS)
 */
require_once __DIR__ . '/../../config/database.php';

class AuthController {
    public function processarLogin() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $identificador = trim($_POST['identificador'] ?? ''); 
            $senha = $_POST['senha'] ?? '';

            if (empty($identificador) || empty($senha)) {
                header("Location: /rustic_iron/public/auth/auth.php?erro=campos_vazios");
                exit;
            }

            $identificadorLimpo = preg_replace('/[^0-9]/', '', $identificador);

            try {
                $pdoMaster = Database::getMasterConnection();

                // 1. Verifica se é Super Admin Global
                $stmtSuper = $pdoMaster->prepare("SELECT * FROM super_admins WHERE email = ?");
                $stmtSuper->execute([$identificador]);
                $superAdmin = $stmtSuper->fetch();

                if ($superAdmin && ($senha === $superAdmin['senha'] || password_verify($senha, $superAdmin['senha']))) {
                    $_SESSION['usuario_id'] = $superAdmin['id'];
                    $_SESSION['usuario_nome'] = $superAdmin['nome'];
                    $_SESSION['usuario_cargo'] = 'superadmin';
                    $_SESSION['tenant_id'] = null;
                    $_SESSION['tenant_db'] = null;

                    header("Location: /rustic_iron/superadmin/superadmin.php");
                    exit;
                }

                // 2. Verifica se é Dono/Admin via auth_lookup
                if (strpos($identificador, '@') !== false) {
                    $stmtMaster = $pdoMaster->prepare("SELECT db_name, tenant_id FROM auth_lookup WHERE email = ?");
                    $stmtMaster->execute([$identificador]);
                } else {
                    $stmtMaster = $pdoMaster->prepare("SELECT db_name, tenant_id FROM auth_lookup WHERE cpf = ? OR cpf = ?");
                    $stmtMaster->execute([$identificador, $identificadorLimpo]);
                }
                $lookup = $stmtMaster->fetch();

                $tenantDbName = $lookup['db_name'] ?? 'rustic_tenant_rustic_iron_matriz'; 
                $tenantId = $lookup['tenant_id'] ?? 1;

                // 3. Conecta ao Banco do Tenant
                $pdoTenant = Database::getTenantConnection($tenantDbName);
                
                $usuario = false;

                // A. PRIMEIRO TENTA NA TABELA COLABORADORES
                try {
                    if (strpos($identificador, '@') !== false) {
                        $stmtCollab = $pdoTenant->prepare("SELECT id, nome, email, cpf, senha, senha_provisoria, cargo, 1 as ativo FROM colaboradores WHERE email = ?");
                        $stmtCollab->execute([$identificador]);
                    } else {
                        $stmtCollab = $pdoTenant->prepare("SELECT id, nome, email, cpf, senha, senha_provisoria, cargo, 1 as ativo FROM colaboradores WHERE cpf = ? OR REPLACE(REPLACE(REPLACE(cpf, '.', ''), '-', ''), ' ', '') = ?");
                        $stmtCollab->execute([$identificador, $identificadorLimpo]);
                    }
                    $usuario = $stmtCollab->fetch(PDO::FETCH_ASSOC);
                } catch (PDOException $ex) {}

                // B. Se não encontrou em colaboradores, tenta na tabela `usuarios`
                if (!$usuario) {
                    if (strpos($identificador, '@') !== false) {
                        $stmtUser = $pdoTenant->prepare("SELECT id, nome, email, cpf, senha, cargo FROM usuarios WHERE email = ?");
                        $stmtUser->execute([$identificador]);
                    } else {
                        $stmtUser = $pdoTenant->prepare("SELECT id, nome, email, cpf, senha, cargo FROM usuarios WHERE cpf = ? OR REPLACE(REPLACE(REPLACE(cpf, '.', ''), '-', ''), ' ', '') = ?");
                        $stmtUser->execute([$identificador, $identificadorLimpo]);
                    }
                    $usuario = $stmtUser->fetch(PDO::FETCH_ASSOC);
                }

                // 4. Validação da senha
                $senhaValida = false;
                if ($usuario) {
                    $senhaPura = $usuario['senha'] ?? '';
                    $senhaProv = $usuario['senha_provisoria'] ?? '';

                    if ($senha === $senhaPura || $senha === $senhaProv || 
                        (!empty($senhaPura) && @password_verify($senha, $senhaPura)) || 
                        (!empty($senhaProv) && @password_verify($senha, $senhaProv))) {
                        $senhaValida = true;
                    }
                }

                if ($usuario && $senhaValida) {
                    $_SESSION['usuario_id'] = $usuario['id'];
                    $_SESSION['usuario_nome'] = $usuario['nome'];
                    $_SESSION['usuario_cargo'] = $usuario['cargo'] ?? 'CEO';
                    $_SESSION['tenant_id'] = $tenantId;
                    $_SESSION['tenant_db'] = $tenantDbName;

                    $cargoBruto = trim($usuario['cargo'] ?? 'CEO');
                    $cargoLower = mb_strtolower($cargoBruto, 'UTF-8');
                    $cargoLowerSemAcento = str_replace(
                        ['ó', 'ô', 'õ', 'á', 'à', 'â', 'ã', 'é', 'ê', 'í', 'ú', 'ç'],
                        ['o', 'o', 'o', 'a', 'a', 'a', 'a', 'e', 'e', 'i', 'u', 'c'],
                        $cargoLower
                    );

                    // 5. REDIRECIONAMENTO (Pega Rececionista e Recepcionista)
                    if (
                        strpos($cargoLowerSemAcento, 'recep') !== false || 
                        strpos($cargoLowerSemAcento, 'recec') !== false || 
                        strpos($cargoLowerSemAcento, 'atendente') !== false || 
                        strpos($cargoLowerSemAcento, 'balcao') !== false || 
                        strpos($cargoLowerSemAcento, 'secretaria') !== false
                    ) {
                        header("Location: ../../public/empresa/recepcao.php");
                        exit;
                    }

                    if (strpos($cargoLowerSemAcento, 'professor') !== false || strpos($cargoLowerSemAcento, 'personal') !== false) {
                        header("Location: ../../public/empresa/professor.php");
                        exit;
                    }

                    if (
                        strpos($cargoLowerSemAcento, 'ceo') !== false || 
                        strpos($cargoLowerSemAcento, 'socio') !== false || 
                        strpos($cargoLowerSemAcento, 'diretor') !== false || 
                        strpos($cargoLowerSemAcento, 'diretoria') !== false
                    ) {
                        header("Location: ../../public/empresa/diretoria.php");
                        exit;
                    }

                    header("Location: ../../public/empresa/gerencia.php");
                    exit;

                } else {
                    header("Location: ../../public/auth/auth.php?erro=usuario_ou_senha_incorretos");
                    exit;
                }

            } catch (PDOException $e) {
                die("Erro no processamento do login: " . $e->getMessage());
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth = new AuthController();
    $auth->processarLogin();
}