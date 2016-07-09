<?php
session_start();

// logout.php?logoutにアクセスしたユーザーをログアウトする
if(isset($_GET['logout'])) {
	session_destroy();
	unset($_SESSION['user']);
	header("Location: index.php");
} else {
	header("Location: index.php");
}
