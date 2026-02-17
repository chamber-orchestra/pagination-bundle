[![PHP Composer](https://github.com/chamber-orchestra/pagination-bundle/actions/workflows/php.yml/badge.svg)](https://github.com/chamber-orchestra/pagination-bundle/actions/workflows/php.yml)
[![codecov](https://codecov.io/gh/chamber-orchestra/pagination-bundle/graph/badge.svg)](https://codecov.io/gh/chamber-orchestra/pagination-bundle)
[![PHPStan](https://img.shields.io/badge/PHPStan-max-brightgreen)](https://phpstan.org/)
[![Latest Stable Version](https://poser.pugx.org/chamber-orchestra/pagination-bundle/v)](https://packagist.org/packages/chamber-orchestra/pagination-bundle)
[![License](https://poser.pugx.org/chamber-orchestra/pagination-bundle/license)](https://packagist.org/packages/chamber-orchestra/pagination-bundle)
![PHP 8.5](https://img.shields.io/badge/PHP-8.5-blue?logo=php)
![Symfony 8](https://img.shields.io/badge/Symfony-8-purple?logo=symfony)

# ChamberOrchestra Pagination Bundle

Symfony bundle for paginating arrays, Doctrine ORM repositories, and Doctrine ORM queries. Ships with a type-based pagination factory, built-in paginators, and Twig rendering helpers.

## Features

- **Type-based pagination** — `PaginationType` (basic next/prev), `RangeType` (numbered page links with surrounding range), and `ExtendedPaginationType` (next/prev with total counts)
- **Cursor-based pagination** — `CursorType` for keyset pagination using a single cursor value with direction derived from QueryBuilder `orderBy`
- **Auto-resolved cursor fields** — ULID entities automatically resolve `cursor_field` and `cursor_getter` from Doctrine metadata
- **Built-in paginators** — `ArrayPaginator`, `EntityRepositoryPaginator`, `QueryPaginator`, and `CursorQueryPaginator`
- **Extended pagination** — optional total element count and page count computation for API metadata
- **Twig integration** — `render_pagination()` function with an overridable sliding template
- **Repository trait** — `PaginationEntityRepositoryTrait` adds `list` / `listBy` helpers to Doctrine repositories
- **Autowiring support** — all services are auto-configured and tagged via Symfony DI

## Installation

```bash
composer require chamber-orchestra/pagination-bundle
```

If you are not using Symfony Flex, register the bundle manually:

```php
// config/bundles.php
return [
    ChamberOrchestra\PaginationBundle\ChamberOrchestraPaginationBundle::class => ['all' => true],
];
```

### Optional dependencies

| Package | Purpose |
|---|---|
| `doctrine/orm` + `doctrine/doctrine-bundle` | Doctrine ORM pagination |
| `symfony/uid` | Auto-resolution of ULID cursor fields |
| `twig/twig` | Twig pagination rendering |

## Usage

### Array pagination

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
            'limit' => 10,
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

### Doctrine EntityRepository pagination

```php
use ChamberOrchestra\PaginationBundle\Paging;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationFactory;
use Doctrine\ORM\EntityRepository;

public function list(EntityRepository $repository, Paging $paging, PaginationFactory $factory): array
{
    $pagination = $factory->create('range', [
        'page' => 1,
        'limit' => 20,
        'extended' => true,
    ]);

    $items = $paging->paginate($repository, $pagination, [
        'criteria' => ['status' => 'active'],
        'orderBy' => ['id' => 'ASC'],
    ]);

    return iterator_to_array($items);
}
```

### Doctrine Query/QueryBuilder pagination

```php
use ChamberOrchestra\PaginationBundle\Paging;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationFactory;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Book;

public function list(EntityManagerInterface $em, Paging $paging, PaginationFactory $factory): array
{
    $query = $em->createQueryBuilder()
        ->select('b')
        ->from(Book::class, 'b')
        ->orderBy('b.id', 'ASC')
        ->getQuery();

    $pagination = $factory->create('range', [
        'page' => 2,
        'limit' => 10,
        'extended' => true,
    ]);

    $items = $paging->paginate($query, $pagination);

    return iterator_to_array($items);
}
```

### Cursor-based pagination

Cursor pagination uses a single `cursor` value instead of page numbers, providing stable results and efficient queries for large datasets. The pagination direction (forward/backward) is derived from the QueryBuilder's `orderBy` clause.

```php
use ChamberOrchestra\PaginationBundle\Paging;
use ChamberOrchestra\PaginationBundle\Pagination\PaginationFactory;
use ChamberOrchestra\PaginationBundle\Pagination\Type\CursorType;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Book;

public function list(
    Request $request,
    EntityManagerInterface $em,
    Paging $paging,
    PaginationFactory $factory,
): array {
    $pagination = $factory->create(CursorType::class, [
        'cursor' => $request->query->get('cursor'),
        'limit' => 20,
    ]);

    $qb = $em->createQueryBuilder()
        ->select('b')
        ->from(Book::class, 'b')
        ->orderBy('b.id', 'ASC');

    $result = $paging->paginate($qb, $pagination, [
        'cursor_field' => 'b.id',
        'cursor_getter' => static fn (Book $book): mixed => $book->getId(),
    ]);

    return [
        'data' => $result,
        'meta' => $pagination->createView()->vars,
        // {
        //   "cursor": "42",
        //   "limit": 20,
        //   "next": "62",
        //   "previous": "43"
        // }
    ];
}
```

**Auto-resolved cursor fields (ULID entities)** — for entities with a ULID primary key, `cursor_field` and `cursor_getter` are auto-resolved from Doctrine metadata. No options needed:

```php
// Entity with #[ORM\Column(type: 'ulid')] identifier — just pass the QueryBuilder
$result = $paging->paginate($qb, $pagination);
```

This is handled by `CursorFieldPaging`, a decorator around `Paging` that is automatically registered when `doctrine/orm` is available. It inspects the QueryBuilder's root entity metadata and resolves the ULID identifier field and getter.

**Reading cursor from request automatically** — when the `cursor` option is omitted, `CursorType` reads it from the `cursor` request query parameter:

```php
// GET /books?cursor=42
$pagination = $factory->create(CursorType::class, [
    'limit' => 20,
    // 'cursor' is read from ?cursor= automatically
]);
```

**Cursor presence indicates page availability** — `getNextCursor()` returns null when there is no next page, and a cursor string when there is. Same for `getPreviousCursor()`.

### Twig rendering (optional)

```twig
{{ render_pagination(pagination_view) }}
```

Default templates are in `src/Resources/views/` and can be overridden in your application.

## Pagination types

| Type | Description | View vars |
|---|---|---|
| `pagination` | Basic next/previous navigation | `current`, `startPage`, `previous`, `next` |
| `range` | Numbered page links with configurable range | `current`, `pagesCount`, `elementsCount`, `startPage`, `endPage`, `previous`, `next`, `pages`, `pageParameter`, `limit` |
| `ExtendedPaginationType` | Next/previous with total counts | `current`, `previous`, `next`, `pagesCount`, `elementsCount` |
| `CursorType` | Cursor-based (keyset) pagination | `cursor`, `limit`, `next`, `previous` |

The `pagination`, `range`, and `ExtendedPaginationType` types accept `page`, `limit` (default 12), `page_parameter`, and `extended` options. The `range` type additionally accepts `page_range` (default 8).

The `CursorType` accepts `cursor` (?string), and `limit` (int, default 12). It requires a `QueryBuilder` target with an `orderBy` clause, and the `cursor_field` + `cursor_getter` (`\Closure`) paginator options (auto-resolved for ULID entities).

## Development

```bash
composer install
composer test        # PHPUnit
composer analyse     # PHPStan (level max)
composer cs-check    # PHP-CS-Fixer (dry-run)
```

## License

MIT
