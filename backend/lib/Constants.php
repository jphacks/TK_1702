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

	public static function getUrlBase()
	{
		return self::$config['static']['url'];
	}

	public static function getLineToken()
    {
        return self::$config['line']['token'];
    }

    public static function getLineChannelSecret()
    {
        return self::$config['line']['secret'];
    }

    public static function getLineReceiverId()
    {
        return self::$config['line']['receiver_id'];
    }

	public static function getFcmAuthorization()
	{
		return self::$config['fcm']['authorization'];
	}
}

Constants::init();
