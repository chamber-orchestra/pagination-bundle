<?php

declare(strict_types=1);

namespace Tests\Unit\Helper;

use ChamberOrchestra\PaginationBundle\Pagination\View\PaginationView;
use ChamberOrchestra\PaginationBundle\Twig\Helper\Processor;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

final class ProcessorTest extends TestCase
{
    public function testRenderMergesViewVarsAndOptions(): void
    {
        $loader = new ArrayLoader([
            'pagination.html.twig' => 'page={{ current }}, limit={{ limit }}, extra={{ extra }}',
        ]);
        $env = new Environment($loader);

        $view = new PaginationView();
        $view->vars = [
            'current' => 3,
            'limit' => 10,
        ];

        $processor = new Processor();

        $rendered = $processor->render($env, $view, 'pagination.html.twig', ['extra' => 'ok']);

        $this->assertSame('page=3, limit=10, extra=ok', $rendered);
    }
}
