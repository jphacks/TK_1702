<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

if ($argc !== 2) {
	throw new Exception('specify filename');
}
(new \SlashApp\Preprocess\CityToGeo\CityToGeo())->load($argv[1]);
