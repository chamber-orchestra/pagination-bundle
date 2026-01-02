# Repository Guidelines

## Project Structure & Module Organization
Source code lives in `src/` under the `ChamberOrchestra\PaginationBundle` namespace. Core pagination types are in `src/Pagination/`, paginators live in `src/Paginator/`, and Symfony integration sits in `src/DependencyInjection/` and `src/Twig/`. Bundle resources (services, templates, translations) are in `src/Resources/` with Twig views in `src/Resources/views/` and translations in `src/Resources/translations/`. Tests are organized under `tests/` with `tests/Unit/` and `tests/Integrational/`.

## Build, Test, and Development Commands
Install dependencies with Composer:
`composer install`

Run the test suite (PHPUnit):
`composer test`

Direct invocation is also acceptable:
`vendor/bin/phpunit`

## Coding Style & Naming Conventions
Follow PSR-12 formatting with 4-space indentation. Use PSR-4 autoloading conventions, matching namespaces to folder paths (e.g., `src/Pagination/Pagination.php` => `ChamberOrchestra\PaginationBundle\Pagination\Pagination`). Use `StudlyCaps` for classes, `CamelCase` for methods, and keep interface and trait names suffixed with `Interface` and `Trait` (see `src/Pagination/PaginationInterface.php` and `src/PaginationTrait.php`).

## Testing Guidelines
Testing uses PHPUnit. Place unit tests in `tests/Unit/` and integration tests in `tests/Integrational/`. Name test classes with a `*Test.php` suffix. Run the suite with `composer test` before opening a pull request. No coverage threshold is currently enforced.

## Commit & Pull Request Guidelines
There is no commit history yet, so no established convention. Use concise, imperative commit messages (e.g., “Add pagination range type”). For pull requests, include a clear description, the motivation, and any relevant test results. If you touch Twig templates or translations, mention the affected files explicitly.

## Release & Tagging
No release process is defined yet. When you introduce one, document the versioning scheme (e.g., SemVer), the tag format (e.g., `v8.0.1`), and the steps to publish a release. Include any required changelog updates and whether tags should be signed.

## Changelog
No changelog is tracked yet. If you add one, place it at `CHANGELOG.md` and keep entries grouped by version with a short list of notable changes. Link the changelog in release notes once tagging is in place.

## Configuration & Symfony Integration Notes
Symfony service wiring is in `src/Resources/config/services.yaml`. Twig pagination templates live in `src/Resources/views/`, and can be overridden by consumers. Translation keys are defined in `src/Resources/translations/`.
