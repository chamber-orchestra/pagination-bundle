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
        $resolver->setNormalizer('page_range', function (Options $options, $value) {
            $limit = \abs((int)$value);
            if (!$limit) {
                throw new InvalidOptionsException("Parameter 'page_range' must be a positive int.");
            }

            return $limit;
        });
    }

    public function buildView(PaginationView $view, PaginationInterface $pagination, array $options = []): void
    {
        /* @var ExtendedPaginationInterface $pagination */
        $pagesCount = PaginationUtil::getPagesCount($pagination);
        $current = $pagination->getPage();

        if ($pagesCount < $current) {
            $current = $pagesCount;
        }

        $pageRange = $options['page_range'];
        if ($pageRange > $pagesCount) {
            $pageRange = $pagesCount;
        }

        $delta = \ceil($pageRange / 2);

        if ($current - $delta > $pagesCount - $pageRange) {
            $pages = \range($pagesCount - $pageRange + 1, $pagesCount);
        } else {
            if ($current - $delta < 0) {
                $delta = $current;
            }

            $offset = $current - $delta;
            $pages = \range($offset + 1, $offset + $pageRange);
        }

        $proximity = \floor($pageRange / 2);
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
            'pages_count' => $pagesCount,
            'elements_count' => $pagination->getElementsCount(),

            'start_page' => $startPage,
            'end_page' => $endPage,
            //navigation
            'previous' => $current - 1 > 0 ? $current - 1 : null,
            'next' => $current + 1 <= $pagesCount ? $current + 1 : null,
            'pages' => $pages,
        ];
    }
}
