#!/usr/bin/env python3
"""
バックアップ状況監視スクリプト
2時間毎にターミナルにバックアップ状況を表示する
"""

import os
import time
import json
from datetime import datetime, timedelta
from pathlib import Path


class StatusMonitor:
    """バックアップ状況監視クラス"""
    
    def __init__(self, project_root: str = None):
        self.project_root = Path(project_root) if project_root else Path.cwd()
        self.status_file = self.project_root / ".backup_status"
        self.monitor_interval = 2 * 60 * 60  # 2時間（秒）
        
    def load_backup_status(self):
        """バックアップ状況を読み込み"""
        if not self.status_file.exists():
            return None
        
        try:
            with open(self.status_file, 'r', encoding='utf-8') as f:
                return json.load(f)
        except Exception as e:
            print(f"❌ ステータス読み込みエラー: {e}")
            return None
    
    def display_status_banner(self):
        """バックアップ状況をバナー形式で表示"""
        status = self.load_backup_status()
        now = datetime.now()
        
        print("\n" + "="*60)
        print("🔄 PersonaAgent Factory - 自動保存監視システム")
        print("="*60)
        print(f"📅 現在時刻: {now.strftime('%Y-%m-%d %H:%M:%S')}")
        
        if not status:
            print("❌ バックアップ状況が取得できませんでした")
            print("   まだバックアップが実行されていない可能性があります")
        else:
            # 基本情報
            print(f"📁 バックアップ数: {status.get('total_backups', 0)} ファイル")
            
            # 最終バックアップ時刻
            if status.get('last_backup'):
                last_backup = datetime.fromisoformat(status['last_backup'])
                time_diff = now - last_backup
                
                if time_diff.total_seconds() < 3600:  # 1時間以内
                    status_icon = "✅"
                    status_text = "正常"
                elif time_diff.total_seconds() < 7200:  # 2時間以内
                    status_icon = "⚠️"
                    status_text = "注意"
                else:  # 2時間超過
                    status_icon = "❌"
                    status_text = "遅延"
                
                print(f"🕐 最終バックアップ: {last_backup.strftime('%Y-%m-%d %H:%M:%S')}")
                print(f"📊 状況: {status_icon} {status_text} ({self.format_time_diff(time_diff)}前)")
                
                # 次回予定時刻
                next_backup = last_backup + timedelta(hours=2)
                if next_backup > now:
                    next_diff = next_backup - now
                    print(f"⏰ 次回予定: {next_backup.strftime('%H:%M:%S')} ({self.format_time_diff(next_diff)}後)")
                else:
                    print(f"⏰ 次回予定: すぐに実行される予定")
            else:
                print("🕐 最終バックアップ: なし")
                print("📊 状況: ❌ 未実行")
            
            # 最新ファイル情報
            if status.get('backups'):
                latest = status['backups'][-1]
                size_mb = latest.get('size', 0) / (1024 * 1024)
                print(f"📄 最新ファイル: {latest.get('filename', 'N/A')} ({size_mb:.2f}MB)")
        
        print("="*60)
        print("💡 手動実行: python docs/scripts/backup_chat.py")
        print("💡 状況確認: python docs/scripts/backup_chat.py status")
        print("="*60 + "\n")
    
    def format_time_diff(self, td: timedelta) -> str:
        """時間差を見やすい形式でフォーマット"""
        total_seconds = int(td.total_seconds())
        
        if total_seconds < 60:
            return f"{total_seconds}秒"
        elif total_seconds < 3600:
            minutes = total_seconds // 60
            return f"{minutes}分"
        else:
            hours = total_seconds // 3600
            minutes = (total_seconds % 3600) // 60
            if minutes > 0:
                return f"{hours}時間{minutes}分"
            else:
                return f"{hours}時間"
    
    def check_backup_health(self):
        """バックアップの健全性をチェック"""
        status = self.load_backup_status()
        if not status:
            return False
        
        if not status.get('last_backup'):
            return False
        
        last_backup = datetime.fromisoformat(status['last_backup'])
        now = datetime.now()
        time_diff = now - last_backup
        
        # 3時間以上バックアップがない場合は異常
        return time_diff.total_seconds() < 3 * 60 * 60
    
    def run_monitor(self):
        """監視を開始"""
        print("🚀 バックアップ状況監視を開始します...")
        print(f"⏱️  監視間隔: {self.monitor_interval // 3600} 時間")
        print("⭐ Ctrl+C で停止できます\n")
        
        try:
            # 開始時に一度表示
            self.display_status_banner()
            
            while True:
                # 2時間待機
                time.sleep(self.monitor_interval)
                
                # ステータス表示
                self.display_status_banner()
                
                # 健全性チェック
                if not self.check_backup_health():
                    print("🚨 警告: バックアップが長時間実行されていません！")
                    print("   手動でバックアップを実行することを推奨します。")
                    print("   実行コマンド: python docs/scripts/backup_chat.py\n")
        
        except KeyboardInterrupt:
            print("\n🛑 監視を停止しました")
            print("👋 お疲れさまでした！")
    
    def run_simple_status(self):
        """シンプルな状況表示（一回のみ）"""
        self.display_status_banner()


def main():
    """メイン関数"""
    import sys
    
    monitor = StatusMonitor()
    
    if len(sys.argv) > 1:
        if sys.argv[1] == "monitor":
            monitor.run_monitor()
        elif sys.argv[1] == "status":
            monitor.run_simple_status()
        else:
            print("使用方法:")
            print("  python status_monitor.py           # 一回だけ状況表示")
            print("  python status_monitor.py monitor   # 2時間毎に継続監視")
            print("  python status_monitor.py status    # 一回だけ状況表示")
    else:
        monitor.run_simple_status()


if __name__ == "__main__":
    main()