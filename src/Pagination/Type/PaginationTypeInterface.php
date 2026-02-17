<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\PaginationBundle\Pagination\Type;

use ChamberOrchestra\PaginationBundle\Pagination\PaginationConfigBuilder;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationInterface;
use ChamberOrchestra\PaginationBundle\Pagination\View\PaginationView;
use Symfony\Component\OptionsResolver\OptionsResolver;

interface PaginationTypeInterface
{
    public function configureOptions(OptionsResolver $resolver): void;

    /**
     * @param array<string, mixed> $options
     */
    public function buildPagination(PaginationConfigBuilder $builder, array $options): void;

    /**
     * @param array<string, mixed> $options
     */
    public function buildView(PaginationView $view, PaginationInterface $pagination, array $options): void;
}
