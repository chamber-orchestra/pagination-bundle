<?php

declare(strict_types=1);

namespace Tests\Unit\Paginator;

use ChamberOrchestra\PaginationBundle\Paginator\AbstractPaginator;
use ChamberOrchestra\PaginationBundle\Paginator\PaginatorInterface;
use PHPUnit\Framework\TestCase;

final class AbstractPaginatorTest extends TestCase
{
    public function testImplementsPaginatorInterface(): void
    {
        $paginator = new class() extends AbstractPaginator {
            public function supports($target): bool
            {
                return false;
            }

            public function count($target, array $options = []): int
            {
                return 0;
            }

            public function paginate($target, \ChamberOrchestra\PaginationBundle\Pagination\PaginationInterface $pagination, array $options = []): iterable
            {
                return [];
            }
        };

        $this->assertInstanceOf(PaginatorInterface::class, $paginator);
    }
}
