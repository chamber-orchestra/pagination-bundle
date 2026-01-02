<?php

declare(strict_types=1);

namespace Tests\Unit\Paginator;

use ChamberOrchestra\PaginationBundle\Paginator\PaginatorInterface;
use PHPUnit\Framework\TestCase;

final class PaginatorInterfaceTest extends TestCase
{
    public function testInterfaceCanBeImplemented(): void
    {
        $paginator = new class() implements PaginatorInterface {
            public function supports($target): bool
            {
                return true;
            }

            public function count($target, array $options = []): int
            {
                return 1;
            }

            public function paginate($target, \ChamberOrchestra\PaginationBundle\Pagination\PaginationInterface $pagination, array $options = []): iterable
            {
                return [$target];
            }
        };

        $this->assertInstanceOf(PaginatorInterface::class, $paginator);
    }
}
