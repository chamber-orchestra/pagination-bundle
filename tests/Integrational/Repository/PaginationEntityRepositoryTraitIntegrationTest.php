<?php

declare(strict_types=1);

namespace Tests\Integrational\Repository;

use ChamberOrchestra\PaginationBundle\Pagination\PaginationInterface;
use ChamberOrchestra\PaginationBundle\Paginator\EntityRepositoryPaginator;
use ChamberOrchestra\PaginationBundle\Paginator\PaginatorRegistry;
use ChamberOrchestra\PaginationBundle\Paging;
use ChamberOrchestra\PaginationBundle\Repository\PaginationEntityRepositoryTrait;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\VarExporter\ProxyHelper;
use Tests\Fixtures\Doctrine\DoctrineTestHelper;
use Tests\Fixtures\Entity\Book;

final class PaginationEntityRepositoryTraitIntegrationTest extends KernelTestCase
{
    public function testListByWithPaginationUsesPaging(): void
    {
        $this->skipIfLazyGhostUnavailable();

        self::bootKernel();

        $entityManager = DoctrineTestHelper::createEntityManager();
        DoctrineTestHelper::seedBooks($entityManager, ['A', 'B', 'C']);

        $repository = new class($entityManager, $entityManager->getClassMetadata(Book::class)) extends EntityRepository {
            use PaginationEntityRepositoryTrait;
        };

        $paging = new Paging(new PaginatorRegistry([new EntityRepositoryPaginator()]));
        $repository->withPaging($paging);

        $pagination = $this->createStub(PaginationInterface::class);
        $pagination->method('getPosition')->willReturn(2);
        $pagination->method('getLimit')->willReturn(1);

        $result = $repository->listBy([], ['id' => 'ASC'], $pagination);

        $this->assertSame(['B'], \array_map(static fn (Book $book) => $book->getTitle(), $result));
    }

    public function testListByWithCriteriaUsesMatchingAndLimit(): void
    {
        $this->skipIfLazyGhostUnavailable();

        self::bootKernel();

        $entityManager = DoctrineTestHelper::createEntityManager();
        DoctrineTestHelper::seedBooks($entityManager, ['A', 'B', 'C']);

        $repository = new class($entityManager, $entityManager->getClassMetadata(Book::class)) extends EntityRepository {
            use PaginationEntityRepositoryTrait;
        };

        $criteria = new Criteria();
        $result = $repository->listBy($criteria, ['id' => 'DESC'], 2);

        $books = $result instanceof \Traversable ? \iterator_to_array($result) : $result;
        $titles = \array_map(static fn (Book $book) => $book->getTitle(), $books);

        $this->assertSame(['C', 'B'], $titles);
        $this->assertSame(['id' => 'DESC'], $criteria->getOrderings());
        $this->assertSame(2, $criteria->getMaxResults());
    }

    public function testListUsesListByWithPagination(): void
    {
        $this->skipIfLazyGhostUnavailable();

        self::bootKernel();

        $entityManager = DoctrineTestHelper::createEntityManager();
        DoctrineTestHelper::seedBooks($entityManager, ['A', 'B', 'C']);

        $repository = new class($entityManager, $entityManager->getClassMetadata(Book::class)) extends EntityRepository {
            use PaginationEntityRepositoryTrait;
        };

        $paging = new Paging(new PaginatorRegistry([new EntityRepositoryPaginator()]));
        $repository->withPaging($paging);

        $pagination = $this->createStub(PaginationInterface::class);
        $pagination->method('getPosition')->willReturn(1);
        $pagination->method('getLimit')->willReturn(2);

        $result = $repository->list(['id' => 'ASC'], $pagination);

        $this->assertSame(['A', 'B'], \array_map(static fn (Book $book) => $book->getTitle(), $result));
    }

    private function skipIfLazyGhostUnavailable(): void
    {
        if (PHP_VERSION_ID < 80400 && (!\class_exists(ProxyHelper::class) || !\method_exists(ProxyHelper::class, 'generateLazyProxy'))) {
            $this->markTestSkipped('symfony/var-exporter is required for Doctrine lazy ghosts.');
        }
    }
}
