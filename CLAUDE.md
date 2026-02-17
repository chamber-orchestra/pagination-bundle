# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

ChamberOrchestra Pagination Bundle is a Symfony bundle for paginating arrays, Doctrine ORM repositories, and Doctrine ORM queries. It features a type-based pagination factory with configurable options via Symfony OptionsResolver, built-in paginators for different data sources, cursor-based (keyset) pagination with automatic ULID cursor resolution, and Twig rendering helpers.

## Build and Test Commands

```bash
# Install dependencies
composer install

# Run all tests
composer test

# Run specific test file
vendor/bin/phpunit tests/Unit/Pagination/PaginationTest.php

# Run tests in specific directory
vendor/bin/phpunit tests/Unit/Paginator/

# Run single test method
vendor/bin/phpunit --filter testMethodName

# Static analysis (PHPStan at max level, src/ only)
composer analyse

# Code style check (dry-run)
composer cs-check

# Code style fix
vendor/bin/php-cs-fixer fix
```

## Architecture

### Pagination Factory

**PaginationFactory** (`src/Pagination/PaginationFactory.php`): Readonly class. Entry point for creating pagination objects. Looks up a `ResolvedPaginationType` from `PaginationRegistry`, calls `createBuilder()` to get a `PaginationConfigBuilder` with resolved options, then calls `buildPagination()` to populate the builder, and finally `getPagination()` to produce the concrete pagination object.

**PaginationRegistry** (`src/Pagination/PaginationRegistry.php`): Receives a `ServiceLocator` of `PaginationTypeInterface` services (tagged `pagination.type`). Wraps each in a `ResolvedPaginationType` on first access and caches the result.

**ResolvedPaginationType** (`src/Pagination/Type/Resolved/ResolvedPaginationType.php`): Wraps a `PaginationTypeInterface`. Lazily creates an `OptionsResolver`, delegates `configureOptions()` to the inner type once. `createBuilder()` resolves options through the resolver and returns a new `PaginationConfigBuilder`. Delegates `buildPagination()` and `buildView()` to the inner type.

### Pagination Types

**PaginationTypeInterface** (`src/Pagination/Type/PaginationTypeInterface.php`): Strategy interface with three methods: `configureOptions(OptionsResolver)` to define and validate options, `buildPagination(PaginationConfigBuilder, $options)` to populate the config builder, and `buildView(PaginationView, $pagination, $options)` to set view template variables.

**AbstractPaginationType** (`src/Pagination/Type/AbstractPaginationType.php`): Base class for page-based types. Configures common options: `page_parameter` (string, default `'page'`), `limit` (positive int, default 12), `page` (int|null, auto-read from request query if null), `extended` (bool, default false). The `page` normalizer reads from `RequestStack::getMainRequest()` when not explicitly provided. `buildPagination()` calls `setPosition()`, `setLimit()`, `setExtended()` on the builder. `buildView()` sets `pageParameter` and `limit` view vars.

**PaginationType** (`src/Pagination/Type/PaginationType.php`): Extends `AbstractPaginationType`. Basic next/prev navigation. View vars: `current`, `startPage`, `previous` (null if page 1), `next`.

**ExtendedPaginationType** (`src/Pagination/Type/ExtendedPaginationType.php`): Extends `AbstractPaginationType` with `extended` defaulting to true. View vars: `current`, `previous`, `next`, `pagesCount`, `elementsCount`.

**RangeType** (`src/Pagination/Type/RangeType.php`): Extends `AbstractPaginationType` with `extended` forced to true and `page_range` option (positive int, default 8). Calculates a sliding window of page numbers centered on the current page. View vars: `current`, `pagesCount`, `elementsCount`, `startPage`, `endPage`, `previous`, `next`, `pages` (array of page numbers), `pageParameter`, `limit`.

**CursorType** (`src/Pagination/Type/CursorType.php`): Standalone type (does not extend `AbstractPaginationType`). Options: `cursor` (string|null, auto-read from request query `cursor` param), `limit` (positive int, default 12). `buildPagination()` sets `setCursor(true)`, `setPosition(1)`, `setExtended(false)`. `buildView()` asserts `CursorPaginationInterface` and sets vars: `cursor`, `limit`, `next`, `previous` from `getNextCursor()`/`getPreviousCursor()`.

### Pagination Config Builder

**PaginationConfigBuilder** (`src/Pagination/PaginationConfigBuilder.php`): Mutable builder that collects `$position` (int), `$limit` (int), `$extended` (bool), and `$cursor` (bool) flags. `getPagination()` produces the concrete class: `CursorPagination` if cursor is true, `ExtendedPagination` if extended is true, otherwise `Pagination`. `getPaginationConfig()` returns a clone for immutable snapshots.

### Pagination Objects

