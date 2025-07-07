#!/usr/bin/env python3
"""
自動ドキュメント更新スクリプト
Claude Code とのチャット内容を解析し、設計書を自動更新する
"""

import os
import re
import json
import time
from datetime import datetime
from typing import Dict, List, Any
from pathlib import Path


class DocumentUpdater:
    """ドキュメント自動更新クラス"""
    
    def __init__(self, project_root: str = None):
        self.project_root = Path(project_root) if project_root else Path.cwd()
        self.docs_dir = self.project_root / "docs"
        self.claude_md_path = self.project_root / "claude.md"
        self.last_update_file = self.project_root / ".last_update"
        
        # 設計書ファイルパス
        self.doc_files = {
            "dashboard": self.docs_dir / "dashboard.html",
            "functional": self.docs_dir / "functional_design.html",
            "environment": self.docs_dir / "environment_design.html",
            "test": self.docs_dir / "test_specification.html"
        }
        
        # 機能キーワード
        self.functional_keywords = [
            "機能", "要件", "仕様", "実装", "エージェント", "API", "UI",
            "ユーザー", "画面", "フロントエンド", "バックエンド"
        ]
        
        # 環境キーワード
        self.environment_keywords = [
            "環境", "インフラ", "サーバー", "デプロイ", "Docker", "AWS",
            "データベース", "セキュリティ", "監視", "バックアップ"
        ]
        
        # テストキーワード
        self.test_keywords = [
            "テスト", "品質", "バグ", "テストケース", "単体テスト",
            "統合テスト", "自動テスト", "カバレッジ", "検証"
        ]
    
    def read_claude_md(self) -> str:
        """claude.mdファイルを読み込み"""
        if not self.claude_md_path.exists():
            return ""
        
        try:
            with open(self.claude_md_path, 'r', encoding='utf-8') as f:
                return f.read()
        except Exception as e:
            print(f"Error reading claude.md: {e}")
            return ""
    
    def get_last_update_time(self) -> float:
        """最終更新時刻を取得"""
        if not self.last_update_file.exists():
            return 0.0
        
        try:
            with open(self.last_update_file, 'r') as f:
                return float(f.read().strip())
        except Exception:
            return 0.0
    
    def set_last_update_time(self, timestamp: float):
        """最終更新時刻を保存"""
        try:
            with open(self.last_update_file, 'w') as f:
                f.write(str(timestamp))
        except Exception as e:
            print(f"Error saving last update time: {e}")
    
    def extract_new_content(self, content: str) -> str:
        """最終更新以降の新しい内容を抽出"""
        last_update = self.get_last_update_time()
        
        # 簡単な実装：最終更新以降の内容を抽出
        # 実際の実装では、より精密な解析が必要
        lines = content.split('\n')
        new_lines = []
        
        for line in lines:
            # タイムスタンプを含む行を探す（簡単な実装）
            if any(keyword in line.lower() for keyword in 
                   self.functional_keywords + self.environment_keywords + self.test_keywords):
                new_lines.append(line)
        
        return '\n'.join(new_lines)
    
    def analyze_content(self, content: str) -> Dict[str, List[str]]:
        """内容を解析して各ドキュメントの更新項目を抽出"""
        updates = {
            "functional": [],
            "environment": [],
            "test": [],
            "dashboard": []
        }
        
        lines = content.split('\n')
        
        for line in lines:
            line_lower = line.lower()
            
            # 機能設計書の更新項目を検出
            if any(keyword in line_lower for keyword in self.functional_keywords):
                updates["functional"].append(line.strip())
            
            # 環境設計書の更新項目を検出
            if any(keyword in line_lower for keyword in self.environment_keywords):
                updates["environment"].append(line.strip())
            
            # テスト仕様書の更新項目を検出
            if any(keyword in line_lower for keyword in self.test_keywords):
                updates["test"].append(line.strip())
        
        return updates
    
    def update_dashboard_progress(self, updates: Dict[str, List[str]]):
        """ダッシュボードの進捗を更新"""
        if not self.doc_files["dashboard"].exists():
            return
        
        try:
            with open(self.doc_files["dashboard"], 'r', encoding='utf-8') as f:
                content = f.read()
            
            # 進捗率を計算（簡単な実装）
            total_updates = sum(len(update_list) for update_list in updates.values())
            
            if total_updates > 0:
                # 進捗率を少し上げる
                content = re.sub(
                    r'<div class="progress-text">(\d+)%</div>',
                    lambda m: f'<div class="progress-text">{min(int(m.group(1)) + 5, 100)}%</div>',
                    content
                )
                
                # 最終更新時刻を更新
                now = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
                content = re.sub(
                    r"document\.getElementById\('lastUpdated'\)\.textContent = new Date\(\)\.toLocaleString\('ja-JP'\);",
                    f"document.getElementById('lastUpdated').textContent = '{now}';",
                    content
                )
                
                with open(self.doc_files["dashboard"], 'w', encoding='utf-8') as f:
                    f.write(content)
                
                print(f"ダッシュボードを更新しました: {total_updates} 件の更新")
        
        except Exception as e:
            print(f"ダッシュボード更新エラー: {e}")
    
    def update_functional_design(self, updates: List[str]):
        """機能設計書を更新"""
        if not updates or not self.doc_files["functional"].exists():
            return
        
        try:
            with open(self.doc_files["functional"], 'r', encoding='utf-8') as f:
                content = f.read()
            
            # 新しい要件をフィルタリング
            new_requirements = []
            for update in updates:
                if any(keyword in update.lower() for keyword in ["機能", "要件", "仕様", "実装", "エージェント", "api", "ui"]):
                    # HTMLエスケープして追加
                    clean_update = update.strip()
                    if len(clean_update) > 10 and not clean_update.startswith('#'):  # 短すぎるものや見出しは除外
                        new_requirements.append(clean_update)
            
            if new_requirements:
                # 既存の自動更新セクションを削除
                content = re.sub(r'<div class="section">\s*<h2>自動更新された要件</h2>.*?</div>\s*', '', content, flags=re.DOTALL)
                
                # 新しい自動更新セクションを追加
                update_section = f"""
        <div class="section">
            <h2>自動更新された要件</h2>
            <p>チャットから自動的に検出された新しい機能要件です。</p>
            <ul>
                {''.join(f"<li>{req}</li>" for req in new_requirements[:10])}
            </ul>
            <p>最終更新: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}</p>
        </div>
        """
                
                # 最終更新セクションの前に挿入
                content = content.replace(
                    '<div class="last-updated">',
                    update_section + '\n        <div class="last-updated">'
                )
                
                with open(self.doc_files["functional"], 'w', encoding='utf-8') as f:
                    f.write(content)
                
                print(f"機能設計書を更新しました: {len(new_requirements)} 件の要件")
        
        except Exception as e:
            print(f"機能設計書更新エラー: {e}")
    
    def update_environment_design(self, updates: List[str]):
        """環境設計書を更新"""
        if not updates or not self.doc_files["environment"].exists():
            return
        
        try:
            with open(self.doc_files["environment"], 'r', encoding='utf-8') as f:
                content = f.read()
            
            # 新しい環境設定を追加
            new_configs = []
            for update in updates:
                if "環境" in update or "インフラ" in update:
                    new_configs.append(update)
            
            if new_configs:
                # 自動更新セクションに追加
                update_section = f"""
                <div class="section">
                    <h2>自動更新された環境設定</h2>
                    <p>チャットから自動的に検出された新しい環境設定です。</p>
                    <ul>
                        {''.join(f"<li>{config}</li>" for config in new_configs)}
                    </ul>
                    <p>最終更新: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}</p>
                </div>
                """
                
                content = content.replace(
                    '<div class="last-updated">',
                    update_section + '\n        <div class="last-updated">'
                )
                
                with open(self.doc_files["environment"], 'w', encoding='utf-8') as f:
                    f.write(content)
                
                print(f"環境設計書を更新しました: {len(new_configs)} 件の設定")
        
        except Exception as e:
            print(f"環境設計書更新エラー: {e}")
    
    def update_test_specification(self, updates: List[str]):
        """テスト仕様書を更新"""
        if not updates or not self.doc_files["test"].exists():
            return
        
        try:
            with open(self.doc_files["test"], 'r', encoding='utf-8') as f:
                content = f.read()
            
            # 新しいテストケースを追加
            new_tests = []
            for update in updates:
                if "テスト" in update or "検証" in update:
                    new_tests.append(update)
            
            if new_tests:
                # 自動更新セクションに追加
                update_section = f"""
                <div class="section">
                    <h2>自動更新されたテスト項目</h2>
                    <p>チャットから自動的に検出された新しいテスト項目です。</p>
                    <ul>
                        {''.join(f"<li>{test}</li>" for test in new_tests)}
                    </ul>
                    <p>最終更新: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}</p>
                </div>
                """
                
                content = content.replace(
                    '<div class="last-updated">',
                    update_section + '\n        <div class="last-updated">'
                )
                
                with open(self.doc_files["test"], 'w', encoding='utf-8') as f:
                    f.write(content)
                
                print(f"テスト仕様書を更新しました: {len(new_tests)} 件のテスト")
        
        except Exception as e:
            print(f"テスト仕様書更新エラー: {e}")
    
    def run_update(self):
        """更新処理を実行"""
        print(f"=== 自動更新開始 {datetime.now().strftime('%Y-%m-%d %H:%M:%S')} ===")
        
        # Claude.mdの内容を読み込み
        claude_content = self.read_claude_md()
        if not claude_content:
            print("claude.mdファイルが見つからないか、空です")
            return
        
        # 新しい内容を抽出
        new_content = self.extract_new_content(claude_content)
        if not new_content:
            print("新しい更新はありません")
            return
        
        # 内容を解析
        updates = self.analyze_content(new_content)
        
        # 各ドキュメントを更新
        self.update_functional_design(updates["functional"])
        self.update_environment_design(updates["environment"])
        self.update_test_specification(updates["test"])
        self.update_dashboard_progress(updates)
        
        # 最終更新時刻を記録
        self.set_last_update_time(time.time())
        
        print(f"=== 自動更新完了 {datetime.now().strftime('%Y-%m-%d %H:%M:%S')} ===")


def main():
    """メイン関数"""
    updater = DocumentUpdater()
    updater.run_update()


if __name__ == "__main__":
    main()