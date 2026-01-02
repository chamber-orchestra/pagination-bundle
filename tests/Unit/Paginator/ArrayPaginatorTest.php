<?php

declare(strict_types=1);

namespace Tests\Unit\Paginator;

use ArrayObject;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationInterface;
use ChamberOrchestra\PaginationBundle\Paginator\ArrayPaginator;
use PHPUnit\Framework\TestCase;

final class ArrayPaginatorTest extends TestCase
{
    public function testSupportsArrayAndArrayObject(): void
    {
        $paginator = new ArrayPaginator();

        $this->assertTrue($paginator->supports([]));
        $this->assertTrue($paginator->supports(new ArrayObject()));
        $this->assertFalse($paginator->supports('nope'));
    }

    public function testPaginateSlicesArray(): void
    {
        $paginator = new ArrayPaginator();
        $pagination = $this->createStub(PaginationInterface::class);
        $pagination->method('getPage')->willReturn(2);
        $pagination->method('getPerPageLimit')->willReturn(2);

        $result = $paginator->paginate([1, 2, 3, 4, 5], $pagination);

        $this->assertSame([3, 4], $result);
    }

    public function testPaginateSlicesArrayObject(): void
    {
        $paginator = new ArrayPaginator();
        $pagination = $this->createStub(PaginationInterface::class);
        $pagination->method('getPage')->willReturn(1);
        $pagination->method('getPerPageLimit')->willReturn(3);

        $result = $paginator->paginate(new ArrayObject([1, 2, 3, 4]), $pagination);

        $this->assertInstanceOf(ArrayObject::class, $result);
        $this->assertSame([1, 2, 3], $result->getArrayCopy());
    }

    public function testCount(): void
    {
        $paginator = new ArrayPaginator();

        $this->assertSame(3, $paginator->count([1, 2, 3]));
    }
}
