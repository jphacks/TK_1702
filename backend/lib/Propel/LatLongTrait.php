<?php
declare(strict_types=1);

namespace SlashApp\Propel;


use Propel\Runtime\ActiveQuery\ModelCriteria;

trait LatLongTrait
{
	protected abstract function getGeometryColumnName(): string;

	/**
	 * @param string $clause
	 * @param string $name
	 * @return $this|ModelCriteria
	 */
	public abstract function withColumn($clause, $name = null);

	public function withLatLong($lat_name = 'latitude', $long_name = 'longitude')
	{
		$geo_column_name = $this->getGeometryColumnName();
		return $this
			->withColumn(sprintf('X(%s)', $geo_column_name), $long_name)
			->withColumn(sprintf('Y(%s)', $geo_column_name), $lat_name);
	}
}
