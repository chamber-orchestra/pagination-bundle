<?php

declare(strict_types=1);

namespace Tests\Integrational\Paginator;

use ChamberOrchestra\PaginationBundle\Pagination\PaginationInterface;
use ChamberOrchestra\PaginationBundle\Paginator\AbstractPaginator;
use ChamberOrchestra\PaginationBundle\Paginator\ArrayPaginator;
use ChamberOrchestra\PaginationBundle\Paginator\EntityRepositoryPaginator;
use ChamberOrchestra\PaginationBundle\Paginator\PaginatorInterface;
use ChamberOrchestra\PaginationBundle\Paginator\QueryPaginator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\VarExporter\ProxyHelper;
use Tests\Fixtures\Doctrine\DoctrineTestHelper;
use Tests\Fixtures\Entity\Book;

final class PaginatorIntegrationTest extends KernelTestCase
{
    public function testArrayPaginatorSlicesCollections(): void
    {
        $paginator = new ArrayPaginator();
        $this->assertInstanceOf(PaginatorInterface::class, $paginator);
        $this->assertInstanceOf(AbstractPaginator::class, $paginator);

        $pagination = $this->createStub(PaginationInterface::class);
        $pagination->method('getPosition')->willReturn(1);
        $pagination->method('getLimit')->willReturn(2);

        $this->assertSame([1, 2], $paginator->paginate([1, 2, 3], $pagination));
    }

    public function testEntityRepositoryPaginatorUsesDoctrineRepository(): void
    {
        $this->skipIfLazyGhostUnavailable();

        self::bootKernel();

        $entityManager = DoctrineTestHelper::createEntityManager();
        DoctrineTestHelper::seedBooks($entityManager, ['A', 'B', 'C']);

        $repository = $entityManager->getRepository(Book::class);

        $paginator = new EntityRepositoryPaginator();
        $pagination = $this->createStub(PaginationInterface::class);
        $pagination->method('getPosition')->willReturn(1);
        $pagination->method('getLimit')->willReturn(2);

        $result = $paginator->paginate($repository, $pagination, ['orderBy' => ['id' => 'ASC']]);

        $this->assertCount(2, $result);
        $this->assertSame(3, $paginator->count($repository));
    }

    public function testQueryPaginatorUsesDoctrineQuery(): void
    {
        $this->skipIfLazyGhostUnavailable();

        self::bootKernel();

        $entityManager = DoctrineTestHelper::createEntityManager();
        DoctrineTestHelper::seedBooks($entityManager, ['A', 'B', 'C', 'D']);

        $query = $entityManager->createQueryBuilder()
            ->select('b')
            ->from(Book::class, 'b')
            ->orderBy('b.id', 'ASC')
            ->getQuery();

        $pagination = $this->createStub(PaginationInterface::class);
        $pagination->method('getPosition')->willReturn(2);
        $pagination->method('getLimit')->willReturn(2);

        $paginator = new QueryPaginator();
        $result = $paginator->paginate($query, $pagination);

        $this->assertSame(['C', 'D'], \array_map(fn (Book $book) => $book->getTitle(), \iterator_to_array($result)));
        $this->assertSame(4, $paginator->count($query));
    }

    private function skipIfLazyGhostUnavailable(): void
    {
        if (PHP_VERSION_ID < 80400 && (!\class_exists(ProxyHelper::class) || !\method_exists(ProxyHelper::class, 'generateLazyProxy'))) {
            $this->markTestSkipped('symfony/var-exporter is required for Doctrine lazy ghosts.');
        }
    }
}
