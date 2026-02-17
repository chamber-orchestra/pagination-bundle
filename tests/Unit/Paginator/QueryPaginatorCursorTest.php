<?php

declare(strict_types=1);

namespace Tests\Unit\Paginator;

use ChamberOrchestra\PaginationBundle\Exception\InvalidArgumentException;
use ChamberOrchestra\PaginationBundle\Pagination\CursorPaginationInterface;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationInterface;
use ChamberOrchestra\PaginationBundle\Paginator\CursorQueryPaginator;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\VarExporter\ProxyHelper;
use Tests\Fixtures\Doctrine\DoctrineTestHelper;
use Tests\Fixtures\Entity\Book;

final class QueryPaginatorCursorTest extends TestCase
{
    private function skipIfLazyGhostUnavailable(): void
    {
        if (PHP_VERSION_ID < 80400 && (!\class_exists(ProxyHelper::class) || !\method_exists(ProxyHelper::class, 'generateLazyGhost'))) {
            $this->markTestSkipped('symfony/var-exporter is required for Doctrine lazy ghosts.');
        }
    }

    private function createCursorPagination(
        int $limit,
        ?string $cursor = null,
    ): CursorPaginationInterface {
        $pagination = $this->createMock(CursorPaginationInterface::class);
        $pagination->method('getLimit')->willReturn($limit);
        $pagination->method('getPosition')->willReturn($cursor);

        return $pagination;
    }

    public function testSupportsQueryBuilderWithCursorPagination(): void
    {
        $paginator = new CursorQueryPaginator();
        $qb = $this->createStub(QueryBuilder::class);
        $cursorPagination = $this->createStub(CursorPaginationInterface::class);
        $normalPagination = $this->createStub(PaginationInterface::class);

        $this->assertTrue($paginator->supports($qb, $cursorPagination));
        $this->assertFalse($paginator->supports($qb, $normalPagination));
        $this->assertFalse($paginator->supports($qb));
        $this->assertFalse($paginator->supports('nope', $cursorPagination));
    }

    public function testFirstPage(): void
    {
        $this->skipIfLazyGhostUnavailable();

        $em = DoctrineTestHelper::createEntityManager();
        DoctrineTestHelper::seedBooks($em, ['A', 'B', 'C', 'D', 'E']);

        $qb = $em->createQueryBuilder()
            ->select('b')
            ->from(Book::class, 'b')
            ->orderBy('b.id', 'ASC');

        $pagination = $this->createMock(CursorPaginationInterface::class);
        $pagination->method('getLimit')->willReturn(3);
        $pagination->method('getPosition')->willReturn(null);

        // First page: no previous cursor, next cursor set (has more items)
        $pagination->expects($this->once())->method('setPreviousCursor')->with(null);
        $pagination->expects($this->once())->method('setNextCursor')->with($this->callback(static fn (mixed $v): bool => \is_string($v)));

        $paginator = new CursorQueryPaginator();
        $results = $paginator->paginate($qb, $pagination, ['cursor_field' => 'b.id', 'cursor_getter' => static fn (Book $b): mixed => $b->getId()]);

        $titles = \array_map(fn (Book $b) => $b->getTitle(), $results);
        $this->assertSame(['A', 'B', 'C'], $titles);
    }

    public function testPaginationWithCursor(): void
    {
        $this->skipIfLazyGhostUnavailable();

        $em = DoctrineTestHelper::createEntityManager();
        DoctrineTestHelper::seedBooks($em, ['A', 'B', 'C', 'D', 'E']);

        $bookB = $em->getRepository(Book::class)->findOneBy(['title' => 'B']);
        $cursorId = (string) $bookB->getId();

        $qb = $em->createQueryBuilder()
            ->select('b')
            ->from(Book::class, 'b')
            ->orderBy('b.id', 'ASC');

        $pagination = $this->createMock(CursorPaginationInterface::class);
        $pagination->method('getLimit')->willReturn(2);
        $pagination->method('getPosition')->willReturn($cursorId);

        // Middle page: both cursors set
        $pagination->expects($this->once())->method('setPreviousCursor')->with($this->callback(static fn (mixed $v): bool => \is_string($v)));
        $pagination->expects($this->once())->method('setNextCursor')->with($this->callback(static fn (mixed $v): bool => \is_string($v)));

        $paginator = new CursorQueryPaginator();
        $results = $paginator->paginate($qb, $pagination, ['cursor_field' => 'b.id', 'cursor_getter' => static fn (Book $b): mixed => $b->getId()]);

        $titles = \array_map(fn (Book $b) => $b->getTitle(), $results);
        $this->assertSame(['C', 'D'], $titles);
    }

