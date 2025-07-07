<?php
/**
 * PersonaAgent Factory Configuration
 * 大規模製造業向けペルソナエージェント設定ファイル
 */

// エラーレポート設定
error_reporting(E_ALL);
ini_set('display_errors', 1);

// タイムゾーン設定
date_default_timezone_set('Asia/Tokyo');

// セッション設定は後で実行

// 環境設定ファイル読み込み
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
    }
}

// データベース設定（今回は使用しない）
/*
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'personaagent_factory');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');
*/

// API設定
define('OPENAI_API_KEY', $_ENV['OPENAI_API_KEY'] ?? '');
define('CLAUDE_API_KEY', $_ENV['CLAUDE_API_KEY'] ?? '');
define('GEMINI_API_KEY', $_ENV['GEMINI_API_KEY'] ?? '');

// Google Sheets API設定
define('GOOGLE_CLIENT_ID', $_ENV['GOOGLE_CLIENT_ID'] ?? '');
define('GOOGLE_CLIENT_SECRET', $_ENV['GOOGLE_CLIENT_SECRET'] ?? '');
define('GOOGLE_REDIRECT_URI', $_ENV['GOOGLE_REDIRECT_URI'] ?? '');

// アプリケーション設定
define('APP_NAME', 'PersonaAgent Factory');
define('APP_VERSION', '1.0.0');
define('APP_URL', $_ENV['APP_URL'] ?? 'https://mokumoku.sakura.ne.jp/persona-factory/');

// セキュリティ設定
define('CSRF_TOKEN_NAME', 'csrf_token');
define('SESSION_TIMEOUT', 3600); // 1時間

// ファイルパス設定
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('LOGS_PATH', ROOT_PATH . '/logs');

// ログディレクトリ作成
if (!is_dir(LOGS_PATH)) {
    mkdir(LOGS_PATH, 0755, true);
}

/**
 * API キーを取得する関数
 */
function getApiKey($provider) {
    switch (strtolower($provider)) {
        case 'openai':
            return OPENAI_API_KEY;
        case 'claude':
            return CLAUDE_API_KEY;
        case 'gemini':
            return GEMINI_API_KEY;
        default:
            return '';
    }
}

/**
 * 利用可能なプロバイダーを取得
 */
function getAvailableProviders() {
    $providers = [];
    if (!empty(OPENAI_API_KEY)) $providers[] = 'openai';
    if (!empty(CLAUDE_API_KEY)) $providers[] = 'claude';
    if (!empty(GEMINI_API_KEY)) $providers[] = 'gemini';
    return $providers;
}

/**
 * CSRFトークン生成
 */
function generateCsrfToken() {
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * CSRFトークン検証
 */
function verifyCsrfToken($token) {
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

/**
 * ログ出力関数
 */
function writeLog($message, $level = 'INFO') {
    $logFile = LOGS_PATH . '/app_' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;
    file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
}

/**
 * エラーハンドラー
 */
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    $message = "Error [{$errno}]: {$errstr} in {$errfile} on line {$errline}";
    writeLog($message, 'ERROR');
    return false;
}

/**
 * 例外ハンドラー
 */
function customExceptionHandler($exception) {
    $message = "Uncaught exception: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine();
    writeLog($message, 'CRITICAL');
}

// エラーハンドラー設定
set_error_handler('customErrorHandler');
set_exception_handler('customExceptionHandler');

/**
 * セキュリティヘッダー設定
 */
function setSecurityHeaders() {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // HTTPS環境でのみHTSTSヘッダーを設定
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

// セキュリティヘッダー適用
setSecurityHeaders();

// セッション設定とスタート
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_secure', 1);
}
session_start();

/**
 * ペルソナデータ読み込み
 */
function loadPersonas() {
    $personasFile = SRC_PATH . '/personas.json';
    if (file_exists($personasFile)) {
        $json = file_get_contents($personasFile);
        $data = json_decode($json, true);
        return $data['personas'] ?? [];
    }
    return [];
}

/**
 * API呼び出し共通関数
 */
function makeApiCall($url, $headers, $data = null, $method = 'POST') {
    $ch = curl_init();
    
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_CUSTOMREQUEST => $method,
    ]);
    
    if ($data !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    if ($error) {
        writeLog("cURL Error: {$error}", 'ERROR');
        return false;
    }
    
    if ($httpCode !== 200) {
        writeLog("HTTP Error {$httpCode}: {$response}", 'ERROR');
        return false;
    }
    
    return $response;
}

// 初期化完了ログ
writeLog("PersonaAgent Factory initialized successfully");

?>