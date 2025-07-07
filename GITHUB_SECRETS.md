# GitHub Secrets 設定ガイド

GitHub Actions での自動デプロイを有効にするために、以下のSecrets を設定してください。

## 🔐 必要なSecrets

### サーバー接続情報

#### FTP/SFTP 接続用
```
FTP_SERVER=mokumoku.sakura.ne.jp
FTP_USERNAME=mokumoku
FTP_PASSWORD=your_ftp_password
```

#### SSH 接続用
```
SSH_HOST=mokumoku.sakura.ne.jp
SSH_USERNAME=mokumoku
SSH_PASSWORD=your_ssh_password
SSH_PORT=22
```

### API キー（オプション）

自動でAPI キーを設定したい場合：

```
OPENAI_API_KEY=sk-your-openai-api-key-here
CLAUDE_API_KEY=sk-ant-your-claude-api-key-here
GEMINI_API_KEY=your-gemini-api-key-here
```

## 📋 Secrets 設定手順

### 1. GitHubリポジトリのSecretsページにアクセス

1. https://github.com/horiken1977/personaagent-factory にアクセス
2. **Settings** タブをクリック
3. 左サイドバーの **Secrets and variables** > **Actions** をクリック

### 2. New repository secret をクリック

各Secretを個別に追加していきます。

### 3. サーバー接続情報を設定

#### FTP_SERVER
- **Name**: `FTP_SERVER`
- **Secret**: `mokumoku.sakura.ne.jp`

#### FTP_USERNAME
- **Name**: `FTP_USERNAME`
- **Secret**: `mokumoku`

#### FTP_PASSWORD
- **Name**: `FTP_PASSWORD`
- **Secret**: `your_actual_ftp_password`

#### SSH_HOST
- **Name**: `SSH_HOST`
- **Secret**: `mokumoku.sakura.ne.jp`

#### SSH_USERNAME
- **Name**: `SSH_USERNAME`
- **Secret**: `mokumoku`

#### SSH_PASSWORD
- **Name**: `SSH_PASSWORD`
- **Secret**: `your_actual_ssh_password`

#### SSH_PORT
- **Name**: `SSH_PORT`
- **Secret**: `22`

### 4. API キーを設定（オプション）

#### OPENAI_API_KEY
- **Name**: `OPENAI_API_KEY`
- **Secret**: `sk-your-actual-openai-api-key`

#### CLAUDE_API_KEY
- **Name**: `CLAUDE_API_KEY`
- **Secret**: `sk-ant-your-actual-claude-api-key`

#### GEMINI_API_KEY
- **Name**: `GEMINI_API_KEY`
- **Secret**: `your-actual-gemini-api-key`

## 🚀 デプロイの実行方法

### 自動デプロイ（推奨）

Secrets設定後、以下のいずれかで自動デプロイが実行されます：

1. **mainブランチにプッシュ**
   ```bash
   git push origin main
   ```

2. **Pull Request作成**
   - mainブランチへのPull Requestを作成

### 手動デプロイ

#### GitHub Actions経由
1. GitHubリポジトリの **Actions** タブにアクセス
2. **Deploy to Sakura Internet** ワークフローを選択
3. **Run workflow** ボタンをクリック

#### ローカルスクリプト経由
```bash
# SSH鍵が設定されている場合
./scripts/deploy.sh
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

#### 1. FTP/SSH接続エラー
- サーバーのアドレス、ユーザー名、パスワードを確認
- Sakura Internetのコントロールパネルで接続設定を確認

#### 2. 権限エラー
- ファイルアップロード先のディレクトリ権限を確認
- `/home/mokumoku/www/` 以下に書き込み権限があることを確認

#### 3. API キー設定エラー
- Secrets に正しいAPI キーが設定されているか確認
- API キーの形式が正しいか確認（先頭の文字列など）

### ログの確認方法

#### GitHub Actions ログ
1. **Actions** タブ > 失敗したワークフローをクリック
2. **deploy** ジョブをクリック
3. 各ステップのログを展開して確認

#### サーバーログ
```bash
# サーバーにSSH接続
ssh mokumoku@mokumoku.sakura.ne.jp

# アプリケーションログ確認
cd /home/mokumoku/www/persona-factory
tail -f logs/app_$(date +%Y-%m-%d).log
```

## 🔒 セキュリティ注意事項

1. **Secrets の管理**
   - 絶対にコードにパスワードやAPI キーを含めない
   - 定期的なパスワード・API キーのローテーション

2. **アクセス制御**
   - リポジトリの管理者権限を最小限に
   - 必要に応じてIP制限の設定

3. **監査**
   - デプロイログの定期確認
   - 異常なアクセスの監視

---

## 📞 サポート

設定でお困りの場合：
1. GitHub Issues で質問
2. DEPLOYMENT.md の詳細手順を参照
3. Sakura Internet のサポートページを確認