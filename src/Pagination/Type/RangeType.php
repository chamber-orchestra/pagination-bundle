<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\PaginationBundle\Pagination\Type;

use ChamberOrchestra\PaginationBundle\Exception\InvalidOptionsException;
use ChamberOrchestra\PaginationBundle\Pagination\ExtendedPaginationInterface;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationInterface;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationUtil;
use ChamberOrchestra\PaginationBundle\Pagination\View\PaginationView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Pagination type that produces numbered page links with a configurable sliding range.
 *
 * Primarily intended for Twig templates where users navigate via page numbers.
 */
class RangeType extends AbstractPaginationType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'page_range' => 8,
            'extended' => true,
        ]);
        // Range Type needs to know the elements amount
        $resolver->setAllowedValues('extended', true);

        $resolver->setAllowedTypes('page_range', 'int');
        $resolver->setNormalizer('page_range', function (Options $options, int $value) {
            $limit = \abs($value);
            if (!$limit) {
                throw new InvalidOptionsException("Parameter 'page_range' must be a positive int.");
            }

            return $limit;
        });
    }

    public function buildView(PaginationView $view, PaginationInterface $pagination, array $options = []): void
    {
        if (!$pagination instanceof ExtendedPaginationInterface) {
            throw new \LogicException(self::class.' requires an ExtendedPaginationInterface.');
        }

        $pagesCount = PaginationUtil::getPagesCount($pagination);
        $current = (int) $pagination->getPosition();

        if ($pagesCount < $current) {
            $current = $pagesCount;
        }

        /** @var int $pageRange */
        $pageRange = $options['page_range'];
        if ($pageRange > $pagesCount) {
            $pageRange = $pagesCount;
        }

        $delta = (int) \ceil($pageRange / 2);

        if ($current - $delta > $pagesCount - $pageRange) {
            $pages = \range($pagesCount - $pageRange + 1, $pagesCount);
        } else {
            if ($current - $delta < 0) {
                $delta = $current;
            }

            $offset = $current - $delta;
            $pages = \range($offset + 1, $offset + $pageRange);
        }

        $proximity = (int) \floor($pageRange / 2);
        $startPage = $current - $proximity;
        $endPage = $current + $proximity;

        if ($startPage < 1) {
            $endPage = \min($endPage + (1 - $startPage), $pagesCount);
            $startPage = 1;
        }

        if ($endPage > $pagesCount) {
            $startPage = \max($startPage - ($endPage - $pagesCount), 1);
            $endPage = $pagesCount;
        }

        $view->vars = [
            'current' => $current,
            'pagesCount' => $pagesCount,
            'elementsCount' => $pagination->getElementsCount(),
            'start' => $startPage,
            'end' => $endPage,
            'previous' => $current - 1 > 0 ? $current - 1 : null,
            'next' => $current + 1 <= $pagesCount ? $current + 1 : null,
            'pages' => $pages,
            'parameter' => $options['parameter'],
            'limit' => $options['limit'],
        ];
    }
}
