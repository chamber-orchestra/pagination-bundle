<?php

declare(strict_types=1);

namespace Tests\Unit\Pagination\Type\Resolved;

use ChamberOrchestra\PaginationBundle\Pagination\PaginationConfigBuilderInterface;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationInterface;
use ChamberOrchestra\PaginationBundle\Pagination\Type\PaginationTypeInterface;
use ChamberOrchestra\PaginationBundle\Pagination\Type\Resolved\ResolvedPaginationTypeFactoryInterface;
use ChamberOrchestra\PaginationBundle\Pagination\Type\Resolved\ResolvedPaginationTypeInterface;
use ChamberOrchestra\PaginationBundle\Pagination\View\PaginationView;
use PHPUnit\Framework\TestCase;

final class ResolvedPaginationTypeInterfacesTest extends TestCase
{
    public function testResolvedPaginationTypeInterfaceCanBeImplemented(): void
    {
        $resolvedType = $this->createStub(ResolvedPaginationTypeInterface::class);

        $resolved = new class($resolvedType) implements ResolvedPaginationTypeInterface {
            public function __construct(private ResolvedPaginationTypeInterface $type)
            {
            }

            public function createBuilder(string $name, array $options): PaginationConfigBuilderInterface
            {
                return new class($this->type) implements PaginationConfigBuilderInterface {
                    public function __construct(private ResolvedPaginationTypeInterface $type)
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
                        return new class() implements PaginationInterface {
                            public function getName(): string
                            {
                                return 'default';
                            }

                            public function getPage(): ?int
                            {
                                return 1;
                            }

                            public function getPerPageLimit(): int
                            {
                                return 10;
                            }

                            public function createView(): PaginationView
                            {
                                return new PaginationView();
                            }
                        };
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

                    public function getType(): ResolvedPaginationTypeInterface
                    {
                        return $this->type;
                    }

                    public function getOptions(): array
                    {
                        return [];
                    }
                };
            }

            public function buildPagination(PaginationConfigBuilderInterface $builder, array $options): void
            {
            }

            public function createView(PaginationInterface $pagination): PaginationView
            {
                return new PaginationView();
            }

            public function buildView(PaginationView $view, PaginationInterface $pagination, array $options): void
            {
            }
        };

        $this->assertInstanceOf(ResolvedPaginationTypeInterface::class, $resolved);
    }

    public function testResolvedPaginationTypeFactoryInterfaceCanBeImplemented(): void
    {
        $resolvedType = $this->createStub(ResolvedPaginationTypeInterface::class);

        $factory = new class($resolvedType) implements ResolvedPaginationTypeFactoryInterface {
            public function __construct(private ResolvedPaginationTypeInterface $resolvedType)
            {
            }

            public function createResolvedPaginationType(PaginationTypeInterface $pagination): ResolvedPaginationTypeInterface
            {
                return $this->resolvedType;
            }
        };

        $this->assertInstanceOf(ResolvedPaginationTypeFactoryInterface::class, $factory);
    }
}
