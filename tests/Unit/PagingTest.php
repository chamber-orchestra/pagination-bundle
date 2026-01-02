<?php

declare(strict_types=1);

namespace Tests\Unit;

use ChamberOrchestra\PaginationBundle\Exception\RuntimeException;
use ChamberOrchestra\PaginationBundle\Paging;
use ChamberOrchestra\PaginationBundle\Pagination\ExtendedPaginationInterface;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationInterface;
use ChamberOrchestra\PaginationBundle\Paginator\PaginatorInterface;
use ChamberOrchestra\PaginationBundle\Paginator\PaginatorRegistry;
use PHPUnit\Framework\TestCase;

final class PagingTest extends TestCase
{
    public function testThrowsWhenNoPaginatorSupportsTarget(): void
    {
        $registry = $this->createStub(PaginatorRegistry::class);
        $registry->method('getSupportedPaginator')->willReturn(null);

        $pagination = $this->createStub(PaginationInterface::class);
        $pagination->method('getPerPageLimit')->willReturn(10);

        $paging = new Paging($registry);

        $this->expectException(RuntimeException::class);
        $paging->paginate('target', $pagination);
    }

    public function testSetsElementsCountForExtendedPagination(): void
    {
        $paginator = $this->createStub(PaginatorInterface::class);
        $paginator->method('count')->willReturn(42);
        $paginator->method('paginate')->willReturn([1, 2]);

        $registry = $this->createStub(PaginatorRegistry::class);
        $registry->method('getSupportedPaginator')->willReturn($paginator);

        $pagination = new class() implements ExtendedPaginationInterface {
            public int $elementsCount = 0;

            public function getName(): string
            {
                return 'test';
            }

            public function getPage(): int|null
            {
                return 1;
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
                return $this->elementsCount;
            }

            public function setElementsCount(int $elementsCount): void
            {
                $this->elementsCount = $elementsCount;
            }
        };

        $paging = new Paging($registry);
        $result = $paging->paginate(['a', 'b'], $pagination);

        $this->assertSame([1, 2], $result);
        $this->assertSame(42, $pagination->getElementsCount());
    }
}
