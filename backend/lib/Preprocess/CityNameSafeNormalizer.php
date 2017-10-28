<?php
declare(strict_types=1);

namespace SlashApp\Preprocess;


class CityNameSafeNormalizer
{
	protected const NORMALIZE_FROM = [
		'1',
		'2',
		'3',
		'4',
		'5',
		'6',
		'7',
		'8',
		'9',
		'１',
		'２',
		'３',
		'４',
		'５',
		'６',
		'７',
		'８',
		'９',
	];

	protected const NORMALOIZE_TO = [
		'一',
		'二',
		'三',
		'四',
		'五',
		'六',
		'七',
		'八',
		'九',
		'一',
		'二',
		'三',
		'四',
		'五',
		'六',
		'七',
		'八',
		'九',
	];

	public static function normalize($text)
	{
		return str_replace(self::NORMALIZE_FROM, self::NORMALOIZE_TO, $text);
	}
}
