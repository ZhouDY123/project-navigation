<?php declare(strict_types=1); require_once __DIR__ . '/config.php'; ?>
<!doctype html>
<html lang="zh-CN" data-theme="light">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="description" content="集中管理并快速访问自研系统与工具。">
  <title><?= htmlspecialchars(APP_NAME, ENT_QUOTES) ?></title>
  <link rel="stylesheet" href="assets/style.css">
  <link rel="stylesheet" href="assets/style-v2.css">
  <link rel="stylesheet" href="assets/theme-glass-matrix.css">
  <link rel="stylesheet" href="assets/front-layout-fixes.css">
  <script src="assets/app.js" defer></script>
</head>
<body>
  <div class="sticky-zone">
  <header class="topbar">
    <a class="brand" href="./" aria-label="返回三奇项目导航首页"><span class="brand-mark sanqi-brand-mark"><img src="assets/sanqi-logo-cropped.png" alt="三奇 3Q"></span><span class="brand-title">三奇<span class="brand-dot" aria-hidden="true"></span>项目导航<small>SANQI PROJECT NAVIGATION</small></span></a>
    <nav id="categories" class="categories" aria-label="项目分类"></nav>
    <div class="header-actions">
      <label class="search"><span>⌕</span><input id="search" type="search" placeholder="搜索项目、描述或标签…" autocomplete="off"><kbd>/</kbd></label>
      <button class="icon-btn" id="themeBtn" title="切换主题" aria-label="切换主题">◐</button>
    </div>
  </header>
  <div class="container fixed-container">
    <section class="intro">
      <div class="intro-copy"><p class="eyebrow">YOUR DIGITAL WORKSPACE</p><h1>你的项目，<span>一触即达</span></h1><p>将每一个系统、工具与灵感，都收进这个轻盈的数字空间。</p></div>
      <div class="overview-card"><div class="overview-label"><span>工作台概览</span><i>LIVE</i></div><div class="stats"><div><strong id="projectCount">—</strong><span>项目总数</span></div><i></i><div><strong id="categoryCount">—</strong><span>分类数量</span></div></div><div class="overview-foot"><span><b></b>服务正常</span><small>点击任意项目快速访问 ↗</small></div></div>
    </section>
    <div class="section-head fixed-list-head"><div><h2 id="listTitle">常用项目</h2><span id="resultCount"></span></div><small>点击项目卡片，在新标签页中打开</small></div>
  </div>
  </div>
  <main class="container project-container">
    <section class="project-section"><div class="grid" id="projectGrid"></div><div id="empty" class="empty" hidden><div>⌕</div><b>没有找到匹配的项目</b><span>试试其他关键词或分类</span></div></section>
  </main>

  <div class="toast" id="toast" role="status"></div>
  <noscript>此页面需要启用 JavaScript。</noscript>
</body></html>
