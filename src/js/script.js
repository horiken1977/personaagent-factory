/**
 * PersonaAgent Factory - メインJavaScript
 * 大規模製造業向けペルソナエージェント フロントエンド制御
 */

class PersonaAgentFactory {
    constructor() {
        this.personas = [];
        this.selectedPersona = null;
        this.selectedProvider = null;
        this.chatHistories = {}; // ペルソナIDごとのチャット履歴
        this.isLoading = false;
        this.inputConfirmed = false; // 入力確定状態
        
        this.init();
    }
    
    async init() {
        try {
            await this.loadPersonas();
            await this.loadProviders();
            this.setupEventListeners();
            this.renderPersonaGrid();
            this.renderProviderSelect();
        } catch (error) {
            console.error('初期化エラー:', error);
            this.showToast('アプリケーションの初期化に失敗しました', 'error');
        }
    }
    
    // ペルソナデータの読み込み
    async loadPersonas() {
        try {
            const response = await fetch('personas.json');
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            const data = await response.json();
            this.personas = data.personas || [];
        } catch (error) {
            console.error('ペルソナデータの読み込みエラー:', error);
            // フォールバック: 最小限のダミーデータ
            this.personas = this.getFallbackPersonas();
        }
    }
    
    // 利用可能なプロバイダーの取得
    async loadProviders() {
        try {
            const response = await fetch('api/providers.php');
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            const data = await response.json();
            this.availableProviders = data.providers || [];
        } catch (error) {
            console.error('プロバイダー情報の取得エラー:', error);
            this.availableProviders = ['openai', 'claude', 'gemini']; // フォールバック
        }
    }
    
    // フォールバックペルソナデータ
    getFallbackPersonas() {
        return [
            {
                id: 'factory_worker_1',
                name: '佐藤 健太',
                age: 28,
                position: '工場内作業員',
                location: '愛知県豊田市',
                main_responsibilities: '自動車部品の組立ライン作業、品質チェック',
                persona_type: '現場作業者'
            },
            {
                id: 'line_manager_1',
                name: '鈴木 誠',
                age: 42,
                position: 'ライン管理者',
                location: '大阪府大阪市',
                main_responsibilities: '生産ライン管理、作業員指導、生産計画調整',
                persona_type: '中間管理職'
            }
        ];
    }
    
