<?php

declare(strict_types=1);

namespace Tests\Unit;

use ChamberOrchestra\PaginationBundle\Pagination\PaginationInterface;
use ChamberOrchestra\PaginationBundle\PagingInterface;
use PHPUnit\Framework\TestCase;

final class PagingInterfaceTest extends TestCase
{
    public function testPagingInterfaceCanBeImplemented(): void
    {
        $paging = new class implements PagingInterface {
            public function paginate(mixed $target, PaginationInterface $pagination, array $options = []): iterable
            {
                return [$target];
            }
        };

        $this->assertInstanceOf(PagingInterface::class, $paging);
    }
}
