<?php

declare(strict_types=1);

namespace Tests\Unit\Paginator;

use ChamberOrchestra\PaginationBundle\Pagination\PaginationInterface;
use ChamberOrchestra\PaginationBundle\Paginator\PaginatorInterface;
use PHPUnit\Framework\TestCase;

final class PaginatorInterfaceTest extends TestCase
{
    public function testInterfaceCanBeImplemented(): void
    {
        $paginator = new class implements PaginatorInterface {
            public function supports(mixed $target, ?PaginationInterface $pagination = null): bool
            {
                return true;
            }

            public function count(mixed $target, array $options = []): int
            {
                return 1;
            }

            public function paginate(mixed $target, PaginationInterface $pagination, array $options = []): iterable
            {
                return [$target];
            }
        };

        $this->assertInstanceOf(PaginatorInterface::class, $paginator);
    }
}
