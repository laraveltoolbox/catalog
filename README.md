> **This is a fork.** It is a copy of [rubytoolbox/catalog](https://github.com/rubytoolbox/catalog)
> (MIT), operated independently at [laravel-toolbox.com](https://www.laravel-toolbox.com) and **not**
> affiliated with or endorsed by The Ruby Toolbox. See [NOTICE](./NOTICE) for details.
> Please report issues with the upstream project upstream, not here.

# Laravel Toolbox Catalog [![CI](https://github.com/laraveltoolbox/catalog/actions/workflows/ci.yml/badge.svg)](https://github.com/laraveltoolbox/catalog/actions/workflows/ci.yml)

Welcome to the [Laravel Toolbox][laraveltoolbox] catalog!

This repository contains the mapping of category groups, categories and Composer
packages for Laravel. Its initial categorization is derived from the
[Laravel Package Ocean][package-ocean] dataset (MIT, see [NOTICE](./NOTICE)) and is
curated further here.

You can find the current exported catalog at https://laraveltoolbox.github.io/catalog

## Catalog guidelines

* Our general policy about unmaintained libraries is to keep them in the catalog,
  even if a maintained fork exists. The rationale behind this is that even if a library
  may be unmaintained, it could still be functional or at least useful when researching
  options and maybe the original code can still provide some ideas for potential future maintainers
  or new approaches to the same problem.
* Categories should have at least 2 entries - if you cannot find an existing category
  for a library, that's fine, feel free to add one, but please find at least one other
  package that tries to solve the same problem

## Contributing

**Help wanted!** Feel free to send pull requests against this repo to add or
moderate existing categories.

If you plan on bigger changes, please consider:

* splitting your changes into multiple separate PRs to avoid merge conflicts
* if your changes could need discussion, please create an
  [issue on the main repo][laraveltoolbox] up-front for further discussion.

## Structure

You can find the catalog in [catalog](./catalog). The structure is validated at build time against the [JSON schema](./json-schema.yml).

The folder structure is as follows:

```
catalog/
  CATEGORY_GROUP_1_PERMALINK/
    _meta.yml # Allows to add metadata about category group
    category_1_permalink.yml # Definition of category and its projects
    ...
  ...
```

Each category group contains a `_meta.yml`, which defines the `name` key (the human
display name of the group) and an optional `description`.

Each `category.yml` currently contains:

* `name` (string, required): Human display name of the category name
* `description` (string, optional): A (markdown-formatted) category description
* `projects` (array of strings, required): The list of projects to list in
  that category. This is the full Composer package name including its vendor
  prefix, e.g. `spatie/laravel-permission`. Packages can be listed in multiple
  categories.

The test suite verifies that every referenced package actually exists on
[packagist.org](https://packagist.org), so a typo or a renamed package fails the
build rather than silently producing an empty entry on the site.

---

[laraveltoolbox]: https://www.github.com/laraveltoolbox/laraveltoolbox
[package-ocean]: https://github.com/HassanZahirnia/laravel-package-ocean
