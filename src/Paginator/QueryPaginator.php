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
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

class QueryPaginator extends AbstractPaginator
{
    public const string HINT_FETCH_JOIN_COLLECTION = 'pagination.fetch_join_collection';

    /**
     * @throws \Exception
     */
    public function paginate($target, PaginationInterface $pagination, array $options = []): iterable
    {
        if ($target instanceof QueryBuilder) {
            $target = $target->getQuery();
        }

        $target
            ->setFirstResult(PaginationUtil::getOffset($pagination))
            ->setMaxResults($pagination->getPerPageLimit());

        $fetchJoinCollection = false;
        if ($target->hasHint(self::HINT_FETCH_JOIN_COLLECTION)) {
            $fetchJoinCollection = (bool)$target->getHint(self::HINT_FETCH_JOIN_COLLECTION);
        }

        $paginator = new Paginator($target, $fetchJoinCollection);

        return $paginator->getIterator();
    }

    public function count($target, array $options = []): int
    {
        if ($target instanceof QueryBuilder) {
            $target = $target->getQuery();
        }

        $fetchJoinCollection = false;
        if ($target->hasHint(self::HINT_FETCH_JOIN_COLLECTION)) {
            $fetchJoinCollection = (bool)$target->getHint(self::HINT_FETCH_JOIN_COLLECTION);
        }

        $paginator = new Paginator($target, $fetchJoinCollection);

        return \count($paginator);
    }

    public function supports($target): bool
    {
        return \class_exists('Doctrine\ORM\Tools\Pagination\Paginator')
            && ($target instanceof Query || $target instanceof QueryBuilder);
    }
}
