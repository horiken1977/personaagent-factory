<?php
/**
 * PersonaAgent Factory - Providers API
 * 利用可能なAIプロバイダー一覧を返すAPI
 */

require_once '../../config/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

try {
    // APIキーの状況をチェック
    $apiKeyStatus = [
        'openai' => !empty(OPENAI_API_KEY) ? 'configured' : 'missing',
        'claude' => !empty(CLAUDE_API_KEY) ? 'configured' : 'missing', 
        'gemini' => !empty(GEMINI_API_KEY) ? 'configured' : 'missing'
    ];
    
    // 詳細デバッグ情報
    $envContent = [];
    $envPath = __DIR__ . '/../../.env';
    if (file_exists($envPath)) {
        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) continue;
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $envContent[trim($key)] = strlen(trim($value)) > 0 ? 'set' : 'empty';
            }
        }
    }
    
    // 利用可能なプロバイダーを取得
    $providers = getAvailableProviders();
    
    // レスポンス生成
    $response = [
        'success' => true,
        'providers' => $providers,
        'count' => count($providers),
        'debug' => [
            'api_key_status' => $apiKeyStatus,
            'env_loaded' => file_exists(__DIR__ . '/../../.env')
        ]
    ];
    
    // ログ出力
    writeLog("Available providers requested: " . implode(', ', $providers) . " | API Keys: " . json_encode($apiKeyStatus));
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    writeLog("Providers API error: " . $e->getMessage(), 'ERROR');
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'プロバイダー情報の取得に失敗しました',
        'providers' => []
    ], JSON_UNESCAPED_UNICODE);
}
?>