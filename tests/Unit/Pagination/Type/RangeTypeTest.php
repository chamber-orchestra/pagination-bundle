<?php

declare(strict_types=1);

namespace Tests\Unit\Pagination\Type;

use ChamberOrchestra\PaginationBundle\Pagination\ExtendedPaginationInterface;
use ChamberOrchestra\PaginationBundle\Pagination\Type\RangeType;
use ChamberOrchestra\PaginationBundle\Pagination\View\PaginationView;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;

final class RangeTypeTest extends TestCase
{
    public function testBuildViewCalculatesRanges(): void
    {
        $pagination = new class() implements ExtendedPaginationInterface {
            public function getName(): string
            {
                return 'range';
            }

            public function getPage(): int|null
            {
                return 3;
            }

            public function getPerPageLimit(): int
            {
                return 2;
            }

            public function createView(): \ChamberOrchestra\PaginationBundle\Pagination\View\PaginationView
            {
                return new \ChamberOrchestra\PaginationBundle\Pagination\View\PaginationView();
            }

            public function getElementsCount(): int
            {
                return 10;
            }

            public function setElementsCount(int $elementsCount): void
            {
            }
        };

        $type = new RangeType(new RequestStack());
        $view = new PaginationView();

        $type->buildView($view, $pagination, ['page_range' => 4]);

        $this->assertSame(3, $view->vars['current']);
        $this->assertSame(5, $view->vars['pages_count']);
        $this->assertSame(10, $view->vars['elements_count']);
        $pages = array_map('intval', $view->vars['pages']);
        $this->assertSame([2, 3, 4, 5], $pages);
        $this->assertSame(2, $view->vars['previous']);
        $this->assertSame(4, $view->vars['next']);
    }
}
