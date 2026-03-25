# 古町「レトロ・ガチャ」ウォーク - チーム共有資料

## 📁 プロジェクト構成

```
static/
├── start_server.bat          ← サーバー起動（ダブルクリック）
├── init_db.php               ← DB初期化（自動実行される）
├── index.html                ← トップページ
├── includes/
│   └── db.php                ← DB接続（共通、全PHPでrequire）
├── api/
│   ├── register.php          ← ユーザー登録
│   ├── login.php             ← ログイン
│   ├── logout.php            ← ログアウト
│   ├── user.php              ← ログイン状態確認
│   ├── gacha.php             ← ガチャ（ランダム店舗）
│   └── shops.php             ← 全店舗一覧
├── pages/
│   ├── login.html            ← ログイン画面
│   ├── register.html         ← 新規登録画面
│   └── gacha.html            ← ガチャ画面
├── images/                   ← 店舗画像を入れるフォルダ
└── db/
    └── gacha.db              ← SQLiteデータベース（自動生成）
```

---

## 🚀 サーバーの起動方法

1. `static` フォルダを開く
2. `start_server.bat` をダブルクリック
3. 表示されたIPアドレスを他の人に共有する

> **注意**: PCのファイアウォールでポート `8080` を許可する必要がある場合があります。

---

## 🔌 API仕様（フロントエンド開発者向け）

全APIはJSON形式。`Content-Type: application/json` で送受信する。

### ユーザー登録
```
POST /api/register.php
Body: { "username": "名前", "password": "パスワード" }
成功: { "success": true, "message": "登録成功", "user": { "id": 1, "username": "名前" } }
失敗: { "success": false, "message": "エラー内容" }
```

### ログイン
```
POST /api/login.php
Body: { "username": "名前", "password": "パスワード" }
成功: { "success": true, "user": { "id": 1, "username": "名前", "coins": 0 } }
```

### ログアウト
```
GET /api/logout.php
=> { "success": true, "message": "ログアウトしました" }
```

### ログイン状態確認
```
GET /api/user.php
ログイン済: { "logged_in": true, "user": { "id": 1, "username": "名前", "coins": 0 } }
未ログイン: { "logged_in": false, "user": null }
```

### ガチャ（ランダム店舗取得）※ログイン必須
```
GET /api/gacha.php
成功: {
  "success": true,
  "shop": {
    "id": 1,
    "name": "カフェ日和",
    "genre": "カフェ",
    "image": "images/cafe_hiyori.jpg",
    "description": "説明文...",
    "address": "新潟県新潟市中央区古町通3番町"
  }
}
```

### 全店舗一覧
```
GET /api/shops.php
=> { "success": true, "shops": [ ... ] }
```

---

## 📝 JavaScriptでのAPI呼び出し例

### ログイン
```javascript
const res = await fetch('/api/login.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ username: 'test', password: 'test' })
});
const data = await res.json();
if (data.success) {
    console.log('ログイン成功:', data.user);
}
```

### ガチャを回す
```javascript
const res = await fetch('/api/gacha.php');
const data = await res.json();
if (data.success) {
    console.log('当たった店:', data.shop.name);
    console.log('ジャンル:', data.shop.genre);
}
```

### Google Mapリンクの生成
```javascript
const mapUrl = 'https://www.google.com/maps/search/?api=1&query='
    + encodeURIComponent(shop.address);
window.open(mapUrl, '_blank');
```

---

## 🗂️ データベース（SQLite）

### usersテーブル
| カラム | 型 | 説明 |
|---|---|---|
| id | INTEGER | 自動採番（主キー） |
| username | TEXT | ユーザー名（一意） |
| password | TEXT | パスワード |
| coins | INTEGER | コイン（初期値0、将来用） |
| created_at | DATETIME | 登録日時 |

### shopsテーブル
| カラム | 型 | 説明 |
|---|---|---|
| id | INTEGER | 自動採番 |
| name | TEXT | 店名 |
| genre | TEXT | ジャンル |
| image | TEXT | 画像パス |
| description | TEXT | 説明文 |
| address | TEXT | 住所 |

### gacha_historyテーブル
| カラム | 型 | 説明 |
|---|---|---|
| id | INTEGER | 自動採番 |
| user_id | INTEGER | ユーザーID |
| shop_id | INTEGER | 店舗ID |
| created_at | DATETIME | ガチャ実行日時 |

---

## 🖼️ 店舗画像について

`images/` フォルダに以下のファイル名で画像を追加してください：

| ファイル名 | 店名 |
|---|---|
| cafe_hiyori.jpg | カフェ日和 |
| menya_ginjiro.jpg | 麺屋 銀次郎 |
| sakadokoro_furumachi.jpg | 酒処 ふるまち |
| monochrome.jpg | 古着屋 モノクローム |
| hanatsuru.jpg | 和菓子処 花鶴 |
| kotonoba.jpg | 雑貨屋 ことのは |
| piccolo.jpg | イタリアン・ピッコロ |
| shiori.jpg | 本と珈琲 栞 |
| komegura.jpg | おにぎり処 米蔵 |
| kazamachi.jpg | ギャラリー風街 |

> 画像がなくても動作します（「画像準備中」と表示されます）。

---

## ⚠️ 注意事項

- **DBをリセットしたい場合**: `db/gacha.db` を削除して `start_server.bat` を再実行
- セキュリティは一切なし（パスワードは平文保存）。プレゼン用デモ限定
- PHPはXAMPPのもの（`C:\xampp\php\php.exe`）を使用
