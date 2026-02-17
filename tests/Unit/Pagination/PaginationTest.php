<?php

declare(strict_types=1);

namespace Tests\Unit\Pagination;

use ChamberOrchestra\PaginationBundle\Pagination\ExtendedPagination;
use ChamberOrchestra\PaginationBundle\Pagination\Pagination;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationConfigBuilder;
use ChamberOrchestra\PaginationBundle\Pagination\Type\Resolved\ResolvedPaginationType;
use ChamberOrchestra\PaginationBundle\Pagination\View\PaginationView;
use PHPUnit\Framework\TestCase;

final class PaginationTest extends TestCase
{
    public function testCreateViewUsesResolvedType(): void
    {
        $resolved = $this->createMock(ResolvedPaginationType::class);

        $resolved->expects($this->once())
            ->method('buildView')
            ->with($this->isInstanceOf(PaginationView::class), $this->isInstanceOf(Pagination::class), ['opt' => true]);

        $config = new PaginationConfigBuilder($resolved, 'default', ['opt' => true]);
        $config->setPosition(1)->setLimit(10)->setExtended(false);

        $pagination = new Pagination($config);

        $this->assertInstanceOf(PaginationView::class, $pagination->createView());
        $this->assertSame('default', $pagination->getName());
        $this->assertSame(1, $pagination->getPosition());
        $this->assertSame(10, $pagination->getLimit());
    }

    public function testExtendedPaginationStoresElementsCount(): void
    {
        $type = $this->createStub(ResolvedPaginationType::class);

        $config = new PaginationConfigBuilder($type, 'default', []);
        $config->setPosition(1)->setLimit(10)->setExtended(true);

        $pagination = new ExtendedPagination($config);
        $pagination->setElementsCount(50);

        $this->assertSame(50, $pagination->getElementsCount());
    }
}
