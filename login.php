<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';

if (is_admin()) { header('Location: admin.php'); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (login_admin(trim((string)($_POST['username'] ?? '')), (string)($_POST['password'] ?? ''))) {
        header('Location: admin.php'); exit;
    }
    usleep(350000);
    $error = '用户名或密码不正确';
}
?>
<!doctype html><html lang="zh-CN"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>三奇.项目导航</title><link rel="stylesheet" href="assets/login.css"><link rel="stylesheet" href="assets/brand-logo.css"></head><body><main><section class="login-card"><div class="brand"><span class="brand-logo"><img src="assets/sanqi-logo-cropped.png" alt="三奇 3Q"></span><div class="brand-title">三奇<span class="brand-dot" aria-hidden="true"></span>项目导航<small>SANQI PROJECT NAVIGATION</small></div></div><div class="intro"><p>ADMIN ACCESS</p><h1>欢迎回来</h1><span>登录后管理项目导航中的内容</span></div><form method="post" autocomplete="on"><label><span>管理员账号</span><div><i>◎</i><input name="username" autocomplete="username" required autofocus placeholder="请输入账号"></div></label><label><span>登录密码</span><div><i>◇</i><input name="password" type="password" autocomplete="current-password" required placeholder="请输入密码"></div></label><?php if ($error): ?><div class="error"><?= htmlspecialchars($error, ENT_QUOTES) ?></div><?php endif; ?><button type="submit">登录管理后台 <b>→</b></button></form><footer><i></i>本地安全会话 · SQLite 数据服务</footer></section><a class="back" href="index.php">← 返回三奇.项目导航</a></main><div class="orb one"></div><div class="orb two"></div></body></html>
