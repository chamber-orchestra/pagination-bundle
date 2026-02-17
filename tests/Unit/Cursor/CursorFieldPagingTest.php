<?php

declare(strict_types=1);

namespace Tests\Unit\Cursor;

use ChamberOrchestra\PaginationBundle\Doctrine\Cursor\CursorFieldPaging;
use ChamberOrchestra\PaginationBundle\Doctrine\Cursor\CursorFieldResolver;
use ChamberOrchestra\PaginationBundle\Pagination\CursorPaginationInterface;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationInterface;
use ChamberOrchestra\PaginationBundle\PagingInterface;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;

final class CursorFieldPagingTest extends TestCase
{
    public function testAutoResolvesCursorField(): void
    {
        $qb = $this->createMock(QueryBuilder::class);
        $pagination = $this->createMock(CursorPaginationInterface::class);

        $getter = static fn (object $entity): mixed => null;
        $resolver = $this->createMock(CursorFieldResolver::class);
        $resolver->expects($this->once())
            ->method('resolve')
            ->with($qb)
            ->willReturn(['cursor_field' => 'a.id', 'cursor_getter' => $getter]);

        $inner = $this->createMock(PagingInterface::class);
        $inner->expects($this->once())
            ->method('paginate')
            ->with($qb, $pagination, $this->callback(function (array $options) use ($getter): bool {
                return 'a.id' === $options['cursor_field'] && $getter === $options['cursor_getter'];
            }))
            ->willReturn([]);

        $paging = new CursorFieldPaging($inner, $resolver);
        $paging->paginate($qb, $pagination);
    }

    public function testSkipsWhenCursorFieldAlreadySet(): void
    {
        $qb = $this->createMock(QueryBuilder::class);
        $pagination = $this->createMock(CursorPaginationInterface::class);

        $resolver = $this->createMock(CursorFieldResolver::class);
        $resolver->expects($this->never())->method('resolve');

        $inner = $this->createMock(PagingInterface::class);
        $inner->expects($this->once())
            ->method('paginate')
            ->with($qb, $pagination, ['cursor_field' => 'b.id'])
            ->willReturn([]);

        $paging = new CursorFieldPaging($inner, $resolver);
        $paging->paginate($qb, $pagination, ['cursor_field' => 'b.id']);
    }

    public function testSkipsForNonCursorPagination(): void
    {
        $qb = $this->createMock(QueryBuilder::class);
        $pagination = $this->createMock(PaginationInterface::class);

        $resolver = $this->createMock(CursorFieldResolver::class);
        $resolver->expects($this->never())->method('resolve');

        $inner = $this->createMock(PagingInterface::class);
        $inner->expects($this->once())
            ->method('paginate')
            ->with($qb, $pagination, [])
            ->willReturn([]);

        $paging = new CursorFieldPaging($inner, $resolver);
        $paging->paginate($qb, $pagination);
    }

    public function testSkipsForNonQueryBuilderTarget(): void
    {
        $target = ['item1', 'item2'];
        $pagination = $this->createMock(CursorPaginationInterface::class);

        $resolver = $this->createMock(CursorFieldResolver::class);
        $resolver->expects($this->never())->method('resolve');

        $inner = $this->createMock(PagingInterface::class);
        $inner->expects($this->once())
            ->method('paginate')
            ->with($target, $pagination, [])
            ->willReturn([]);

        $paging = new CursorFieldPaging($inner, $resolver);
        $paging->paginate($target, $pagination);
    }
}
