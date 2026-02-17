<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\PaginationBundle\Doctrine\Cursor;

use ChamberOrchestra\PaginationBundle\Exception\LogicException;
use Doctrine\ORM\QueryBuilder;

readonly class CursorFieldResolver
{
    /**
     * @return array{cursor_field: string, cursor_getter: \Closure(object): mixed}
     */
    public function resolve(QueryBuilder $qb): array
    {
        /** @var list<class-string> $rootEntities */
        $rootEntities = $qb->getRootEntities();
        /** @var list<string> $rootAliases */
        $rootAliases = $qb->getRootAliases();

        if ([] === $rootEntities || [] === $rootAliases) {
            throw new LogicException('Cannot resolve cursor field: the QueryBuilder has no root entity. Call ->from() before using cursor pagination.');
        }

        $entity = $rootEntities[0];
        $alias = $rootAliases[0];

        $metadata = $qb->getEntityManager()->getClassMetadata($entity);
        $identifierFields = $metadata->getIdentifierFieldNames();

        if (1 !== \count($identifierFields)) {
            throw new LogicException(\sprintf('Cursor pagination requires a single identifier field, but entity "%s" has %d.', $entity, \count($identifierFields)));
        }

        $idField = $identifierFields[0];
        $type = $metadata->getTypeOfField($idField);

        if ('ulid' !== $type && 'uuid' !== $type) {
            throw new LogicException(\sprintf('Cursor pagination requires a time-sortable identifier (ulid, uuid v6, or uuid v7), but entity "%s" has type "%s".', $entity, $type ?? 'unknown'));
        }

        if ('uuid' === $type) {
            $this->assertTimeSortableUuid($entity, $idField);
        }

        return [
            'cursor_field' => \sprintf('%s.%s', $alias, $idField),
            'cursor_getter' => static fn (object $entity): mixed => $metadata->getFieldValue($entity, $idField),
        ];
    }

    /**
     * Verifies that a uuid-typed ID property is not explicitly typed as a non-time-sortable variant.
     *
     * Allows: Uuid (base), UuidV1, UuidV6, UuidV7.
     * Rejects: UuidV4, UuidV5 (not monotonically ordered).
     *
     * @param class-string $entity
     */
    private function assertTimeSortableUuid(string $entity, string $idField): void
    {
        try {
            $ref = new \ReflectionProperty($entity, $idField);
        } catch (\ReflectionException) {
            return; // cannot inspect — skip validation
        }

        $refType = $ref->getType();
        if (!$refType instanceof \ReflectionNamedType) {
            return;
        }

        $typeName = $refType->getName();
        $nonSortable = [
            'Symfony\Component\Uid\UuidV4',
            'Symfony\Component\Uid\UuidV5',
        ];

        if (\in_array($typeName, $nonSortable, true)) {
            throw new LogicException(\sprintf('Cursor pagination requires a time-sortable identifier (uuid v6 or uuid v7), but entity "%s" uses "%s" which is not monotonically ordered.', $entity, $typeName));
        }
    }
}
