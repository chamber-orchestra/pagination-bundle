<?php

declare(strict_types=1);

namespace Tests\Integrational\Helper;

use ChamberOrchestra\PaginationBundle\Pagination\View\PaginationView;
use ChamberOrchestra\PaginationBundle\Twig\Helper\Processor;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

final class ProcessorIntegrationTest extends KernelTestCase
{
    public function testRenderUsesTwigEnvironment(): void
    {
        self::bootKernel();

        $loader = new ArrayLoader([
            'template.html.twig' => 'page={{ current }}',
        ]);
        $env = new Environment($loader);

        $view = new PaginationView();
        $view->vars = ['current' => 1];

        $processor = new Processor();

        $this->assertSame('page=1', $processor->render($env, $view, 'template.html.twig'));
    }
}
