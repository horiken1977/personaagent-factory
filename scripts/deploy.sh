#!/bin/bash

# PersonaAgent Factory - Sakura Internet Deployment Script
# Sakura Internet サーバーへの手動デプロイ用スクリプト

set -e

# カラー定義
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# 設定
SERVER_HOST=${SERVER_HOST:-"mokumoku.sakura.ne.jp"}
SERVER_USER=${SERVER_USER:-"mokumoku"}
SERVER_PATH=${SERVER_PATH:-"/home/mokumoku/www/persona-factory"}
LOCAL_PATH=$(pwd)

echo -e "${BLUE}🚀 PersonaAgent Factory デプロイメント開始${NC}"
echo "=========================================="

# 前提条件チェック
echo -e "${YELLOW}📋 前提条件をチェック中...${NC}"

# rsync の確認
if ! command -v rsync &> /dev/null; then
    echo -e "${RED}❌ rsync がインストールされていません${NC}"
    exit 1
fi

# SSH接続テスト
echo -e "${YELLOW}🔑 SSH接続をテスト中...${NC}"
if ! ssh -o ConnectTimeout=10 -o BatchMode=yes "${SERVER_USER}@${SERVER_HOST}" exit 2>/dev/null; then
    echo -e "${RED}❌ SSH接続に失敗しました${NC}"
    echo "以下を確認してください:"
    echo "1. SSH鍵が設定されている"
    echo "2. サーバーアドレスが正しい"
    echo "3. ユーザー名が正しい"
    exit 1
fi

echo -e "${GREEN}✅ SSH接続成功${NC}"

# ファイル同期
echo -e "${YELLOW}📁 ファイルを同期中...${NC}"

# 除外ファイルリスト
cat > /tmp/rsync_exclude << EOF
.git/
.github/
node_modules/
docs/
test/
backups/
logs/
.env
.DS_Store
Thumbs.db
*.log
.last_update
.backup_status
README.md
DEPLOYMENT.md
scripts/
EOF

# rsync でファイル同期
rsync -avz --delete \
    --exclude-from=/tmp/rsync_exclude \
    "${LOCAL_PATH}/" \
    "${SERVER_USER}@${SERVER_HOST}:${SERVER_PATH}/"

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ ファイル同期完了${NC}"
else
    echo -e "${RED}❌ ファイル同期に失敗しました${NC}"
    exit 1
fi

# サーバー側設定
echo -e "${YELLOW}⚙️ サーバー側設定を実行中...${NC}"

ssh "${SERVER_USER}@${SERVER_HOST}" << 'ENDSSH'
cd /home/mokumoku/www/persona-factory

echo "📁 ディレクトリ構造を確認中..."
ls -la

# .env ファイルの作成（存在しない場合のみ）
if [ ! -f .env ]; then
    echo "📝 .env ファイルを作成中..."
    cp .env.example .env
    
    # 本番環境用設定
    sed -i 's/DEBUG_MODE=true/DEBUG_MODE=false/g' .env
    sed -i 's|APP_URL=.*|APP_URL=https://mokumoku.sakura.ne.jp/persona-factory/|g' .env
    
    echo "⚠️ .env ファイルが作成されました。API キーを手動で設定してください。"
else
    echo "✅ .env ファイルは既に存在します"
fi

# ログディレクトリ作成
echo "📂 ログディレクトリを作成中..."
mkdir -p logs
chmod 755 logs

# 権限設定
echo "🔐 ファイル権限を設定中..."
find . -type f -name "*.php" -exec chmod 644 {} \;
find . -type f -name "*.html" -exec chmod 644 {} \;
find . -type f -name "*.css" -exec chmod 644 {} \;
find . -type f -name "*.js" -exec chmod 644 {} \;
find . -type f -name "*.json" -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;

echo "✅ サーバー側設定完了"
ENDSSH

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ サーバー側設定完了${NC}"
else
    echo -e "${RED}❌ サーバー側設定に失敗しました${NC}"
    exit 1
fi

# ヘルスチェック
echo -e "${YELLOW}🏥 ヘルスチェック実行中...${NC}"
sleep 5

HTTP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" "https://${SERVER_HOST}/persona-factory/" 2>/dev/null || echo "000")

if [ "$HTTP_STATUS" -eq 200 ] || [ "$HTTP_STATUS" -eq 302 ]; then
    echo -e "${GREEN}✅ アプリケーションは正常にアクセス可能です (HTTP $HTTP_STATUS)${NC}"
else
    echo -e "${YELLOW}⚠️ ヘルスチェックで想定外のステータス: HTTP $HTTP_STATUS${NC}"
    echo "これは初回デプロイの場合、API キー未設定によるリダイレクトの可能性があります"
fi

# 完了メッセージ
echo "=========================================="
echo -e "${GREEN}🎉 デプロイメント完了!${NC}"
echo ""
echo -e "${BLUE}📱 アプリケーション URL:${NC}"
echo "   https://${SERVER_HOST}/persona-factory/"
echo ""
echo -e "${YELLOW}⚠️ 次のステップ:${NC}"
echo "1. サーバーの .env ファイルでAPI キーを設定"
echo "2. アプリケーションの動作確認"
echo ""
echo -e "${BLUE}🔧 API キー設定方法:${NC}"
echo "   ssh ${SERVER_USER}@${SERVER_HOST}"
echo "   cd ${SERVER_PATH}"
echo "   nano .env  # API キーを設定"
echo ""

# クリーンアップ
rm -f /tmp/rsync_exclude

echo -e "${GREEN}✨ すべての処理が完了しました！${NC}"