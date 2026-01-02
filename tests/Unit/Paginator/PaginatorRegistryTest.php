<?php

declare(strict_types=1);

namespace Tests\Unit\Paginator;

use ChamberOrchestra\PaginationBundle\Paginator\PaginatorInterface;
use ChamberOrchestra\PaginationBundle\Paginator\PaginatorRegistry;
use PHPUnit\Framework\TestCase;

final class PaginatorRegistryTest extends TestCase
{
    public function testReturnsFirstSupportedPaginator(): void
    {
        $first = $this->createStub(PaginatorInterface::class);
        $second = $this->createStub(PaginatorInterface::class);

        $first->method('supports')->willReturn(false);
        $second->method('supports')->willReturn(true);

        $registry = new PaginatorRegistry([$first, $second]);

        $this->assertSame($second, $registry->getSupportedPaginator('target'));
    }

    public function testReturnsNullWhenNoneSupports(): void
    {
        $first = $this->createStub(PaginatorInterface::class);
        $first->method('supports')->willReturn(false);

        $registry = new PaginatorRegistry([$first]);

        $this->assertNull($registry->getSupportedPaginator('target'));
    }
}
