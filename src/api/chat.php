<?php
/**
 * PersonaAgent Factory - Chat API
 * ペルソナとのチャット機能を提供するAPI
 */

require_once '../../config/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// POSTリクエストのみ許可
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // リクエストデータを取得
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON input');
    }
    
    // 必須パラメータのチェック
    $requiredFields = ['message', 'persona', 'provider'];
    foreach ($requiredFields as $field) {
        if (!isset($input[$field]) || empty($input[$field])) {
            throw new Exception("Missing required field: {$field}");
        }
    }
    
    $userMessage = trim($input['message']);
    $persona = $input['persona'];
    $provider = $input['provider'];
    $history = $input['history'] ?? [];
    
    // バリデーション
    if (strlen($userMessage) > 2000) {
        throw new Exception('メッセージが長すぎます（最大2000文字）');
    }
    
    if (!in_array($provider, getAvailableProviders())) {
        throw new Exception('指定されたプロバイダーは利用できません');
    }
    
    // ペルソナに基づいたシステムプロンプトを生成
    $systemPrompt = generateSystemPrompt($persona);
    
    // AI APIを呼び出して応答を取得
    $response = callAIProvider($provider, $systemPrompt, $userMessage, $history);
    
    // レスポンス生成
    $result = [
        'success' => true,
        'response' => $response,
        'persona' => $persona['name'],
        'provider' => $provider,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    // ログ出力
    writeLog("Chat request processed - Persona: {$persona['name']}, Provider: {$provider}");
    
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    writeLog("Chat API error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine(), 'ERROR');
    writeLog("Request data: " . json_encode($_POST), 'ERROR');
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'debug' => [
            'file' => basename($e->getFile()),
            'line' => $e->getLine(),
            'provider_available' => in_array($provider ?? 'unknown', getAvailableProviders()),
            'timestamp' => date('Y-m-d H:i:s')
        ]
    ], JSON_UNESCAPED_UNICODE);
}

/**
 * ペルソナに基づいたシステムプロンプト生成
 */
function generateSystemPrompt($persona) {
    $prompt = "あなたは{$persona['name']}として振る舞ってください。\n\n";
    $prompt .= "【基本情報】\n";
    $prompt .= "- 名前: {$persona['name']}\n";
    $prompt .= "- 年齢: {$persona['age']}歳\n";
    $prompt .= "- 職種: {$persona['position']}\n";
    $prompt .= "- 勤務地: {$persona['location']}\n";
    
    if (isset($persona['years_of_experience'])) {
        $prompt .= "- 経験年数: {$persona['years_of_experience']}\n";
    }
    
    if (isset($persona['main_responsibilities'])) {
        $prompt .= "- 主な業務: {$persona['main_responsibilities']}\n";
    }
    
    $prompt .= "\n【役割と行動指針】\n";
    $prompt .= "1. 製造業の現場経験者として、実務的で具体的なアドバイスを提供してください\n";
    $prompt .= "2. あなたの職種と経験に基づいた視点で回答してください\n";
    $prompt .= "3. 専門用語は適度に使用し、必要に応じて分かりやすく説明してください\n";
    $prompt .= "4. 現場での実体験や課題を織り交ぜながら回答してください\n";
    $prompt .= "5. 丁寧で親しみやすい口調を心がけてください\n";
    
    if (isset($persona['daily_challenges'])) {
        $prompt .= "\n【あなたが普段直面している課題】\n";
        $prompt .= $persona['daily_challenges'] . "\n";
    }
    
    if (isset($persona['key_motivations'])) {
        $prompt .= "\n【あなたのモチベーション】\n";
        $prompt .= $persona['key_motivations'] . "\n";
    }
    
    $prompt .= "\n相談者の質問に対して、あなたの立場と経験を活かした実用的なアドバイスを提供してください。";
    
    return $prompt;
}

/**
 * AI プロバイダー呼び出し
 */
function callAIProvider($provider, $systemPrompt, $userMessage, $history = []) {
    switch ($provider) {
        case 'openai':
            return callOpenAI($systemPrompt, $userMessage, $history);
        case 'claude':
            return callClaude($systemPrompt, $userMessage, $history);
        case 'gemini':
            return callGemini($systemPrompt, $userMessage, $history);
        default:
            throw new Exception('Unknown provider: ' . $provider);
    }
}

/**
 * OpenAI API 呼び出し
 */
