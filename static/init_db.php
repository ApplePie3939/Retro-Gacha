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

    // shop_coin_historyテーブル (QRコードスキャンでのコイン獲得履歴)
    $pdo->exec("
        CREATE TABLE shop_coin_history (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            shop_id INTEGER NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(user_id, shop_id)
        )
    ");
    echo "shop_coin_historyテーブルを作成しました\n";

    // ===== 店舗データ投入 =====
    $shops = [
        [
            'name' => '魚と地酒とワイン りべら',
            'genre' => '酒',
            'image' => 'images/localsake_ribera.jpg',
            'description' => '落ち着いた空間で頂く和食と地酒、そしてワイン。',
            'address' => '新潟市中央区古町通9番町1475-4'
        ],
        [
            'name' => '豚米（とんべい） ',
            'genre' => 'おにぎり',
            'image' => 'images/tonbei.jpg',
            'description' => '「人生最高のおにぎり」を掲げる、ごちそうおにぎりと豚汁の専門店。',
            'address' => '新潟県新潟市中央区古町通4番町632'
        ],
        [
            'name' => '麺亭',
            'genre' => 'ラーメン',
            'image' => 'images/mentei.jpg',
            'description' => '夜遅くまで営業し、飲み歩きの後や深夜の小腹を満たすのにぴったりな町中華',
            'address' => '新潟県新潟市中央区古町通8-1452-2藤和ビル1F'
        ],
        [
            'name' => 'シン鉄板ビストロ',
            'genre' => 'お肉',
            'image' => 'images/bisutro.jpg',
            'description' => '品質にこだわった新潟県産和牛を鉄板で焼き上げるライブ感をお楽しみください！',
            'address' => '新潟県新潟市中央区古町通8番町1493'
        ],
        [
            'name' => '喜ぐち',
            'genre' => '居酒屋',
            'image' => 'images/kiguti.jpg',
            'description' => '創業は昭和40年、世代を超えて愛され続けるローカル酒場',
            'address' => '新潟県新潟市中央区古町通10番町1720'
        ],
        [
            'name' => 'おまかせ食堂 ななや',
            'genre' => '定食屋',
            'image' => 'images/nanaya.jpg',
            'description' => '古町で味わう“お母さん”の味',
            'address' => '新潟県新潟市中央区古町通7番町1005-3'
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

    $qr_dir = __DIR__ . '/images/qrcodes';
    if (!file_exists($qr_dir)) {
        mkdir($qr_dir, 0777, true);
    }

    $stmt = $pdo->prepare("
        INSERT INTO shops (name, genre, image, description, address)
        VALUES (:name, :genre, :image, :description, :address)
    ");

    foreach ($shops as $shop) {
        $stmt->execute($shop);
        $shop_id = $pdo->lastInsertId();

        // QRコード生成して保存 (QRServer APIを使用)
        $qr_data = json_encode(['shop_id' => (int)$shop_id]);
        $qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&format=png&data=" . urlencode($qr_data);

        // 外部APIを叩いて画像を保存
        $qr_image = @file_get_contents($qr_url);
        if ($qr_image !== false) {
            file_put_contents($qr_dir . "/shop_{$shop_id}.png", $qr_image);
            echo "店舗「{$shop['name']}」の店舗データとQRコード画像を生成しました。\n";
        } else {
            echo "店舗「{$shop['name']}」のQRコード画像生成に失敗しました（外部APIエラー）。\n";
        }
    }
    echo count($shops) . "件の店舗データを投入しました\n";

    echo "\n===== 初期化完了 =====\n";
    echo "データベース: {$db_path}\n";
} catch (PDOException $e) {
    echo "エラー: " . $e->getMessage() . "\n";
    exit(1);
}
