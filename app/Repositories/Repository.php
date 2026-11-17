<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

/**
 * DESIGN PATTERN: Repository
 *
 * Classe base dos repositórios. Cada Repository é o ÚNICO lugar que
 * escreve SQL para a sua tabela. Controllers e Services pedem
 * "me dá o cliente 5" sem saber se é MySQL, PostgreSQL ou SQLite.
 *
 * Todas as consultas usam prepared statements (proteção contra SQL Injection,
 * problema que existia no salvar_produto.php original).
 */
abstract class Repository
{
    protected PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        // A conexão vem do Singleton (ou pode ser injetada, o que facilita testes).
        $this->pdo = $pdo ?? Database::getInstance()->getConnection();
    }

    public function pdo(): PDO
    {
        return $this->pdo;
    }
}
