<?php

declare(strict_types=1);

namespace Tests\Unit\Paginator;

use ChamberOrchestra\PaginationBundle\Pagination\PaginationInterface;
use ChamberOrchestra\PaginationBundle\Paginator\PaginatorInterface;
use ChamberOrchestra\PaginationBundle\Paginator\PaginatorRegistry;
use PHPUnit\Framework\TestCase;

final class PaginatorRegistryTest extends TestCase
{
    public function testReturnsFirstSupportedPaginator(): void
    {
        $pagination = $this->createStub(PaginationInterface::class);

        $first = $this->createStub(PaginatorInterface::class);
        $second = $this->createStub(PaginatorInterface::class);

        $first->method('supports')->willReturn(false);
        $second->method('supports')->willReturn(true);

        $registry = new PaginatorRegistry([$first, $second]);

        $this->assertSame($second, $registry->getSupportedPaginator('target', $pagination));
    }

    public function testReturnsNullWhenNoneSupports(): void
    {
        $pagination = $this->createStub(PaginationInterface::class);

        $first = $this->createStub(PaginatorInterface::class);
        $first->method('supports')->willReturn(false);

        $registry = new PaginatorRegistry([$first]);

        $this->assertNull($registry->getSupportedPaginator('target', $pagination));
    }
}
