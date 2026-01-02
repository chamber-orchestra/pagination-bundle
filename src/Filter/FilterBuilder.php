<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\PaginationBundle\Filter;

use Doctrine\Common\Collections\Criteria;
use Symfony\Component\Form\FormInterface;

class FilterBuilder
{
    public function createWithForm(FormInterface $form, array $mappings = []): Criteria
    {
        if ($form->isSubmitted() && $form->isValid()) {
            return $this->createWithArray($form->getData(), $mappings);
        }

        return new Criteria();
    }

    public function createWithArray(array $data, array $mappings = []): Criteria
    {
        return self::build(new Criteria(), $data, $mappings);
    }

    public function build(Criteria $criteria, array $data = [], array $mappings = []): Criteria
    {
        foreach ($data as $name => $value) {
            if (null === $value) {
                continue;
            }

            if (isset($mappings[$name])) {
                if (!\is_callable($mappings[$name])) {
                    throw new \TypeError(\sprintf('Passed mapping for filter key %s must be callable.', $name));
                }

                \call_user_func($mappings[$name], $criteria, $value, $data);
                continue;
            }

            if (\is_iterable($value)) {
                $criteria->andWhere(Criteria::expr()->in($name, \is_array($value) ? $value : \iterator_to_array($value)));
                continue;
            }

            if (\is_object($value)) {
                $criteria->andWhere(Criteria::expr()->eq($name, $value));
                continue;
            }

            if (\is_bool($value)) {
                $criteria->andWhere(Criteria::expr()->eq($name, $value));
                continue;
            }

            if (\is_string($value)) {
                if ('^' === \mb_substr($value, 0, 1)) {
                    $criteria->andWhere(
                        Criteria::expr()->startsWith($name, \mb_substr($value, 1))
                    );
                    continue;
                }

                if ('$' === \mb_substr($value, -1, 1)) {
                    $criteria->andWhere(Criteria::expr()->endsWith($name, \mb_substr($value, 0, -1)));
                    continue;
                }
            }

            if (\is_string($value) && $value[-1] === '$') {
                $criteria->andWhere(Criteria::expr()->endsWith($name, $value));
                continue;
            }

            $criteria->andWhere(Criteria::expr()->contains($name, $value));
        }

        return $criteria;
    }
}