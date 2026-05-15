<?php
declare(strict_types=1);

require __DIR__ . '/src/bootstrap.php';

start_secure_session();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($method !== 'GET') {
    verify_csrf();
}

try {
    route($action, $method);
} catch (Throwable $e) {
    $debug = app_config()['app']['debug'] ?? false;
    json_response(['ok' => false, 'error' => $debug ? $e->getMessage() : 'Errore interno.'], 500);
}

function route(string $action, string $method): void
{
    match ($action) {
        'session' => session_info(),
        'login' => login($method),
        'logout' => logout($method),
        'request-reset' => request_reset($method),
        'reset-password' => reset_password($method),
        'dashboard' => dashboard($method),
        'users' => users($method),
        'user-save' => user_save($method),
        'profile-save' => profile_save($method),
        'calendar' => calendar($method),
        'shopping' => shopping($method),
        'family' => family_day($method),
        'reminders' => reminders($method),
        'notes' => notes($method),
        'notifications' => notifications($method),
        'settings' => settings($method),
        default => json_response(['ok' => false, 'error' => 'Endpoint non trovato.'], 404),
    };
}

function session_info(): void
{
    json_response(['ok' => true, 'user' => current_user(), 'csrf' => $_SESSION['csrf_token']]);
}

function login(string $method): void
{
    if ($method !== 'POST') json_response(['ok' => false], 405);
    $data = request_json();
    $name = clean_string($data['name'] ?? '', 120);
    $password = (string) ($data['password'] ?? '');
    $stmt = db()->prepare('SELECT * FROM users WHERE name = ? AND active = 1 LIMIT 1');
    $stmt->execute([$name]);
    $user = $stmt->fetch();
    if (!$user || !password_verify($password, $user['password_hash'])) {
        usleep(250000);
        json_response(['ok' => false, 'error' => 'Credenziali non valide.'], 401);
    }
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    json_response(['ok' => true, 'user' => current_user(), 'csrf' => $_SESSION['csrf_token']]);
}

function logout(string $method): void
{
    if ($method !== 'POST') json_response(['ok' => false], 405);
    $_SESSION = [];
    session_destroy();
    json_response(['ok' => true]);
}

function request_reset(string $method): void
{
    if ($method !== 'POST') json_response(['ok' => false], 405);
    $email = clean_string(request_json()['email'] ?? '', 190);
    $stmt = db()->prepare('SELECT id, email FROM users WHERE email = ? AND active = 1 LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user) {
        $token = bin2hex(random_bytes(32));
        $hash = hash('sha256', $token);
        $minutes = (int) (app_config()['app']['password_reset_token_minutes'] ?? 30);
        db()->prepare('INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL ? MINUTE))')->execute([$user['id'], $hash, $minutes]);
        $url = rtrim(app_config()['app']['base_url'], '/') . '/?reset=' . $token;
        $message = sprintf("Reset password FamilyPlan: %s\n", $url);
        @file_put_contents(app_config()['mail']['log_file'], $message, FILE_APPEND | LOCK_EX);
        @mail($email, 'Recupero password FamilyPlan', $message, 'From: ' . app_config()['mail']['from']);
    }
    json_response(['ok' => true, 'message' => 'Se l’email è registrata riceverai un link di recupero.']);
}

function reset_password(string $method): void
{
    if ($method !== 'POST') json_response(['ok' => false], 405);
    $data = request_json();
    $tokenHash = hash('sha256', (string) ($data['token'] ?? ''));
    $password = (string) ($data['password'] ?? '');
    if (strlen($password) < 10) json_response(['ok' => false, 'error' => 'Password troppo corta.'], 422);
    $stmt = db()->prepare('SELECT * FROM password_resets WHERE token_hash = ? AND used_at IS NULL AND expires_at > NOW() ORDER BY id DESC LIMIT 1');
    $stmt->execute([$tokenHash]);
    $reset = $stmt->fetch();
    if (!$reset) json_response(['ok' => false, 'error' => 'Token non valido o scaduto.'], 422);
    db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([password_hash($password, PASSWORD_DEFAULT), $reset['user_id']]);
    db()->prepare('UPDATE password_resets SET used_at = NOW() WHERE id = ?')->execute([$reset['id']]);
    json_response(['ok' => true]);
}

