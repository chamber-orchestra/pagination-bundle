<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\PaginationBundle\Twig;

use ChamberOrchestra\PaginationBundle\Pagination\View\PaginationView;
use ChamberOrchestra\PaginationBundle\Twig\Helper\Processor;
use Twig\Environment;
use Twig\Extension\RuntimeExtensionInterface;

readonly class PaginationRuntime implements RuntimeExtensionInterface
{
    public function __construct(private Processor $processor)
    {
    }

    /**
     * @param array<string, mixed> $viewParams
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function render(Environment $env, PaginationView $pagination, string $template = '@ChamberOrchestraPagination/pagination/sliding.html.twig', array $viewParams = []): string
    {
        return $this->processor->render($env, $pagination, $template, $viewParams);
    }
}
