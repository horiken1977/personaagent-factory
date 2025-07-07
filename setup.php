<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>セットアップ - PersonaAgent Factory</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .setup-container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            width: 100%;
        }
        
        .header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .header h1 {
            color: #2c3e50;
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        
        .header p {
            color: #7f8c8d;
            font-size: 1.1em;
        }
        
        .setup-section {
            margin-bottom: 30px;
        }
        
        .setup-section h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 1.3em;
        }
        
        .setup-section p {
            color: #7f8c8d;
            margin-bottom: 15px;
            line-height: 1.6;
        }
        
        .code-block {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
            color: #2c3e50;
            overflow-x: auto;
        }
        
        .warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
            color: #856404;
        }
        
        .info {
            background: #e8f4f8;
            border: 1px solid #bee5eb;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
            color: #0c5460;
        }
        
        .steps {
            counter-reset: step-counter;
        }
        
        .step {
            counter-increment: step-counter;
            margin-bottom: 25px;
            padding-left: 40px;
            position: relative;
        }
        
        .step::before {
            content: counter(step-counter);
            position: absolute;
            left: 0;
            top: 0;
            background: #667eea;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        .btn {
            background: #667eea;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            font-size: 1em;
        }
        
        .btn:hover {
            background: #5a6fd8;
            transform: translateY(-2px);
        }
        
        .center {
            text-align: center;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <div class="header">
            <h1>PersonaAgent Factory</h1>
            <p>大規模製造業向けペルソナエージェント セットアップ</p>
        </div>
        
        <div class="warning">
            <strong>⚠️ API キーが設定されていません</strong><br>
            アプリケーションを使用するには、最低1つのAI APIキーを設定する必要があります。
        </div>
        
        <div class="setup-section">
            <h3>🔧 セットアップ手順</h3>
            
            <div class="steps">
                <div class="step">
                    <strong>.envファイルの作成</strong>
                    <p>.env.exampleファイルを.envとしてコピーしてください。</p>
                    <div class="code-block">cp .env.example .env</div>
                </div>
                
                <div class="step">
                    <strong>API キーの取得</strong>
                    <p>以下のサービスから API キーを取得してください（少なくとも1つ必要）：</p>
                    <ul style="margin-left: 20px; margin-top: 10px;">
                        <li><strong>OpenAI:</strong> <a href="https://platform.openai.com/api-keys" target="_blank">https://platform.openai.com/api-keys</a></li>
                        <li><strong>Anthropic Claude:</strong> <a href="https://console.anthropic.com/" target="_blank">https://console.anthropic.com/</a></li>
                        <li><strong>Google Gemini:</strong> <a href="https://makersuite.google.com/app/apikey" target="_blank">https://makersuite.google.com/app/apikey</a></li>
                    </ul>
                </div>
                
                <div class="step">
                    <strong>.envファイルの編集</strong>
                    <p>取得したAPI キーを.envファイルに設定してください。</p>
                    <div class="code-block">
# 例: OpenAI API キーを設定する場合<br>
OPENAI_API_KEY=sk-your-actual-api-key-here<br><br>
# 例: Claude API キーを設定する場合<br>
CLAUDE_API_KEY=sk-ant-your-actual-api-key-here<br><br>
# 例: Gemini API キーを設定する場合<br>
GEMINI_API_KEY=your-actual-gemini-api-key-here
                    </div>
                </div>
                
                <div class="step">
                    <strong>アプリケーションへのアクセス</strong>
                    <p>API キー設定後、このページを更新するか、以下のボタンをクリックしてアプリケーションにアクセスしてください。</p>
                </div>
            </div>
        </div>
        
        <div class="info">
            <strong>💡 ヒント</strong><br>
            複数のAI プロバイダーを設定することで、より多様な対話体験が可能になります。各プロバイダーには異なる特徴があります：
            <ul style="margin-top: 10px; margin-left: 20px;">
                <li><strong>OpenAI GPT-4:</strong> 汎用的で高品質な対話</li>
                <li><strong>Claude:</strong> 長い文脈の理解に優れ、安全性重視</li>
                <li><strong>Gemini:</strong> Google の最新AI、マルチモーダル対応</li>
            </ul>
        </div>
        
        <div class="center">
            <a href="index.php" class="btn">🔄 設定を確認してアプリケーションを開始</a>
        </div>
        
        <div class="setup-section" style="margin-top: 40px;">
            <h3>📞 サポート</h3>
            <p>セットアップでお困りの場合は、プロジェクトのドキュメントを参照するか、システム管理者にお問い合わせください。</p>
        </div>
    </div>
</body>
</html>