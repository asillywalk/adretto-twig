<?php

namespace Sillynet\Adretto\WpTwig\Service;

use Twig\Loader\FilesystemLoader;

class CustomTwigFilesystemLoader extends FilesystemLoader
{
    protected function findTemplate(string $name, bool $throw = true)
    {
        $name = $this->parseName($name);

        return parent::findTemplate($name, $throw);
    }

    protected function parseName(string $name): string
    {
        $withSlashes = str_replace('.', '/', $name);
        return $withSlashes . '.html.twig';
    }
}
