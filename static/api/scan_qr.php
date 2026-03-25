<?php
// =============================================
// QRコード読み取り API
// POST /api/scan_qr.php
// ログイン必須。受け取ったshop_idのコイン（100コイン）を獲得する。
// 一度取得した店舗からは再度取得できない。
// =============================================

require_once __DIR__ . '/../includes/db.php';

// ログインチェック
require_login();

// POSTリクエストのみ受け付ける
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => '無効なリクエストです'], 405);
}

// JSONデータを受け取る
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);

if (!isset($input['shop_id'])) {
    json_response(['success' => false, 'message' => '店舗IDが指定されていません'], 400);
}

$shop_id = (int)$input['shop_id'];
$user_id = (int)$_SESSION['user_id'];

// 店舗が存在するか確認
$stmt = $pdo->prepare("SELECT id, name FROM shops WHERE id = :shop_id");
$stmt->execute(['shop_id' => $shop_id]);
$shop = $stmt->fetch();

if (!$shop) {
    json_response(['success' => false, 'message' => '存在しない店舗です'], 404);
}

// 既に取得済みか確認
$stmt = $pdo->prepare("SELECT id FROM shop_coin_history WHERE user_id = :user_id AND shop_id = :shop_id");
$stmt->execute(['user_id' => $user_id, 'shop_id' => $shop_id]);
if ($stmt->fetch()) {
    json_response(['success' => false, 'message' => '既にこのお店のコインは取得済みです'], 400);
}

// コイン獲得処理 (トランザクション開始)
try {
    $pdo->beginTransaction();

    // 取得履歴を記録
    $stmt = $pdo->prepare("INSERT INTO shop_coin_history (user_id, shop_id) VALUES (:user_id, :shop_id)");
    $stmt->execute(['user_id' => $user_id, 'shop_id' => $shop_id]);

    // ユーザーに100コイン付与
    $reward_coins = 100;
    $stmt = $pdo->prepare("UPDATE users SET coins = coins + :coins WHERE id = :user_id");
    $stmt->execute(['coins' => $reward_coins, 'user_id' => $user_id]);

    // 最新のコイン数を取得
    $stmt = $pdo->prepare("SELECT coins FROM users WHERE id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $user = $stmt->fetch();

    $pdo->commit();

    json_response([
        'success' => true,
        'message' => "「{$shop['name']}」のQRコードを読み取り、{$reward_coins}コインを獲得しました！",
        'coins' => (int)$user['coins'],
        'shop_name' => $shop['name']
    ]);

} catch (PDOException $e) {
    $pdo->rollBack();
    json_response(['success' => false, 'message' => 'データベースエラーが発生しました'], 500);
}