function dashboard(string $method): void
{
    $user = require_user();
    if ($method === 'GET') {
        $stmt = db()->prepare('SELECT widget_key, enabled, sort_order FROM dashboard_widgets WHERE user_id = ? ORDER BY sort_order');
        $stmt->execute([$user['id']]);
        json_response(['ok' => true, 'widgets' => $stmt->fetchAll()]);
    }
    if ($method === 'POST') {
        $data = request_json();
        $widgets = $data['widgets'] ?? [];
        db()->prepare('DELETE FROM dashboard_widgets WHERE user_id = ?')->execute([$user['id']]);
        $stmt = db()->prepare('INSERT INTO dashboard_widgets (user_id, widget_key, enabled, sort_order) VALUES (?, ?, ?, ?)');
        foreach ($widgets as $i => $widget) {
            $stmt->execute([$user['id'], clean_string($widget['key'] ?? '', 40), !empty($widget['enabled']) ? 1 : 0, $i]);
        }
        json_response(['ok' => true]);
    }
    json_response(['ok' => false], 405);
}

function users(string $method): void
{
    $user = require_user();
    if ($method !== 'GET') json_response(['ok' => false], 405);
    $sql = 'SELECT id, name, phone, email, birth_date, role, category, parent_id, personal_info, active FROM users ORDER BY category, name';
    json_response(['ok' => true, 'users' => db()->query($sql)->fetchAll(), 'canAdmin' => $user['role'] === 'admin']);
}

function user_save(string $method): void
{
    $actor = require_user();
    if ($method !== 'POST') json_response(['ok' => false], 405);
    $d = request_json();
    $id = (int) ($d['id'] ?? 0);
    $isAdmin = $actor['role'] === 'admin';
    $isParent = is_parent_category($actor['category']);
    if (!$isAdmin && !$isParent) json_response(['ok' => false, 'error' => 'Permesso genitore o admin richiesto.'], 403);

    $name = clean_string($d['name'] ?? '', 120);
    $phone = clean_string($d['phone'] ?? '', 30);
    $email = clean_string($d['email'] ?? '', 190);
    $category = clean_string($d['category'] ?? 'familiare', 30);
    $role = in_array(($d['role'] ?? 'familiare'), ['admin', 'familiare'], true) ? $d['role'] : 'familiare';
    $parentId = !empty($d['parent_id']) ? (int) $d['parent_id'] : null;

    if (!$isAdmin) {
        $category = 'figlio';
        $role = 'familiare';
        $phone = '';
        $parentId = (int) $actor['id'];
        if ($id > 0) {
            $stmt = db()->prepare("SELECT id, birth_date FROM users WHERE id=? AND category='figlio' AND parent_id=?");
            $stmt->execute([$id, $actor['id']]);
            $child = $stmt->fetch();
            if (!$child) json_response(['ok' => false, 'error' => 'Figlio non associato a questo genitore.'], 403);
            $age = $child['birth_date'] ? (int) (new DateTime($child['birth_date']))->diff(new DateTime('today'))->y : 0;
            if ($age >= 14) {
                db()->prepare('UPDATE users SET email=?, personal_info=? WHERE id=?')
                    ->execute([$email ?: null, clean_string($d['personal_info'] ?? '', 1000), $id]);
                notify_users([$id], 'user', 'Profilo figlio aggiornato', $actor['name'] . ' ha aggiornato informazioni limitate.', (int) $actor['id']);
                json_response(['ok' => true, 'id' => $id, 'limited' => true]);
            }
        }
    }

    if ($name === '') json_response(['ok' => false, 'error' => 'Nome richiesto.'], 422);
    if ($category !== 'figlio' && $phone === '') json_response(['ok' => false, 'error' => 'Telefono richiesto per gli adulti.'], 422);
    if ($id > 0) {
        db()->prepare('UPDATE users SET name=?, phone=?, email=?, birth_date=?, role=?, category=?, parent_id=?, personal_info=?, active=? WHERE id=?')
            ->execute([$name, $phone ?: null, $email ?: null, ($d['birth_date'] ?? null) ?: null, $role, $category, $parentId, clean_string($d['personal_info'] ?? '', 1000), !empty($d['active']) || !$isAdmin ? 1 : 0, $id]);
    } else {
        $password = (string) ($d['password'] ?? bin2hex(random_bytes(6)));
        if ($isAdmin && strlen($password) < 10) json_response(['ok' => false, 'error' => 'Password iniziale di almeno 10 caratteri.'], 422);
        db()->prepare('INSERT INTO users (name, phone, email, birth_date, password_hash, role, category, parent_id, personal_info, active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)')
            ->execute([$name, $phone ?: null, $email ?: null, ($d['birth_date'] ?? null) ?: null, password_hash($password, PASSWORD_DEFAULT), $role, $category, $parentId, clean_string($d['personal_info'] ?? '', 1000)]);
        $id = (int) db()->lastInsertId();
        seed_widgets($id, $role);
    }
    notify_users([$id], 'user', 'Profilo aggiornato', $actor['name'] . ' ha aggiornato il profilo.', (int) $actor['id']);
    json_response(['ok' => true, 'id' => $id]);
}

