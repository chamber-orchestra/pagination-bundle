<?php

declare(strict_types=1);

namespace Tests\Unit\Pagination\Type;

use ChamberOrchestra\PaginationBundle\Pagination\PaginationInterface;
use ChamberOrchestra\PaginationBundle\Pagination\Type\PaginationType;
use ChamberOrchestra\PaginationBundle\Pagination\View\PaginationView;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;

final class PaginationTypeTest extends TestCase
{
    public function testBuildViewSetsNavigationVars(): void
    {
        $pagination = $this->createStub(PaginationInterface::class);
        $pagination->method('getPage')->willReturn(3);

        $type = new PaginationType(new RequestStack());
        $view = new PaginationView();

        $type->buildView($view, $pagination, []);

        $this->assertSame(3, $view->vars['current']);
        $this->assertSame(1, $view->vars['start_page']);
        $this->assertSame(2, $view->vars['previous']);
        $this->assertSame(4, $view->vars['next']);
    }
}