**PaginationInterface** (`src/Pagination/PaginationInterface.php`): Contract with `getPosition(): int|string|null`, `getLimit(): int`, `getName(): string`, `createView(): PaginationView`.

**Pagination** (`src/Pagination/Pagination.php`): Base implementation. Delegates `getPosition()`, `getLimit()`, `getName()` to the stored `PaginationConfigBuilder`. `createView()` calls `ResolvedPaginationType::buildView()` via the config's type.

**ExtendedPaginationInterface** (`src/Pagination/ExtendedPaginationInterface.php`): Extends `PaginationInterface`, adds `getElementsCount(): int` and `setElementsCount(int): void`.

**ExtendedPagination** (`src/Pagination/ExtendedPagination.php`): Extends `Pagination`, implements `ExtendedPaginationInterface`. Stores a nullable `$elementsCount` set by the paginator during `Paging::paginate()`.

**CursorPaginationInterface** (`src/Pagination/CursorPaginationInterface.php`): Extends `PaginationInterface`, adds `setNextCursor(?string)`, `getNextCursor(): ?string`, `setPreviousCursor(?string)`, `getPreviousCursor(): ?string`.

**CursorPagination** (`src/Pagination/CursorPagination.php`): Extends `Pagination`, implements `CursorPaginationInterface`. Overrides `getPosition()` to return `options['cursor']` as `?string` instead of the builder's int position. Stores `$nextCursor` and `$previousCursor` set by `CursorQueryPaginator` during pagination.

**PaginationView** (`src/Pagination/View/PaginationView.php`): Simple data holder with a public `$vars` array populated by pagination types during `buildView()`.

### Pagination Utility

**PaginationUtil** (`src/Pagination/PaginationUtil.php`): Static utility. `getOffset()` computes `abs((int)position - 1) * limit`. `getPagesCount()` computes `ceil(elementsCount / limit)`, throws `LogicException` if pagination is not `ExtendedPaginationInterface`.

### Paging Layer

**PagingInterface** (`src/PagingInterface.php`): Contract with `paginate($target, PaginationInterface, $options): iterable`.

**Paging** (`src/Paging.php`): Readonly class. Main orchestrator. Validates limit is set, asks `PaginatorRegistry` for a supporting paginator (throws `RuntimeException` if none found), calls `count()` + `setElementsCount()` for `ExtendedPaginationInterface`, then delegates to `paginator->paginate()`.

### Paginators

**PaginatorInterface** (`src/Paginator/PaginatorInterface.php`): Contract with `supports($target, ?$pagination): bool`, `count($target, $options): int`, `paginate($target, $pagination, $options): iterable`.

**AbstractPaginator** (`src/Paginator/AbstractPaginator.php`): Empty abstract base class implementing `PaginatorInterface`.

**PaginatorRegistry** (`src/Paginator/PaginatorRegistry.php`): Readonly class. Receives `iterable<PaginatorInterface>` (tagged `pagination.paginator`). `getSupportedPaginator()` iterates paginators and returns the first one where `supports()` returns true.

**ArrayPaginator** (`src/Paginator/ArrayPaginator.php`): Supports `array` and `ArrayObject`. Uses `array_slice()` with offset from `PaginationUtil::getOffset()`. Returns `ArrayObject` when input is `ArrayObject`, plain array otherwise.

**EntityRepositoryPaginator** (`src/Paginator/EntityRepositoryPaginator.php`): Supports `EntityRepository`. Accepts `criteria` (array or `Criteria`) and `orderBy` options. For `Criteria`: calls `matching()` then sets `firstResult`/`maxResults`/`orderBy` on the criteria. For arrays: delegates to `findBy()`.

**QueryPaginator** (`src/Paginator/QueryPaginator.php`): Supports `Query` and `QueryBuilder` (when `Doctrine\ORM\Tools\Pagination\Paginator` exists). Sets `firstResult`/`maxResults` on the query. Respects `HINT_FETCH_JOIN_COLLECTION` query hint to control join collection behavior. Wraps results in `Doctrine\ORM\Tools\Pagination\Paginator`.

**CursorQueryPaginator** (`src/Paginator/CursorQueryPaginator.php`): Supports `QueryBuilder` + `CursorPaginationInterface`. Requires `cursor_field` and `cursor_getter` (Closure) in options. Applies cursor filter as a `WHERE` clause (using `>` or `<` depending on ASC/DESC order). Fetches `limit+1` results to detect next page existence. Sets `nextCursor` and `previousCursor` on the pagination object, then strips the extra result before returning.

### Doctrine Cursor Integration

