<?php

namespace App\Core;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'constants.php';

/**
 * Renders view templates inside application layouts.
 */
class View
{
    /**
     * Render a view file and return the completed HTML.
     */
    public static function render(string $view, array $data = [], string $layout = 'layouts/main'): string
    {
        $viewPath = APP_PATH . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR . $view . '.php';
        $layoutPath = APP_PATH . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR . $layout . '.php';

        if (!is_file($viewPath)) {
            http_response_code(500);
            return 'View not found.';
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        if (!is_file($layoutPath)) {
            return $content;
        }

        ob_start();
        require $layoutPath;

        return ob_get_clean();
    }
}
