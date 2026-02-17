<?php

declare(strict_types=1);

namespace Tests\Unit\Pagination;

use ChamberOrchestra\PaginationBundle\PaginationAwareTrait;
use ChamberOrchestra\PaginationBundle\PagingInterface;
use PHPUnit\Framework\TestCase;

final class PaginationAwareTraitTest extends TestCase
{
    public function testWithPagingSetsProperty(): void
    {
        $object = new class {
            use PaginationAwareTrait;

            public function getPaging(): ?PagingInterface
            {
                return $this->paging;
            }
        };

        $paging = $this->createStub(PagingInterface::class);
        $object->withPaging($paging);

        $this->assertSame($paging, $object->getPaging());
    }
}
