# PersonaAgent Factory

パーソナライズされたAIエージェントを作成・管理するためのアプリケーション開発プロジェクト

## 📋 プロジェクト概要

PersonaAgent Factory は、ユーザーが独自の要件に基づいて、カスタマイズされたAIエージェントを簡単に作成・デプロイできるプラットフォームです。

## 🏗️ プロジェクト構造

```
personaagent_factory/
├── docs/                           # ドキュメント類
│   ├── index.html                 # ドキュメントポータル
│   ├── dashboard.html             # 開発ダッシュボード
│   ├── functional_design.html     # 機能設計書
│   ├── environment_design.html    # 環境設計書
│   ├── test_specification.html    # テスト仕様書
│   ├── _config.yml                # GitHub Pages設定
│   └── scripts/                   # 自動更新スクリプト
│       ├── auto_update.py         # チャット解析・自動更新
│       └── backup_chat.py         # チャット自動保存
├── test/                          # テスト関連
│   ├── scripts/                   # テスト実行スクリプト
│   └── data/                      # テストデータ
├── backups/                       # チャットバックアップ
├── claude.md                      # Claude Code メモリファイル
└── README.md                      # このファイル
```

## 🚀 特徴

### 📊 自動ドキュメント管理
- 開発ダッシュボードで進捗を可視化
- 機能設計書、環境設計書、テスト仕様書を自動生成
- GitHub Pages での公開対応

### 🔄 自動更新システム
- Claude Code とのチャット内容から自動的に設計書を更新
- キーワードベースの内容解析
- リアルタイムでの進捗反映

### 💾 自動バックアップ
- 2時間毎のチャット履歴自動保存
- 最大48ファイル（4日分）の履歴保持
- システムクラッシュ時の安全な復旧

## 🛠️ 技術スタック

### フロントエンド
- HTML5, CSS3, JavaScript
- レスポンシブデザイン
- GitHub Pages

### バックエンド（予定）
- Python/Node.js
- RESTful API
- OAuth2.0 認証

### データベース（予定）
- PostgreSQL/MongoDB

### 自動化
- Python スクリプト
- cron/launchd 対応

## 📖 ドキュメント

開発ドキュメントは以下で確認できます：

- [📊 開発ダッシュボード](docs/dashboard.html) - プロジェクト進捗とフェーズ管理
- [⚙️ 機能設計書](docs/functional_design.html) - アプリケーションの機能要件
- [🏗️ 環境設計書](docs/environment_design.html) - インフラ・環境構成
- [🧪 テスト仕様書](docs/test_specification.html) - テスト計画・テストケース

## 🔧 セットアップ

### 1. 自動更新スクリプトの実行

```bash
# 手動実行
python docs/scripts/auto_update.py

# チャットバックアップ
python docs/scripts/backup_chat.py

# バックアップ状況確認
python docs/scripts/backup_chat.py status
```

### 2. 定期実行設定

#### cron設定（Linux/macOS）
```bash
# crontab -e で以下を追加
0 */2 * * * cd /path/to/personaagent_factory && python docs/scripts/backup_chat.py
```

#### launchd設定（macOS）
```bash
# ~/Library/LaunchAgents/com.personaagent.backup.plist を作成
# 詳細は claude.md を参照
```

### 3. GitHub Pages設定

1. GitHubリポジトリを作成
2. `docs/` フォルダをGitHub Pagesソースに設定
3. `docs/_config.yml` でサイト設定をカスタマイズ

## 🎯 開発ロードマップ

### フェーズ1: 環境構築・基盤整備 ✅
- [x] プロジェクト構造作成
- [x] ドキュメント管理システム
- [x] 自動更新スクリプト
- [x] バックアップシステム

### フェーズ2: 基本機能開発 🔄
- [ ] AIエージェント作成機能
- [ ] 設定管理機能
- [ ] 基本的なUI/UX

### フェーズ3: 高度機能開発 📋
- [ ] デプロイ機能
- [ ] 監視・ログ機能
- [ ] パフォーマンス最適化

### フェーズ4: テスト・品質保証 📋
- [ ] 自動テスト実装
- [ ] パフォーマンステスト
- [ ] セキュリティテスト

### フェーズ5: デプロイ・運用開始 📋
- [ ] 本番環境構築
- [ ] 運用監視システム
- [ ] ユーザードキュメント

## 🤝 貢献

このプロジェクトは Claude Code を使用して開発されています。

## 📄 ライセンス

このプロジェクトは MIT ライセンスの下で公開されています。

## 📞 サポート

質問や問題がある場合は、以下の方法でお問い合わせください：

- GitHub Issues
- claude.md での開発ログ確認
- 開発ダッシュボードでの進捗確認

---

最終更新: 2025-01-07