#!/usr/bin/env python3
"""
チャット自動保存スクリプト
Claude Code とのチャット履歴を定期的に保存する
"""

import os
import re
import time
import json
import shutil
from datetime import datetime
from pathlib import Path
from typing import Dict, List, Any


class ChatBackup:
    """チャット自動保存クラス"""
    
    def __init__(self, project_root: str = None):
        self.project_root = Path(project_root) if project_root else Path.cwd()
        self.claude_md_path = self.project_root / "claude.md"
        self.backup_dir = self.project_root / "backups"
        self.backup_dir.mkdir(exist_ok=True)
        
        # 設定
        self.backup_interval = 2 * 60 * 60  # 2時間（秒）
        self.max_backups = 48  # 最大保存数（4日分）
        self.status_file = self.project_root / ".backup_status"
        
    def create_backup(self) -> bool:
        """バックアップを作成"""
        try:
            if not self.claude_md_path.exists():
                print(f"警告: {self.claude_md_path} が存在しません")
                return False
            
            # バックアップファイル名
            timestamp = datetime.now().strftime('%Y%m%d_%H%M%S')
            backup_filename = f"claude_backup_{timestamp}.md"
            backup_path = self.backup_dir / backup_filename
            
            # ファイルをコピー
            shutil.copy2(self.claude_md_path, backup_path)
            
            # バックアップ情報を記録
            self.update_backup_status(backup_filename)
            
            print(f"✅ バックアップ作成: {backup_filename}")
            return True
            
        except Exception as e:
            print(f"❌ バックアップ作成エラー: {e}")
            return False
    
    def update_backup_status(self, backup_filename: str):
        """バックアップ状況を更新"""
        try:
            # 現在のステータスを読み込み
            status = self.load_backup_status()
            
            # 新しいバックアップ情報を追加
            status["backups"].append({
                "filename": backup_filename,
                "timestamp": datetime.now().isoformat(),
                "size": os.path.getsize(self.backup_dir / backup_filename)
            })
            
            # 古いバックアップを削除
            if len(status["backups"]) > self.max_backups:
                old_backup = status["backups"].pop(0)
                old_file = self.backup_dir / old_backup["filename"]
                if old_file.exists():
                    old_file.unlink()
                    print(f"🗑️  古いバックアップを削除: {old_backup['filename']}")
            
            # 統計情報を更新
            status["last_backup"] = datetime.now().isoformat()
            status["total_backups"] = len(status["backups"])
            
            # ステータスファイルに保存
            with open(self.status_file, 'w', encoding='utf-8') as f:
                json.dump(status, f, indent=2, ensure_ascii=False)
                
        except Exception as e:
            print(f"❌ バックアップ状況更新エラー: {e}")
    
    def load_backup_status(self) -> Dict[str, Any]:
        """バックアップ状況を読み込み"""
        if not self.status_file.exists():
            return {
                "backups": [],
                "last_backup": None,
                "total_backups": 0
            }
        
        try:
            with open(self.status_file, 'r', encoding='utf-8') as f:
                return json.load(f)
        except Exception as e:
            print(f"❌ バックアップ状況読み込みエラー: {e}")
            return {
                "backups": [],
                "last_backup": None,
                "total_backups": 0
            }
    
    def display_status(self):
        """バックアップ状況を表示"""
        status = self.load_backup_status()
        
        print("\n" + "="*50)
        print("📋 チャットバックアップ状況")
        print("="*50)
        print(f"📁 バックアップ数: {status['total_backups']}")
        
        if status['last_backup']:
            last_backup = datetime.fromisoformat(status['last_backup'])
            print(f"🕐 最終バックアップ: {last_backup.strftime('%Y-%m-%d %H:%M:%S')}")
        else:
            print("🕐 最終バックアップ: なし")
        
        # 最新の3件を表示
        if status['backups']:
            print("\n📄 最新バックアップ:")
            for backup in status['backups'][-3:]:
                timestamp = datetime.fromisoformat(backup['timestamp'])
                size_mb = backup['size'] / (1024 * 1024)
                print(f"  • {backup['filename']} ({size_mb:.2f}MB) - {timestamp.strftime('%m/%d %H:%M')}")
        
        print("="*50)
    
    def update_claude_md(self):
        """claude.mdファイルに自動保存情報を記録"""
        try:
            # 現在の内容を読み込み
            content = ""
            if self.claude_md_path.exists():
                with open(self.claude_md_path, 'r', encoding='utf-8') as f:
                    content = f.read()
            
            # 自動保存情報を追加
            backup_info = f"""
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
最終更新: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}

このセクションは自動保存スクリプトにより自動更新されます。
"""
            
            # 既存の自動保存情報を削除
            content = re.sub(r'# 自動保存システム設定.*?(?=\n#|\Z)', '', content, flags=re.DOTALL)
            
            # 新しい情報を追加
            content = content.rstrip() + "\n" + backup_info
            
            # ファイルに保存
            with open(self.claude_md_path, 'w', encoding='utf-8') as f:
                f.write(content)
            
            print(f"📝 claude.mdを更新しました")
            
        except Exception as e:
            print(f"❌ claude.md更新エラー: {e}")
    
    def run_backup(self):
        """バックアップ処理を実行"""
        print(f"\n⏰ 自動保存開始: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
        
        # バックアップを作成
        if self.create_backup():
            # claude.mdを更新
            self.update_claude_md()
            
            # 状況を表示
            self.display_status()
            
            print(f"✅ 自動保存完了: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
        else:
            print(f"❌ 自動保存失敗: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    
    def run_daemon(self):
        """デーモンモードで実行（2時間毎）"""
        print(f"🔄 自動保存デーモン開始: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
        print(f"⏱️  保存間隔: {self.backup_interval // 3600} 時間")
        print("⭐ Ctrl+C で停止できます")
        
        while True:
            try:
                self.run_backup()
                
                # 次の実行まで待機
                next_run = datetime.now().replace(second=0, microsecond=0)
                next_run = next_run.replace(hour=next_run.hour + 2)
                print(f"⏰ 次回実行予定: {next_run.strftime('%Y-%m-%d %H:%M:%S')}")
                print("💤 待機中... (2時間毎に状況をお知らせします)")
                
                # 30分毎にカウントダウン表示
                for i in range(4):  # 2時間 = 4 × 30分
                    time.sleep(30 * 60)  # 30分待機
                    remaining_hours = 2 - (i + 1) * 0.5
                    if remaining_hours > 0:
                        print(f"⏳ 次回バックアップまで {remaining_hours}時間")
                
            except KeyboardInterrupt:
                print("\n🛑 自動保存デーモンを停止しました")
                break
            except Exception as e:
                print(f"❌ デーモンエラー: {e}")
                print("🔄 1分後に再試行します...")
                time.sleep(60)  # 1分待機してから再試行


def main():
    """メイン関数"""
    import sys
    
    backup = ChatBackup()
    
    if len(sys.argv) > 1:
        if sys.argv[1] == "daemon":
            backup.run_daemon()
        elif sys.argv[1] == "status":
            backup.display_status()
        else:
            print("Usage: python backup_chat.py [daemon|status]")
    else:
        backup.run_backup()


if __name__ == "__main__":
    main()