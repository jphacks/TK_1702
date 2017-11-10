<?php

namespace ORM;

use ORM\Base\VideoQuery as BaseVideoQuery;
use SlashApp\Propel\LatLongTrait;

/**
 * Skeleton subclass for performing query and update operations on the 'video' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 */
class VideoQuery extends BaseVideoQuery
{
	use LatLongTrait;

	protected function getGeometryColumnName(): string
	{
		return 'Video.Location';
	}
}
