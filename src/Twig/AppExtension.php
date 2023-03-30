<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('slugify', [$this, 'slugify']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('function_name', [$this, 'doSomething']),
        ];
    }

    public function slugify($string)
    {
        $string = preg_replace('/\s+/', '-', trim($string));
        $string = preg_replace('/[^A-Za-z0-9\-]+/', '', $string);
        return mb_strtolower($string, 'UTF-8');
    }
}
