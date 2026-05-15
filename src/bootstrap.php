<?php
declare(strict_types=1);

const FAMILYPLAN_ROOT = __DIR__ . '/..';

function app_config(): array
{
    static $config = null;
    if ($config !== null) {
        return $config;
    }

    $local = FAMILYPLAN_ROOT . '/config.local.php';
    $sample = FAMILYPLAN_ROOT . '/config.sample.php';
    $config = require (is_file($local) ? $local : $sample);
    return $config;
}

function start_secure_session(): void
{
    $config = app_config();
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_name($config['app']['session_name'] ?? 'familyplan_session');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $db = app_config()['db'];
    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $db['host'], $db['port'], $db['name'], $db['charset']);
    $pdo = new PDO($dsn, $db['user'], $db['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    return $pdo;
}

function json_response(array $payload, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function request_json(): array
{
    $raw = file_get_contents('php://input') ?: '';
    $data = json_decode($raw, true);
    if ($raw !== '' && !is_array($data)) {
        json_response(['ok' => false, 'error' => 'JSON non valido.'], 400);
    }
    return $data ?? [];
}

function clean_string(?string $value, int $max = 255): string
{
    $value = trim((string) $value);
    if (mb_strlen($value) > $max) {
        $value = mb_substr($value, 0, $max);
    }
    return $value;
}

function current_user(): ?array
{
    start_secure_session();
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    $stmt = db()->prepare('SELECT id, name, phone, email, birth_date, role, category, parent_id, second_parent_id, personal_info, theme, created_at FROM users WHERE id = ? AND active = 1');
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

function require_user(): array
{
    $user = current_user();
    if (!$user) {
        json_response(['ok' => false, 'error' => 'Accesso richiesto.'], 401);
    }
    return $user;
}

function require_admin(): array
{
    $user = require_user();
    if ($user['role'] !== 'admin') {
        json_response(['ok' => false, 'error' => 'Permesso admin richiesto.'], 403);
    }
    return $user;
}

function verify_csrf(): void
{
    start_secure_session();
    $header = app_config()['app']['csrf_header'] ?? 'HTTP_X_CSRF_TOKEN';
    $token = $_SERVER[$header] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        json_response(['ok' => false, 'error' => 'Token di sicurezza non valido.'], 419);
    }
}

function is_parent_category(?string $category): bool
{
    return in_array($category, ['mamma', 'papà', 'papa'], true);
}

function notify_users(array $userIds, string $type, string $title, string $body, ?int $actorId = null): void
{
    $pdo = db();
    $stmt = $pdo->prepare('INSERT INTO notifications (user_id, actor_id, type, title, body) VALUES (?, ?, ?, ?, ?)');
    foreach (array_unique(array_filter($userIds)) as $userId) {
        $stmt->execute([(int) $userId, $actorId, $type, $title, $body]);
    }
}

function shared_user_ids(?array $ids, int $ownerId): array
{
    $ids = array_map('intval', $ids ?? []);
    $ids[] = $ownerId;
    return array_values(array_unique(array_filter($ids)));
}
