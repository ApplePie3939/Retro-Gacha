<?php

/**
 * 既存のデータベースに不足している shop_coin_history テーブルを追加するスクリプト
 * * このスクリプトは、ユーザーが特定の店舗でコインを獲得した履歴を保存するための
 * テーブルを作成し、重複取得を防止する制約を設定します。
 */

$db_path = __DIR__ . '/../db/gacha.db';

try {
    /** @var PDO $pdo データベース接続インスタンス */
    $pdo = new PDO('sqlite:' . $db_path);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    /**
     * shop_coin_history テーブルの作成
     * * id: 履歴を一意に識別するための自動連番ID
     * user_id: コインを獲得したユーザーの識別子
     * shop_id: コインを発行した店舗の識別子
     * created_at: レコードが作成された日時（デフォルトは現在時刻）
     * UNIQUE: 同一ユーザーによる同一店舗での重複獲得を防止
     */
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS shop_coin_history (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            shop_id INTEGER NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(user_id, shop_id)
        )
    ");
    echo "成功: shop_coin_history テーブルを追加しました！\n";
    echo "これで通信エラーは解消され、QRコードでコインを獲得できるようになります。\n";
} catch (PDOException $e) {

    /** エラー発生時のメッセージ出力 */
    echo "エラー: " . $e->getMessage() . "\n";
}
