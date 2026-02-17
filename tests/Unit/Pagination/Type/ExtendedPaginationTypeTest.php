<?php

declare(strict_types=1);

namespace Tests\Unit\Pagination\Type;

use ChamberOrchestra\PaginationBundle\Pagination\ExtendedPaginationInterface;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationInterface;
use ChamberOrchestra\PaginationBundle\Pagination\Type\ExtendedPaginationType;
use ChamberOrchestra\PaginationBundle\Pagination\View\PaginationView;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;

final class ExtendedPaginationTypeTest extends TestCase
{
    private function createType(): ExtendedPaginationType
    {
        return new ExtendedPaginationType(new RequestStack());
    }

    private function createPagination(int $position, int $limit, int $elementsCount): ExtendedPaginationInterface
    {
        $pagination = $this->createStub(ExtendedPaginationInterface::class);
        $pagination->method('getPosition')->willReturn($position);
        $pagination->method('getLimit')->willReturn($limit);
        $pagination->method('getElementsCount')->willReturn($elementsCount);
        $pagination->method('getName')->willReturn('extended');

        return $pagination;
    }

    public function testBuildViewSetsExtendedVars(): void
    {
        $type = $this->createType();
        $view = new PaginationView();
        $pagination = $this->createPagination(position: 2, limit: 10, elementsCount: 35);

        $type->buildView($view, $pagination, ['parameter' => 'page', 'limit' => 10]);

        $this->assertSame(2, $view->vars['current']);
        $this->assertSame(1, $view->vars['previous']);
        $this->assertSame(3, $view->vars['next']);
        $this->assertSame(4, $view->vars['pagesCount']); // ceil(35/10) = 4
        $this->assertSame(35, $view->vars['elementsCount']);
    }

    public function testBuildViewClampsPreviousToNullOnFirstPage(): void
    {
        $type = $this->createType();
        $view = new PaginationView();
        $pagination = $this->createPagination(position: 1, limit: 10, elementsCount: 20);

        $type->buildView($view, $pagination, ['parameter' => 'page', 'limit' => 10]);

        $this->assertNull($view->vars['previous']);
        $this->assertSame(2, $view->vars['next']);
    }

    public function testBuildViewClampsNextToNullOnLastPage(): void
    {
        $type = $this->createType();
        $view = new PaginationView();
        $pagination = $this->createPagination(position: 3, limit: 10, elementsCount: 30);

        $type->buildView($view, $pagination, ['parameter' => 'page', 'limit' => 10]);

        $this->assertNull($view->vars['next']);
        $this->assertSame(2, $view->vars['previous']);
        $this->assertSame(3, $view->vars['pagesCount']);
    }

    public function testThrowsForNonExtendedPagination(): void
    {
        $type = $this->createType();
        $pagination = $this->createStub(PaginationInterface::class);

        $this->expectException(\LogicException::class);
        $type->buildView(new PaginationView(), $pagination, []);
    }
}
