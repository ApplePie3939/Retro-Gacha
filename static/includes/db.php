<?php
// =============================================
// データベース接続 共通ファイル
// 全てのAPIファイルでこのファイルをrequireする
// =============================================

// セッション開始
session_start();

// データベースファイルのパス
$db_path = __DIR__ . '/../db/gacha.db';

// SQLite接続
try {
    $pdo = new PDO('sqlite:' . $db_path);
    // エラーモードを例外に設定
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // 連想配列でフェッチ
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'DB接続エラー: ' . $e->getMessage()]);
    exit;
}

// JSON レスポンス用ヘッダー
function json_response($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// ログインチェック（未ログインなら401を返す）
function require_login() {
    if (!isset($_SESSION['user_id'])) {
        json_response(['success' => false, 'message' => 'ログインが必要です'], 401);
    }
}
