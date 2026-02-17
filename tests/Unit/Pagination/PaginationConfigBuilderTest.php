<?php

declare(strict_types=1);

namespace Tests\Unit\Pagination;

use ChamberOrchestra\PaginationBundle\Pagination\CursorPagination;
use ChamberOrchestra\PaginationBundle\Pagination\ExtendedCursorPagination;
use ChamberOrchestra\PaginationBundle\Pagination\ExtendedPagination;
use ChamberOrchestra\PaginationBundle\Pagination\Pagination;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationConfigBuilder;
use ChamberOrchestra\PaginationBundle\Pagination\Type\Resolved\ResolvedPaginationType;
use PHPUnit\Framework\TestCase;

final class PaginationConfigBuilderTest extends TestCase
{
    public function testBuilderStoresValues(): void
    {
        $type = $this->createStub(ResolvedPaginationType::class);
        $builder = new PaginationConfigBuilder($type, 'default', ['page' => 3]);

        $builder->setPosition(2)->setLimit(15)->setExtended(false);

        $this->assertSame('default', $builder->getName());
        $this->assertSame(2, $builder->getPosition());
        $this->assertSame(15, $builder->getLimit());
        $this->assertFalse($builder->isExtended());
        $this->assertSame($type, $builder->getType());
        $this->assertSame(['page' => 3], $builder->getOptions());
    }

    public function testGetPaginationReturnsConcreteInstances(): void
    {
        $type = $this->createStub(ResolvedPaginationType::class);
        $builder = new PaginationConfigBuilder($type, 'default', []);
        $builder->setPosition(1)->setLimit(10)->setExtended(true);

        $pagination = $builder->getPagination();
        $this->assertInstanceOf(ExtendedPagination::class, $pagination);

        $builder->setExtended(false);
        $pagination = $builder->getPagination();
        $this->assertInstanceOf(Pagination::class, $pagination);
    }

    public function testSetCursorReturnsCursorPagination(): void
    {
        $type = $this->createStub(ResolvedPaginationType::class);
        $builder = new PaginationConfigBuilder($type, 'default', []);
        $builder->setPosition(1)->setLimit(10)->setCursor(true);

        $pagination = $builder->getPagination();
        $this->assertInstanceOf(CursorPagination::class, $pagination);
    }

    public function testSetCursorAndExtendedReturnsExtendedCursorPagination(): void
    {
        $type = $this->createStub(ResolvedPaginationType::class);
        $builder = new PaginationConfigBuilder($type, 'default', []);
        $builder->setPosition(1)->setLimit(10)->setCursor(true)->setExtended(true);

        $pagination = $builder->getPagination();
        $this->assertInstanceOf(ExtendedCursorPagination::class, $pagination);
    }

    public function testIsCursorDefaultsToFalse(): void
    {
        $type = $this->createStub(ResolvedPaginationType::class);
        $builder = new PaginationConfigBuilder($type, 'default', []);

        $this->assertFalse($builder->isCursor());
    }

    public function testGetPaginationConfigReturnsClone(): void
    {
        $type = $this->createStub(ResolvedPaginationType::class);
        $builder = new PaginationConfigBuilder($type, 'default', []);

        $config = $builder->getPaginationConfig();

        $this->assertNotSame($builder, $config);
        $this->assertSame($builder->getName(), $config->getName());
    }
}
