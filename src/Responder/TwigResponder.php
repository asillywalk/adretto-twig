<?php

namespace Sillynet\Adretto\WpTwig\Responder;

use Sillynet\Adretto\Responder\Responder;
use Twig\Environment as TwigEnvironment;
use Sillynet\Adretto\WpTwig\Service\Twig;

abstract class TwigResponder implements Responder
{
    protected TwigEnvironment $twig;

    public function __construct(Twig $twig)
    {
        $this->twig = $twig->getTwigEnvironment();
    }

    /**
     * @param array<string, mixed>  $data
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    protected function render(string $templatePath, array $data = []): void
    {
        echo $this->twig->render($templatePath, $data);
    }
}
