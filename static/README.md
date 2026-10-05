# 古町レトロ・ガチャウォーク — ローカルPHP版 開発ガイド

この文書は、PHPとSQLiteで動作するローカル版の環境構築、API、データベース構成をまとめた開発者向け資料です。アプリの概要や公開デモについては、[プロジェクトのREADME](../README.md) を参照してください。

## 動作環境

- PHP 7.4以上
- PDO SQLite拡張
- JavaScriptが有効なWebブラウザ
- QRコード画像を生成する場合はインターネット接続

QRコードの読み取りにはブラウザのカメラ、または画像ファイルを使用します。スマートフォンなど別端末からアクセスする場合は、同じネットワーク内からPCのIPアドレスへ接続できます。ただし、多くのブラウザではカメラ利用にHTTPSの安全な接続が必要です。HTTP接続では画像ファイルから読み取るか、HTTPSで配信できる開発用トンネルを使用してください。

## 起動方法

### 方法1: PHPコマンドを使用する

リポジトリのルートから `static` ディレクトリへ移動し、DBを初期化してからサーバーを起動します。

```bash
cd static
php init_db.php
php -S 0.0.0.0:8080
```

ブラウザで [http://localhost:8080](http://localhost:8080) を開きます。サーバーを停止するには `Ctrl+C` を押してください。

> **注意:** `php init_db.php` は既存の `db/gacha.db` を削除して再作成します。ユーザー、コイン、履歴、所持クーポンもすべて初期化されます。

初期化処理では8店舗と4種類のクーポンを登録し、外部のQR Code APIから `images/qrcodes/shop_1.png` ～ `shop_8.png` を取得します。外部通信に失敗した場合、DBの初期化は続行されますが、該当するQR画像は生成されません。

### 方法2: XAMPP用バッチを使用する

WindowsでXAMPPを `C:\xampp` にインストールしている場合は、`static` フォルダにある `start_server.bat` をダブルクリックします。

このバッチは次の処理を行います。

1. `C:\xampp\php\php.exe` の存在を確認する
2. `db/gacha.db` が存在しない場合だけDBを初期化する
3. PCのIPv4アドレスとローカルURLを表示する
4. `0.0.0.0:8080` でPHPサーバーを起動する

別端末から接続できない場合は、両端末が同じネットワークにいることと、Windowsファイアウォールでポート `8080` が許可されていることを確認してください。

## ディレクトリ構成

```text
static/
├── api/
│   ├── buy_coupon.php      # クーポン交換
│   ├── coupons.php         # クーポン一覧
│   ├── gacha.php           # ランダムな店舗の取得
│   ├── login.php           # ログイン
│   ├── logout.php          # ログアウト
│   ├── my_coupons.php      # 所持クーポン一覧
│   ├── register.php        # ユーザー登録
│   ├── scan_qr.php         # QR報酬の付与
│   ├── shops.php           # 店舗一覧
│   └── user.php            # ログイン状態の確認
├── db/
│   └── gacha.db            # SQLiteデータベース
├── images/
│   ├── gacha/              # ガチャ演出用画像
│   └── qrcodes/            # 店舗QRコード
├── includes/
│   └── db.php              # DB接続、セッション、JSON応答
├── js/
│   ├── api-client.js       # PHP／Pages共通APIクライアント
│   └── config.js           # ローカル版の接続設定
├── pages/
│   ├── css/                # 画面スタイル
│   ├── coupons.html        # クーポン画面
│   ├── gacha.html          # ガチャ画面
│   ├── login.html          # ログイン画面
│   ├── qr.html             # QRコード読み取り画面
│   └── register.html       # 新規登録画面
├── index.html              # トップページ
├── init_db.php             # DBとサンプルデータの再作成
└── start_server.bat        # XAMPP向け起動スクリプト
```

## API

APIはJSONを返します。ログイン状態はPHPセッションで管理されるため、ログイン必須のAPIを呼び出すときは同じブラウザセッションを使用します。

| メソッド | エンドポイント | ログイン | 概要 |
|---|---|---:|---|
| `POST` | `/api/register.php` | 不要 | ユーザーを登録し、自動ログインする |
| `POST` | `/api/login.php` | 不要 | ユーザー名とパスワードでログインする |
| `GET` | `/api/logout.php` | 不要 | セッションを破棄する |
| `GET` | `/api/user.php` | 不要 | ログイン状態とユーザー情報を返す |
| `GET` | `/api/shops.php` | 不要 | 全店舗をID順で返す |
| `GET` | `/api/gacha.php` | 必須 | ランダムな1店舗を返し、履歴へ記録する |
| `POST` | `/api/scan_qr.php` | 必須 | 店舗QRの読み取りを記録し、100コインを付与する |
| `GET` | `/api/coupons.php` | 必須 | クーポンを必要コイン数の昇順で返す |
| `GET` | `/api/my_coupons.php` | 必須 | ログインユーザーの所持クーポンを返す |
| `POST` | `/api/buy_coupon.php` | 必須 | コインを消費してクーポンを取得する |

### ユーザー登録

```http
POST /api/register.php
Content-Type: application/json

{"username":"demo","password":"secret"}
```

成功時は次の形式で返します。

```json
{
  "success": true,
  "message": "登録成功",
  "user": { "id": 1, "username": "demo" }
}
```

### ログイン

```http
POST /api/login.php
Content-Type: application/json

{"username":"demo","password":"secret"}
```

```json
{
  "success": true,
  "message": "ログイン成功",
  "user": { "id": 1, "username": "demo", "coins": 0 }
}
```

### QR報酬の獲得

```http
POST /api/scan_qr.php
Content-Type: application/json

{"shop_id":1}
```

成功すると100コインを獲得します。同じユーザーが同じ店舗から再度コインを受け取ることはできません。

```json
{
  "success": true,
  "message": "「店舗名」のQRコードを読み取り、100コインを獲得しました！",
  "coins": 100,
  "shop_name": "店舗名"
}
```

### クーポンの交換

```http
POST /api/buy_coupon.php
Content-Type: application/json

{"coupon_id":3}
```

所持コインが足りる場合、コインを差し引いて所持クーポンへ追加します。

```json
{
  "success": true,
  "message": "「大盛り無料券」を購入しました！",
  "remaining_coins": 0
}
```

## データベース

`init_db.php` は次の6テーブルを作成します。

### `users`

| カラム | 型 | 説明 |
|---|---|---|
| `id` | INTEGER | 主キー、自動採番 |
| `username` | TEXT | 一意のユーザー名 |
| `password` | TEXT | パスワード（平文） |
| `coins` | INTEGER | 所持コイン、初期値0 |
| `created_at` | DATETIME | 登録日時 |

### `shops`

| カラム | 型 | 説明 |
|---|---|---|
| `id` | INTEGER | 主キー、自動採番 |
| `name` | TEXT | 店舗名 |
| `genre` | TEXT | ジャンル |
| `image` | TEXT | 店舗画像のパス |
| `description` | TEXT | 店舗の説明 |
| `address` | TEXT | 住所 |

### `gacha_history`

| カラム | 型 | 説明 |
|---|---|---|
| `id` | INTEGER | 主キー、自動採番 |
| `user_id` | INTEGER | ユーザーID |
| `shop_id` | INTEGER | ガチャで選ばれた店舗ID |
| `created_at` | DATETIME | 実行日時 |

### `shop_coin_history`

| カラム | 型 | 説明 |
|---|---|---|
| `id` | INTEGER | 主キー、自動採番 |
| `user_id` | INTEGER | ユーザーID |
| `shop_id` | INTEGER | コインを獲得した店舗ID |
| `created_at` | DATETIME | 獲得日時 |

`user_id` と `shop_id` の組み合わせは一意で、同じ店舗からの重複獲得を防ぎます。

### `coupons`

| カラム | 型 | 説明 |
|---|---|---|
| `id` | INTEGER | 主キー、自動採番 |
| `name` | TEXT | クーポン名 |
| `description` | TEXT | 特典内容 |
| `coin_price` | INTEGER | 交換に必要なコイン数 |

### `user_coupons`

| カラム | 型 | 説明 |
|---|---|---|
| `id` | INTEGER | 主キー、自動採番 |
| `user_id` | INTEGER | ユーザーID |
| `coupon_id` | INTEGER | クーポンID |
| `is_used` | INTEGER | 使用状態、初期値0 |
| `created_at` | DATETIME | 取得日時 |

## DBをリセットする

`static` ディレクトリで次を実行します。

```bash
php init_db.php
```

既存DBが削除され、テーブルとサンプルデータが作り直されます。データを残したい場合は、実行前に `db/gacha.db` を別の場所へコピーしてください。

## セキュリティ上の注意

- このアプリは開発・プレゼンテーション用のプロトタイプです。
- パスワードはハッシュ化せず、SQLiteへ平文で保存します。普段使用しているパスワードを入力しないでください。
- 認証は基本的なPHPセッションのみで、CSRF対策など公開サービス向けの保護は実装していません。
- ローカルPHP版をインターネットへ公開しないでください。
- QRコードには店舗IDを表すJSON（例: `{"shop_id":1}`）が格納されています。

## GitHub Pagesへの公開

初回のみ、GitHubリポジトリの `Settings` → `Pages` → `Build and deployment` → `Source` を **GitHub Actions** に設定します。以降は `main` ブランチへのpushでテストとデプロイが自動実行されます。

デプロイ時は `deploy/pages-config.js` を `js/config.js` として配置し、ブラウザの `localStorage` を使うデモAPIへ切り替えます。成果物には静的ファイルだけが含まれ、PHP API、SQLite DB、DB初期化スクリプトは公開されません。

## 関連情報

- [プロジェクト概要と公開デモ](../README.md)
- [GitHub Pagesデプロイ設定](../.github/workflows/deploy-pages.yml)
- [Pages版デモAPIのテスト](../tests/demo-api.test.cjs)