    // イベントリスナーの設定
    setupEventListeners() {
        // ペルソナ選択
        document.addEventListener('click', (e) => {
            if (e.target.closest('.persona-card')) {
                this.handlePersonaSelection(e.target.closest('.persona-card'));
            }
        });
        
        // モーダル制御
        const modal = document.getElementById('confirmModal');
        const closeModal = document.getElementById('closeModal');
        const cancelSelection = document.getElementById('cancelSelection');
        const startChat = document.getElementById('startChat');
        
        [closeModal, cancelSelection].forEach(btn => {
            btn?.addEventListener('click', () => this.hideModal());
        });
        
        startChat?.addEventListener('click', () => this.startChat());
        
        // モーダル背景クリックで閉じる
        modal?.addEventListener('click', (e) => {
            if (e.target === modal) this.hideModal();
        });
        
        // プロバイダー選択
        const providerSelect = document.getElementById('llmProvider');
        providerSelect?.addEventListener('change', (e) => {
            this.handleProviderSelection(e.target.value);
        });
        
        // チャット機能
        const chatInput = document.getElementById('chatInput');
        const sendMessage = document.getElementById('sendMessage');
        const backToSelection = document.getElementById('backToSelection');
        const clearChat = document.getElementById('clearChat');
        const exportHistory = document.getElementById('exportHistory');
        const showLogs = document.getElementById('showLogs');
        
        chatInput?.addEventListener('input', () => this.handleInputChange());
        chatInput?.addEventListener('keydown', (e) => this.handleKeyDown(e));
        
        sendMessage?.addEventListener('click', () => this.sendMessage());
        backToSelection?.addEventListener('click', () => this.backToSelection());
        clearChat?.addEventListener('click', () => this.clearChat());
        exportHistory?.addEventListener('click', () => this.exportChatHistory());
        showLogs?.addEventListener('click', () => this.showLogsModal());
        
        // ログモーダル関連
        const closeLogsModal = document.getElementById('closeLogsModal');
        const refreshLogs = document.getElementById('refreshLogs');
        const showErrorLogs = document.getElementById('showErrorLogs');
        const showAllLogs = document.getElementById('showAllLogs');
        
        closeLogsModal?.addEventListener('click', () => this.hideLogsModal());
        refreshLogs?.addEventListener('click', () => this.loadLogs('recent'));
        showErrorLogs?.addEventListener('click', () => this.loadLogs('error'));
        showAllLogs?.addEventListener('click', () => this.loadLogs('recent'));
        
        // ESCキーでモーダルを閉じる
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.hideModal();
            }
        });
    }
    
    // ペルソナグリッドのレンダリング
    renderPersonaGrid() {
        const grid = document.getElementById('personaGrid');
        if (!grid) return;
        
        grid.innerHTML = this.personas.map(persona => this.createPersonaCard(persona)).join('');
    }
    
    // ペルソナカードの生成
    createPersonaCard(persona) {
        const avatar = this.getPersonaAvatar(persona.position);
        const experience = persona.years_of_experience || '不明';
        const income = persona.household_income || '非公開';
        
        return `
            <div class="persona-card" data-persona-id="${persona.id}">
                <div class="persona-header">
                    <div class="persona-avatar">${avatar}</div>
                    <div class="persona-info">
                        <h3>${persona.name}</h3>
                        <div class="persona-position">${persona.position}</div>
                    </div>
                </div>
                <div class="persona-details">
                    <p><strong>担当業務:</strong> ${this.truncateText(persona.main_responsibilities, 80)}</p>
                    <div class="persona-meta">
                        <div class="meta-item">年齢: ${persona.age}歳</div>
                        <div class="meta-item">経験: ${experience}</div>
                        <div class="meta-item">年収: ${income}</div>
                    </div>
                </div>
            </div>
        `;
    }
    
    // 職種に応じたアバターの取得
    getPersonaAvatar(position) {
        const avatarMap = {
            '工場内作業員': '👷',
            'ライン管理者': '👨‍💼',
            '工場システム担当者': '👨‍💻',
            '工場システム管理者': '👩‍💻',
            '工場長': '👔',
            '営業': '🤝'
        };
        return avatarMap[position] || '👤';
    }
    
    // テキストの切り詰め
    truncateText(text, maxLength) {
        if (!text) return '';
        return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
    }
    
    // プロバイダーセレクトのレンダリング
    renderProviderSelect() {
        const select = document.getElementById('llmProvider');
        if (!select) return;
        
        const providerNames = {
            'openai': 'OpenAI GPT-4',
            'claude': 'Anthropic Claude 4',
            'gemini': 'Google Gemini 2.0'
        };
        
        select.innerHTML = '<option value="">選択してください</option>' +
            this.availableProviders.map(provider => 
                `<option value="${provider}">${providerNames[provider] || provider}</option>`
            ).join('');
    }
    
    // ペルソナ選択の処理
    handlePersonaSelection(card) {
        // 既存の選択を解除
        document.querySelectorAll('.persona-card').forEach(c => c.classList.remove('selected'));
        
        // 新しい選択を設定
        card.classList.add('selected');
        
        const personaId = card.dataset.personaId;
        this.selectedPersona = this.personas.find(p => p.id === personaId);
        
        if (this.selectedPersona) {
            this.showPersonaConfirmation();
        }
    }
    
    // ペルソナ確認モーダルの表示
    showPersonaConfirmation() {
        const modal = document.getElementById('confirmModal');
        const personaInfo = document.getElementById('selectedPersonaInfo');
        
        if (modal && personaInfo && this.selectedPersona) {
            personaInfo.innerHTML = this.createPersonaInfoDisplay(this.selectedPersona);
            this.showModal();
        }
    }
    
    // ペルソナ情報表示の生成
    createPersonaInfoDisplay(persona) {
        return `
            <div class="persona-header">
                <div class="persona-avatar">${this.getPersonaAvatar(persona.position)}</div>
                <div class="persona-info">
                    <h3>${persona.name}</h3>
                    <div class="persona-position">${persona.position}</div>
                </div>
            </div>
            <div class="persona-details">
                <p><strong>年齢:</strong> ${persona.age}歳</p>
                <p><strong>勤務地:</strong> ${persona.location}</p>
                <p><strong>経験:</strong> ${persona.years_of_experience || '詳細不明'}</p>
                <p><strong>主な業務:</strong> ${persona.main_responsibilities}</p>
            </div>
        `;
    }
    
    // プロバイダー選択の処理
    handleProviderSelection(provider) {
        this.selectedProvider = provider;
        this.updateProviderStatus(provider);
    }
    
    // プロバイダーステータスの更新
    updateProviderStatus(provider) {
        const indicator = document.getElementById('statusIndicator');
        const statusText = document.getElementById('statusText');
        
        if (!indicator || !statusText) return;
        
        if (provider) {
            indicator.className = 'status-indicator connected';
            indicator.textContent = '●';
            statusText.textContent = `${this.getProviderName(provider)} が選択されています`;
        } else {
            indicator.className = 'status-indicator disconnected';
            indicator.textContent = '●';
            statusText.textContent = 'プロバイダーを選択してください';
        }
    }
    
    // プロバイダー名の取得
    getProviderName(provider) {
        const names = {
            'openai': 'OpenAI GPT-4',
            'claude': 'Anthropic Claude',
            'gemini': 'Google Gemini'
        };
        return names[provider] || provider;
    }
    
    // モーダル表示
    showModal() {
        const modal = document.getElementById('confirmModal');
        if (modal) {
            modal.classList.add('show');
        }
    }
    
    // モーダル非表示
    hideModal() {
        const modal = document.getElementById('confirmModal');
        if (modal) {
            modal.classList.remove('show');
        }
    }
    
    // チャット開始
    startChat() {
        if (!this.selectedPersona) {
            this.showToast('ペルソナが選択されていません', 'error');
            return;
        }
        
        if (!this.selectedProvider) {
            this.showToast('AI プロバイダーを選択してください', 'error');
            return;
        }
        
        this.hideModal();
        this.showChatInterface();
        this.initializeChat();
    }
    
    // チャットインターフェースの表示
    showChatInterface() {
        const chatContainer = document.getElementById('chatContainer');
        const mainContainer = document.querySelector('.container');
        
        if (chatContainer && mainContainer) {
            mainContainer.style.display = 'none';
            chatContainer.style.display = 'flex';
            
            // チャットヘッダーの更新
            this.updateChatHeader();
        }
    }
    
    // チャットヘッダーの更新
    updateChatHeader() {
        const avatar = document.getElementById('chatPersonaAvatar');
        const name = document.getElementById('chatPersonaName');
        const position = document.getElementById('chatPersonaPosition');
        
        if (avatar && name && position && this.selectedPersona) {
            avatar.textContent = this.getPersonaAvatar(this.selectedPersona.position);
            name.textContent = this.selectedPersona.name;
            position.textContent = this.selectedPersona.position;
        }
    }
    
    // チャット初期化
    initializeChat() {
        // 選択されたペルソナの履歴を初期化（既存の履歴があればそれを使用）
        const personaId = this.selectedPersona.id;
        if (!this.chatHistories[personaId]) {
            this.chatHistories[personaId] = [];
        }
        this.displayWelcomeMessage();
        this.focusChatInput();
        
        // 既存の履歴があれば表示
        this.displayExistingHistory(personaId);
    }
    
    // 現在のペルソナのチャット履歴を取得
    getCurrentChatHistory() {
        if (!this.selectedPersona) return [];
        return this.chatHistories[this.selectedPersona.id] || [];
    }
    
    // 既存の履歴を表示
    displayExistingHistory(personaId) {
        const history = this.chatHistories[personaId];
        const messagesContainer = document.getElementById('chatMessages');
        
        if (history && history.length > 0 && messagesContainer) {
            // ウェルカムメッセージの後に既存の履歴を追加
            history.forEach(item => {
                const messageElement = document.createElement('div');
                messageElement.className = `message ${item.type}-message`;
                
                const contentElement = document.createElement('div');
                contentElement.className = 'message-content';
                contentElement.innerHTML = this.formatMessageContent(item.content);
                
                messageElement.appendChild(contentElement);
                messagesContainer.appendChild(messageElement);
            });
            
            // 最下部にスクロール
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
    }
    
    // ウェルカムメッセージの表示
    displayWelcomeMessage() {
        const welcomeContent = document.getElementById('welcomeMessageContent');
        if (welcomeContent && this.selectedPersona) {
            const message = this.generateWelcomeMessage(this.selectedPersona);
            welcomeContent.innerHTML = message;
        }
    }
    
    // ウェルカムメッセージの生成
    generateWelcomeMessage(persona) {
        return `
            <p>こんにちは！私は<strong>${persona.name}</strong>です。</p>
            <p>現在、${persona.position}として働いており、主に${persona.main_responsibilities}を担当しています。</p>
            <p>製造現場での経験を活かして、お客様のご質問やご相談にお答えします。どのようなことでもお気軽にお話しください。</p>
        `;
    }
    
    // チャット入力にフォーカス
    focusChatInput() {
        const chatInput = document.getElementById('chatInput');
        if (chatInput) {
            chatInput.focus();
        }
    }
    
    // 入力変更の処理
    // キーボード入力処理
    handleKeyDown(e) {
        const chatInput = document.getElementById('chatInput');
        if (!chatInput) return;
        
        if (e.key === 'Enter') {
            if (e.shiftKey) {
                // Shift+Enter: 改行（デフォルト動作）
                return;
            }
            
            e.preventDefault();
            
            const inputValue = chatInput.value.trim();
            if (!inputValue) return;
            
            if (!this.inputConfirmed) {
                // 第1回Enter: 入力確定
                this.inputConfirmed = true;
                this.updateInputStatus('✓ 入力が確定されました。もう一度Enterで送信してください');
                chatInput.style.borderColor = '#28a745';
            } else {
                // 第2回Enter: メッセージ送信
                this.sendMessage();
            }
        } else {
            // その他のキー入力: 確定状態をリセット
            if (this.inputConfirmed) {
                this.inputConfirmed = false;
                chatInput.style.borderColor = '';
                this.handleInputChange();
            }
        }
    }
    
    handleInputChange() {
        const chatInput = document.getElementById('chatInput');
        const sendButton = document.getElementById('sendMessage');
        const inputStatus = document.getElementById('inputStatus');
        
        // 入力内容が変更されたら確定状態をリセット
        if (this.inputConfirmed) {
            this.inputConfirmed = false;
            if (chatInput) chatInput.style.borderColor = '';
        }
        
        if (chatInput && sendButton && inputStatus) {
            const value = chatInput.value.trim();
            const isEmpty = value.length === 0;
            
            sendButton.disabled = isEmpty || this.isLoading;
            
            if (isEmpty) {
                inputStatus.textContent = 'メッセージを入力してください';
            } else if (this.isLoading) {
                inputStatus.textContent = 'AI が応答を生成中...';
            } else if (this.inputConfirmed) {
                inputStatus.textContent = '✓ 入力が確定されました。もう一度Enterで送信してください';
            } else {
                inputStatus.textContent = `${value.length} 文字 (Enterで入力確定)`;
            }
        }
    }
    
    // 入力ステータス更新
    updateInputStatus(message) {
        const statusElement = document.getElementById('inputStatus');
        if (statusElement) {
            statusElement.textContent = message;
        }
    }
    
    // メッセージ送信
    async sendMessage() {
        const chatInput = document.getElementById('chatInput');
        if (!chatInput || this.isLoading) return;
        
        const message = chatInput.value.trim();
        if (!message) return;
        
        // ユーザーメッセージを表示
        this.addMessage(message, 'user');
        chatInput.value = '';
        
        // 入力確定状態をリセット
        this.inputConfirmed = false;
        chatInput.style.borderColor = '';
        
        this.handleInputChange();
        
        // AI応答を取得
        await this.getAIResponse(message);
    }
    
    // メッセージをチャットに追加
    addMessage(content, type) {
        const messagesContainer = document.getElementById('chatMessages');
        if (!messagesContainer) return;
        
        const messageElement = document.createElement('div');
        messageElement.className = `message ${type}-message`;
        
        const avatar = type === 'user' ? '👤' : this.getPersonaAvatar(this.selectedPersona.position);
        
        messageElement.innerHTML = `
            <div class="message-avatar">${avatar}</div>
            <div class="message-content">${this.formatMessage(content)}</div>
        `;
        
        messagesContainer.appendChild(messageElement);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
        
        // ペルソナごとのチャット履歴に追加
        if (this.selectedPersona) {
            const personaId = this.selectedPersona.id;
            if (!this.chatHistories[personaId]) {
                this.chatHistories[personaId] = [];
            }
            this.chatHistories[personaId].push({ type, content, timestamp: new Date() });
        }
    }
    
    // メッセージのフォーマット
    formatMessage(content) {
        // 改行をbrタグに変換
        return content.replace(/\n/g, '<br>');
    }
    
    // AI応答の取得
    async getAIResponse(userMessage) {
        this.setLoading(true);
        
        try {
            const response = await fetch('api/chat.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    message: userMessage,
                    persona: this.selectedPersona,
                    provider: this.selectedProvider,
                    history: this.getCurrentChatHistory().slice(-10) // 現在のペルソナの直近10件の履歴
                })
            });
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                this.addMessage(data.response, 'persona');
                // 履歴を保存
                await this.saveChatHistory(userMessage, data.response);
            } else {
                throw new Error(data.error || 'AI応答の取得に失敗しました');
            }
            
        } catch (error) {
            console.error('AI応答エラー:', error);
            this.addMessage('申し訳ございません。現在システムに問題が発生しています。しばらく経ってから再度お試しください。', 'persona');
            this.showToast('AI応答の取得に失敗しました', 'error');
        } finally {
            this.setLoading(false);
        }
    }
    
    // ローディング状態の設定
    setLoading(loading) {
        this.isLoading = loading;
        const spinner = document.getElementById('loadingSpinner');
        const sendButton = document.getElementById('sendMessage');
        
        if (spinner) {
            spinner.style.display = loading ? 'flex' : 'none';
        }
        
        if (sendButton) {
            sendButton.disabled = loading;
        }
        
        this.handleInputChange();
    }
    
    // ペルソナ選択に戻る
    backToSelection() {
        const chatContainer = document.getElementById('chatContainer');
        const mainContainer = document.querySelector('.container');
        
        if (chatContainer && mainContainer) {
            chatContainer.style.display = 'none';
            mainContainer.style.display = 'block';
        }
        
        // 選択をリセット
        document.querySelectorAll('.persona-card').forEach(c => c.classList.remove('selected'));
        this.selectedPersona = null;
    }
    
    // チャットをクリア
    clearChat() {
        if (confirm('このペルソナとのチャット履歴をクリアしますか？')) {
            // 現在のペルソナの履歴をクリア
            if (this.selectedPersona) {
                this.chatHistories[this.selectedPersona.id] = [];
            }
            
            const messagesContainer = document.getElementById('chatMessages');
            if (messagesContainer) {
                // ウェルカムメッセージ以外を削除
                const messages = messagesContainer.querySelectorAll('.message:not(.welcome-message .message)');
                messages.forEach(msg => msg.remove());
            }
            this.showToast('チャット履歴をクリアしました');
        }
    }
    
    // トーストメッセージの表示
    showToast(message, type = 'success') {
        const toast = document.getElementById('toast');
        const toastContent = document.getElementById('toastContent');
        
        if (toast && toastContent) {
            toastContent.textContent = message;
            toast.className = `toast ${type}`;
            toast.classList.add('show');
            
            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }
    }
    
    // チャット履歴を保存
    async saveChatHistory(userMessage, aiResponse) {
        if (!this.selectedPersona || !this.selectedProvider) return;
        
        try {
            const response = await fetch('api/history.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    persona_id: this.selectedPersona.id,
                    persona_name: this.selectedPersona.name,
                    provider: this.selectedProvider,
                    user_message: userMessage,
                    ai_response: aiResponse
                })
            });
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            if (!data.success) {
                throw new Error(data.error || '履歴保存に失敗しました');
            }
        } catch (error) {
            console.warn('履歴保存エラー:', error);
            // エラーが発生してもチャット機能は継続
        }
    }
    
    // チャット履歴をCSVでエクスポート（全ペルソナの履歴を含む）
    async exportChatHistory() {
        try {
            this.showToast('全ペルソナの履歴をエクスポート中...', 'info');
            
            const url = 'api/history.php?export=csv';
            const link = document.createElement('a');
            link.href = url;
            link.download = `persona_chat_history_all_${new Date().toISOString().slice(0, 19).replace(/:/g, '-')}.csv`;
            link.style.display = 'none';
            
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            this.showToast('全ペルソナの履歴をエクスポートしました', 'success');
        } catch (error) {
            console.error('エクスポートエラー:', error);
            this.showToast('エクスポートに失敗しました', 'error');
        }
    }
    
    // ログモーダルを表示
    showLogsModal() {
        const modal = document.getElementById('logsModal');
        if (modal) {
            modal.style.display = 'flex';
            this.loadLogs('recent');
        }
    }
    
    // ログモーダルを非表示
    hideLogsModal() {
        const modal = document.getElementById('logsModal');
        if (modal) {
            modal.style.display = 'none';
        }
    }
    
    // ログを読み込み
    async loadLogs(type = 'recent') {
        try {
            const response = await fetch(`api/logs.php?action=${type}&lines=100`);
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            if (data.success) {
                this.displayLogs(data.logs, type);
            } else {
                throw new Error(data.error || 'ログの取得に失敗しました');
            }
        } catch (error) {
            console.error('ログ取得エラー:', error);
            this.displayLogs(['ログの取得に失敗しました: ' + error.message], 'error');
        }
    }
    
    // ログを表示
    displayLogs(logs, type) {
        const logOutput = document.getElementById('logOutput');
        if (logOutput) {
            if (logs.length === 0) {
                logOutput.textContent = `${type === 'error' ? 'エラー' : ''}ログが見つかりません。`;
            } else {
                logOutput.textContent = logs.join('\n');
                // 最下部にスクロール
                logOutput.scrollTop = logOutput.scrollHeight;
            }
        }
    }
}

// アプリケーション初期化
document.addEventListener('DOMContentLoaded', () => {
    window.personaAgentFactory = new PersonaAgentFactory();
});

// グローバルエラーハンドラー
window.addEventListener('error', (event) => {
    console.error('Global error:', event.error);
});

window.addEventListener('unhandledrejection', (event) => {
    console.error('Unhandled promise rejection:', event.reason);
});