<?php

declare(strict_types=1);

namespace Tests\Unit\Paginator;

use ChamberOrchestra\PaginationBundle\Pagination\PaginationInterface;
use ChamberOrchestra\PaginationBundle\Paginator\QueryPaginator;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\VarExporter\ProxyHelper;
use Tests\Fixtures\Doctrine\DoctrineTestHelper;
use Tests\Fixtures\Entity\Book;

final class QueryPaginatorTest extends TestCase
{
    public function testSupportsQueryAndQueryBuilder(): void
    {
        $paginator = new QueryPaginator();

        $query = $this->createStub(Query::class);
        $builder = $this->createStub(QueryBuilder::class);

        $this->assertTrue($paginator->supports($query));
        $this->assertTrue($paginator->supports($builder));
        $this->assertFalse($paginator->supports('nope'));
    }

    public function testPaginateWithQueryBuilder(): void
    {
        $this->skipIfLazyGhostUnavailable();

        $entityManager = DoctrineTestHelper::createEntityManager();
        DoctrineTestHelper::seedBooks($entityManager, ['A', 'B', 'C', 'D']);

        $builder = $entityManager->createQueryBuilder()
            ->select('b')
            ->from(Book::class, 'b')
            ->orderBy('b.id', 'ASC');

        $pagination = $this->createStub(PaginationInterface::class);
        $pagination->method('getPosition')->willReturn(2);
        $pagination->method('getLimit')->willReturn(2);

        $paginator = new QueryPaginator();

        $result = $paginator->paginate($builder, $pagination);

        $this->assertSame(['C', 'D'], \array_map(fn (Book $book) => $book->getTitle(), \iterator_to_array($result)));
    }

    public function testCountWithQuery(): void
    {
        $this->skipIfLazyGhostUnavailable();

        $entityManager = DoctrineTestHelper::createEntityManager();
        DoctrineTestHelper::seedBooks($entityManager, ['A', 'B', 'C']);

        $query = $entityManager->createQueryBuilder()
            ->select('b')
            ->from(Book::class, 'b')
            ->getQuery();

        $pagination = $this->createStub(PaginationInterface::class);
        $pagination->method('getPosition')->willReturn(1);
        $pagination->method('getLimit')->willReturn(2);

        $paginator = new QueryPaginator();

        $this->assertSame(3, $paginator->count($query));

        $result = $paginator->paginate($query, $pagination);
        $this->assertCount(2, \iterator_to_array($result));
    }

    private function skipIfLazyGhostUnavailable(): void
    {
        if (PHP_VERSION_ID < 80400 && (!\class_exists(ProxyHelper::class) || !\method_exists(ProxyHelper::class, 'generateLazyGhost'))) {
            $this->markTestSkipped('symfony/var-exporter is required for Doctrine lazy ghosts.');
        }
    }

    public function testPaginateHonorsFetchJoinCollectionHint(): void
    {
        $this->skipIfLazyGhostUnavailable();

        $entityManager = DoctrineTestHelper::createEntityManager();
        DoctrineTestHelper::seedBooks($entityManager, ['A', 'B', 'C', 'D']);

        $query = $entityManager->createQueryBuilder()
            ->select('b')
            ->from(Book::class, 'b')
            ->orderBy('b.id', 'ASC')
            ->getQuery();

        $query->setHint(QueryPaginator::HINT_FETCH_JOIN_COLLECTION, false);

        $pagination = $this->createStub(PaginationInterface::class);
        $pagination->method('getPosition')->willReturn(1);
        $pagination->method('getLimit')->willReturn(2);

        $paginator = new class extends QueryPaginator {
            public ?bool $fetchJoinCollection = null;

            protected function createPaginator(Query $query, bool $fetchJoinCollection): Paginator
            {
                $this->fetchJoinCollection = $fetchJoinCollection;

                return parent::createPaginator($query, $fetchJoinCollection);
            }
        };

        $paginator->paginate($query, $pagination);

        $this->assertFalse($paginator->fetchJoinCollection);
    }

    public function testCountHonorsFetchJoinCollectionHint(): void
    {
        $this->skipIfLazyGhostUnavailable();

        $entityManager = DoctrineTestHelper::createEntityManager();
        DoctrineTestHelper::seedBooks($entityManager, ['A', 'B', 'C']);

        $query = $entityManager->createQueryBuilder()
            ->select('b')
            ->from(Book::class, 'b')
            ->getQuery();

        $query->setHint(QueryPaginator::HINT_FETCH_JOIN_COLLECTION, true);

        $paginator = new class extends QueryPaginator {
            public ?bool $fetchJoinCollection = null;

            protected function createPaginator(Query $query, bool $fetchJoinCollection): Paginator
            {
                $this->fetchJoinCollection = $fetchJoinCollection;

                return parent::createPaginator($query, $fetchJoinCollection);
            }
        };

        $this->assertSame(3, $paginator->count($query));
        $this->assertTrue($paginator->fetchJoinCollection);
    }
}
