<?php
/**
 * PersonaAgent Factory - Logs API
 * アプリケーションログの表示・取得機能
 */

require_once '../../config/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

try {
    $action = $_GET['action'] ?? 'recent';
    $lines = intval($_GET['lines'] ?? 50);
    
    switch ($action) {
        case 'recent':
            echo json_encode(getRecentLogs($lines));
            break;
        case 'error':
            echo json_encode(getErrorLogs($lines));
            break;
        case 'files':
            echo json_encode(getLogFiles());
            break;
        case 'file':
            $filename = $_GET['filename'] ?? '';
            echo json_encode(getLogFile($filename, $lines));
            break;
        default:
            throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

/**
 * 最新のログを取得
 */
function getRecentLogs($lines = 50) {
    $logFile = LOGS_PATH . '/app_' . date('Y-m-d') . '.log';
    $logs = [];
    
    if (file_exists($logFile)) {
        $logs = array_slice(file($logFile, FILE_IGNORE_NEW_LINES), -$lines);
    }
    
    return [
        'success' => true,
        'logs' => $logs,
        'file' => basename($logFile),
        'count' => count($logs)
    ];
}

/**
 * エラーログのみを取得
 */
function getErrorLogs($lines = 50) {
    $logFile = LOGS_PATH . '/app_' . date('Y-m-d') . '.log';
    $allLogs = [];
    
    if (file_exists($logFile)) {
        $allLogs = file($logFile, FILE_IGNORE_NEW_LINES);
    }
    
    // エラーレベルのログのみフィルタ
    $errorLogs = array_filter($allLogs, function($log) {
        return strpos($log, '[ERROR]') !== false || strpos($log, '[CRITICAL]') !== false;
    });
    
    // 最新N件を取得
    $errorLogs = array_slice($errorLogs, -$lines);
    
    return [
        'success' => true,
        'logs' => array_values($errorLogs),
        'file' => basename($logFile),
        'count' => count($errorLogs)
    ];
}

/**
 * 利用可能なログファイル一覧を取得
 */
function getLogFiles() {
    $files = [];
    
    if (is_dir(LOGS_PATH)) {
        $logFiles = glob(LOGS_PATH . '/*.log');
        foreach ($logFiles as $file) {
            $files[] = [
                'name' => basename($file),
                'size' => filesize($file),
                'modified' => date('Y-m-d H:i:s', filemtime($file))
            ];
        }
        
        // 更新日時順でソート
        usort($files, function($a, $b) {
            return strtotime($b['modified']) - strtotime($a['modified']);
        });
    }
    
    return [
        'success' => true,
        'files' => $files,
        'count' => count($files)
    ];
}

/**
 * 特定のログファイルを取得
 */
function getLogFile($filename, $lines = 50) {
    $logFile = LOGS_PATH . '/' . basename($filename); // セキュリティ対策
    $logs = [];
    
    if (file_exists($logFile) && pathinfo($logFile, PATHINFO_EXTENSION) === 'log') {
        $logs = array_slice(file($logFile, FILE_IGNORE_NEW_LINES), -$lines);
    } else {
        throw new Exception('Log file not found or invalid');
    }
    
    return [
        'success' => true,
        'logs' => $logs,
        'file' => basename($logFile),
        'count' => count($logs)
    ];
}
?>