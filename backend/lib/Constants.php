<?php
declare(strict_types=1);

namespace SlashApp;

class Constants
{
	private static $config;

	public static function init()
	{
		self::$config = DistConfigLoader::create()
			->addSubst('BASE_DIR', __DIR__ . '/..')
			->loadYamlWithDist('slashapp.yaml', true)
			->getConfig();
	}

	public static function getStaticBasePath()
	{
		return self::$config['static']['base_path'];
	}

	public static function getStaticActualDirectory()
	{
		return self::$config['static']['directory'];
	}

	public static function getConfig()
	{
		return self::$config;
	}
}

Constants::init();
