<?php

declare(strict_types=1);

namespace App\Core;

class View
{
    public static function render(string $template, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $path = __DIR__ . '/../Views/' . $template . '.php';

        if (!file_exists($path)) {
            http_response_code(404);
            echo 'Template não encontrado.';
            return;
        }

        require __DIR__ . '/../Views/layouts/header.php';
        require $path;
        require __DIR__ . '/../Views/layouts/footer.php';
    }
}
