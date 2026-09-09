<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function respond(array $data, int $status = 200): never { http_response_code($status); echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); exit; }
function input(): array { $data = json_decode(file_get_contents('php://input'), true); return is_array($data) ? $data : $_POST; }
function clip(string $value, int $length): string { return function_exists('mb_substr') ? mb_substr($value, 0, $length) : substr($value, 0, $length * 4); }
function clean_project(array $data): array {
    $name = trim((string)($data['name'] ?? ''));
    $url = trim((string)($data['url'] ?? ''));
    if ($name === '' || $url === '') throw new InvalidArgumentException('项目名称和地址不能为空');
    if (!filter_var($url, FILTER_VALIDATE_URL) || !in_array(parse_url($url, PHP_URL_SCHEME), ['http','https'], true)) throw new InvalidArgumentException('请输入有效的 http/https 地址');
    $tags = $data['tags'] ?? [];
    if (is_string($tags)) $tags = preg_split('/[,，]/u', $tags, -1, PREG_SPLIT_NO_EMPTY);
    $tags = array_values(array_unique(array_filter(array_map(fn($v) => clip(trim((string)$v), 24), (array)$tags))));
    return [clip($name,80), $url, clip(trim((string)($data['icon'] ?? '🚀')),12) ?: '🚀', clip(trim((string)($data['description'] ?? '')),240), (string)($data['category_id'] ?? ''), json_encode($tags, JSON_UNESCAPED_UNICODE), ($data['environment'] ?? 'local') === 'online' ? 'online' : 'local'];
}

try {
    $pdo = db(); $method = $_SERVER['REQUEST_METHOD']; $action = $_GET['action'] ?? 'list';
    if ($method === 'GET' && $action === 'list') {
        $categories = $pdo->query('SELECT id,name,icon FROM categories ORDER BY sort_order,name')->fetchAll();
        $projects = $pdo->query('SELECT id,name,url,icon,description,category_id,tags,environment,click_count,last_opened_at FROM projects ORDER BY sort_order,id')->fetchAll();
        foreach ($projects as &$p) $p['tags'] = json_decode($p['tags'], true) ?: [];
        respond(['ok'=>true,'categories'=>$categories,'projects'=>$projects]);
    }
    if ($method === 'POST' && $action === 'track') {
        $id = (int)(input()['id'] ?? 0);
        if ($id < 1) throw new InvalidArgumentException('项目编号无效');
        $stmt = $pdo->prepare('UPDATE projects SET click_count=click_count+1,last_opened_at=CURRENT_TIMESTAMP WHERE id=?');
        $stmt->execute([$id]);
        if ($stmt->rowCount() !== 1) throw new InvalidArgumentException('项目不存在');
        respond(['ok'=>true]);
    }
    if (in_array($method, ['POST','PUT','PATCH','DELETE'], true)) {
        if (!is_admin()) respond(['ok'=>false,'message'=>'登录已失效，请重新登录'],401);
        if (!valid_csrf((string)($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''))) respond(['ok'=>false,'message'=>'安全校验失败，请刷新后台页面'],403);
    }
    if ($method === 'POST' && $action === 'save') {
        $data=input(); $values=clean_project($data);
        $exists=$pdo->prepare('SELECT 1 FROM categories WHERE id=?'); $exists->execute([$values[4]]);
        if (!$exists->fetchColumn()) throw new InvalidArgumentException('请选择有效分类');
        if (!empty($data['id'])) {
            $stmt=$pdo->prepare('UPDATE projects SET name=?,url=?,icon=?,description=?,category_id=?,tags=?,environment=?,updated_at=CURRENT_TIMESTAMP WHERE id=?');
            $stmt->execute([...$values,(int)$data['id']]); $id=(int)$data['id'];
        } else {
            $stmt=$pdo->prepare('INSERT INTO projects(name,url,icon,description,category_id,tags,environment,sort_order) VALUES(?,?,?,?,?,?,?,(SELECT COALESCE(MAX(sort_order),0)+10 FROM projects))');
            $stmt->execute($values); $id=(int)$pdo->lastInsertId();
        }
        respond(['ok'=>true,'id'=>$id]);
    }
    if ($method === 'POST' && $action === 'reorder') {
        $ids = array_values(array_unique(array_map('intval', (array)(input()['ids'] ?? []))));
        $total = (int)$pdo->query('SELECT COUNT(*) FROM projects')->fetchColumn();
        if (count($ids) !== $total || in_array(0, $ids, true)) throw new InvalidArgumentException('排序数据不完整，请刷新页面后重试');
        $known = array_map('intval', $pdo->query('SELECT id FROM projects')->fetchAll(PDO::FETCH_COLUMN));
        sort($known); $check = $ids; sort($check);
        if ($known !== $check) throw new InvalidArgumentException('排序数据无效，请刷新页面后重试');
        $pdo->beginTransaction();
        $stmt = $pdo->prepare('UPDATE projects SET sort_order=?,updated_at=CURRENT_TIMESTAMP WHERE id=?');
        foreach ($ids as $index => $id) $stmt->execute([($index + 1) * 10, $id]);
        $pdo->commit();
        respond(['ok'=>true]);
    }
    if ($method === 'DELETE' && $action === 'delete') {
        $id=(int)($_GET['id']??0); $stmt=$pdo->prepare('DELETE FROM projects WHERE id=?'); $stmt->execute([$id]);
        respond(['ok'=>true,'deleted'=>$stmt->rowCount()]);
    }
    respond(['ok'=>false,'message'=>'接口不存在'],404);
} catch (InvalidArgumentException $e) { respond(['ok'=>false,'message'=>$e->getMessage()],422); }
  catch (Throwable $e) { error_log((string)$e); respond(['ok'=>false,'message'=>'服务暂时不可用'],500); }
