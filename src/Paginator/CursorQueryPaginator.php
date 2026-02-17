<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\PaginationBundle\Paginator;

use ChamberOrchestra\PaginationBundle\Exception\InvalidArgumentException;
use ChamberOrchestra\PaginationBundle\Pagination\CursorPaginationInterface;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationInterface;
use Doctrine\ORM\QueryBuilder;

class CursorQueryPaginator extends AbstractPaginator
{
    public function supports(mixed $target, ?PaginationInterface $pagination = null): bool
    {
        return $target instanceof QueryBuilder && $pagination instanceof CursorPaginationInterface;
    }

    public function count(mixed $target, array $options = []): int
    {
        throw new \BadMethodCallException('Cursor pagination does not support counting.');
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return list<object>
     */
    public function paginate(mixed $target, PaginationInterface $pagination, array $options = []): iterable
    {
        if (!$target instanceof QueryBuilder || !$pagination instanceof CursorPaginationInterface) {
            throw new InvalidArgumentException('CursorQueryPaginator requires a QueryBuilder target and CursorPaginationInterface pagination.');
        }

        if (!isset($options['cursor_field'])) {
            throw new InvalidArgumentException('The "cursor_field" option is required for cursor pagination.');
        }

        if (!isset($options['cursor_getter']) || !$options['cursor_getter'] instanceof \Closure) {
            throw new InvalidArgumentException('The "cursor_getter" option must be a Closure for cursor pagination.');
        }

        /** @var string $cursorField */
        $cursorField = $options['cursor_field'];
        /** @var \Closure(object): mixed $cursorGetter */
        $cursorGetter = $options['cursor_getter'];
        $limit = $pagination->getLimit();
        $rawPosition = $pagination->getPosition();
        $cursor = \is_string($rawPosition) ? $rawPosition : null;

        $this->applyCursor($target, $cursorField, $cursor, $limit);

        /** @var list<object> $results */
        $results = $target->getQuery()->getResult();
        $hasMore = \count($results) > $limit;

        if ($hasMore) {
            \array_pop($results);
        }

        if (\count($results) > 0) {
            $first = $results[0];
            $last = $results[\count($results) - 1];

            /** @var \Stringable|string $firstId */
            $firstId = $cursorGetter($first);
            /** @var \Stringable|string $lastId */
            $lastId = $cursorGetter($last);

            $pagination->setPreviousCursor(null !== $cursor ? (string) $firstId : null);
            $pagination->setNextCursor($hasMore ? (string) $lastId : null);
        }

        return $results;
    }

    private function applyCursor(QueryBuilder $qb, string $field, ?string $cursor, int $limit): void
    {
        if (null !== $cursor) {
            $isDesc = $this->isDescOrder($qb, $field);
            $expr = $isDesc
                ? $qb->expr()->lt($field, ':cursor')
                : $qb->expr()->gt($field, ':cursor');

            $qb
                ->andWhere($expr)
                ->setParameter('cursor', $cursor);
        }

        $qb->setMaxResults($limit + 1);
    }

    private function isDescOrder(QueryBuilder $qb, string $field): bool
    {
        /** @var \Doctrine\ORM\Query\Expr\OrderBy[] $orderByParts */
        $orderByParts = $qb->getDQLPart('orderBy');

        foreach ($orderByParts as $orderBy) {
            foreach ($orderBy->getParts() as $part) {
                if (\str_starts_with($part, $field.' ')) {
                    return \str_ends_with($part, ' DESC');
                }
            }
        }

        return false;
    }
}