**CursorFieldPaging** (`src/Doctrine/Cursor/CursorFieldPaging.php`): Readonly class implementing `PagingInterface`. Decorator around `Paging`. When the target is a `QueryBuilder`, the pagination is `CursorPaginationInterface`, and no `cursor_field` option is set, it auto-resolves cursor options via `CursorFieldResolver` and merges them into the options array. Otherwise delegates directly to inner `Paging`.

**CursorFieldResolver** (`src/Doctrine/Cursor/CursorFieldResolver.php`): Readonly class. Inspects a `QueryBuilder`'s root entity metadata. Validates the entity has a single identifier field of ULID type (throws `LogicException` otherwise). Returns `cursor_field` (aliased field name) and `cursor_getter` (closure reading field value via metadata).

### Twig Integration

**PaginationRuntime** (`src/Twig/PaginationRuntime.php`): Readonly Twig runtime extension. Delegates `render()` to `Processor`.

**Processor** (`src/Twig/Helper/Processor.php`): Renders a `PaginationView` using Twig's `Environment::render()`, merging `$view->vars` with optional `$viewOptions` via `array_replace_recursive`. Default template: `@ChamberOrchestraPagination/pagination/sliding.html.twig`.

### Repository Trait

**PaginationEntityRepositoryTrait** (`src/Repository/PaginationEntityRepositoryTrait.php`): Trait for Doctrine `EntityRepository`. Uses `PaginationAwareTrait` to inject `Paging`. Provides `list()` and `listBy()` methods that accept `PaginationInterface|int|null`. When given a `PaginationInterface`, delegates to `Paging::paginate()` with the repository as target. When given a `Criteria`, uses `matching()`. Otherwise falls back to `findBy()`.

### Helper Traits

**PaginationTrait** (`src/PaginationTrait.php`): For services with a `$container` property. Provides `createPagination($type, $options)` that fetches `PaginationFactory` from the container.

**PaginationAwareTrait** (`src/PaginationAwareTrait.php`): Stores a nullable `$paging` property with a `#[Required]` setter `withPaging()`.

### View Layer

**PaginatedView** (`src/View/PaginatedView.php`): Extends `DataView` from `chamber-orchestra/view-bundle`. Wraps paginated entries with pagination metadata extracted from `PaginationView::$vars`. Supports mapping via callable or class name. Used for API responses with pagination headers.

### Service Configuration

Services are autowired and autoconfigured via `src/Resources/config/services.php`. `PaginatorInterface` implementations are auto-tagged with `pagination.paginator`. `PaginationTypeInterface` implementations are auto-tagged with `pagination.type` (also via `ChamberOrchestraPaginationExtension::load()`). `PaginatorRegistry` receives paginators via `tagged_iterator`, `PaginationRegistry` receives types via `tagged_locator`. `PaginationFactory` is the only public service. `CursorFieldPaging` decorates `Paging` conditionally (only when `Doctrine\ORM\QueryBuilder` class exists).

## Testing

- **Unit tests**: `tests/Unit/` — mirror the `src/` structure, test individual classes in isolation with mocked dependencies
- **Integration tests**: `tests/Integrational/` — test bundle integration with Symfony and Doctrine
- **Test kernel**: `tests/Integrational/TestKernel.php` boots minimal Symfony with FrameworkBundle, TwigBundle, and ChamberOrchestraPaginationBundle
- **Fixtures**: `tests/Fixtures/Doctrine/DoctrineTestHelper.php` creates in-memory SQLite EntityManagers; `tests/Fixtures/Entity/` contains test entities (`Book` with auto-increment ID, `Article` with ULID)
- Doctrine tests may skip via `skipIfLazyGhostUnavailable()` on PHP < 8.4 without symfony/var-exporter

When writing tests, follow existing patterns: unit tests under `tests/Unit/` mirroring `src/` structure, integration tests under `tests/Integrational/` extending `KernelTestCase`.

## Code Style

- PHP 8.5+ with strict types (`declare(strict_types=1);`)
- PSR-4 autoloading: `ChamberOrchestra\PaginationBundle\` → `src/`, `Tests\` → `tests/`
- PER-CS 2.0 + Symfony ruleset enforced by php-cs-fixer
- Native function calls backslash-prefixed: `\count()`, `\array_map()`, `\sprintf()`
- No global namespace imports — use fully qualified or `use` statements
- Single quotes, short array syntax, alphabetical imports, trailing commas in multiline
- Readonly classes for immutable services and value objects

## Dependencies

- Requires PHP 8.5, Symfony 8.0 components (`dependency-injection`, `config`, `framework-bundle`, `runtime`, `options-resolver`)
- Optional: `doctrine/orm` 3.6+ and `doctrine/doctrine-bundle` for Doctrine paginators, `twig/twig` 3.x for Twig rendering, `chamber-orchestra/view-bundle` 8.0 for `PaginatedView`, `symfony/uid` for ULID cursor support
- Main branch is `main`
