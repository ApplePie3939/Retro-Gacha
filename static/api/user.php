<?php
// =============================================
// ログイン状態確認 API
// GET /api/user.php
// =============================================

require_once __DIR__ . '/../includes/db.php';

if (isset($_SESSION['user_id'])) {
    // ログイン済み → ユーザー情報を返す
    $stmt = $pdo->prepare("SELECT id, username, coins FROM users WHERE id = :id");
    $stmt->execute(['id' => $_SESSION['user_id']]);
    $user = $stmt->fetch();

    json_response([
        'logged_in' => true,
        'user' => [
            'id' => (int)$user['id'],
            'username' => $user['username'],
            'coins' => (int)$user['coins']
        ]
    ]);
} else {
    // 未ログイン
    json_response([
        'logged_in' => false,
        'user' => null
    ]);
}
