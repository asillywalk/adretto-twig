<?php

namespace Sillynet\Adretto\WpTwig\Service;

use Psr\Container\ContainerInterface;
use Sillynet\Adretto\Configuration\ThemeConfiguration;
use Sillynet\Adretto\Theme;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFunction;

/**
 * @phpstan-type FunctionDefinition array{string, callable}
 */
class TwigWordpressBridge extends AbstractExtension implements GlobalsInterface
{
    public const FILTER_FUNCTIONS = 'twigwpbridge_filter_functions';
    public const FILTER_GLOBALS = 'twigwpbridge_filter_globals';

    protected ThemeConfiguration $config;
    protected string $textDomain;

    public function __construct(
        ContainerInterface $container,
        ThemeConfiguration $config
    ) {
        $rawTextDomain = $container->get('textDomain');
        $this->textDomain =
            !empty($rawTextDomain) && is_string($rawTextDomain)
                ? $rawTextDomain
                : 'sillynet';
        $this->config = $config;
    }

    /**
     * @return array<string, mixed>
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function getGlobals(): array
    {
        $baseDefinitions = $this->getGlobalDefinitions();
        $baseDefinitions = array_merge($baseDefinitions, $this->parseGlobals());
        return apply_filters(self::FILTER_GLOBALS, $baseDefinitions);
    }

    /**
     * @return array<string, mixed>
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    protected function parseGlobals(): array
    {
        $rawTwigConfig = $this->config->get('twig');
        $globalDefinitionsFromConfig =
            is_array($rawTwigConfig) &&
            array_key_exists('globals', $rawTwigConfig)
                ? $rawTwigConfig['globals']
                : [];
        $definitions = [];
        foreach (
            $globalDefinitionsFromConfig
            as $definitionName => $definition
        ) {
            $definitionName = (string) $definitionName;
            if (array_key_exists('class', $definition)) {
                $definitions[$definitionName] = Theme::getInstance()
                    ->getContainer()
                    ->get($definition['class']);
            } elseif (array_key_exists('value', $definition)) {
                $definitions[$definitionName] = $definition['value'];
            }
        }

        return $definitions;
    }

    /**
     * @return array<TwigFunction>
     */
    public function getFunctions(): array
    {
        $baseDefinitions = $this->getFunctionDefinitions();
        $definitions = apply_filters(self::FILTER_FUNCTIONS, $baseDefinitions);

        return array_map([$this, 'createFunction'], $definitions);
    }

    /**
     * @param FunctionDefinition $definition
     */
    protected function createFunction(array $definition): TwigFunction
    {
        return new TwigFunction(...$definition);
    }

    /**
     * @return array<string, mixed>
     */
    protected function getGlobalDefinitions(): array
    {
        global $post;

        return [
            '_textDomain' => $this->textDomain,
            'postId' => $post->ID,
            'post' => $post,
        ];
    }

    /**
     * @return array<FunctionDefinition>
     */
    protected function getFunctionDefinitions(): array
    {
        return [
            ['the_content', 'the_content'],
            ['the_title', 'the_title'],
            ['get_the_title', 'get_the_title'],
            ['get_bloginfo', 'get_bloginfo'],
            ['get_template_directory_uri', 'get_template_directory_uri'],
            ['get_theme_file_uri', 'get_theme_file_uri'],
            ['home_url', 'home_url'],
            ['wp_nav_menu', 'wp_nav_menu'],
            ['has_nav_menu', 'has_nav_menu'],
            [
                '__',
                function (string $string): string {
                    return __($string, $this->textDomain);
                },
            ],
            [
                'getContainer',
                function (): ContainerInterface {
                    return Theme::getInstance()->getContainer();
                },
            ],
            [
                'containerGet',
                function (string $entry) {
                    return Theme::getInstance()
                        ->getContainer()
                        ->get($entry);
                },
            ],
        ];
    }
}