function callOpenAI($systemPrompt, $userMessage, $history) {
    $apiKey = getApiKey('openai');
    if (empty($apiKey)) {
        throw new Exception('OpenAI API key not configured');
    }
    
    // メッセージ履歴を構築
    $messages = [
        ['role' => 'system', 'content' => $systemPrompt]
    ];
    
    // 履歴を追加（最新10件まで）
    foreach (array_slice($history, -10) as $item) {
        $role = $item['type'] === 'user' ? 'user' : 'assistant';
        $messages[] = ['role' => $role, 'content' => $item['content']];
    }
    
    // 現在のユーザーメッセージを追加
    $messages[] = ['role' => 'user', 'content' => $userMessage];
    
    $data = json_encode([
        'model' => 'gpt-4',
        'messages' => $messages,
        'max_tokens' => 1500,
        'temperature' => 0.7
    ]);
    
    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ];
    
    $response = makeApiCall('https://api.openai.com/v1/chat/completions', $headers, $data);
    
    if (!$response) {
        throw new Exception('OpenAI API call failed');
    }
    
    $result = json_decode($response, true);
    
    if (isset($result['error'])) {
        throw new Exception('OpenAI API error: ' . $result['error']['message']);
    }
    
    if (!isset($result['choices'][0]['message']['content'])) {
        throw new Exception('Invalid response from OpenAI API');
    }
    
    return trim($result['choices'][0]['message']['content']);
}

/**
 * Claude API 呼び出し
 * 利用可能なモデル: claude-3-5-sonnet-latest (Sonnet 4), claude-3-opus-latest (Opus 4)
 */
function callClaude($systemPrompt, $userMessage, $history) {
    $apiKey = getApiKey('claude');
    if (empty($apiKey)) {
        writeLog("Claude API key is empty: " . var_export($apiKey, true), 'ERROR');
        throw new Exception('Claude API key not configured');
    }
    
    writeLog("Claude API call starting with key length: " . strlen($apiKey), 'INFO');
    
    // Claude用のメッセージ形式を構築
    $messages = [];
    
    // 履歴を追加
    foreach (array_slice($history, -10) as $item) {
        $role = $item['type'] === 'user' ? 'user' : 'assistant';
        $messages[] = ['role' => $role, 'content' => $item['content']];
    }
    
    // 現在のユーザーメッセージを追加
    $messages[] = ['role' => 'user', 'content' => $userMessage];
    
    $data = json_encode([
        'model' => 'claude-3-5-sonnet-latest',  // Claude Sonnet 4 最新版
        'system' => $systemPrompt,
        'messages' => $messages,
        'max_tokens' => 1500
    ]);
    
    $headers = [
        'Content-Type: application/json',
        'x-api-key: ' . $apiKey,
        'anthropic-version: 2023-06-01'
    ];
    
    $response = makeApiCall('https://api.anthropic.com/v1/messages', $headers, $data);
    
    if (!$response) {
        writeLog("Claude API call failed - no response received", 'ERROR');
        throw new Exception('Claude API call failed');
    }
    
    writeLog("Claude API response received: " . substr($response, 0, 200) . "...", 'INFO');
    
    $result = json_decode($response, true);
    
    if (!$result) {
        writeLog("Claude API response JSON decode failed: " . json_last_error_msg(), 'ERROR');
        writeLog("Raw response: " . $response, 'ERROR');
        throw new Exception('Claude API response decode failed');
    }
    
    if (isset($result['error'])) {
        writeLog("Claude API error: " . json_encode($result['error']), 'ERROR');
        throw new Exception('Claude API error: ' . ($result['error']['message'] ?? 'Unknown error'));
    }
    
    if (!isset($result['content'][0]['text'])) {
        writeLog("Claude API invalid response structure: " . json_encode($result), 'ERROR');
        throw new Exception('Invalid response from Claude API - missing content');
    }
    
    return trim($result['content'][0]['text']);
}

/**
 * Gemini API 呼び出し
 * 利用可能なモデル: gemini-2.0-flash-exp (最新), gemini-1.5-pro, gemini-1.5-flash
 */
function callGemini($systemPrompt, $userMessage, $history) {
    $apiKey = getApiKey('gemini');
    if (empty($apiKey)) {
        throw new Exception('Gemini API key not configured');
    }
    
    // Gemini用のコンテンツを構築
    $prompt = $systemPrompt . "\n\n";
    
    // 履歴を追加
    foreach (array_slice($history, -10) as $item) {
        $role = $item['type'] === 'user' ? 'User' : 'Assistant';
        $prompt .= "{$role}: {$item['content']}\n";
    }
    
    $prompt .= "User: {$userMessage}\nAssistant: ";
    
    $data = json_encode([
        'contents' => [
            ['parts' => [['text' => $prompt]]]
        ],
        'generationConfig' => [
            'maxOutputTokens' => 1500,
            'temperature' => 0.7
        ]
    ]);
    
    $headers = [
        'Content-Type: application/json'
    ];
    
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-exp:generateContent?key={$apiKey}";
    $response = makeApiCall($url, $headers, $data);
    
    if (!$response) {
        throw new Exception('Gemini API call failed');
    }
    
    $result = json_decode($response, true);
    
    if (isset($result['error'])) {
        throw new Exception('Gemini API error: ' . $result['error']['message']);
    }
    
    if (!isset($result['candidates'][0]['content']['parts'][0]['text'])) {
        throw new Exception('Invalid response from Gemini API');
    }
    
    return trim($result['candidates'][0]['content']['parts'][0]['text']);
}

?>