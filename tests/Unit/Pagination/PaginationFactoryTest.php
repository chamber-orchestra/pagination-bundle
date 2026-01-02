<?php

declare(strict_types=1);

namespace Tests\Unit\Pagination;

use ChamberOrchestra\PaginationBundle\Pagination\PaginationConfigBuilderInterface;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationFactory;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationInterface;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationRegistry;
use ChamberOrchestra\PaginationBundle\Pagination\Type\Resolved\ResolvedPaginationTypeInterface;
use PHPUnit\Framework\TestCase;

final class PaginationFactoryTest extends TestCase
{
    public function testCreatesPaginationFromType(): void
    {
        $pagination = $this->createStub(PaginationInterface::class);

        $builder = new class($pagination) implements PaginationConfigBuilderInterface {
            public function __construct(private PaginationInterface $pagination)
            {
            }

            public function setPage(int $page): PaginationConfigBuilderInterface
            {
                return $this;
            }

            public function setPerPageLimit(int $limit): PaginationConfigBuilderInterface
            {
                return $this;
            }

            public function setExtended(bool $extended): PaginationConfigBuilderInterface
            {
                return $this;
            }

            public function getPagination(): PaginationInterface
            {
                return $this->pagination;
            }

            public function getPaginationConfig(): \ChamberOrchestra\PaginationBundle\Pagination\PaginationConfigInterface
            {
                return $this;
            }

            public function getName(): string
            {
                return 'default';
            }

            public function isExtended(): bool
            {
                return false;
            }

            public function getPage(): int
            {
                return 1;
            }

            public function getPerPageLimit(): int
            {
                return 10;
            }

            public function getType(): \ChamberOrchestra\PaginationBundle\Pagination\Type\Resolved\ResolvedPaginationTypeInterface
            {
                throw new \RuntimeException('Not used');
            }

            public function getOptions(): array
            {
                return ['page' => 1];
            }
        };

        $type = $this->createMock(ResolvedPaginationTypeInterface::class);
        $type->expects($this->once())
            ->method('createBuilder')
            ->with('default', ['page' => 1])
            ->willReturn($builder);
        $type->expects($this->once())
            ->method('buildPagination')
            ->with($builder, ['page' => 1]);

        $registry = $this->createMock(PaginationRegistry::class);
        $registry->expects($this->once())
            ->method('getType')
            ->with('default')
            ->willReturn($type);

        $factory = new PaginationFactory($registry);

        $this->assertSame($pagination, $factory->create('default', ['page' => 1]));
    }
}
