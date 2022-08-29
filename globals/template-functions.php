<?php

use Sillynet\Adretto\WpTwig\Service\Twig;

if (!function_exists("i")) {
    function i($template, $data = [])
    {
        $theme = \Sillynet\Adretto\Theme::getInstance();
        $container = $theme->getContainer();
        $renderer = $container->get(Twig::class);
        try {
            echo $renderer->render($template, $data);
        } catch (\Exception $e) {
            error_log(
                'Could not load template "' .
                    $template .
                    '". Exception: ' .
                    json_encode([
                        $e->getMessage(),
                        $e->getFile(),
                        $e->getLine(),
                    ])
            );
        }
    }
}
