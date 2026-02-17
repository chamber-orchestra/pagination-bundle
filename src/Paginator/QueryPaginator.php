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
     * @param array<string, mixed> $options
     *
     * @return iterable<mixed>
     *
     * @throws \Exception
     */
    public function paginate(mixed $target, PaginationInterface $pagination, array $options = []): iterable
    {
        $query = $target instanceof QueryBuilder ? $target->getQuery() : $target;
        \assert($query instanceof Query);

        $query
            ->setFirstResult(PaginationUtil::getOffset($pagination))
            ->setMaxResults($pagination->getLimit());

        $fetchJoinCollection = false;
        if ($query->hasHint(self::HINT_FETCH_JOIN_COLLECTION)) {
            $fetchJoinCollection = (bool) $query->getHint(self::HINT_FETCH_JOIN_COLLECTION);
        }

        return $this->createPaginator($query, $fetchJoinCollection)->getIterator();
    }

    /**
     * @param array<string, mixed> $options
     */
    public function count(mixed $target, array $options = []): int
    {
        $query = $target instanceof QueryBuilder ? $target->getQuery() : $target;
        \assert($query instanceof Query);

        $fetchJoinCollection = false;
        if ($query->hasHint(self::HINT_FETCH_JOIN_COLLECTION)) {
            $fetchJoinCollection = (bool) $query->getHint(self::HINT_FETCH_JOIN_COLLECTION);
        }

        return \count($this->createPaginator($query, $fetchJoinCollection));
    }

    /**
     * @return Paginator<mixed>
     */
    protected function createPaginator(Query $query, bool $fetchJoinCollection): Paginator
    {
        return new Paginator($query, $fetchJoinCollection);
    }

    public function supports(mixed $target, ?PaginationInterface $pagination = null): bool
    {
        return \class_exists('Doctrine\ORM\Tools\Pagination\Paginator')
            && ($target instanceof Query || $target instanceof QueryBuilder);
    }
}
