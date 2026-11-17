<?php

declare(strict_types=1);

namespace App\Core;

use InvalidArgumentException;
use PDO;

/**
 * DESIGN PATTERN: Factory (Simple Factory)
 *
 * Cria a conexão PDO correta de acordo com o DB_DRIVER do .env
 * (sqlite, mysql ou pgsql). O resto do sistema não sabe — e não precisa
 * saber — qual banco está sendo usado.
 */
final class ConnectionFactory
{
    public static function criar(): PDO
    {
        $driver = strtolower(Env::get('DB_DRIVER', 'sqlite'));

        switch ($driver) {
            case 'sqlite':
                return self::sqlite();
            case 'mysql':
                return self::mysql();
            case 'pgsql':
            case 'postgres':
            case 'postgresql':
                return self::pgsql();
            default:
                throw new InvalidArgumentException("DB_DRIVER inválido: {$driver}. Use sqlite, mysql ou pgsql.");
        }
    }

    private static function opcoes(): array
    {
        return [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];
    }

    private static function sqlite(): PDO
    {
        $caminho = Env::get('DB_SQLITE_PATH', 'storage/pdv.sqlite');
        if (!preg_match('/^([a-zA-Z]:)?[\/\\\\]/', $caminho)) {
            $caminho = ROOT_PATH . '/' . $caminho;
        }
        $bancoNovo = !is_file($caminho);

        $pdo = new PDO('sqlite:' . $caminho, null, null, self::opcoes());
        $pdo->exec('PRAGMA foreign_keys = ON');

        // Primeira execução: cria as tabelas e os dados de exemplo automaticamente.
        if ($bancoNovo) {
            try {
                $pdo->exec((string) file_get_contents(ROOT_PATH . '/database/schema.sqlite.sql'));
                $pdo->exec((string) file_get_contents(ROOT_PATH . '/database/seed.sql'));
            } catch (\Throwable $e) {
                $pdo = null;
                @unlink($caminho); // apaga o arquivo incompleto para tentar de novo na próxima vez
                throw $e;
            }
        }
        return $pdo;
    }

    private static function mysql(): PDO
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            Env::get('DB_HOST', 'localhost'),
            Env::get('DB_PORT', '3306'),
            Env::get('DB_NAME', 'pdv')
        );
        return new PDO($dsn, Env::get('DB_USER', 'root'), Env::get('DB_PASS', ''), self::opcoes());
    }

    private static function pgsql(): PDO
    {
        $url = Env::get('DATABASE_URL') ?? Env::get('SRVDB');

        if ($url) {
            $partes = parse_url($url);
            if (!$partes || !isset($partes['host'])) {
                throw new InvalidArgumentException('DATABASE_URL em formato inválido.');
            }
            $host  = $partes['host'];
            $porta = $partes['port'] ?? 5432;
            $user  = urldecode($partes['user'] ?? '');
            $senha = urldecode($partes['pass'] ?? '');
            $banco = ltrim($partes['path'] ?? '', '/');
        } else {
            $host  = Env::get('DB_HOST', 'localhost');
            $porta = Env::get('DB_PORT', '5432');
            $user  = Env::get('DB_USER', 'postgres');
            $senha = Env::get('DB_PASS', '');
            $banco = Env::get('DB_NAME', 'pdv');
        }

        $dsn = "pgsql:host={$host};port={$porta};dbname={$banco}";

        // Neon exige SSL e, em hospedagens com libpq antiga, o "endpoint" nas options
        // (mesma solução usada no db.php do professor).
        if (str_contains($host, 'neon.tech')) {
            $endpoint = explode('.', $host)[0];
            $dsn .= ";sslmode=require;options='endpoint={$endpoint}'";
        } elseif ($ssl = Env::get('DB_SSLMODE')) {
            $dsn .= ";sslmode={$ssl}";
        }

        return new PDO($dsn, $user, $senha, self::opcoes());
    }
}
