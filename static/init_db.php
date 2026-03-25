<?php
// =============================================
// データベース初期化スクリプト
// このファイルを実行すると、テーブル作成 + 店舗データ投入を行う
// 使い方: php init_db.php
// =============================================

// dbフォルダがなければ作成
$db_dir = __DIR__ . '/db';
if (!file_exists($db_dir)) {
    mkdir($db_dir, 0777, true);
    echo "dbフォルダを作成しました\n";
}

$db_path = $db_dir . '/gacha.db';

// 既存DBがあれば削除して再作成
if (file_exists($db_path)) {
    unlink($db_path);
    echo "既存のデータベースを削除しました\n";
}

try {
    $pdo = new PDO('sqlite:' . $db_path);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ===== テーブル作成 =====

    // usersテーブル
    $pdo->exec("
        CREATE TABLE users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            coins INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "usersテーブルを作成しました\n";

    // shopsテーブル
    $pdo->exec("
        CREATE TABLE shops (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            genre TEXT NOT NULL,
            image TEXT NOT NULL,
            description TEXT NOT NULL,
            address TEXT NOT NULL
        )
    ");
    echo "shopsテーブルを作成しました\n";

    // gacha_historyテーブル
    $pdo->exec("
        CREATE TABLE gacha_history (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            shop_id INTEGER NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "gacha_historyテーブルを作成しました\n";

    // ===== 店舗データ投入 =====
    $shops = [
        [
            'name' => 'カフェ日和',
            'genre' => 'カフェ',
            'image' => 'images/cafe_hiyori.jpg',
            'description' => '古町の路地裏にひっそり佇む隠れ家カフェ。自家焙煎コーヒーと手作りスイーツが人気。',
            'address' => '新潟県新潟市中央区古町通3番町'
        ],
        [
            'name' => '麺屋 銀次郎',
            'genre' => 'ラーメン',
            'image' => 'images/menya_ginjiro.jpg',
            'description' => '濃厚味噌ラーメンが看板メニュー。新潟の地元食材を活かしたこだわりの一杯。',
            'address' => '新潟県新潟市中央区古町通5番町'
        ],
        [
            'name' => '酒処 ふるまち',
            'genre' => '居酒屋',
            'image' => 'images/sakadokoro_furumachi.jpg',
            'description' => '新潟の地酒を30種以上取り揃えた老舗居酒屋。旬の魚料理が絶品。',
            'address' => '新潟県新潟市中央区古町通7番町'
        ],
        [
            'name' => '古着屋 モノクローム',
            'genre' => '古着',
            'image' => 'images/monochrome.jpg',
            'description' => 'ヴィンテージからモダンまで、個性的なセレクトが光る古着屋。',
            'address' => '新潟県新潟市中央区古町通4番町'
        ],
        [
            'name' => '和菓子処 花鶴',
            'genre' => '和菓子',
            'image' => 'images/hanatsuru.jpg',
            'description' => '創業80年の老舗和菓子店。季節の上生菓子と抹茶セットが人気。',
            'address' => '新潟県新潟市中央区古町通2番町'
        ],
        [
            'name' => '雑貨屋 ことのは',
            'genre' => '雑貨',
            'image' => 'images/kotonoba.jpg',
            'description' => '新潟の作家によるハンドメイド雑貨を中心に扱うセレクトショップ。',
            'address' => '新潟県新潟市中央区古町通6番町'
        ],
        [
            'name' => 'イタリアン・ピッコロ',
            'genre' => 'イタリアン',
            'image' => 'images/piccolo.jpg',
            'description' => '地元野菜をふんだんに使ったパスタが自慢の小さなイタリアン。ランチが大人気。',
            'address' => '新潟県新潟市中央区古町通8番町'
        ],
        [
            'name' => '本と珈琲 栞',
            'genre' => 'ブックカフェ',
            'image' => 'images/shiori.jpg',
            'description' => '古書と新刊が並ぶ静かなブックカフェ。読書好きの憩いの場。',
            'address' => '新潟県新潟市中央区古町通3番町'
        ],
        [
            'name' => 'おにぎり処 米蔵',
            'genre' => 'おにぎり',
            'image' => 'images/komegura.jpg',
            'description' => '新潟産コシヒカリを使った手握りおにぎり専門店。具材は20種以上。',
            'address' => '新潟県新潟市中央区古町通5番町'
        ],
        [
            'name' => 'ギャラリー風街',
            'genre' => 'ギャラリー',
            'image' => 'images/kazamachi.jpg',
            'description' => '地元アーティストの作品を展示・販売するギャラリー。月替わりの企画展を開催。',
            'address' => '新潟県新潟市中央区古町通9番町'
        ],
    ];

    $stmt = $pdo->prepare("
        INSERT INTO shops (name, genre, image, description, address)
        VALUES (:name, :genre, :image, :description, :address)
    ");

    foreach ($shops as $shop) {
        $stmt->execute($shop);
    }
    echo count($shops) . "件の店舗データを投入しました\n";

    echo "\n===== 初期化完了 =====\n";
    echo "データベース: {$db_path}\n";

} catch (PDOException $e) {
    echo "エラー: " . $e->getMessage() . "\n";
    exit(1);
}
