<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\PaginationBundle\Pagination\Type\Resolved;

use ChamberOrchestra\PaginationBundle\Pagination\PaginationConfigBuilder;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationConfigBuilderInterface;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationInterface;
use ChamberOrchestra\PaginationBundle\Pagination\Type\PaginationTypeInterface;
use ChamberOrchestra\PaginationBundle\Pagination\View\PaginationView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResolvedPaginationType implements ResolvedPaginationTypeInterface
{
    private OptionsResolver|null $optionsResolver = null;

    public function __construct(private readonly PaginationTypeInterface $innerType)
    {
    }

    public function getOptionsResolver(): OptionsResolver
    {
        if (null === $this->optionsResolver) {
            $this->optionsResolver = new OptionsResolver();

            $this->innerType->configureOptions($this->optionsResolver);
        }

        return $this->optionsResolver;
    }

    public function buildPagination(PaginationConfigBuilderInterface $builder, array $options): void
    {
        $this->innerType->buildPagination($builder, $options);
    }

    public function createView(PaginationInterface $pagination): PaginationView
    {
        return new PaginationView();
    }

    public function buildView(PaginationView $view, PaginationInterface $pagination, array $options): void
    {
        $this->innerType->buildView($view, $pagination, $options);
        $this->innerType->finishView($view, $pagination, $options);
    }

    public function createBuilder(string $name, array $options): PaginationConfigBuilderInterface
    {
        return new PaginationConfigBuilder($this, $name, $this->getOptionsResolver()->resolve($options));
    }
}