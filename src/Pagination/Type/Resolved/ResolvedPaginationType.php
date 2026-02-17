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
use ChamberOrchestra\PaginationBundle\Pagination\PaginationInterface;
use ChamberOrchestra\PaginationBundle\Pagination\Type\PaginationTypeInterface;
use ChamberOrchestra\PaginationBundle\Pagination\View\PaginationView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResolvedPaginationType
{
    private ?OptionsResolver $optionsResolver = null;

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

    /**
     * @param array<string, mixed> $options
     */
    public function buildPagination(PaginationConfigBuilder $builder, array $options): void
    {
        $this->innerType->buildPagination($builder, $options);
    }

    /**
     * @param array<string, mixed> $options
     */
    public function buildView(PaginationView $view, PaginationInterface $pagination, array $options): void
    {
        $this->innerType->buildView($view, $pagination, $options);
    }

    /**
     * @param array<string, mixed> $options
     */
    public function createBuilder(string $name, array $options): PaginationConfigBuilder
    {
        /** @var array<string, mixed> $resolved */
        $resolved = $this->getOptionsResolver()->resolve($options);

        return new PaginationConfigBuilder($this, $name, $resolved);
    }
}
