<?php

declare(strict_types=1);

namespace Catalog;

use JsonException;
use Symfony\Component\Yaml\Yaml;

/**
 * Reads the catalog sources and renders the export the site consumes.
 *
 * The sources are the YAML files under catalog/: one directory per category
 * group, holding a _meta.yml for the group itself and one file per category.
 * Nothing here is written by hand — build/catalog.json is generated from them
 * and published to GitHub Pages, which is where The Laravel Toolbox reads it.
 *
 * Ported from lib/catalog.rb, which the upstream Ruby project wrote. The
 * output is matched byte for byte: the key order, the two-space indentation,
 * unescaped slashes and the trailing newline are all what Ruby's
 * JSON.pretty_generate produced, so replacing the build does not rewrite the
 * published file.
 */
final class Catalog
{
    private readonly string $root;

    public function __construct(?string $root = null)
    {
        $this->root = $root ?? __DIR__.'/../catalog';
    }

    /**
     * @return array{category_groups: list<array<string, mixed>>}
     */
    public function toArray(): array
    {
        return ['category_groups' => $this->categoryGroups()];
    }

    /**
     * @throws JsonException
     */
    public function toJson(): string
    {
        $json = json_encode(
            $this->toArray(),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
        );

        // PHP indents with four spaces and Ruby with two. Structural
        // whitespace is the only whitespace at the start of a line — a newline
        // inside a string is encoded as \n — so halving it is safe.
        $json = preg_replace_callback(
            '/^ +/m',
            static fn (array $matches): string => str_repeat(' ', (int) (strlen($matches[0]) / 2)),
            $json,
        );

        // `File#puts` ended the file with a newline.
        return $json."\n";
    }

    /**
     * @throws JsonException
     */
    public function export(?string $path = null): string
    {
        $path ??= __DIR__.'/../build/catalog.json';

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        file_put_contents($path, $this->toJson());

        return $path;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function categoryGroups(): array
    {
        $groups = [];

        // glob() sorts, as Dir.glob does, so the groups come out in the same
        // order on any machine.
        foreach (glob($this->root.'/*', GLOB_ONLYDIR) ?: [] as $path) {
            $meta = Yaml::parseFile($path.'/_meta.yml');

            $groups[] = [
                'categories' => $this->categoriesAt($path),
                'description' => $meta['description'] ?? null,
                'name' => $meta['name'] ?? null,
                'permalink' => basename($path),
            ];
        }

        return $groups;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function categoriesAt(string $path): array
    {
        $categories = [];

        foreach (glob($path.'/*.yml') ?: [] as $file) {
            if (basename($file) === '_meta.yml') {
                continue;
            }

            // The union keeps the keys the file declares in the order it
            // declares them and appends the permalink, which is what merging
            // onto the parsed hash did.
            $categories[] = Yaml::parseFile($file) + ['permalink' => basename($file, '.yml')];
        }

        return $categories;
    }

    /**
     * The JSON schema the export is validated against.
     *
     * @return array<string, mixed>
     */
    public static function schema(): array
    {
        return Yaml::parseFile(__DIR__.'/../json-schema.yml');
    }
}
