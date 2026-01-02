<?php

declare(strict_types=1);

namespace Tests\Unit\Pagination;

use ChamberOrchestra\PaginationBundle\Pagination\ExtendedPagination;
use ChamberOrchestra\PaginationBundle\Pagination\Pagination;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationConfigBuilder;
use ChamberOrchestra\PaginationBundle\Pagination\Type\Resolved\ResolvedPaginationTypeInterface;
use PHPUnit\Framework\TestCase;

final class PaginationConfigBuilderTest extends TestCase
{
    public function testBuilderStoresValues(): void
    {
        $type = $this->createStub(ResolvedPaginationTypeInterface::class);
        $builder = new PaginationConfigBuilder($type, 'default', ['page' => 3]);

        $builder->setPage(2)->setPerPageLimit(15)->setExtended(false);

        $this->assertSame('default', $builder->getName());
        $this->assertSame(2, $builder->getPage());
        $this->assertSame(15, $builder->getPerPageLimit());
        $this->assertFalse($builder->isExtended());
        $this->assertSame($type, $builder->getType());
        $this->assertSame(['page' => 3], $builder->getOptions());
    }

    public function testGetPaginationReturnsConcreteInstances(): void
    {
        $type = $this->createStub(ResolvedPaginationTypeInterface::class);
        $builder = new PaginationConfigBuilder($type, 'default', []);
        $builder->setPage(1)->setPerPageLimit(10)->setExtended(true);

        $pagination = $builder->getPagination();
        $this->assertInstanceOf(ExtendedPagination::class, $pagination);

        $builder->setExtended(false);
        $pagination = $builder->getPagination();
        $this->assertInstanceOf(Pagination::class, $pagination);
    }

    public function testGetPaginationConfigReturnsClone(): void
    {
        $type = $this->createStub(ResolvedPaginationTypeInterface::class);
        $builder = new PaginationConfigBuilder($type, 'default', []);

        $config = $builder->getPaginationConfig();

        $this->assertNotSame($builder, $config);
        $this->assertSame($builder->getName(), $config->getName());
    }
}
