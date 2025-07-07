<?php
/**
 * PersonaAgent Factory - Entry Point
 * 大規模製造業向けペルソナエージェント メインエントリーポイント
 */

require_once 'config/config.php';

// API キーの設定確認
$availableProviders = getAvailableProviders();

if (empty($availableProviders)) {
    // API キーが設定されていない場合はセットアップページにリダイレクト
    header('Location: setup.php');
    exit;
} else {
    // API キーが設定されている場合はメインアプリケーションにリダイレクト
    header('Location: src/index.html');
    exit;
}
?>