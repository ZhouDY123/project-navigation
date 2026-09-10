<?php
declare(strict_types=1);

const APP_NAME = '三奇.项目导航';
define('DB_PATH', PHP_SAPI === 'cli-server'
    ? __DIR__ . '/.data/devhub.sqlite'
    : '/tmp/apc_project_navigation/devhub.sqlite');
const ADMIN_USERNAME = 'admin';
const ADMIN_PASSWORD_HASH = '$2y$12$elE3PieGpcMn5g9pX0VHbuP.On3Xo0RiiReZ.c/0af.EGUxyY26su';