function profile_save(string $method): void
{
    $user = require_user();
    if ($method !== 'POST') json_response(['ok' => false], 405);
    $d = request_json();
    db()->prepare('UPDATE users SET email=?, birth_date=?, personal_info=?, theme=? WHERE id=?')
        ->execute([clean_string($d['email'] ?? '', 190), ($d['birth_date'] ?? null) ?: null, clean_string($d['personal_info'] ?? '', 1000), in_array(($d['theme'] ?? 'system'), ['light', 'dark', 'system'], true) ? $d['theme'] : 'system', $user['id']]);
    json_response(['ok' => true, 'user' => current_user()]);
}

function calendar(string $method): void
{
    $user = require_user();
    if ($method === 'GET') {
        $start = $_GET['start'] ?? date('Y-m-01');
        $end = $_GET['end'] ?? date('Y-m-t');
        $stmt = db()->prepare('SELECT e.*, u.name AS created_by_name FROM events e JOIN users u ON u.id=e.created_by WHERE e.starts_at BETWEEN ? AND ? ORDER BY e.starts_at');
        $stmt->execute([$start . ' 00:00:00', $end . ' 23:59:59']);
        json_response(['ok' => true, 'events' => $stmt->fetchAll()]);
    }
    if ($method === 'POST') {
        $d = request_json();
        db()->prepare('INSERT INTO events (title, description, starts_at, ends_at, child_id, created_by) VALUES (?, ?, ?, ?, ?, ?)')
            ->execute([clean_string($d['title'] ?? '', 160), clean_string($d['description'] ?? '', 1000), ($d['starts_at'] ?? ''), ($d['ends_at'] ?? null) ?: null, !empty($d['child_id']) ? (int) $d['child_id'] : null, $user['id']]);
        notify_users(all_user_ids(), 'calendar', 'Nuovo evento', $user['name'] . ' ha creato un evento.', (int) $user['id']);
        json_response(['ok' => true]);
    }
    json_response(['ok' => false], 405);
}

