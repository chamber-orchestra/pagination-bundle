<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\PaginationBundle\Pagination\Type;

use ChamberOrchestra\PaginationBundle\Pagination\PaginationInterface;
use ChamberOrchestra\PaginationBundle\Pagination\View\PaginationView;

class PaginationType extends AbstractPaginationType
{
    public function buildView(PaginationView $view, PaginationInterface $pagination, array $options = []): void
    {
        parent::buildView($view, $pagination, $options);

        $current = (int) $pagination->getPosition();

        /** @var array<string, mixed> $merged */
        $merged = \array_replace_recursive($view->vars, [
            'current' => $current,
            'start' => 1,
            'previous' => $current - 1 > 0 ? $current - 1 : null,
            'next' => $current + 1, // always set — total count unknown; consumers determine end-of-data from empty results
        ]);
        $view->vars = $merged;
    }
}
