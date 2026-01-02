<?php

declare(strict_types=1);

namespace Tests\Unit\Pagination\Type\Resolved;

use ChamberOrchestra\PaginationBundle\Pagination\Type\PaginationTypeInterface;
use ChamberOrchestra\PaginationBundle\Pagination\Type\Resolved\ResolvedPaginationType;
use ChamberOrchestra\PaginationBundle\Pagination\Type\Resolved\ResolvedPaginationTypeFactory;
use PHPUnit\Framework\TestCase;

final class ResolvedPaginationTypeFactoryTest extends TestCase
{
    public function testCreatesResolvedType(): void
    {
        $factory = new ResolvedPaginationTypeFactory();
        $inner = $this->createStub(PaginationTypeInterface::class);

        $resolved = $factory->createResolvedPaginationType($inner);

        $this->assertInstanceOf(ResolvedPaginationType::class, $resolved);
    }
}
