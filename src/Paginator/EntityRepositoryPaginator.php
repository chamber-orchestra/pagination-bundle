<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\PaginationBundle\Paginator;

use ChamberOrchestra\PaginationBundle\Pagination\PaginationInterface;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationUtil;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository;

class EntityRepositoryPaginator extends AbstractPaginator
{
    /**
     * @param EntityRepository $target
     */
    public function paginate($target, PaginationInterface $pagination, array $options = []): iterable
    {
        $criteria = [];
        if (isset($options['criteria'])) {
            $criteria = $options['criteria'];
        }

        $orderBy = null;
        if (isset($options['orderBy'])) {
            $orderBy = $options['orderBy'];
        }

        if ($criteria instanceof Criteria) {
            $collection = $target->matching($criteria);

            $criteria
                ->setFirstResult(PaginationUtil::getOffset($pagination))
                ->setMaxResults($pagination->getPerPageLimit())
                ->orderBy($orderBy ?: []);

            return $collection;
        }

        return $target->findBy($criteria, $orderBy, $pagination->getPerPageLimit(), PaginationUtil::getOffset($pagination));
    }

    public function count($target, array $options = []): int
    {
        $criteria = [];
        if (isset($options['criteria'])) {
            $criteria = $options['criteria'];
        }

        if ($criteria instanceof Criteria) {
            $collection = $target->matching($criteria);

            return $collection->count();
        }

        return $target->count($criteria);
    }

    public function supports($target): bool
    {
        return $target instanceof EntityRepository;
    }
}
