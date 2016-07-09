# 仮タイトル
【PHP7対応】ログイン・会員登録機能を作る方法【2016年版】

# KW
php ログイン  590
php 会員登録  70

# PHPのログインシステム用のDBを作成（SQL）
phpMyAdminのSQLに下記を貼り付け。

```sql
CREATE DATABASE register_func DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
CREATE TABLE register_func. users (
user_id INT( 5 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
username VARCHAR( 25 ) NOT NULL ,
email VARCHAR( 35 ) NOT NULL ,
password VARCHAR( 60 ) NOT NULL ,
UNIQUE (email)
);
```
# DB接続のIDとPASSを保管するファイルを作成する
新規作成ファイル：core/config.php

```php
$host = "localhost";
$username = "root";
$password = "root";
$dbname = "register_func";
```

# coreフォルダへのアクセス制限ファイル（.htaccess）を作成
新規作成ファイル：core/.htaccess

```
<Files ~ "\.(dwt|php)$">
Deny from all
</Files>
```

# DBと接続するPHPファイルを作成する
新規作成ファイル：dbconnect.php

```sql
require_once('./core/config.php');

$mysqli = new mysqli($host, $username, $password, $dbname);
if ($mysqli->connect_error) {
	error_log($mysqli->connect_error);
	exit;
}
```

# 会員登録ページを作る
新規作成ファイル：register.php
まずはDB接続とセッションスタートを記述します。
セッションってなにそれ美味しいのって人は、<a href="http://techacademy.jp/magazine/4970">PHPでセッションを使う方法【初心者向け】</a>をどうぞ。

```php
session_start();
if( isset($_SESSION['user']) != "") {
	// ログイン済みの場合はリダイレクト
	header("Location: home.php");
}
// DBとの接続
include_once 'dbconnect.php';
```

# 会員登録フォームを作る
編集ファイル：register.php
HTMLの時間です。下記のとおり。

```html
<!DOCTYPE HTML>
<html lang="ja">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>PHPの会員登録機能</title>
<link rel="stylesheet" href="style.css">

<!-- Bootstrap読み込み（スタイリングのため） -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap.min.css">
</head>
<body>
<div class="col-xs-6 col-xs-offset-3">

<form method="post">
	<h1>会員登録フォーム</h1>
	<div class="form-group">
		<input type="text" class="form-control" name="username" placeholder="ユーザー名" required />
	</div>
	<div class="form-group">
		<input type="email"  class="form-control" name="email" placeholder="メールアドレス" required />
	</div>
	<div class="form-group">
		<input type="password" class="form-control" name="password" placeholder="パスワード" required />
	</div>
	<button type="submit" class="btn btn-default" name="signup">会員登録する</button>
	<a href="index.php">ログインはこちら</a>
</form>

</div>
</body>
</html>
```

# 会員登録フォームの情報をDBに保存する
編集ファイル：register.php
会員登録ボタン（signup）が押された時に実行されるスクリプトです。
会員登録が成功するとメッセージが表示されます。
Photo: 会員登録完了の画像

```php
// signupがPOSTされたときに下記を実行
if(isset($_POST['signup'])) {

	$username = $mysqli->real_escape_string($_POST['username']);
	$email = $mysqli->real_escape_string($_POST['email']);
	$password = $mysqli->real_escape_string($_POST['password']);
	$password = password_hash($password, PASSWORD_DEFAULT);

	// POSTされた情報をDBに格納する
	$query = "INSERT INTO users(username,email,password) VALUES('$username','$email','$password')";

	if($mysqli->query($query)) {  ?>
		<div class="alert alert-success" role="alert">登録しました</div>
		<?php } else { ?>
		<div class="alert alert-danger" role="alert">エラーが発生しました。</div>
		<?php
	}
}
```

# ログインページ作成
新規作成ファイル：index.php
Emailとパスワードでユーザー認証して、一致した場合はログインできます。
説明するまでもないですよね。よくあるログインページです。
まずは下記のようにDBに接続します。

