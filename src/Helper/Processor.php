<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\PaginationBundle\Helper;

use ChamberOrchestra\PaginationBundle\Pagination\View\PaginationView;
use Twig\Environment;

class Processor
{
    /**
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function render(Environment $env, PaginationView $view, ?string $template = null, array $viewOptions = []): string
    {
        return $env->render($template, \array_replace_recursive($view->vars, $viewOptions));
    }
}
