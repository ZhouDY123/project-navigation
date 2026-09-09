<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;

    if (!extension_loaded('pdo_sqlite')) {
        throw new RuntimeException('PDO SQLite 扩展未启用，请查看 README.md 中的启动说明。');
    }
    $dir = dirname(DB_PATH);
    if (!is_dir($dir)) mkdir($dir, 0775, true);

    $pdo = new PDO('sqlite:' . DB_PATH, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec('PRAGMA foreign_keys = ON; PRAGMA journal_mode = WAL; PRAGMA busy_timeout = 5000;');
    migrate($pdo);
    return $pdo;
}

function migrate(PDO $pdo): void
{
    $pdo->exec(<<<'SQL'
        CREATE TABLE IF NOT EXISTS categories (
            id TEXT PRIMARY KEY,
            name TEXT NOT NULL,
            icon TEXT NOT NULL DEFAULT '◻',
            sort_order INTEGER NOT NULL DEFAULT 0
        );
        CREATE TABLE IF NOT EXISTS projects (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            url TEXT NOT NULL,
            icon TEXT NOT NULL DEFAULT '🚀',
            description TEXT NOT NULL DEFAULT '',
            category_id TEXT NOT NULL,
            tags TEXT NOT NULL DEFAULT '[]',
            environment TEXT NOT NULL DEFAULT 'local' CHECK(environment IN ('local','online')),
            sort_order INTEGER NOT NULL DEFAULT 0,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY(category_id) REFERENCES categories(id)
        );
        CREATE INDEX IF NOT EXISTS idx_projects_category_sort ON projects(category_id, sort_order);
        CREATE TABLE IF NOT EXISTS settings (
            key TEXT PRIMARY KEY,
            value TEXT NOT NULL,
            updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        );
        SQL);

    $columns = $pdo->query('PRAGMA table_info(projects)')->fetchAll(PDO::FETCH_COLUMN, 1);
    if (!in_array('is_featured', $columns, true)) $pdo->exec('ALTER TABLE projects ADD COLUMN is_featured INTEGER NOT NULL DEFAULT 1');
    if (!in_array('click_count', $columns, true)) $pdo->exec('ALTER TABLE projects ADD COLUMN click_count INTEGER NOT NULL DEFAULT 0');
    if (!in_array('last_opened_at', $columns, true)) $pdo->exec('ALTER TABLE projects ADD COLUMN last_opened_at TEXT');

    $setting = $pdo->prepare('INSERT OR IGNORE INTO settings(key, value) VALUES(?, ?)');
    $setting->execute(['admin_password_hash', ADMIN_PASSWORD_HASH]);

    if ((int)$pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn() === 0) {
        $pdo->beginTransaction();
        $category = $pdo->prepare('INSERT INTO categories(id,name,icon,sort_order) VALUES(?,?,?,?)');
        foreach ([['web','Web 应用','◫',10],['tools','效率工具','◇',20],['admin','后台管理','▦',30],['docs','文档知识','◰',40]] as $row) $category->execute($row);
        $project = $pdo->prepare('INSERT INTO projects(name,url,icon,description,category_id,tags,environment,sort_order) VALUES(?,?,?,?,?,?,?,?)');
        foreach (seed_projects() as $row) $project->execute($row);
        $pdo->commit();
    }
    $pdo->exec('PRAGMA optimize;');
}

function seed_projects(): array
{
    return [
        ['任务看板系统','http://localhost:8081','📊','营销中心任务派发、进度跟踪与到期提醒。','web','["PHP","大屏"]','local',10],
        ['供应商评审系统','https://example.com/supplier','🏭','供应商档案、合格供方清单与评审流程管理。','admin','["PHP","评审"]','online',20],
        ['讲师培训扫码评分','https://example.com/training','🎓','培训报名、签到、扫码评分与现场数据展示。','web','["培训","扫码"]','online',30],
        ['电商店铺展板','http://localhost:8082','🛒','多平台店铺经营数据汇总与实时看板。','web','["PHP","数据大屏"]','local',40],
        ['任职管理系统','http://localhost:8083','👥','岗位标准、人才评估与任职资格决策留痕。','admin','["HR","管理"]','local',50],
        ['JSON 格式化工具','https://jsonformatter.org','{ }','在线格式化、压缩与校验 JSON 数据。','tools','["JSON","开发工具"]','online',60],
        ['接口调试台','http://localhost:8084','⌁','HTTP 接口测试，支持历史记录与环境变量。','tools','["API","调试"]','local',70],
        ['项目开发文档','http://localhost:8080/docs','📝','集中查阅项目规范、接口说明与开发记录。','docs','["文档","规范"]','local',80],
    ];
}
