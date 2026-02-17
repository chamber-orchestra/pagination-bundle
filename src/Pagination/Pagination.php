<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\PaginationBundle\Pagination;

use ChamberOrchestra\PaginationBundle\Pagination\View\PaginationView;

class Pagination implements PaginationInterface
{
    public function __construct(protected readonly PaginationConfigBuilder $config)
    {
    }

    public function createView(): PaginationView
    {
        $type = $this->config->getType();
        $options = $this->config->getOptions();

        $view = new PaginationView();
        $type->buildView($view, $this, $options);

        return $view;
    }

    public function getName(): string
    {
        return $this->config->getName();
    }

    public function getPosition(): int|string|null
    {
        return $this->config->getPosition();
    }

    public function getLimit(): int
    {
        return $this->config->getLimit();
    }
}
