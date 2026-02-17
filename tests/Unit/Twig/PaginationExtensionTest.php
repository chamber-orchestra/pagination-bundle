<?php

declare(strict_types=1);

namespace Tests\Unit\Twig;

use ChamberOrchestra\PaginationBundle\Pagination\View\PaginationView;
use ChamberOrchestra\PaginationBundle\Twig\Helper\Processor;
use ChamberOrchestra\PaginationBundle\Twig\PaginationExtension;
use ChamberOrchestra\PaginationBundle\Twig\PaginationRuntime;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Error\RuntimeError;
use Twig\Loader\ArrayLoader;
use Twig\RuntimeLoader\RuntimeLoaderInterface;
use Twig\TwigFunction;

final class PaginationExtensionTest extends TestCase
{
    public function testGetFunctionsDefinesRenderPagination(): void
    {
        $extension = new PaginationExtension();
        $functions = $extension->getFunctions();

        $this->assertCount(1, $functions);
        $this->assertInstanceOf(TwigFunction::class, $functions[0]);
        $this->assertSame('render_pagination', $functions[0]->getName());
        $this->assertSame([PaginationRuntime::class, 'render'], $functions[0]->getCallable());
    }

    public function testRenderPaginationFunctionRendersView(): void
    {
        $loader = new ArrayLoader([
            'index.html.twig' => '{{ render_pagination(pagination_view, "pagination.html.twig") }}',
            'pagination.html.twig' => 'page={{ current }}',
        ]);
        $env = new Environment($loader);
        $env->addExtension(new PaginationExtension());
        $env->addRuntimeLoader(new class implements RuntimeLoaderInterface {
            public function load(string $class): ?object
            {
                if (PaginationRuntime::class !== $class) {
                    return null;
                }

                return new PaginationRuntime(new Processor());
            }
        });

        $view = new PaginationView();
        $view->vars = ['current' => 4];

        $rendered = $env->render('index.html.twig', [
            'pagination_view' => $view,
        ]);

        $this->assertSame('page=4', $rendered);
    }

    public function testRenderPaginationFailsWithoutRuntimeLoader(): void
    {
        $loader = new ArrayLoader([
            'index.html.twig' => '{{ render_pagination(pagination_view, "pagination.html.twig") }}',
            'pagination.html.twig' => 'page={{ current }}',
        ]);
        $env = new Environment($loader);
        $env->addExtension(new PaginationExtension());

        $view = new PaginationView();
        $view->vars = ['current' => 1];

        $this->expectException(RuntimeError::class);
        $env->render('index.html.twig', [
            'pagination_view' => $view,
        ]);
    }
}
