<?php
declare(strict_types=1);

namespace Chat;


class ExceptionErrorHandler
{
	private static $registered;

	public static function register()
	{
		if (!self::$registered) {
			self::$registered = true;
			set_error_handler([new ExceptionErrorHandler(), 'handle']);
		}
	}

	public function handle($severity, $message, $file, $line)
	{
		if (error_reporting() & $severity) {
			throw new \ErrorException($message, 0, $severity, $file, $line);
		}
	}
}
