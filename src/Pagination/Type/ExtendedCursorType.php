<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\PaginationBundle\Pagination\Type;

use ChamberOrchestra\PaginationBundle\Pagination\ExtendedPaginationInterface;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationConfigBuilder;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationInterface;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationUtil;
use ChamberOrchestra\PaginationBundle\Pagination\View\PaginationView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExtendedCursorType extends CursorType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'extended' => true,
        ]);
    }

    /**
     * @param array<string, mixed> $options
     */
    public function buildPagination(PaginationConfigBuilder $builder, array $options): void
    {
        parent::buildPagination($builder, $options);
        $builder->setExtended(true);
    }

    /**
     * @param array<string, mixed> $options
     */
    public function buildView(PaginationView $view, PaginationInterface $pagination, array $options): void
    {
        if (!$pagination instanceof ExtendedPaginationInterface) {
            throw new \LogicException(self::class.' requires an ExtendedPaginationInterface.');
        }

        parent::buildView($view, $pagination, $options);

        $pagesCount = PaginationUtil::getPagesCount($pagination);

        /** @var array<string, mixed> $merged */
        $merged = \array_replace_recursive($view->vars, [
            'pagesCount' => $pagesCount,
            'elementsCount' => $pagination->getElementsCount(),
        ]);
        $view->vars = $merged;
    }
}
