<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\PaginationBundle\Pagination\Type\Normalizer;

use ChamberOrchestra\PaginationBundle\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Options;

final class LimitNormalizer
{
    private function __construct()
    {
    }

    public static function normalize(Options $options, int $value): int
    {
        $limit = \abs($value);
        if (!$limit) {
            throw new InvalidOptionsException("Parameter 'limit' must be a positive int.");
        }

        return $limit;
    }
}
