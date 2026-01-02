<?php

declare(strict_types=1);

namespace Tests\Unit\Pagination;

use ChamberOrchestra\PaginationBundle\Pagination\ExtendedPagination;
use ChamberOrchestra\PaginationBundle\Pagination\Pagination;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationConfigInterface;
use ChamberOrchestra\PaginationBundle\Pagination\Type\Resolved\ResolvedPaginationTypeInterface;
use ChamberOrchestra\PaginationBundle\Pagination\View\PaginationView;
use PHPUnit\Framework\TestCase;

final class PaginationTest extends TestCase
{
    public function testCreateViewUsesResolvedType(): void
    {
        $resolved = $this->createMock(ResolvedPaginationTypeInterface::class);

        $view = new PaginationView();
        $resolved->expects($this->once())
            ->method('createView')
            ->willReturn($view);
        $resolved->expects($this->once())
            ->method('buildView')
            ->with($view, $this->isInstanceOf(Pagination::class), ['opt' => true]);

        $config = $this->createStub(PaginationConfigInterface::class);
        $config->method('getType')->willReturn($resolved);
        $config->method('getOptions')->willReturn(['opt' => true]);
        $config->method('getName')->willReturn('default');
        $config->method('getPage')->willReturn(1);
        $config->method('getPerPageLimit')->willReturn(10);

        $pagination = new Pagination($config);

        $this->assertSame($view, $pagination->createView());
        $this->assertSame('default', $pagination->getName());
        $this->assertSame(1, $pagination->getPage());
        $this->assertSame(10, $pagination->getPerPageLimit());
    }

    public function testExtendedPaginationStoresElementsCount(): void
    {
        $config = $this->createStub(PaginationConfigInterface::class);
        $config->method('getType')->willReturn($this->createStub(ResolvedPaginationTypeInterface::class));
        $config->method('getOptions')->willReturn([]);
        $config->method('getName')->willReturn('default');
        $config->method('getPage')->willReturn(1);
        $config->method('getPerPageLimit')->willReturn(10);

        $pagination = new ExtendedPagination($config);
        $pagination->setElementsCount(50);

        $this->assertSame(50, $pagination->getElementsCount());
    }
}
