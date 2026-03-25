<?php
// =============================================
// 全店舗一覧 API
// GET /api/shops.php
// =============================================

require_once __DIR__ . '/../includes/db.php';

$stmt = $pdo->query("SELECT * FROM shops ORDER BY id");
$shops = $stmt->fetchAll();

json_response([
    'success' => true,
    'shops' => $shops
]);
