<?php
// =============================================
// ログイン API
// POST /api/login.php
// リクエスト: { "username": "xxx", "password": "xxx" }
// =============================================

require_once __DIR__ . '/../includes/db.php';

// POSTのみ受付
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'POSTメソッドのみ対応'], 405);
}

// リクエストボディを取得
$input = json_decode(file_get_contents('php://input'), true);

$username = $input['username'] ?? '';
$password = $input['password'] ?? '';

// バリデーション
if (empty($username) || empty($password)) {
    json_response(['success' => false, 'message' => 'ユーザー名とパスワードを入力してください'], 400);
}

// ユーザー検索
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username AND password = :password");
$stmt->execute(['username' => $username, 'password' => $password]);
$user = $stmt->fetch();

if (!$user) {
    json_response(['success' => false, 'message' => 'ユーザー名またはパスワードが違います'], 401);
}

// セッションにユーザー情報を保存
$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];

json_response([
    'success' => true,
    'message' => 'ログイン成功',
    'user' => [
        'id' => (int)$user['id'],
        'username' => $user['username'],
        'coins' => (int)$user['coins']
    ]
]);
