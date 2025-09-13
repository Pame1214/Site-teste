<?php
/**
 * SUPER DEBUG PHP - Testa tudo: ambiente, PHP, arquivos, banco, web, permissões, logs, curl, API
 * Uso: php debug_tudo.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "====================\n";
echo "🛠️ SUPER DEBUG DO SISTEMA DE CHAT\n";
echo "Data: " . date('Y-m-d H:i:s') . "\n";
echo "====================\n\n";

// --------- 1. Ambiente e PHP ---------
echo "🔎 Ambiente e PHP\n";
echo "Sistema operacional: " . PHP_OS . "\n";
echo "Versão do PHP: " . PHP_VERSION . "\n";
echo "SAPI: " . php_sapi_name() . "\n";
echo "Diretório Atual: " . __DIR__ . "\n\n";

// --------- 2. Extensões PHP ---------
echo "🔎 Extensões PHP\n";
$exts = ['pdo', 'pdo_mysql', 'mysqli', 'mbstring', 'json', 'openssl', 'curl', 'zip'];
foreach ($exts as $ext) {
    echo extension_loaded($ext) ? "✅ $ext\n" : "❌ $ext\n";
}
echo "\n";

// --------- 3. Permissões de arquivos/folders ---------
echo "🔎 Permissões e Arquivos Principais\n";
$arquivos = [
    'index.php', 'login.php', 'register.php', 'room.php', 'logout.php', 'config.php',
    'lib/db.php', 'lib/chat.js', 'lib/style.css',
    'api/send_message.php', 'api/get_messages.php', 'api/get_users.php',
    'api/admin_block_user.php', 'api/admin_delete_message.php'
];
foreach ($arquivos as $f) {
    $p = __DIR__ . '/' . $f;
    if (file_exists($p)) {
        echo "✅ $f existe, permissão: " . substr(sprintf('%o', fileperms($p)), -4) . "\n";
    } else {
        echo "❌ $f FALTANDO\n";
    }
}
echo "\n";

// --------- 4. Variáveis de ambiente importantes ---------
echo "🔎 Variáveis de ambiente\n";
echo "PATH: " . getenv('PATH') . "\n";
echo "DOCUMENT_ROOT: " . ($_SERVER['DOCUMENT_ROOT'] ?? '') . "\n\n";

// --------- 5. Configuração de banco ---------
echo "🔎 Configuração do banco\n";
$config = __DIR__ . '/config.php';
if (file_exists($config)) {
    include $config;
    $DB_HOST = defined('DB_HOST') ? DB_HOST : '';
    $DB_NAME = defined('DB_NAME') ? DB_NAME : '';
    $DB_USER = defined('DB_USER') ? DB_USER : '';
    $DB_PASS = defined('DB_PASS') ? DB_PASS : '';
    echo "Host: $DB_HOST\nBanco: $DB_NAME\nUser: $DB_USER\n";
} else {
    echo "❌ config.php não encontrado\n";
}
echo "\n";

// --------- 6. Teste de PDO MySQL ---------
echo "🔎 Teste de conexão PDO MySQL\n";
try {
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    echo "✅ Conexão PDO OK\n";
    $tabelas = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tabelas: " . implode(', ', $tabelas) . "\n";
    foreach(['users','messages'] as $tb) {
        if (in_array($tb, $tabelas)) {
            echo "Estrutura $tb:\n";
            $desc = $pdo->query("DESCRIBE `$tb`")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($desc as $col) echo "- {$col['Field']} ({$col['Type']})\n";
        } else {
            echo "❌ Tabela $tb não existe!\n";
        }
    }
} catch(Exception $e) {
    echo "❌ Erro na conexão PDO: " . $e->getMessage() . "\n";
}
echo "\n";

// --------- 7. Teste de escrita em pasta ---------
echo "🔎 Teste de escrita em pasta\n";
$testfile = __DIR__ . '/debug_test.txt';
$try = @file_put_contents($testfile, "Teste de escrita: ".date('Y-m-d H:i:s'));
if ($try !== false && file_exists($testfile)) {
    echo "✅ Escrita permitida em " . __DIR__ . "\n";
    unlink($testfile);
} else {
    echo "❌ Não foi possível escrever em " . __DIR__ . "\n";
}
echo "\n";

// --------- 8. Teste de funções de sistema ---------
echo "🔎 Funções de sistema (shell_exec, exec)\n";
if (function_exists('shell_exec')) {
    $out = shell_exec('ls -l ' . escapeshellarg(__DIR__));
    echo $out ? "✅ shell_exec OK\n" : "❌ shell_exec falhou\n";
} else {
    echo "❌ shell_exec desabilitado\n";
}
if (function_exists('exec')) {
    exec('ls -l', $lines, $code);
    echo $code === 0 ? "✅ exec OK\n" : "❌ exec falhou\n";
} else {
    echo "❌ exec desabilitado\n";
}
echo "\n";

// --------- 9. Teste HTTP (cURL) nas rotas principais ---------
echo "🔎 Testes HTTP (cURL)\n";
$urls = ['/', '/login.php', '/room.php', '/api/send_message.php'];
foreach ($urls as $u) {
    $url = "http://localhost$u";
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        echo "$url -> HTTP $code\n";
    } else {
        echo "❌ cURL desabilitado\n";
        break;
    }
}
echo "\n";

// --------- 10. LOGS DE ERRO PHP ---------
echo "🔎 Logs de erro PHP (últimas 10 linhas)\n";
$php_log = ini_get('error_log') ?: '/var/log/php_errors.log';
if (file_exists($php_log)) {
    $lines = file($php_log);
    $last = array_slice($lines, -10);
    foreach ($last as $l) echo $l;
} else {
    echo "❌ Log PHP não encontrado ($php_log)\n";
}
echo "\n";

// --------- 11. LOGS DE SERVIDOR WEB ---------
echo "🔎 Logs de Apache/Nginx (últimas 10 linhas)\n";
foreach(['/var/log/apache2/error.log','/var/log/nginx/error.log'] as $logfile) {
    if (file_exists($logfile)) {
        echo "\n$logfile:\n";
        $lines = file($logfile);
        $last = array_slice($lines, -10);
        foreach ($last as $l) echo $l;
    }
}
echo "\n";

// --------- 12. Teste de sessão PHP ---------
echo "🔎 Teste de sessão PHP\n";
session_start();
$_SESSION['debug_test'] = 'ok';
if ($_SESSION['debug_test'] === 'ok') {
    echo "✅ Sessão funciona\n";
} else {
    echo "❌ Sessão falhou\n";
}
session_destroy();
echo "\n";

// --------- 13. Teste de JSON ---------
echo "🔎 Teste de JSON\n";
$arr = ['a'=>1,'b'=>2];
$j = json_encode($arr);
if ($j && json_decode($j, true) == $arr) {
    echo "✅ JSON OK\n";
} else {
    echo "❌ JSON falhou\n";
}
echo "\n";

// --------- 14. Teste de mbstring ---------
echo "🔎 Teste de mbstring\n";
if (function_exists('mb_strlen')) {
    $len = mb_strlen("çãõ");
    echo "✅ mbstring OK, mb_strlen: $len\n";
} else {
    echo "❌ mbstring desabilitado\n";
}
echo "\n";

echo "====================\n";
echo "✅ SUPER DEBUG FINALIZADO\n";
echo "====================\n";