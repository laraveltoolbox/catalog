<?php

declare(strict_types=1);

use Catalog\Catalog;

/**
 * The catalog as the export renders it, built once for the whole suite.
 *
 * @return array{category_groups: list<array<string, mixed>>}
 */
function catalog(): array
{
    static $catalog;

    return $catalog ??= (new Catalog)->toArray();
}

/**
 * Every category in the catalog, flattened out of its group.
 *
 * @return list<array<string, mixed>>
 */
function categories(): array
{
    return array_merge(...array_column(catalog()['category_groups'], 'categories'));
}

/**
 * Every package the catalog references, lowercased and deduplicated, the way
 * packagist writes its own names.
 *
 * @return list<string>
 */
function referencedPackages(): array
{
    $packages = array_map(
        strtolower(...),
        array_merge(...array_column(categories(), 'projects')),
    );

    $packages = array_unique($packages);
    sort($packages);

    return array_values($packages);
}
