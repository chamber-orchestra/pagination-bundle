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
     * @param array<string, mixed> $options
     *
     * @return iterable<mixed>
     */
    public function paginate(mixed $target, PaginationInterface $pagination, array $options = []): iterable
    {
        \assert($target instanceof EntityRepository);

        $criteria = $options['criteria'] ?? [];
        /** @var array<string, 'ASC'|'DESC'>|null $orderBy */
        $orderBy = $options['orderBy'] ?? null;

        if ($criteria instanceof Criteria) {
            $criteria
                ->setFirstResult(PaginationUtil::getOffset($pagination))
                ->setMaxResults($pagination->getLimit())
                ->orderBy($orderBy ?: []);

            return $target->matching($criteria);
        }

        \assert(\is_array($criteria));
        /** @var array<string, mixed> $arrayCriteria */
        $arrayCriteria = $criteria;

        return $target->findBy($arrayCriteria, $orderBy, $pagination->getLimit(), PaginationUtil::getOffset($pagination));
    }

    /**
     * @param array<string, mixed> $options
     */
    public function count(mixed $target, array $options = []): int
    {
        \assert($target instanceof EntityRepository);

        $criteria = $options['criteria'] ?? [];

        if ($criteria instanceof Criteria) {
            return $target->matching($criteria)->count();
        }

        \assert(\is_array($criteria));
        /** @var array<string, mixed> $arrayCriteria */
        $arrayCriteria = $criteria;

        return $target->count($arrayCriteria);
    }

    public function supports(mixed $target, ?PaginationInterface $pagination = null): bool
    {
        return $target instanceof EntityRepository;
    }
}
