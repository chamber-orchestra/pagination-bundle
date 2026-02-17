<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\PaginationBundle\View;

use ChamberOrchestra\PaginationBundle\Pagination\PaginationInterface;
use ChamberOrchestra\ViewBundle\View\DataView;
use ChamberOrchestra\ViewBundle\View\ViewInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PaginatedView extends DataView
{
    /** @var array<string, mixed> */
    public array $metadata;

    /**
     * @param iterable<mixed> $entries
     */
    public function __construct(
        iterable $entries,
        PaginationInterface $pagination,
        callable|string|null $map = null,
    ) {
        if (\is_string($map)) {
            if (!\class_exists($map)) {
                throw new \RuntimeException(\sprintf('Mapping class %s does not exists', $map));
            }
            $map = static fn (object $v) => new $map($v);
        }

        parent::__construct(\array_values(\array_map($map ?? [static::class, 'map'], \is_array($entries) ? $entries : \iterator_to_array($entries))));
        $this->metadata = $pagination->createView()->vars;
    }

    public function getHeaders(): array
    {
        $headers = [];
        foreach ($this->metadata as $key => $value) {
            $replaced = \preg_replace('/[A-Z]([A-Z](?![a-z]))*/', '-$0', $key) ?? $key;
            $key = \trim(\ucwords($replaced), '-');
            $headers['Pagination-'.$key] = $value;
            $headers['X-Pagination-'.$key] = $value;
        }

        $filtered = [];
        foreach ($headers as $key => $value) {
            if (!\is_scalar($value)) {
                continue;
            }

            $filtered[$key] = \is_bool($value) ? ($value ? '1' : '0') : (string) $value;
        }

        return parent::getHeaders() + $filtered;
    }

    /**
     * @param array<mixed>|object $value
     */
    protected static function map(array|object $value): ViewInterface
    {
        throw new \RuntimeException(\sprintf('%s should be defined or mapping closure should be passed for value %s', __METHOD__, \get_debug_type($value)));
    }

    public function normalize(NormalizerInterface $normalizer, ?string $format = null, array $context = []): array|string|int|float|bool
    {
        /** @var array<mixed>|string|int|float|bool $result */
        $result = $normalizer->normalize([
            'data' => $this->data,
            'metadata' => \array_filter($this->metadata),
        ], $format, $context);

        return $result;
    }
}
