<?php
declare(strict_types=1);

namespace SlashApp;

class DistConfigLoader
{
	/** @var string */
	const CONFIG_BASE_DIR = __DIR__ . '/../config';
	/** @var array */
	protected $config = [];
	/** @var array */
	protected $subst = [];

	public static function create(): DistConfigLoader
	{
		return new DistConfigLoader();
	}

	public function loadYamlWithDist(string $filename, bool $required = true): DistConfigLoader
	{
		$distfile = $filename . '.dist';
		return $this
			->loadYaml($distfile, $required)
			->loadYaml($filename, false);
	}

	public function loadYaml(string $filename, bool $required = true): DistConfigLoader
	{
		$filepath = self::CONFIG_BASE_DIR . '/' . $filename;
		if (!file_exists($filepath)) {
			if ($required) {
				throw new \RuntimeException('required config file "' . $filepath . '" is not found');
			} else {
				return $this;
			}
		}
		$result = yaml_parse_file($filepath, 0, $ndocs, [
			'subst' => function ($value, $tag, $flags) {
				return $this->handleSubst($value);
			}
		]);
		if (!is_array($result)) {
			throw new \RuntimeException('config file "' . $filepath . '" cannot load as yaml or array missing');
		}

		$this->config = $this->mergeConfig($this->config, $result);
		return $this;
	}

	public function getConfig(): array
	{
		return $this->config;
	}

	function mergeConfig($array, $array1): array
	{
		foreach ($array1 as $key => $value) {
			// create new key in $array, if it is empty or not an array
			if (!isset($array[$key]) || (isset($array[$key]) && !is_array($array[$key]))) {
				$array[$key] = array();
			}

			// overwrite the value in the base array
			if (is_array($value)) {
				$value = $this->mergeConfig($array[$key], $value);
			} elseif ($value === '!unset') {
				unset($array[$key]);
				continue;
			}
			$array[$key] = $value;
		}
		return $array;
	}

	public function addSubst($key, $value): DistConfigLoader
	{
		$this->subst['%' . $key . '%'] = $value;
		return $this;
	}

	public function handleSubst($value)
	{
		return str_replace(array_keys($this->subst), array_values($this->subst), $value);
	}
}
