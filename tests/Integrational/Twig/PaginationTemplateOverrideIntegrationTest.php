<?php

declare(strict_types=1);

namespace Tests\Integrational\Twig;

use ChamberOrchestra\PaginationBundle\Helper\Processor;
use ChamberOrchestra\PaginationBundle\Pagination\View\PaginationView;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

final class PaginationTemplateOverrideIntegrationTest extends TestCase
{
    public function testCustomTemplateOverrideWins(): void
    {
        $loader = new FilesystemLoader();
        $loader->addPath(__DIR__ . '/../../Fixtures/Twig/override');
        $loader->addPath(__DIR__ . '/../../../src/Resources/views');

        $env = new Environment($loader);

        $view = new PaginationView();
        $view->vars = ['current' => 2];

        $processor = new Processor();

        $rendered = $processor->render($env, $view, 'pagination/sliding.html.twig');

        $this->assertSame('override current=2', rtrim($rendered));
    }
}
