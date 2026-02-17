<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\PaginationBundle\Repository;

use ChamberOrchestra\PaginationBundle\Exception\LogicException;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationInterface;
use ChamberOrchestra\PaginationBundle\PaginationAwareTrait;
use ChamberOrchestra\PaginationBundle\PagingInterface;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository;

/**
 * @mixin EntityRepository<object>
 */
trait PaginationEntityRepositoryTrait
{
    use PaginationAwareTrait;

    public function list(?array $orderBy = null, PaginationInterface|int|null $pagination = null): iterable
    {
        return $this->listBy([], $orderBy, $pagination);
    }

    public function listBy(Criteria|array|null $criteria = null, ?array $orderBy = null, PaginationInterface|int|null $pagination = null): iterable
    {
        if ($pagination instanceof PaginationInterface) {
            return $this->paginate($this, $pagination, [
                'criteria' => $criteria,
                'orderBy' => $orderBy,
            ]);
        }

        if ($criteria instanceof Criteria) {
            $criteria->orderBy($orderBy ?: [])->setMaxResults($pagination);

            return $this->matching($criteria);
        }

        return $this->findBy($criteria, $orderBy, $pagination);
    }

    protected function paginate(mixed $target, PaginationInterface $pagination, array $options = []): iterable
    {
        if (null === $this->paging) {
            throw new LogicException(\sprintf('To use pagination with EntityRepository you should create custom EntityRepository and inject %s as its argument.', PagingInterface::class));
        }

        return $this->paging->paginate($target, $pagination, $options);
    }
}
