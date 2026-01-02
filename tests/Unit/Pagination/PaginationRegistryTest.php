<?php

declare(strict_types=1);

namespace Tests\Unit\Pagination;

use ChamberOrchestra\PaginationBundle\Pagination\PaginationRegistry;
use ChamberOrchestra\PaginationBundle\Pagination\Type\PaginationTypeInterface;
use ChamberOrchestra\PaginationBundle\Pagination\Type\Resolved\ResolvedPaginationTypeFactoryInterface;
use ChamberOrchestra\PaginationBundle\Pagination\Type\Resolved\ResolvedPaginationTypeInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ServiceLocator;

final class PaginationRegistryTest extends TestCase
{
    public function testResolvesAndCachesTypes(): void
    {
        $innerType = $this->createStub(PaginationTypeInterface::class);
        $resolved = $this->createStub(ResolvedPaginationTypeInterface::class);

        $factory = $this->createMock(ResolvedPaginationTypeFactoryInterface::class);
        $factory->expects($this->once())
            ->method('createResolvedPaginationType')
            ->with($innerType)
            ->willReturn($resolved);

        $locator = new ServiceLocator([
            'default' => static fn() => $innerType,
        ]);

        $registry = new PaginationRegistry($factory, $locator);

        $first = $registry->getType('default');
        $second = $registry->getType('default');

        $this->assertSame($resolved, $first);
        $this->assertSame($resolved, $second);
    }
}
