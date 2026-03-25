<?php
// =============================================
// ガチャ API（ランダム店舗取得）
// GET /api/gacha.php
// ログイン必須。ランダムに1店舗を返し、履歴に保存する。
// =============================================

require_once __DIR__ . '/../includes/db.php';

// ログインチェック
require_login();

// ランダムに1店舗を取得
$stmt = $pdo->query("SELECT * FROM shops ORDER BY RANDOM() LIMIT 1");
$shop = $stmt->fetch();

if (!$shop) {
    json_response(['success' => false, 'message' => '店舗データがありません'], 404);
}

// ガチャ履歴を保存
$stmt = $pdo->prepare("INSERT INTO gacha_history (user_id, shop_id) VALUES (:user_id, :shop_id)");
$stmt->execute([
    'user_id' => $_SESSION['user_id'],
    'shop_id' => $shop['id']
]);

json_response([
    'success' => true,
    'shop' => [
        'id' => (int)$shop['id'],
        'name' => $shop['name'],
        'genre' => $shop['genre'],
        'image' => $shop['image'],
        'description' => $shop['description'],
        'address' => $shop['address']
    ]
]);
