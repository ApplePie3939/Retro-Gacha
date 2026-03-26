<?php
// =============================================
// クーポン購入 API
// POST /api/buy_coupon.php
// =============================================

require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    json_response(['success' => false, 'message' => 'ログインしていません']);
}

$user_id = $_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true);
$coupon_id = isset($input['coupon_id']) ? (int)$input['coupon_id'] : 0;

if ($coupon_id <= 0) {
    json_response(['success' => false, 'message' => '無効なリクエストです']);
}

try {
    $pdo->beginTransaction();

    // クーポン情報を取得
    $stmt = $pdo->prepare("SELECT * FROM coupons WHERE id = :id");
    $stmt->execute(['id' => $coupon_id]);
    $coupon = $stmt->fetch();

    if (!$coupon) {
        $pdo->rollBack();
        json_response(['success' => false, 'message' => 'クーポンが見つかりません']);
    }

    // ユーザー情報を取得
    $stmt = $pdo->prepare("SELECT coins FROM users WHERE id = :id");
    $stmt->execute(['id' => $user_id]);
    $user = $stmt->fetch();

    if ($user['coins'] < $coupon['coin_price']) {
        $pdo->rollBack();
        json_response(['success' => false, 'message' => 'コインが足りません']);
    }

    // コインを減らす
    $stmt = $pdo->prepare("UPDATE users SET coins = coins - :price WHERE id = :id");
    $stmt->execute(['price' => $coupon['coin_price'], 'id' => $user_id]);

    // user_couponsに追加
    $stmt = $pdo->prepare("INSERT INTO user_coupons (user_id, coupon_id) VALUES (:user_id, :coupon_id)");
    $stmt->execute(['user_id' => $user_id, 'coupon_id' => $coupon_id]);

    $pdo->commit();

    json_response([
        'success' => true, 
        'message' => '「' . $coupon['name'] . '」を購入しました！', 
        'remaining_coins' => $user['coins'] - $coupon['coin_price']
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    json_response(['success' => false, 'message' => '購入処理中にエラーが発生しました: ' . $e->getMessage()]);
}
