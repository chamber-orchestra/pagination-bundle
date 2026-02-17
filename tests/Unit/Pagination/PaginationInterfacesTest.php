<?php

declare(strict_types=1);

namespace Tests\Unit\Pagination;

use ChamberOrchestra\PaginationBundle\Pagination\ExtendedPaginationInterface;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationInterface;
use ChamberOrchestra\PaginationBundle\Pagination\View\PaginationView;
use PHPUnit\Framework\TestCase;

final class PaginationInterfacesTest extends TestCase
{
    public function testInterfacesCanBeImplemented(): void
    {
        $pagination = new class implements PaginationInterface {
            public function getName(): string
            {
                return 'default';
            }

            public function getPosition(): int|string|null
            {
                return 1;
            }

            public function getLimit(): int
            {
                return 10;
            }

            public function createView(): PaginationView
            {
                return new PaginationView();
            }
        };

        $extended = new class implements ExtendedPaginationInterface {
            public function getName(): string
            {
                return 'extended';
            }

            public function getPosition(): int|string|null
            {
                return 1;
            }

            public function getLimit(): int
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

        $this->assertInstanceOf(PaginationInterface::class, $pagination);
        $this->assertInstanceOf(ExtendedPaginationInterface::class, $extended);
    }
}
