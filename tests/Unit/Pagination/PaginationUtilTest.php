<?php

declare(strict_types=1);

namespace Tests\Unit\Pagination;

use ChamberOrchestra\PaginationBundle\Exception\LogicException;
use ChamberOrchestra\PaginationBundle\Pagination\ExtendedPaginationInterface;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationInterface;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationUtil;
use PHPUnit\Framework\TestCase;

final class PaginationUtilTest extends TestCase
{
    public function testGetOffsetUsesPageAndLimit(): void
    {
        $pagination = $this->createStub(PaginationInterface::class);
        $pagination->method('getPage')->willReturn(3);
        $pagination->method('getPerPageLimit')->willReturn(5);

        $this->assertSame(10, PaginationUtil::getOffset($pagination));
    }

    public function testGetPagesCountRequiresExtendedPagination(): void
    {
        $pagination = $this->createStub(PaginationInterface::class);

        $this->expectException(LogicException::class);
        PaginationUtil::getPagesCount($pagination);
    }

    public function testGetPagesCountUsesElementsCount(): void
    {
        $pagination = new class() implements ExtendedPaginationInterface {
            public function getName(): string
            {
                return 'range';
            }

            public function getPage(): int|null
            {
                return 1;
            }

            public function getPerPageLimit(): int
            {
                return 3;
            }

            public function createView(): \ChamberOrchestra\PaginationBundle\Pagination\View\PaginationView
            {
                return new \ChamberOrchestra\PaginationBundle\Pagination\View\PaginationView();
            }

            public function getElementsCount(): int
            {
                return 8;
            }

            public function setElementsCount(int $elementsCount): void
            {
            }
        };

        $this->assertSame(3, PaginationUtil::getPagesCount($pagination));
    }
}
