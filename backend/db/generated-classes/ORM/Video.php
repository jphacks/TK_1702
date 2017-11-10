<?php

namespace ORM;

use ORM\Base\Video as BaseVideo;
use SlashApp\Constants;
use SlashApp\Propel\GeometryType;

/**
 * Skeleton subclass for representing a row from the 'video' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 */
class Video extends BaseVideo
{
	public function formatAsApi()
	{
		$lat = null;
		$long = null;
		$friendly_name = null;
		if ($this->hasVirtualColumn('latitude')) {
			$lat = $this->getVirtualColumn('latitude');
			$long = $this->getVirtualColumn('longitude');
			$matched_area = AreaQuery::create()
				->where(GeometryType::mbrContainsWhereClause($lat, $long, 'Area.AreaPolygon'))
				->findOne();
			if ($friendly_name) {
				$friendly_name = $matched_area->getFriendlyName();
			}
		}

		return [
			"created_at" => $this->getCreatedAt()->getTimestamp(),
			"owner_id" => $this->getOwnerId(),
			"file_name" => Constants::getStaticBasePath() . '/video/' . $this->getFileName(),
			"thumb_name" => Constants::getStaticBasePath() . '/thumb/' . $this->getThumbName(),
			"latitude" => $lat,
			"long" => $long,
			"location" => $friendly_name,
		];
	}
}
