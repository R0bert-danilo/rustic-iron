<?php
/**
 * RUSTIC IRON - API DE LISTAGEM DE UNIDADES PARA O SUPERADMIN
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config/database.php';

session_start();

// Aqui podes adicionar uma verificação se o utilizador logado é superadmin, se necessário.

try {
    // Conecta ao Banco Mestre onde ficam guardadas todas as academias do SaaS
    $pdoMaster = Database::getMasterConnection();
    
    $stmt = $pdoMaster->query("SELECT * FROM tenants ORDER BY criado_em DESC");
    $tenants = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'sucesso' => true,
        'dados' => $tenants
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'sucesso' => false,
        'erro' => 'Erro ao carregar as unidades: ' . $e->getMessage()
    ]);
}