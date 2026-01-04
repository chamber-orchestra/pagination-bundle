<?php

declare(strict_types=1);

namespace Tests\Unit\Repository;

use ChamberOrchestra\PaginationBundle\Exception\LogicException;
use ChamberOrchestra\PaginationBundle\PagingInterface;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationInterface;
use ChamberOrchestra\PaginationBundle\Repository\PaginationEntityRepositoryTrait;
use Doctrine\Common\Collections\Criteria;
use PHPUnit\Framework\TestCase;

final class PaginationEntityRepositoryTraitTest extends TestCase
{
    public function testListUsesListBy(): void
    {
        $object = new class() {
            use PaginationEntityRepositoryTrait;

            public array $listByArgs = [];

            public function listBy(Criteria|array|null $criteria = null, ?array $orderBy = null, PaginationInterface|int|null $pagination = null): iterable
            {
                $this->listByArgs = [$criteria, $orderBy, $pagination];

                return ['ok'];
            }
        };

        $result = $object->list(['id' => 'DESC'], 3);

        $this->assertSame(['ok'], $result);
        $this->assertSame([[], ['id' => 'DESC'], 3], $object->listByArgs);
    }

    public function testListByWithPaginationUsesPaging(): void
    {
        $pagination = $this->createStub(PaginationInterface::class);
        $criteria = ['status' => 'active'];
        $orderBy = ['id' => 'ASC'];

        $object = new class() {
            use PaginationEntityRepositoryTrait;

            public array $paginateArgs = [];

            public function paginate(mixed $target, PaginationInterface $pagination, array $options = []): iterable
            {
                $this->paginateArgs = [$target, $pagination, $options];

                return ['paged'];
            }
        };

        $result = $object->listBy($criteria, $orderBy, $pagination);

        $this->assertSame(['paged'], $result);
        $this->assertSame([$object, $pagination, ['criteria' => $criteria, 'orderBy' => $orderBy]], $object->paginateArgs);
    }

    public function testListByWithCriteriaUsesMatching(): void
    {
        $criteria = new Criteria();

        $object = new class() {
            use PaginationEntityRepositoryTrait;

            public ?Criteria $matchedCriteria = null;

            public function matching(Criteria $criteria): iterable
            {
                $this->matchedCriteria = $criteria;

                return ['matched'];
            }
        };

        $result = $object->listBy($criteria, ['id' => 'DESC'], 4);

        $this->assertSame(['matched'], $result);
        $this->assertSame($criteria, $object->matchedCriteria);
        $this->assertSame(['id' => 'DESC'], $criteria->getOrderings());
        $this->assertSame(4, $criteria->getMaxResults());
    }

    public function testListByWithCriteriaAndNullPaginationDoesNotLimit(): void
    {
        $criteria = new Criteria();

        $object = new class() {
            use PaginationEntityRepositoryTrait;

            public ?Criteria $matchedCriteria = null;

            public function matching(Criteria $criteria): iterable
            {
                $this->matchedCriteria = $criteria;

                return ['matched'];
            }
        };

        $result = $object->listBy($criteria, ['id' => 'ASC'], null);

        $this->assertSame(['matched'], $result);
        $this->assertSame($criteria, $object->matchedCriteria);
        $this->assertSame(['id' => 'ASC'], $criteria->getOrderings());
        $this->assertNull($criteria->getMaxResults());
    }

    public function testListByWithArrayCriteriaUsesFindBy(): void
    {
        $criteria = ['status' => 'active'];
        $orderBy = ['id' => 'DESC'];

        $object = new class() {
            use PaginationEntityRepositoryTrait;

            public array $findByArgs = [];

            public function findBy(...$args): array
            {
                $this->findByArgs = $args;

                return ['found'];
            }
        };

        $result = $object->listBy($criteria, $orderBy, 2);

        $this->assertSame(['found'], $result);
        $this->assertSame([$criteria, $orderBy, 2], $object->findByArgs);
    }

    public function testPaginateThrowsWhenPagingMissing(): void
    {
        $pagination = $this->createStub(PaginationInterface::class);

        $object = new class() {
            use PaginationEntityRepositoryTrait;

            public function callPaginate(mixed $target, PaginationInterface $pagination, array $options = []): iterable
            {
                return $this->paginate($target, $pagination, $options);
            }
        };

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('PagingInterface');

        $object->callPaginate('target', $pagination);
    }

    public function testPaginateUsesPaging(): void
    {
        $pagination = $this->createStub(PaginationInterface::class);
        $paging = $this->createMock(PagingInterface::class);
        $paging->expects($this->once())
            ->method('paginate')
            ->with('target', $pagination, ['order' => 'ASC'])
            ->willReturn(['paged']);

        $object = new class() {
            use PaginationEntityRepositoryTrait;

            public function callPaginate(mixed $target, PaginationInterface $pagination, array $options = []): iterable
            {
                return $this->paginate($target, $pagination, $options);
            }
        };

        $object->withPaging($paging);
        $result = $object->callPaginate('target', $pagination, ['order' => 'ASC']);

        $this->assertSame(['paged'], $result);
    }
}
