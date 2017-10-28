<?php
declare(strict_types=1);

namespace Chat;

use Cascade\Cascade;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Monolog\Registry;
use ORM\Map\UserTableMap;
use Propel\Runtime\Propel;

class LoggerProvider
{
	/** @var Logger */
	private static $base_logger;
	private static $uid;
	private static $initialized = false;

	public static function init()
	{
		if (self::$initialized) {
			return;
		}
		self::$initialized = true;
		$config_loader = DistConfigLoader::create()
			->loadYamlWithDist('logger.yaml')
			->loadYamlWithDist('logger-dev.yaml', false);
		if (defined('CH_TEST')) {
			$config_loader->loadYamlWithDist('logger-test.yaml', false);
		}
		Cascade::fileConfig($config_loader->getConfig());
		$logger = Cascade::getLogger('__base__');

		$uid_processor = null;
		foreach ($logger->getProcessors() as $processor) {
			if ($processor instanceof UidProcessor) {
				$uid_processor = $processor;
				break;
			}
		}
		if ($uid_processor === null) {
			$uid_processor = new UidProcessor();
			$logger->pushProcessor($uid_processor);
		}

		$propel_logger = Cascade::getLogger('propel');
		Propel::getServiceContainer()->setLogger('defaultLogger', $propel_logger);
		if (IS_DEVELOPMENT) {
			$con = Propel::getWriteConnection(UserTableMap::DATABASE_NAME);
			$con->useDebug(true);
		}

		self::$base_logger = $logger;
		self::$uid = $uid_processor->getUid();
	}

	protected static function getBaseConfig()
	{
		return yaml_parse_file(__DIR__ . '/../config/logger.yaml.dist');
	}

	public static function getLogger($name): Logger
	{
		if (Registry::hasLogger($name)) {
			return Registry::getInstance($name);
		} else {
			return self::$base_logger->withName($name);
		}
	}

	public static function getUid()
	{
		return self::$uid;
	}
}
