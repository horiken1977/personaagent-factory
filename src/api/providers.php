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
    // 利用可能なプロバイダーを取得
    $providers = getAvailableProviders();
    
    // レスポンス生成
    $response = [
        'success' => true,
        'providers' => $providers,
        'count' => count($providers)
    ];
    
    // ログ出力
    writeLog("Available providers requested: " . implode(', ', $providers));
    
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