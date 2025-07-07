# PersonaAgent Factory 開発メモ

## プロジェクト概要
PersonaAgent Factory は、パーソナライズされたAIエージェントを作成・管理するためのアプリケーションです。

## 開発環境構成

### フォルダ構造
```
personaagent_factory/
├── docs/                           # ドキュメント類
│   ├── dashboard.html             # 開発ダッシュボード
│   ├── functional_design.html     # 機能設計書
│   ├── environment_design.html    # 環境設計書
│   ├── test_specification.html    # テスト仕様書
│   └── scripts/                   # 自動更新スクリプト
│       ├── auto_update.py         # チャット解析・自動更新
│       └── backup_chat.py         # チャット自動保存
├── test/                          # テスト関連
│   ├── scripts/                   # テスト実行スクリプト
│   └── data/                      # テストデータ
├── backups/                       # チャットバックアップ
└── claude.md                      # Claude Code メモリファイル
```

### 開発ツール
- IDE: Claude Code
- OS: macOS (Darwin 24.5.0)
- バージョン管理: Git
- ドキュメント: HTML形式
- 自動化: Python スクリプト

## 実装済み機能

### 1. ドキュメント管理システム
- [x] 開発ダッシュボード（進捗グラフ、フェーズ表示）
- [x] 機能設計書テンプレート
- [x] 環境設計書テンプレート
- [x] テスト仕様書テンプレート
- [x] GitHub Pages対応（予定）

### 2. 自動更新システム
- [x] チャット解析・自動更新スクリプト
- [x] 設計書の自動更新機能
- [x] キーワードベースの内容抽出

### 3. バックアップシステム
- [x] 2時間毎の自動保存
- [x] 最大48ファイル（4日分）保存
- [x] バックアップ状況の表示
- [x] claude.mdの自動更新

## 次の開発予定

### 短期（1-2週間）
- [ ] GitHub Pages設定
- [ ] 自動更新スクリプトの改善
- [ ] エラーハンドリングの強化

### 中期（1-2ヶ月）
- [ ] 実際のアプリケーション機能開発
- [ ] データベース設計
- [ ] API設計

### 長期（3-6ヶ月）
- [ ] 本格的なAIエージェント機能
- [ ] 本番環境デプロイ
- [ ] ユーザーテスト

## 技術仕様

### フロントエンド
- HTML5, CSS3, JavaScript
- 予定: React/Vue.js

### バックエンド
- 予定: Python/Node.js
- API: RESTful

### データベース
- 予定: PostgreSQL/MongoDB

### インフラ
- 予定: Docker, AWS/Azure
- 開発環境: ローカル + GitHub

#
### 設定内容
- 保存間隔: 2時間毎
- 最大保存数: 48ファイル（4日分）
- 保存場所: `backups/` フォルダ
- ステータスファイル: `.backup_status`

### 実行方法
```bash
# 手動実行
python docs/scripts/backup_chat.py

# 状況確認
python docs/scripts/backup_chat.py status

# デーモンモード（2時間毎自動実行 + 30分毎カウントダウン表示）
python docs/scripts/backup_chat.py daemon

# 自動更新スクリプト実行
python docs/scripts/auto_update.py

# 監視システム（2時間毎にターミナルに状況表示）
python docs/scripts/status_monitor.py monitor

# 一回だけ状況表示
python docs/scripts/status_monitor.py
```

### cron設定例（Linux/macOS）
```bash
# 2時間毎に実行
0 */2 * * * cd /path/to/personaagent_factory && python docs/scripts/backup_chat.py

# 自動更新も同時実行
0 */2 * * * cd /path/to/personaagent_factory && python docs/scripts/backup_chat.py && python docs/scripts/auto_update.py
```

### launchd設定例（macOS）
`~/Library/LaunchAgents/com.personaagent.backup.plist` を作成:
```xml
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">
<dict>
    <key>Label</key>
    <string>com.personaagent.backup</string>
    <key>ProgramArguments</key>
    <array>
        <string>/usr/bin/python3</string>
        <string>/path/to/personaagent_factory/docs/scripts/backup_chat.py</string>
    </array>
    <key>StartInterval</key>
    <integer>7200</integer>
    <key>WorkingDirectory</key>
    <string>/path/to/personaagent_factory</string>
</dict>
</plist>
```

## バックアップ状況
最終更新: 2025-01-07 21:45:00

このセクションは自動保存スクリプトにより自動更新されます。

---

このファイルは Claude Code のメモリファイルとして機能し、開発の進捗や設定情報を記録します。

# 自動保存システム設定

## 設定内容
- 保存間隔: 2時間毎
- 最大保存数: 48ファイル（4日分）
- 保存場所: `backups/` フォルダ
- ステータスファイル: `.backup_status`

## 実行方法
```bash
# 手動実行
python docs/scripts/backup_chat.py

# 定期実行（cron設定例）
# 2時間毎に実行
0 */2 * * * cd /path/to/project && python docs/scripts/backup_chat.py

# macOSの場合（launchd設定例）
# ~/Library/LaunchAgents/com.personaagent.backup.plist を作成
```

## バックアップ状況
最終更新: 2025-07-07 14:29:46

このセクションは自動保存スクリプトにより自動更新されます。