```php
ob_start();
session_start();
if( isset($_SESSION['user']) != "") {
	header("Location: home.php");
}
include_once 'dbconnect.php';
```

# ログインページのログインフォーム作成
編集ファイル：index.php
register.phpを作った時とほぼ同じです。

```html
<!DOCTYPE HTML>
<html lang="ja">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>PHPのログイン機能</title>
<link rel="stylesheet" href="style.css">
<!-- Bootstrap読み込み（スタイリングのため） -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap.min.css">
</head>
</head>
<body>
<div class="col-xs-6 col-xs-offset-3">

<form method="post">
	<h1>ログインフォーム</h1>
	<div class="form-group">
		<input type="email"  class="form-control" name="email" placeholder="メールアドレス" required />
	</div>
	<div class="form-group">
		<input type="password" class="form-control" name="password" placeholder="パスワード" required />
	</div>
	<button type="submit" class="btn btn-default" name="login">ログインする</button>
	<a href="register.php">会員登録はこちら</a>
</form>

</div>
</body>
</html>
```

# ログインページのログインシステムをPHPで作成
編集ファイル：index.php
ちょいややこしいですが、コメント多めに入れています。

```php
// ログインボタンがクリックされたときに下記を実行
if(isset($_POST['login'])) {

	$email = $mysqli->real_escape_string($_POST['email']);
	$password = $mysqli->real_escape_string($_POST['password']);

	// クエリの実行
	$query = "SELECT * FROM users WHERE email='$email'";
	$result = $mysqli->query($query);
	if (!$result) {
		print('クエリーが失敗しました。' . $mysqli->error);
		$mysqli->close();
		exit();
	}

	// パスワード(暗号化済み）とユーザーIDの取り出し
	while ($row = $result->fetch_assoc()) {
		$db_hashed_pwd = $row['password'];
		$user_id = $row['user_id'];
	}

	// データベースの切断
	$result->close();

	// ハッシュ化されたパスワードがマッチするかどうかを確認
	if (password_verify($password, $db_hashed_pwd)) {
		$_SESSION['user'] = $user_id;
		header("Location: home.php");
		exit;
	} else { ?>
		<div class="alert alert-danger" role="alert">メールアドレスとパスワードが一致しません。</div>
	<?php }
}
```
# ログイン後のマイページを作成
新規作成ファイル：home.php
home.phpに訪れたユーザーがログインしているかどうかを判別して、
ログイン済みのユーザーには会員情報を表示します。

```php
session_start();
include_once 'dbconnect.php';
if(!isset($_SESSION['user'])) {
	header("Location: index.php");
}

// ユーザーIDからユーザー名を取り出す
$query = "SELECT * FROM users WHERE user_id=".$_SESSION['user']."";
$result = $mysqli->query($query);

$result = $mysqli->query($query);
if (!$result) {
	print('クエリーが失敗しました。' . $mysqli->error);
	$mysqli->close();
	exit();
}

// ユーザー情報の取り出し
while ($row = $result->fetch_assoc()) {
	$username = $row['username'];
	$email = $row['email'];
}

// データベースの切断
$result->close();
?>
```


```html
<!DOCTYPE HTML>
<html lang="ja">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>PHPのマイページ機能</title>
<link rel="stylesheet" href="style.css">
<!-- Bootstrap読み込み（スタイリングのため） -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap.min.css">
</head>
</head>
<body>
<div class="col-xs-6 col-xs-offset-3">

<h1>プロフィール</h1>
<ul>
	<li>名前：<?php echo $username; ?></li>
	<li>メールアドレス：<?php echo $email; ?></li>
</ul>
<a href="logout.php?logout">ログアウト</a>

</div>
</body>
</html>
```

# さいごに、、ログアウトページの作成
新規作成ファイル：logout.php
とっても簡単です。下記みれば分かるかなと。

```php
session_start();

// logout.php?logoutにアクセスしたユーザーをログアウトする
if(isset($_GET['logout'])) {
	session_destroy();
	unset($_SESSION['user']);
	header("Location: index.php");
} else {
	header("Location: index.php");
}
```

```php
```
```php
```
```php
```
```php
```










