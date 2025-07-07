# GitHub Secrets 設定ガイド

GitHub Actions での自動デプロイを有効にするために、以下のSecrets を設定してください。

## 🔐 必要なSecrets

既存のpersonaagentリポジトリと同じ変数名を使用します：

### サーバー接続情報

```
SFTP_PASSWORD=your_sakura_password
SSH_USERNAME=your_sakura_username  
SSH_HOST=your_sakura_host
SSH_REMOTE_PATH=/home/mokumoku/www/persona-factory
```

## 📋 Secrets 設定手順

### 1. GitHubリポジトリのSecretsページにアクセス

1. https://github.com/horiken1977/personaagent-factory にアクセス
2. **Settings** タブをクリック
3. 左サイドバーの **Secrets and variables** > **Actions** をクリック

### 2. 既存のpersonaagentリポジトリから設定をコピー

既存のSecretsが定義されているが値がNullの状態のため、以下の手順で設定：

#### SFTP_PASSWORD
- **Name**: `SFTP_PASSWORD`
- **Secret**: Sakura Internetのパスワード

#### SSH_USERNAME  
- **Name**: `SSH_USERNAME`
- **Secret**: Sakura Internetのユーザー名

#### SSH_HOST
- **Name**: `SSH_HOST`
- **Secret**: Sakura Internetのホスト名

#### SSH_REMOTE_PATH
- **Name**: `SSH_REMOTE_PATH`
- **Secret**: `/home/mokumoku/www/persona-factory`

## 🚀 デプロイの実行方法

### 自動デプロイ（推奨）

GitHub Secrets設定後、以下のいずれかで自動デプロイが実行されます：

1. **mainブランチにプッシュ**
   ```bash
   git push origin main
   ```

2. **手動実行**
   - GitHubリポジトリの **Actions** タブにアクセス
   - **Deploy to Production** ワークフローを選択
   - **Run workflow** ボタンをクリック

## 🔧 デプロイ後の手動設定

デプロイ完了後、**唯一の手動作業**として以下を実行：

### API キーの設定

1. **サーバーにSSH接続**
   ```bash
   ssh your_username@your_sakura_host
   ```

2. **ディレクトリ移動**
   ```bash
   cd /home/mokumoku/www/persona-factory
   ```

3. **.envファイル編集**
   ```bash
   nano .env
   ```

4. **API キーを設定**
   ```env
   # OpenAI API設定
   OPENAI_API_KEY=sk-your-actual-openai-api-key

   # Anthropic Claude API設定  
   CLAUDE_API_KEY=sk-ant-your-actual-claude-api-key

   # Google Gemini API設定
   GEMINI_API_KEY=your-actual-gemini-api-key
   ```

5. **保存して終了**
   ```
   Ctrl+X → Y → Enter
   ```

## 🔍 デプロイ状況の確認

### GitHub Actions
1. リポジトリの **Actions** タブでワークフローの実行状況を確認
2. 緑色のチェックマーク = 成功
3. 赤色のX = 失敗（ログを確認）

### 本番サイト
デプロイ成功後、以下のURLでアクセス可能：
```
https://mokumoku.sakura.ne.jp/persona-factory/
```

## ⚠️ トラブルシューティング

### よくある問題

#### 1. SSH/SFTP接続エラー
```
Error: rsync error: unexplained error (code 255)
```
- **原因**: SFTP_PASSWORD、SSH_USERNAME、SSH_HOST の設定エラー
- **解決**: GitHub Secrets の値を確認・修正

#### 2. 権限エラー
```
Error: Permission denied
```
- **原因**: サーバー上のディレクトリ権限
- **解決**: SSH_REMOTE_PATH の書き込み権限を確認

#### 3. API キー設定後もセットアップ画面が表示
- **原因**: .env ファイルのAPI キー形式エラー
- **解決**: API キーの形式を確認（sk-で始まるかなど）

### 成功パターンの確認

✅ **正常なデプロイログ例**：
```
✅ Application is accessible (HTTP 200)
🚀 Deployment completed successfully!
🌐 Site URL: https://mokumoku.sakura.ne.jp/persona-factory/
```

## 🔄 既存リポジトリとの違い

### personaagent → personaagent-factory

| 項目 | personaagent | personaagent-factory |
|------|-------------|-------------------|
| **URL** | `/persona/` | `/persona-factory/` |
| **ペルソナ** | 北米消費者10名 | 製造業職種10名 |
| **対象業界** | 食品・調味料 | 大規模製造業 |
| **Secrets** | 同じ変数名 | 同じ変数名 |
| **デプロイ方式** | rsync + sshpass | rsync + sshpass |

## 📞 サポート

### GitHub Secrets が保存できない場合

1. **ブラウザキャッシュをクリア**
2. **プライベートブラウザで再試行**  
3. **GitHub Supportに連絡**

### デプロイエラーの場合

1. **Actions タブのログを確認**
2. **既存のpersonaagentリポジトリのSecrets値と比較**
3. **Sakura Internetのコントロールパネルで接続設定確認**

---

## ✅ チェックリスト

デプロイ設定完了の確認：

- [ ] GitHub Secrets 4つすべて設定済み
- [ ] GitHub Actions ワークフロー正常実行
- [ ] https://mokumoku.sakura.ne.jp/persona-factory/ にアクセス可能
- [ ] サーバー上の .env ファイルにAPI キー設定済み
- [ ] アプリケーションの動作確認完了

**GitHub Secrets設定完了後は、mainブランチへのプッシュで自動デプロイされ、手動でのAPI キー設定のみで運用開始できます！**