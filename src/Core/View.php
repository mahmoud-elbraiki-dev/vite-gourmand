<?php
declare(strict_types=1);

namespace App\Core;

final class View
{
    /** Rend un gabarit dans le layout principal. */
    public static function render(string $template, array $data = []): void
    {
        $data['__template'] = ROOT_PATH . '/templates/' . $template . '.php';
        extract($data, EXTR_SKIP);
        require ROOT_PATH . '/templates/layout.php';
    }

    public static function partial(string $template, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        require ROOT_PATH . '/templates/' . $template . '.php';
    }

    public static function json(mixed $data, int $code = 200): never
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
