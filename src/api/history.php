<?php
/**
 * PersonaAgent Factory - Chat History API
 * チャット履歴の保存・取得・エクスポート機能
 */

require_once '../../config/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// セッションIDの生成（ユーザー識別用）
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = uniqid('user_', true);
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'POST':
            handleSaveHistory();
            break;
        case 'GET':
            if (isset($_GET['export']) && $_GET['export'] === 'csv') {
                handleExportHistory();
            } else {
                handleGetHistory();
            }
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    }
} catch (Exception $e) {
    writeLog("History API error: " . $e->getMessage(), 'ERROR');
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

/**
 * チャット履歴を保存
 */
function handleSaveHistory() {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON input');
    }
    
    $requiredFields = ['persona_id', 'persona_name', 'provider', 'user_message', 'ai_response'];
    foreach ($requiredFields as $field) {
        if (!isset($input[$field])) {
            throw new Exception("Missing required field: {$field}");
        }
    }
    
    $historyData = [
        'id' => uniqid('chat_', true),
        'user_id' => $_SESSION['user_id'],
        'timestamp' => date('Y-m-d H:i:s'),
        'persona_id' => $input['persona_id'],
        'persona_name' => $input['persona_name'],
        'provider' => $input['provider'],
        'user_message' => $input['user_message'],
        'ai_response' => $input['ai_response'],
        'session_id' => session_id()
    ];
    
    // ファイルに保存
    $historyFile = LOGS_PATH . '/chat_history_' . date('Y-m') . '.json';
    saveHistoryToFile($historyFile, $historyData);
    
    writeLog("Chat history saved: " . $historyData['id']);
    
    echo json_encode([
        'success' => true,
        'message' => 'チャット履歴が保存されました',
        'history_id' => $historyData['id']
    ]);
}

/**
 * チャット履歴を取得
 */
function handleGetHistory() {
    $userId = $_SESSION['user_id'];
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 100;
    
    $histories = loadUserHistory($userId, $limit);
    
    echo json_encode([
        'success' => true,
        'histories' => $histories,
        'count' => count($histories),
        'user_id' => $userId
    ]);
}

/**
 * チャット履歴をCSVでエクスポート
 */
function handleExportHistory() {
    $userId = $_SESSION['user_id'];
    $histories = loadUserHistory($userId, 0); // 全履歴
    
    // CSVヘッダー設定
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="persona_chat_history_' . date('Y-m-d_H-i-s') . '.csv"');
    header('Cache-Control: no-cache, must-revalidate');
    
    // BOM追加（Excel対応）
    echo "\xEF\xBB\xBF";
    
    // CSVヘッダー
    $headers = [
        '日時',
        'ペルソナ名',
        'AIプロバイダー',
        'ユーザーメッセージ',
        'AI応答',
        '履歴ID'
    ];
    
    $output = fopen('php://output', 'w');
    fputcsv($output, $headers);
    
    // データ出力
    foreach ($histories as $history) {
        $row = [
            $history['timestamp'],
            $history['persona_name'],
            $history['provider'],
            $history['user_message'],
            $history['ai_response'],
            $history['id']
        ];
        fputcsv($output, $row);
    }
    
    fclose($output);
    writeLog("Chat history exported: " . count($histories) . " records for user " . $userId);
    exit;
}

/**
 * 履歴をファイルに保存
 */
function saveHistoryToFile($filename, $data) {
    $histories = [];
    
    // 既存ファイルを読み込み
    if (file_exists($filename)) {
        $content = file_get_contents($filename);
        if ($content) {
            $histories = json_decode($content, true) ?: [];
        }
    }
    
    // 新しいデータを追加
    $histories[] = $data;
    
    // ファイルに保存
    file_put_contents($filename, json_encode($histories, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX);
}

/**
 * ユーザーの履歴を読み込み
 */
function loadUserHistory($userId, $limit = 100) {
    $allHistories = [];
    
    // 過去6ヶ月のファイルを検索
    for ($i = 0; $i < 6; $i++) {
        $date = date('Y-m', strtotime("-{$i} months"));
        $filename = LOGS_PATH . '/chat_history_' . $date . '.json';
        
        if (file_exists($filename)) {
            $content = file_get_contents($filename);
            if ($content) {
                $histories = json_decode($content, true) ?: [];
                $userHistories = array_filter($histories, function($h) use ($userId) {
                    return isset($h['user_id']) && $h['user_id'] === $userId;
                });
                $allHistories = array_merge($allHistories, $userHistories);
            }
        }
    }
    
    // 日時順でソート（新しい順）
    usort($allHistories, function($a, $b) {
        return strtotime($b['timestamp']) - strtotime($a['timestamp']);
    });
    
    // 制限適用
    if ($limit > 0) {
        $allHistories = array_slice($allHistories, 0, $limit);
    }
    
    return $allHistories;
}

?>