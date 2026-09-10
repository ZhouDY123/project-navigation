<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
require_admin_page();

$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = (string)($_POST['current_password'] ?? '');
    $new = (string)($_POST['new_password'] ?? '');
    $confirm = (string)($_POST['confirm_password'] ?? '');
    if (!valid_csrf((string)($_POST['csrf_token'] ?? ''))) $error = '页面已过期，请刷新后重试';
    elseif (strlen($new) < 8) $error = '新密码至少需要 8 个字符';
    elseif ($new !== $confirm) $error = '两次输入的新密码不一致';
    elseif (hash_equals($current, $new)) $error = '新密码不能与当前密码相同';
    elseif (!change_admin_password($current, $new)) $error = '当前密码不正确';
    else $success = '密码修改成功，下次登录请使用新密码';
}
?>
<!doctype html><html lang="zh-CN"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>三奇.项目导航</title><link rel="stylesheet" href="assets/admin.css"><link rel="stylesheet" href="assets/admin-auth.css"><link rel="stylesheet" href="assets/password.css"><link rel="stylesheet" href="assets/brand-logo.css"></head><body>
<aside class="sidebar"><a class="brand" href="admin.php"><span class="brand-logo"><img src="assets/sanqi-logo-cropped.png" alt="三奇 3Q"></span><div class="brand-title">三奇<span class="brand-dot">.</span>项目导航<small>SANQI PROJECT NAVIGATION</small></div></a><nav><a href="categories.php">▤ 分类管理</a><a href="admin.php">▦ 项目管理</a><a class="active" href="password.php">◇ 修改密码</a><a href="index.php">↗ 浏览前台</a><form action="logout.php" method="post"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES) ?>"><button type="submit">⇥ 退出登录</button></form></nav><div class="db-state"><i></i><div>安全会话已启用<small>管理员身份已验证</small></div></div></aside>
<main><header><div><p>WORKSPACE / SECURITY</p><h1>修改登录密码</h1><span>定期更新密码可以更好地保护管理后台</span></div></header><section class="password-layout"><div class="password-card"><div class="card-head"><span>◇</span><div><h2>更新管理员密码</h2><p>请输入当前密码，并设置一个新的安全密码。</p></div></div><form method="post" autocomplete="off"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES) ?>"><label><span>当前密码</span><input name="current_password" type="password" autocomplete="current-password" required placeholder="输入当前登录密码"></label><label><span>新密码</span><input name="new_password" type="password" autocomplete="new-password" required minlength="8" placeholder="至少 8 个字符"></label><label><span>确认新密码</span><input name="confirm_password" type="password" autocomplete="new-password" required minlength="8" placeholder="再次输入新密码"></label><?php if ($error): ?><div class="message error-message"><?= htmlspecialchars($error, ENT_QUOTES) ?></div><?php endif; ?><?php if ($success): ?><div class="message success-message"><?= htmlspecialchars($success, ENT_QUOTES) ?></div><?php endif; ?><footer><a href="admin.php">取消</a><button class="primary" type="submit">确认修改</button></footer></form></div><aside class="tips"><p>SECURITY TIPS</p><h3>安全密码建议</h3><ul><li>使用至少 8 个字符</li><li>组合字母、数字和特殊符号</li><li>避免使用姓名或简单连续数字</li><li>不要与其他系统共用密码</li></ul><div><i></i>密码仅以安全哈希形式保存</div></aside></section></main></body></html>
