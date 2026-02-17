<?php

declare(strict_types=1);

namespace Tests\Unit\Paginator;

use ChamberOrchestra\PaginationBundle\Pagination\PaginationInterface;
use ChamberOrchestra\PaginationBundle\Paginator\EntityRepositoryPaginator;
use Doctrine\Common\Collections\AbstractLazyCollection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;

final class EntityRepositoryPaginatorTest extends TestCase
{
    public function testSupportsEntityRepository(): void
    {
        $repository = $this->createStub(EntityRepository::class);

        $paginator = new EntityRepositoryPaginator();

        $this->assertTrue($paginator->supports($repository));
        $this->assertFalse($paginator->supports('nope'));
    }

    public function testPaginateWithCriteriaUsesMatching(): void
    {
        $criteria = new Criteria();
        $collection = new class([1, 2, 3]) extends AbstractLazyCollection implements Selectable {
            public function __construct(private array $items)
            {
            }

            protected function doInitialize(): void
            {
                $this->collection = new ArrayCollection($this->items);
            }

            public function matching(Criteria $criteria): ArrayCollection
            {
                $this->initialize();

                return $this->collection->matching($criteria);
            }
        };

        $repository = $this->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['matching'])
            ->getMock();
        $repository->expects($this->once())
            ->method('matching')
            ->with($criteria)
            ->willReturn($collection);

        $pagination = $this->createStub(PaginationInterface::class);
        $pagination->method('getPosition')->willReturn(2);
        $pagination->method('getLimit')->willReturn(5);

        $paginator = new EntityRepositoryPaginator();
        $result = $paginator->paginate($repository, $pagination, ['criteria' => $criteria, 'orderBy' => ['id' => 'DESC']]);

        $this->assertSame($collection, $result);
        $this->assertSame(5, $criteria->getMaxResults());
        $this->assertSame(5, $criteria->getFirstResult());
        $this->assertSame(['id' => 'DESC'], $criteria->getOrderings());
    }

    public function testPaginateWithArrayCriteriaUsesFindBy(): void
    {
        $repository = $this->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['findBy'])
            ->getMock();

        $repository->expects($this->once())
            ->method('findBy')
            ->with(['status' => 'active'], ['id' => 'ASC'], 10, 0)
            ->willReturn([1, 2]);

        $pagination = $this->createStub(PaginationInterface::class);
        $pagination->method('getPosition')->willReturn(1);
        $pagination->method('getLimit')->willReturn(10);

        $paginator = new EntityRepositoryPaginator();
        $result = $paginator->paginate($repository, $pagination, ['criteria' => ['status' => 'active'], 'orderBy' => ['id' => 'ASC']]);

        $this->assertSame([1, 2], $result);
    }

    public function testCountWithCriteriaUsesMatching(): void
    {
        $criteria = new Criteria();
        $collection = new class([1, 2, 3]) extends AbstractLazyCollection implements Selectable {
            public function __construct(private array $items)
            {
            }

            protected function doInitialize(): void
            {
                $this->collection = new ArrayCollection($this->items);
            }

            public function matching(Criteria $criteria): ArrayCollection
            {
                $this->initialize();

                return $this->collection->matching($criteria);
            }
        };

        $repository = $this->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['matching'])
            ->getMock();
        $repository->expects($this->once())
            ->method('matching')
            ->with($criteria)
            ->willReturn($collection);

        $paginator = new EntityRepositoryPaginator();

        $this->assertSame(3, $paginator->count($repository, ['criteria' => $criteria]));
    }

    public function testCountWithArrayCriteriaUsesRepositoryCount(): void
    {
        $repository = $this->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['count'])
            ->getMock();

        $repository->expects($this->once())
            ->method('count')
            ->with(['status' => 'active'])
            ->willReturn(7);

        $paginator = new EntityRepositoryPaginator();

        $this->assertSame(7, $paginator->count($repository, ['criteria' => ['status' => 'active']]));
    }
}
