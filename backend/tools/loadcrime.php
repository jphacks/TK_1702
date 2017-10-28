<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

if ($argc !== 3) {
	throw new Exception('specify filename and date');
}

(new \SlashApp\Preprocess\CrimeStat\CrimeStatCsv())->load($argv[1], new DateTime($argv[2]));
