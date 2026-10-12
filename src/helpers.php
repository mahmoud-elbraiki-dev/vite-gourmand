<?php
declare(strict_types=1);

/** Lecture d'une variable d'environnement avec valeur par défaut. */
function env(string $key, ?string $default = null): ?string
{
    $v = $_ENV[$key] ?? getenv($key);
    return ($v === false || $v === null || $v === '') ? $default : (string) $v;
}

/** Échappement HTML systématique des sorties (protection XSS). */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Construit une URL interne. */
function url(string $path = '/', array $query = []): string
{
    $path = '/' . ltrim($path, '/');
    return $query ? $path . '?' . http_build_query($query) : $path;
}

function asset(string $path): string
{
    return '/' . ltrim($path, '/');
}

function redirect(string $path, array $query = []): never
{
    header('Location: ' . url($path, $query));
    exit;
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function take_flashes(): array
{
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

/** Conserve les anciennes saisies pour réafficher un formulaire en erreur. */
function old(string $key, string $default = ''): string
{
    return (string) ($_SESSION['old'][$key] ?? $default);
}

function keep_old(array $data): void
{
    unset($data['password'], $data['password_confirm'], $data['_csrf']);
    $_SESSION['old'] = $data;
}

function clear_old(): void
{
    unset($_SESSION['old']);
}

function euros(float|string $amount): string
{
    return number_format((float) $amount, 2, ',', ' ') . ' €';
}

function date_fr(?string $date, bool $withTime = false): string
{
    if (!$date) {
        return '';
    }
    $ts = strtotime($date);
    return $withTime ? date('d/m/Y à H:i', $ts) : date('d/m/Y', $ts);
}

function post(string $key, string $default = ''): string
{
    return trim((string) ($_POST[$key] ?? $default));
}

function query(string $key, string $default = ''): string
{
    return trim((string) ($_GET[$key] ?? $default));
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function abort(int $code, string $message = ''): never
{
    http_response_code($code);
    App\Core\View::render('pages/error', [
        'title' => 'Erreur ' . $code,
        'code' => $code,
        'message' => $message ?: ($code === 403 ? 'Accès refusé.' : 'Page introuvable.'),
    ]);
    exit;
}
