<?php
// =============================================
// クーポン一覧取得 API
// GET /api/coupons.php
// =============================================

require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    json_response(['success' => false, 'message' => 'ログインしていません']);
}

$stmt = $pdo->query("SELECT * FROM coupons ORDER BY coin_price ASC");
$coupons = $stmt->fetchAll();

json_response(['success' => true, 'coupons' => $coupons]);
