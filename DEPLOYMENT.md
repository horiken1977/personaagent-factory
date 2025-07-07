# PersonaAgent Factory - デプロイメントガイド

## 📋 概要

PersonaAgent Factory は、大規模製造業向けのペルソナエージェントアプリケーションです。
Sakura Internet の shared hosting 環境（mokumoku.sakura.ne.jp）にデプロイする手順を説明します。

## 🎯 デプロイ先

- **URL**: https://mokumoku.sakura.ne.jp/persona-factory/
- **ディレクトリ**: /home/mokumoku/www/persona-factory/
- **PHP バージョン**: 7.4 以上

## 📁 ファイル構成

```
persona-factory/
├── index.php                  # エントリーポイント
├── setup.php                  # 初期設定画面
├── .env                       # 環境設定ファイル（デプロイ時に作成）
├── .env.example              # 環境設定テンプレート
├── config/
│   └── config.php            # アプリケーション設定
├── src/
│   ├── index.html            # メインUI
│   ├── personas.json         # ペルソナデータ
│   ├── css/
│   │   └── style.css         # スタイルシート
│   ├── js/
│   │   └── script.js         # フロントエンドJavaScript
│   └── api/
│       ├── providers.php     # プロバイダーAPI
│       └── chat.php          # チャットAPI
├── logs/                     # ログディレクトリ（自動作成）
└── docs/                     # ドキュメント（既存）
```

## 🚀 デプロイ手順

### 1. ファイルアップロード

以下のファイル・フォルダを `/home/mokumoku/www/persona-factory/` にアップロード：

```bash
# 必要なファイル・フォルダ
- index.php
- setup.php
- .env.example
- config/
- src/
```

### 2. 環境設定ファイルの作成

```bash
# .env ファイルを作成
cp .env.example .env
```

### 3. .env ファイルの編集

```env
# OpenAI API設定
OPENAI_API_KEY=your_actual_openai_api_key_here

# Anthropic Claude API設定
CLAUDE_API_KEY=your_actual_claude_api_key_here

# Google Gemini API設定
GEMINI_API_KEY=your_actual_gemini_api_key_here

# アプリケーション設定
APP_URL=https://mokumoku.sakura.ne.jp/persona-factory/

# デバッグモード（本番環境では false）
DEBUG_MODE=false
```

### 4. ディレクトリ権限設定

```bash
# ログディレクトリの権限設定
chmod 755 /home/mokumoku/www/persona-factory/
chmod 755 /home/mokumoku/www/persona-factory/logs/
```

### 5. API キーの取得

#### OpenAI API キー
1. https://platform.openai.com/ にアクセス
2. アカウント作成・ログイン
3. API Keys メニューから新しいキーを作成

#### Anthropic Claude API キー
1. https://console.anthropic.com/ にアクセス
2. アカウント作成・ログイン
3. API Keys から新しいキーを作成

#### Google Gemini API キー
1. https://makersuite.google.com/app/apikey にアクセス
2. Google アカウントでログイン
3. API キーを作成

### 6. 動作確認

1. ブラウザで https://mokumoku.sakura.ne.jp/persona-factory/ にアクセス
2. API キーが未設定の場合、セットアップ画面が表示される
3. API キー設定後、メインアプリケーションが表示される

## 🔧 設定オプション

### デバッグモード

本番環境では `.env` ファイルで以下を設定：

```env
DEBUG_MODE=false
```

### ログ設定

- ログファイル: `/logs/app_YYYY-MM-DD.log`
- ログローテーション: 日次
- ログレベル: INFO, ERROR, CRITICAL

### セキュリティ設定

- CSRF トークン保護
- XSS 防止ヘッダー
- HTTPS 強制（本番環境）
- セッションセキュリティ

## 🔍 トラブルシューティング

### 1. 500 エラーが発生する場合

```bash
# エラーログを確認
tail -f /home/mokumoku/www/persona-factory/logs/app_$(date +%Y-%m-%d).log
```

一般的な原因：
- PHP バージョンが 7.4 未満
- ディレクトリ権限の問題
- .env ファイルの構文エラー

### 2. API 呼び出しが失敗する場合

- API キーが正しく設定されているか確認
- インターネット接続の確認
- API プロバイダーの利用制限の確認

### 3. ペルソナが表示されない場合

- `src/personas.json` ファイルの存在確認
- JSON 構文の確認
- ファイル権限の確認

## 📊 監視・メンテナンス

### ログ監視

```bash
# エラーログの監視
grep ERROR /home/mokumoku/www/persona-factory/logs/app_*.log

# アクセス状況の確認
grep "PersonaAgent Factory initialized" /home/mokumoku/www/persona-factory/logs/app_*.log
```

### 定期メンテナンス

1. **週次**: ログファイルのサイズ確認
2. **月次**: API 利用量の確認
3. **四半期**: セキュリティ更新の確認

### バックアップ

推奨バックアップ対象：
- `.env` ファイル（API キーを含む）
- `src/personas.json`（カスタマイズした場合）
- ログファイル（必要に応じて）

## 🔄 更新手順

1. 本番環境のバックアップ作成
2. 新しいファイルをアップロード
3. 設定ファイルの更新（必要に応じて）
4. 動作確認

## 📞 サポート

- **技術的な問題**: GitHub Issues
- **設定に関する質問**: デプロイメントログを参照
- **緊急時**: アプリケーションログを確認

## 🔐 セキュリティ注意事項

1. **API キーの管理**
   - .env ファイルを Git に含めない
   - 定期的なキーローテーション
   - 最小権限の原則

2. **アクセス制御**
   - 必要に応じて IP 制限
   - HTTPS 必須
   - セッション管理

3. **監査**
   - アクセスログの定期確認
   - 異常なトラフィックの監視
   - セキュリティ更新の適用

---

## 📋 チェックリスト

デプロイ完了時の確認項目：

- [ ] 全ファイルのアップロード完了
- [ ] .env ファイルの作成・設定
- [ ] API キーの設定・テスト
- [ ] ディレクトリ権限の設定
- [ ] アプリケーションの動作確認
- [ ] ペルソナ選択の動作確認
- [ ] チャット機能の動作確認
- [ ] エラーログの確認
- [ ] セキュリティ設定の確認
- [ ] バックアップ手順の確認