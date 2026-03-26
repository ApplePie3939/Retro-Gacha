<?php
// =============================================
// 所持クーポン一覧取得 API
// GET /api/my_coupons.php
// =============================================

require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    json_response(['success' => false, 'message' => 'ログインしていません']);
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT uc.id as user_coupon_id, uc.is_used, uc.created_at, c.name, c.description, c.coin_price
    FROM user_coupons uc
    JOIN coupons c ON uc.coupon_id = c.id
    WHERE uc.user_id = :user_id
    ORDER BY uc.created_at DESC
");
$stmt->execute(['user_id' => $user_id]);
$my_coupons = $stmt->fetchAll();

json_response(['success' => true, 'my_coupons' => $my_coupons]);
