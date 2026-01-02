<?php

declare(strict_types=1);

namespace Tests\Unit\Pagination;

use ChamberOrchestra\PaginationBundle\Pagination\ExtendedPaginationInterface;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationConfigBuilderInterface;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationConfigInterface;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationInterface;
use ChamberOrchestra\PaginationBundle\Pagination\Type\Resolved\ResolvedPaginationTypeInterface;
use ChamberOrchestra\PaginationBundle\Pagination\View\PaginationView;
use PHPUnit\Framework\TestCase;

final class PaginationInterfacesTest extends TestCase
{
    public function testInterfacesCanBeImplemented(): void
    {
        $pagination = new class() implements PaginationInterface {
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

        $extended = new class() implements ExtendedPaginationInterface {
            public function getName(): string
            {
                return 'extended';
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

            public function getElementsCount(): int
            {
                return 1;
            }

            public function setElementsCount(int $elementsCount): void
            {
            }
        };

        $resolvedType = $this->createStub(ResolvedPaginationTypeInterface::class);

        $config = new class($resolvedType) implements PaginationConfigInterface {
            public function __construct(private ResolvedPaginationTypeInterface $type)
            {
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

        $builder = new class($resolvedType) implements PaginationConfigBuilderInterface {
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

            public function getPaginationConfig(): PaginationConfigInterface
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

        $this->assertInstanceOf(PaginationInterface::class, $pagination);
        $this->assertInstanceOf(ExtendedPaginationInterface::class, $extended);
        $this->assertInstanceOf(PaginationConfigInterface::class, $config);
        $this->assertInstanceOf(PaginationConfigBuilderInterface::class, $builder);
    }
}
