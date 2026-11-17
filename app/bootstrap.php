<?php
/**
 * Inicialização da aplicação: autoload, .env, fuso horário e sessão.
 * Incluído pelo Front Controller (public/index.php).
 */

declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('PUBLIC_PATH', ROOT_PATH . '/public');

// Autoload PSR-4 simples: App\Controllers\ClienteController -> app/Controllers/ClienteController.php
spl_autoload_register(function (string $classe): void {
    $prefixo = 'App\\';
    if (strncmp($classe, $prefixo, strlen($prefixo)) !== 0) {
        return;
    }
    $relativo = str_replace('\\', '/', substr($classe, strlen($prefixo)));
    $arquivo = APP_PATH . '/' . $relativo . '.php';
    if (is_file($arquivo)) {
        require $arquivo;
    }
});

App\Core\Env::carregar(ROOT_PATH . '/.env');

date_default_timezone_set(App\Core\Env::get('APP_TIMEZONE', 'America/Cuiaba'));

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require APP_PATH . '/helpers.php';
