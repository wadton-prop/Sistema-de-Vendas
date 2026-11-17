<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

/**
 * DESIGN PATTERN: Singleton
 *
 * Garante que exista UMA única conexão com o banco por requisição.
 * No código original cada arquivo fazia include 'db.php' e usava "global $conn";
 * aqui todos os Repositories pedem a conexão para Database::getInstance().
 */
final class Database
{
    private static ?Database $instancia = null;
    private PDO $pdo;

    // Construtor privado: ninguém fora da classe pode dar "new Database()".
    private function __construct()
    {
        $this->pdo = ConnectionFactory::criar();
    }

    // Impede clonar a instância única.
    private function __clone()
    {
    }

    public static function getInstance(): Database
    {
        if (self::$instancia === null) {
            self::$instancia = new Database();
        }
        return self::$instancia;
    }

    public function getConnection(): PDO
    {
        return $this->pdo;
    }
}
