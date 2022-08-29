<?php

namespace Sillynet\Adretto\WpTwig\Service;

use Sillynet\Adretto\Configuration\ThemeConfiguration;
use Twig\Environment;
use Twig\Extension\OptimizerExtension;
use Twig\Loader\FilesystemLoader;
use Twig\Loader\LoaderInterface;

use function get_template_directory;

class Twig
{
    protected Environment $twigEnvironment;

    /**
     * @var array<string, mixed>
     */
    protected array $config;

    public function __construct(
        TwigWordpressBridge $wpBridge,
        ThemeConfiguration $configuration
    ) {
        $isProduction = getenv('WORDPRESS_ENV') === 'production';
        $rawConfig = $configuration->get('twig');
        $this->config = is_array($rawConfig) ? $rawConfig : [];
        $loader = $this->getLoader();

        $options = [];

        if ($isProduction) {
            $options['cache'] = get_template_directory() . '/var/twig/';
        } else {
            $options['auto_reload'] = true;
        }
        $this->twigEnvironment = new Environment($loader, $options);

        if ($isProduction) {
            $this->twigEnvironment->addExtension(new OptimizerExtension());
        }

        $this->twigEnvironment->addExtension($wpBridge);
    }

    public function getTwigEnvironment(): Environment
    {
        return $this->twigEnvironment;
    }

    /**
     * @param array<string, mixed>  $data
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function render(string $templateName, array $data): string
    {
        return $this->twigEnvironment->render($templateName, $data);
    }

    protected function getLoader(): LoaderInterface
    {
        $useCustomLoader = $this->config['templateShorthand'] ?? false;
        $templateDir = $this->config['templateDirectory'] ?? '/templates/';
        $loaderOptions = [get_template_directory() . $templateDir];
        if ($useCustomLoader) {
            return new CustomTwigFilesystemLoader($loaderOptions);
        } else {
            return new FilesystemLoader($loaderOptions);
        }
    }
}
