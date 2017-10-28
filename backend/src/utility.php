<?php
declare(strict_types=1);

use Propel\Runtime\Collection\ObjectCollection;
use Psr\Http\Message\ServerRequestInterface;

function h(string $str): string
{
	return htmlspecialchars($str, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function starts_with(string $str, string $prefix): bool
{
	return strpos($str, $prefix) === 0;
}

function ends_with(string $text, string $suffix): bool
{
	return $suffix === substr($text, -strlen($suffix));
}

function is_api_endpoint(ServerRequestInterface $request): bool
{
	return !!preg_match('@/?api/@', $request->getUri()->getPath());
}

function format_bytes($size, $precision = 2)
{
	$base = log($size, 1024);
	$suffixes = ['Bytes', 'KiB', 'MiB', 'GiB'];

	$floored_base = (int)floor($base);
	return number_format(pow(1024, $base - $floored_base), $precision) . $suffixes[$floored_base];
}

function transaction(callable $callback)
{
	return \Propel\Runtime\Propel::getWriteConnection(\ORM\Map\UserTableMap::DATABASE_NAME)
		->transaction($callback);
}

function format_as_api($data)
{
	if (is_array($data) || $data instanceof ObjectCollection) {
		$arr = [];
		foreach ($data as $element) {
			$arr[] = format_as_api($element);
		}
		return $arr;
	} else if ($data instanceof DateTimeInterface) {
		return $data->format(DateTime::RFC3339);
	} else if (is_object($data) && method_exists($data, 'formatAsApi')) {
		return $data->formatAsApi();
	} else {
		return $data;
	}
}
