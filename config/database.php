<?php
/**
 * RUSTIC IRON - CONFIGURAÇÃO E CONEXÃO PDO COM O MYSQL (MULTI-TENANT)
 */

// Definições globais e caminhos absolutos para evitar erros de diretório
define('BASE_URL', 'http://localhost/rustic_iron/public/');
define('APP_NAME', 'Rustic Iron SaaS');

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/rustic_iron/');
}

class Database {
    private static $host = '127.0.0.1';
    private static $username = 'root';
    private static $password = ''; // Altera se o teu XAMPP tiver password[cite: 11]

    public static function getMasterConnection() {
        try {
            $pdo = new PDO("mysql:host=" . self::$host . ";dbname=rustic_master;charset=utf8mb4", self::$username, self::$password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            return $pdo;
        } catch (PDOException $e) {
            die("Erro na ligação ao Banco Mestre: " . $e->getMessage());
        }
    }

    public static function getTenantConnection($dbName) {
        try {
            // Sanitização do nome do banco por segurança
            $dbNameSanitized = preg_replace('/[^a-zA-Z0-9_]/', '', $dbName);
            $pdo = new PDO("mysql:host=" . self::$host . ";dbname=" . $dbNameSanitized . ";charset=utf8mb4", self::$username, self::$password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            return $pdo;
        } catch (PDOException $e) {
            die("Erro na ligação ao banco da academia ($dbName): " . $e->getMessage());
        }
    }
}