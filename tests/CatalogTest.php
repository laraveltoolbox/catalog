<?php

declare(strict_types=1);

use Catalog\Catalog;
use JsonSchema\Validator;

/**
 * Port of spec/catalog_spec.rb.
 *
 * The point of this suite is that a pull request cannot publish a broken
 * catalog: the export has to match the schema, a category may not list the
 * same package twice, and every package named here has to exist on packagist.
 */
it('renders the catalog as an array', function () {
    expect(catalog())->toBeArray()
        ->and(catalog()['category_groups'])->not->toBeEmpty();
});

it('writes the export to a file', function () {
    $path = tempnam(sys_get_temp_dir(), 'catalog').'.json';

    expect(file_exists($path))->toBeFalse();

    (new Catalog)->export($path);

    expect(file_get_contents($path))->toBe((new Catalog)->toJson());

    unlink($path);
});

it('validates against the schema', function () {
    // The validator wants objects rather than associative arrays, and the
    // schema is authored in YAML for the same reason the catalog is: it is
    // read by people more often than by machines.
    $data = json_decode(json_encode(catalog(), JSON_THROW_ON_ERROR), flags: JSON_THROW_ON_ERROR);
    $schema = json_decode(json_encode(Catalog::schema(), JSON_THROW_ON_ERROR), flags: JSON_THROW_ON_ERROR);

    $validator = new Validator;
    $validator->validate($data, $schema);

    $errors = array_map(
        static fn (array $error): string => "{$error['property']}: {$error['message']}",
        $validator->getErrors(),
    );

    expect($errors)->toBe([]);
});

it('lists no package twice within a category', function () {
    $duplicates = [];

    foreach (categories() as $category) {
        $repeated = array_diff_assoc($category['projects'], array_unique($category['projects']));

        foreach ($repeated as $package) {
            $duplicates[] = "{$category['permalink']}: {$package}";
        }
    }

    expect($duplicates)->toBe([]);
});

it('references packages by their vendor-prefixed composer name', function () {
    $malformed = array_values(array_filter(
        referencedPackages(),
        static fn (string $package): bool => substr_count($package, '/') !== 1,
    ));

    expect($malformed)->toBe([]);
});

it('references only packages that exist on packagist', function () {
    // The full list of names published on packagist, about 12 MB of JSON.
    $response = @file_get_contents('https://packagist.org/packages/list.json');

    if ($response === false) {
        test()->markTestSkipped('packagist could not be reached');
    }

    $available = array_flip(array_map(
        strtolower(...),
        json_decode($response, true, flags: JSON_THROW_ON_ERROR)['packageNames'],
    ));

    $missing = array_values(array_filter(
        referencedPackages(),
        static fn (string $package): bool => ! isset($available[$package]),
    ));

    expect($missing)->toBe([]);
});
