<?php
// =============================================
// ユーザー登録 API
// POST /api/register.php
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

// ユーザー名の重複チェック
$stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
$stmt->execute(['username' => $username]);
if ($stmt->fetch()) {
    json_response(['success' => false, 'message' => 'そのユーザー名は既に使われています'], 409);
}

// ユーザー作成（パスワードはそのまま保存）
$stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
$stmt->execute(['username' => $username, 'password' => $password]);

$user_id = $pdo->lastInsertId();

// 登録後、自動ログイン
$_SESSION['user_id'] = $user_id;
$_SESSION['username'] = $username;

json_response([
    'success' => true,
    'message' => '登録成功',
    'user' => [
        'id' => (int)$user_id,
        'username' => $username
    ]
]);
