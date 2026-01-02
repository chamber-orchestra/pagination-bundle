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
    public function __construct(protected readonly PaginationConfigInterface $config)
    {
    }

    public function createView(): PaginationView
    {
        $type = $this->config->getType();
        $options = $this->config->getOptions();

        $view = $type->createView($this);
        $type->buildView($view, $this, $options);

        return $view;
    }

    public function getName(): string
    {
        return $this->config->getName();
    }

    public function getPage(): int
    {
        return $this->config->getPage();
    }

    public function getPerPageLimit(): int
    {
        return $this->config->getPerPageLimit();
    }
}
