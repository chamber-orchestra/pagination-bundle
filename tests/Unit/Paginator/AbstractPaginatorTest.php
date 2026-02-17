<?php

declare(strict_types=1);

namespace Tests\Unit\Paginator;

use ChamberOrchestra\PaginationBundle\Pagination\PaginationInterface;
use ChamberOrchestra\PaginationBundle\Paginator\AbstractPaginator;
use ChamberOrchestra\PaginationBundle\Paginator\PaginatorInterface;
use PHPUnit\Framework\TestCase;

final class AbstractPaginatorTest extends TestCase
{
    public function testImplementsPaginatorInterface(): void
    {
        $paginator = new class extends AbstractPaginator {
            public function supports(mixed $target, ?PaginationInterface $pagination = null): bool
            {
                return false;
            }

            public function count(mixed $target, array $options = []): int
            {
                return 0;
            }

            public function paginate(mixed $target, PaginationInterface $pagination, array $options = []): iterable
            {
                return [];
            }
        };

        $this->assertInstanceOf(PaginatorInterface::class, $paginator);
    }
}
