# ChamberOrchestra Pagination Bundle

Symfony bundle for building pagination metadata and paginating arrays, Doctrine repositories, and Doctrine queries. It ships with paginator services, pagination types, Twig rendering helpers, and a small filter builder.

## Features

- Pagination factory + registry with type resolution
- Paginators for arrays, Doctrine ORM repositories, and Doctrine ORM queries
- Extended pagination that can compute total pages
- Twig helper to render pagination views
- FilterBuilder to build Doctrine Criteria from arrays or forms

## Installation

Install via Composer:

```bash
composer require chamber-orchestra/pagination-bundle
```

Enable the bundle (if not using Symfony Flex auto-registration):

```php
// config/bundles.php
return [
    ChamberOrchestra\PaginationBundle\ChamberOrchestraPaginationBundle::class => ['all' => true],
];
```

## Dependencies

Runtime:

- PHP 8.4
- symfony/framework-bundle, symfony/dependency-injection, symfony/config, symfony/options-resolver
- chamber-orchestra/view-bundle

Optional (for Doctrine integration):

- doctrine/orm
- doctrine/doctrine-bundle

Twig rendering:

- twig/twig

## Basic Usage

Create pagination with the factory and paginate data with the paging service:

```php
use ChamberOrchestra\PaginationBundle\Paging;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationFactory;

final class BookController
{
    public function __construct(
        private Paging $paging,
        private PaginationFactory $paginationFactory,
    ) {
    }

    public function index(): array
    {
        $pagination = $this->paginationFactory->create('range', [
            'page' => 1,
            'per_page_limit' => 10,
            'extended' => true,
        ]);

        $items = ['a', 'b', 'c'];
        $result = $this->paging->paginate($items, $pagination);

        return [
            'data' => $result,
            'meta' => $pagination->createView()->vars,
        ];
    }
}
```

## Doctrine ORM Integration

EntityRepository pagination:

```php
use ChamberOrchestra\PaginationBundle\Paging;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationFactory;
use Doctrine\ORM\EntityRepository;

public function list(EntityRepository $repository, Paging $paging, PaginationFactory $factory): array
{
    $pagination = $factory->create('range', [
        'page' => 1,
        'per_page_limit' => 20,
        'extended' => true,
    ]);

    $items = $paging->paginate($repository, $pagination, [
        'criteria' => ['status' => 'active'],
        'orderBy' => ['id' => 'ASC'],
    ]);

    return iterator_to_array($items);
}
```

Query/QueryBuilder pagination:

```php
use ChamberOrchestra\PaginationBundle\Paging;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationFactory;
use Doctrine\ORM\EntityManagerInterface;
use Tests\Fixtures\Entity\Book;

public function list(EntityManagerInterface $em, Paging $paging, PaginationFactory $factory): array
{
    $query = $em->createQueryBuilder()
        ->select('b')
        ->from(Book::class, 'b')
        ->orderBy('b.id', 'ASC')
        ->getQuery();

    $pagination = $factory->create('range', [
        'page' => 2,
        'per_page_limit' => 10,
        'extended' => true,
    ]);

    $items = $paging->paginate($query, $pagination);

    return iterator_to_array($items);
}
```

## Twig Rendering

Use `render_pagination` to render a PaginationView:

```twig
{{ render_pagination(pagination_view) }}
```

Default templates live in `src/Resources/views/` and can be overridden in your app.

## FilterBuilder

Build Doctrine Criteria from array input or a Symfony form:

```php
use ChamberOrchestra\PaginationBundle\Filter\FilterBuilder;

$builder = new FilterBuilder();
$criteria = $builder->createWithArray([
    'name' => '^Jo',
    'status' => 'active$',
    'roles' => ['admin', 'user'],
]);
```

## Tests

Install dependencies and run the suite:

```bash
composer install
composer test
```
