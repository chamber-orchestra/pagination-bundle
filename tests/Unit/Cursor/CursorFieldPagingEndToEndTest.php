<?php

declare(strict_types=1);

namespace Tests\Unit\Cursor;

use ChamberOrchestra\PaginationBundle\Doctrine\Cursor\CursorFieldPaging;
use ChamberOrchestra\PaginationBundle\Doctrine\Cursor\CursorFieldResolver;
use ChamberOrchestra\PaginationBundle\Pagination\CursorPaginationInterface;
use ChamberOrchestra\PaginationBundle\Paginator\CursorQueryPaginator;
use ChamberOrchestra\PaginationBundle\Paginator\PaginatorRegistry;
use ChamberOrchestra\PaginationBundle\Paging;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\VarExporter\ProxyHelper;
use Tests\Fixtures\Doctrine\DoctrineTestHelper;
use Tests\Fixtures\Entity\Article;

final class CursorFieldPagingEndToEndTest extends TestCase
{
    private function skipIfLazyGhostUnavailable(): void
    {
        if (PHP_VERSION_ID < 80400 && (!\class_exists(ProxyHelper::class) || !\method_exists(ProxyHelper::class, 'generateLazyGhost'))) {
            $this->markTestSkipped('symfony/var-exporter is required for Doctrine lazy ghosts.');
        }
    }

    public function testAutoResolvedCursorFieldWithRealDb(): void
    {
        $this->skipIfLazyGhostUnavailable();

        $em = DoctrineTestHelper::createEntityManagerWithUlid();

        $ulid1 = new Ulid('01000000000000000000000001');
        $ulid2 = new Ulid('01000000000000000000000002');
        $ulid3 = new Ulid('01000000000000000000000003');

        DoctrineTestHelper::seedArticles($em, [
            ['title' => 'First', 'id' => $ulid1],
            ['title' => 'Second', 'id' => $ulid2],
            ['title' => 'Third', 'id' => $ulid3],
        ]);

        $qb = $em->createQueryBuilder()
            ->select('a')
            ->from(Article::class, 'a')
            ->orderBy('a.id', 'ASC');

        $pagination = $this->createMock(CursorPaginationInterface::class);
        $pagination->method('getLimit')->willReturn(2);
        $pagination->method('getPosition')->willReturn(null);

        // First page: no previous cursor, next cursor set (has more items)
        $pagination->expects($this->once())->method('setPreviousCursor')->with(null);
        $pagination->expects($this->once())->method('setNextCursor')->with($this->callback(static fn (mixed $v): bool => \is_string($v)));

        $registry = new PaginatorRegistry([new CursorQueryPaginator()]);
        $inner = new Paging($registry);
        $resolver = new CursorFieldResolver();
        $paging = new CursorFieldPaging($inner, $resolver);

        // No cursor_field passed — should be auto-resolved
        $results = $paging->paginate($qb, $pagination);

        $titles = \array_map(fn (Article $a) => $a->getTitle(), $results);
        $this->assertSame(['First', 'Second'], $titles);
    }
}
