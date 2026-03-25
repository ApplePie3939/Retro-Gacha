<?php
// =============================================
// ログアウト API
// GET /api/logout.php
// =============================================

require_once __DIR__ . '/../includes/db.php';

// セッション破棄
$_SESSION = [];
session_destroy();

json_response(['success' => true, 'message' => 'ログアウトしました']);