function shopping(string $method): void
{
    $user = require_user();
    if ($method === 'GET') {
        db()->exec("UPDATE shopping_lists SET archived_at = NOW() WHERE archived_at IS NULL AND completed_at IS NOT NULL AND completed_at < DATE_SUB(NOW(), INTERVAL 7 DAY)");
        $stmt = db()->prepare('SELECT sl.*, u.name owner_name FROM shopping_lists sl JOIN users u ON u.id=sl.owner_id WHERE sl.owner_id=? OR sl.shared=1 ORDER BY COALESCE(sl.archived_at, sl.list_date) DESC');
        $stmt->execute([$user['id']]);
        $lists = $stmt->fetchAll();
        foreach ($lists as &$list) {
            $it = db()->prepare('SELECT * FROM shopping_items WHERE list_id=? ORDER BY checked, id');
            $it->execute([$list['id']]);
            $list['items'] = $it->fetchAll();
        }
        json_response(['ok' => true, 'lists' => $lists]);
    }
    if ($method === 'POST') {
        $d = request_json();
        $id = (int) ($d['id'] ?? 0);
        if (!empty($d['archive'])) {
            db()->prepare("UPDATE shopping_lists SET archived_at=NOW() WHERE id=? AND (owner_id=? OR ? IN (SELECT id FROM users WHERE role='admin'))")->execute([$id, $user['id'], $user['id']]);
            notify_users(all_user_ids(), 'shopping', 'Lista archiviata', $user['name'] . ' ha archiviato una lista.', (int) $user['id']);
            json_response(['ok' => true]);
        }
        if ($id === 0) {
            db()->prepare('INSERT INTO shopping_lists (title, list_date, owner_id, shared) VALUES (?, ?, ?, ?)')->execute([clean_string($d['title'] ?? 'Spesa', 160), ($d['list_date'] ?? null) ?: date('Y-m-d'), $user['id'], !empty($d['shared']) ? 1 : 0]);
            $id = (int) db()->lastInsertId();
        } else {
            db()->prepare('UPDATE shopping_lists SET title=?, list_date=?, shared=? WHERE id=? AND owner_id=?')->execute([clean_string($d['title'] ?? 'Spesa', 160), ($d['list_date'] ?? null) ?: date('Y-m-d'), !empty($d['shared']) ? 1 : 0, $id, $user['id']]);
            db()->prepare('DELETE FROM shopping_items WHERE list_id=?')->execute([$id]);
        }
        $stmt = db()->prepare('INSERT INTO shopping_items (list_id, label, checked) VALUES (?, ?, ?)');
        foreach (($d['items'] ?? []) as $item) {
            $stmt->execute([$id, clean_string($item['label'] ?? '', 180), !empty($item['checked']) ? 1 : 0]);
        }
        db()->prepare('UPDATE shopping_lists SET completed_at = (SELECT IF(COUNT(*) > 0 AND SUM(checked)=COUNT(*), NOW(), NULL) FROM shopping_items WHERE list_id=?) WHERE id=?')->execute([$id, $id]);
        notify_users(!empty($d['shared']) ? all_user_ids() : [$user['id']], 'shopping', 'Lista spesa aggiornata', $user['name'] . ' ha aggiornato una lista.', (int) $user['id']);
        json_response(['ok' => true, 'id' => $id]);
    }
    json_response(['ok' => false], 405);
}

function family_day(string $method): void
{
    $user = require_user();
    if ($method === 'GET') {
        $date = $_GET['date'] ?? date('Y-m-d');
        $stmt = db()->prepare('SELECT ft.*, c.name child_name, a.name assignee_name FROM family_tasks ft JOIN users c ON c.id=ft.child_id LEFT JOIN users a ON a.id=ft.assignee_id WHERE task_date=? ORDER BY task_time');
        $stmt->execute([$date]);
        json_response(['ok' => true, 'tasks' => $stmt->fetchAll(), 'canManageChildren' => is_parent_category($user['category']) || $user['role'] === 'admin']);
    }
    if ($method === 'POST') {
        $d = request_json();
        db()->prepare('INSERT INTO family_tasks (child_id, assignee_id, task_date, task_time, type, notes, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)')
            ->execute([(int) $d['child_id'], !empty($d['assignee_id']) ? (int) $d['assignee_id'] : null, ($d['task_date'] ?? date('Y-m-d')), ($d['task_time'] ?? null) ?: null, clean_string($d['type'] ?? 'impegno', 80), clean_string($d['notes'] ?? '', 1000), $user['id']]);
        notify_users(all_user_ids(), 'family', 'Impegno figli', $user['name'] . ' ha aggiunto un impegno figli.', (int) $user['id']);
        json_response(['ok' => true]);
    }
    json_response(['ok' => false], 405);
}

