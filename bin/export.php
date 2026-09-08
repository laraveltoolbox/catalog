#!/usr/bin/env php
<?php

declare(strict_types=1);

use Catalog\Catalog;

require __DIR__.'/../vendor/autoload.php';

$path = (new Catalog)->export($argv[1] ?? null);

echo 'Exported '.$path.PHP_EOL;
