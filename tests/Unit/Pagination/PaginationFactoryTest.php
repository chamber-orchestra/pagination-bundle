<?php

declare(strict_types=1);

namespace Tests\Unit\Pagination;

use ChamberOrchestra\PaginationBundle\Pagination\PaginationConfigBuilder;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationFactory;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationInterface;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationRegistry;
use ChamberOrchestra\PaginationBundle\Pagination\Type\Resolved\ResolvedPaginationType;
use PHPUnit\Framework\TestCase;

final class PaginationFactoryTest extends TestCase
{
    public function testCreatesPaginationFromType(): void
    {
        $type = $this->createMock(ResolvedPaginationType::class);

        $builder = new PaginationConfigBuilder($type, 'default', ['page' => 1]);

        $type->expects($this->once())
            ->method('createBuilder')
            ->with('default', ['page' => 1])
            ->willReturn($builder);

        $type->expects($this->once())
            ->method('buildPagination')
            ->with($builder, ['page' => 1])
            ->willReturnCallback(function (PaginationConfigBuilder $b): void {
                $b->setPosition(1)->setLimit(10)->setExtended(false);
            });

        $registry = $this->createMock(PaginationRegistry::class);
        $registry->expects($this->once())
            ->method('getType')
            ->with('default')
            ->willReturn($type);

        $factory = new PaginationFactory($registry);
        $result = $factory->create('default', ['page' => 1]);

        $this->assertInstanceOf(PaginationInterface::class, $result);
    }
}