function reminders(string $method): void
{
    $user = require_user();
    if ($method === 'GET') {
        $stmt = db()->prepare('SELECT * FROM reminders WHERE owner_id=? OR shared=1 ORDER BY due_at IS NULL, due_at');
        $stmt->execute([$user['id']]);
        json_response(['ok' => true, 'reminders' => $stmt->fetchAll()]);
    }
    if ($method === 'POST') {
        $d = request_json();
        db()->prepare('INSERT INTO reminders (title, due_at, recurrence, owner_id, shared, completed_at) VALUES (?, ?, ?, ?, ?, ?)')
            ->execute([clean_string($d['title'] ?? '', 180), ($d['due_at'] ?? null) ?: null, clean_string($d['recurrence'] ?? 'none', 30), $user['id'], !empty($d['shared']) ? 1 : 0, !empty($d['completed']) ? date('Y-m-d H:i:s') : null]);
        notify_users(!empty($d['shared']) ? all_user_ids() : [$user['id']], 'reminder', 'Promemoria', $user['name'] . ' ha creato un promemoria.', (int) $user['id']);
        json_response(['ok' => true]);
    }
    json_response(['ok' => false], 405);
}

function notes(string $method): void
{
    $user = require_user();
    if ($method === 'GET') {
        $stmt = db()->prepare('SELECT * FROM notes WHERE owner_id=? ORDER BY archived_at IS NULL DESC, updated_at DESC');
        $stmt->execute([$user['id']]);
        json_response(['ok' => true, 'notes' => $stmt->fetchAll()]);
    }
    if ($method === 'POST') {
        $d = request_json();
        db()->prepare('INSERT INTO notes (title, body, owner_id, archived_at) VALUES (?, ?, ?, ?)')->execute([clean_string($d['title'] ?? '', 160), clean_string($d['body'] ?? '', 5000), $user['id'], !empty($d['archived']) ? date('Y-m-d H:i:s') : null]);
        json_response(['ok' => true]);
    }
    json_response(['ok' => false], 405);
}

function notifications(string $method): void
{
    $user = require_user();
    if ($method === 'GET') {
        $stmt = db()->prepare('SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 50');
        $stmt->execute([$user['id']]);
        json_response(['ok' => true, 'notifications' => $stmt->fetchAll()]);
    }
    if ($method === 'POST') {
        db()->prepare('UPDATE notifications SET read_at=NOW() WHERE user_id=?')->execute([$user['id']]);
        json_response(['ok' => true]);
    }
    json_response(['ok' => false], 405);
}

function settings(string $method): void
{
    require_admin();
    if ($method === 'GET') {
        json_response(['ok' => true, 'settings' => db()->query('SELECT setting_key, setting_value FROM app_settings ORDER BY setting_key')->fetchAll()]);
    }
    if ($method === 'POST') {
        $stmt = db()->prepare('INSERT INTO app_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)');
        foreach ((request_json()['settings'] ?? []) as $key => $value) {
            $stmt->execute([clean_string((string) $key, 80), clean_string((string) $value, 1000)]);
        }
        json_response(['ok' => true]);
    }
    json_response(['ok' => false], 405);
}

function all_user_ids(): array
{
    return array_map('intval', db()->query('SELECT id FROM users WHERE active=1')->fetchAll(PDO::FETCH_COLUMN));
}

function seed_widgets(int $userId, string $role): void
{
    $widgets = ['calendar', 'shopping', 'family', 'reminders', 'notes', 'profile'];
    if ($role === 'admin') array_push($widgets, 'settings', 'users');
    $stmt = db()->prepare('INSERT INTO dashboard_widgets (user_id, widget_key, enabled, sort_order) VALUES (?, ?, 1, ?)');
    foreach ($widgets as $i => $widget) $stmt->execute([$userId, $widget, $i]);
}
