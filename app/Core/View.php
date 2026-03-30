<?php

declare(strict_types=1);

namespace App\Core;

class View
{
    public static function render(string $template, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $templatePath = __DIR__ . '/../Views/' . $template . '.php';

        if (!file_exists($templatePath)) {
            http_response_code(404);
            echo 'Template não encontrado.';
            return;
        }

        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/' . $template . '.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }
}