    public function testDescOrderPagination(): void
    {
        $this->skipIfLazyGhostUnavailable();

        $em = DoctrineTestHelper::createEntityManager();
        DoctrineTestHelper::seedBooks($em, ['A', 'B', 'C', 'D', 'E']);

        $bookD = $em->getRepository(Book::class)->findOneBy(['title' => 'D']);
        $cursorId = (string) $bookD->getId();

        $qb = $em->createQueryBuilder()
            ->select('b')
            ->from(Book::class, 'b')
            ->orderBy('b.id', 'DESC');

        $pagination = $this->createMock(CursorPaginationInterface::class);
        $pagination->method('getLimit')->willReturn(2);
        $pagination->method('getPosition')->willReturn($cursorId);

        // DESC with cursor: previous cursor set, next cursor set (has more)
        $pagination->expects($this->once())->method('setPreviousCursor')->with($this->callback(static fn (mixed $v): bool => \is_string($v)));
        $pagination->expects($this->once())->method('setNextCursor')->with($this->callback(static fn (mixed $v): bool => \is_string($v)));

        $paginator = new CursorQueryPaginator();
        $results = $paginator->paginate($qb, $pagination, ['cursor_field' => 'b.id', 'cursor_getter' => static fn (Book $b): mixed => $b->getId()]);

        $titles = \array_map(fn (Book $b) => $b->getTitle(), $results);
        $this->assertSame(['C', 'B'], $titles);
    }

    public function testLastPageHasNoNextCursor(): void
    {
        $this->skipIfLazyGhostUnavailable();

        $em = DoctrineTestHelper::createEntityManager();
        DoctrineTestHelper::seedBooks($em, ['A', 'B', 'C']);

        $bookB = $em->getRepository(Book::class)->findOneBy(['title' => 'B']);
        $cursorId = (string) $bookB->getId();

        $qb = $em->createQueryBuilder()
            ->select('b')
            ->from(Book::class, 'b')
            ->orderBy('b.id', 'ASC');

        $pagination = $this->createMock(CursorPaginationInterface::class);
        $pagination->method('getLimit')->willReturn(5);
        $pagination->method('getPosition')->willReturn($cursorId);

        // Last page: previous cursor set, next cursor null
        $pagination->expects($this->once())->method('setPreviousCursor')->with($this->callback(static fn (mixed $v): bool => \is_string($v)));
        $pagination->expects($this->once())->method('setNextCursor')->with(null);

        $paginator = new CursorQueryPaginator();
        $results = $paginator->paginate($qb, $pagination, ['cursor_field' => 'b.id', 'cursor_getter' => static fn (Book $b): mixed => $b->getId()]);

        $titles = \array_map(fn (Book $b) => $b->getTitle(), $results);
        $this->assertSame(['C'], $titles);
    }

    public function testEmptyResults(): void
    {
        $this->skipIfLazyGhostUnavailable();

        $em = DoctrineTestHelper::createEntityManager();

        $qb = $em->createQueryBuilder()
            ->select('b')
            ->from(Book::class, 'b')
            ->orderBy('b.id', 'ASC');

        $pagination = $this->createMock(CursorPaginationInterface::class);
        $pagination->method('getLimit')->willReturn(10);
        $pagination->method('getPosition')->willReturn(null);

        $pagination->expects($this->never())->method('setNextCursor');
        $pagination->expects($this->never())->method('setPreviousCursor');

        $paginator = new CursorQueryPaginator();
        $results = $paginator->paginate($qb, $pagination, ['cursor_field' => 'b.id', 'cursor_getter' => static fn (Book $b): mixed => $b->getId()]);

        $this->assertSame([], $results);
    }

    public function testThrowsWithoutCursorField(): void
    {
        $this->skipIfLazyGhostUnavailable();

        $em = DoctrineTestHelper::createEntityManager();
        $qb = $em->createQueryBuilder()
            ->select('b')
            ->from(Book::class, 'b')
            ->orderBy('b.id', 'ASC');

        $pagination = $this->createCursorPagination(10);

        $paginator = new CursorQueryPaginator();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "cursor_field" option is required');
        $paginator->paginate($qb, $pagination, []);
    }

    public function testCustomCursorGetter(): void
    {
        $this->skipIfLazyGhostUnavailable();

        $em = DoctrineTestHelper::createEntityManager();
        DoctrineTestHelper::seedBooks($em, ['A', 'B']);

        $qb = $em->createQueryBuilder()
            ->select('b')
            ->from(Book::class, 'b')
            ->orderBy('b.id', 'ASC');

        $pagination = $this->createMock(CursorPaginationInterface::class);
        $pagination->method('getLimit')->willReturn(10);
        $pagination->method('getPosition')->willReturn(null);

        // No more items, so next cursor is null; no previous page, so previous cursor is null
        $pagination->expects($this->once())->method('setPreviousCursor')->with(null);
        $pagination->expects($this->once())->method('setNextCursor')->with(null);

        $paginator = new CursorQueryPaginator();
        $paginator->paginate($qb, $pagination, [
            'cursor_field' => 'b.id',
            'cursor_getter' => static fn (Book $b): string => $b->getTitle(),
        ]);
    }
}
