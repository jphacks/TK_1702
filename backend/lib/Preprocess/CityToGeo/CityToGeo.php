<?php
declare(strict_types=1);

namespace SlashApp\Preprocess\CityToGeo;


use ORM\Area;
use ORM\AreaQuery;
use SlashApp\Propel\GeometryType;

class CityToGeo
{
	public function load(string $filename)
	{
		$xml = simplexml_load_string(file_get_contents($filename));
		transaction(function () use ($xml) {
			foreach ($xml->xpath('gml:featureMember') as $member) {
				/** @var \SimpleXMLElement $member */
				foreach ($member->children('fme', true) as $wrapper) {
					/** @var \SimpleXMLElement $wrapper */
					$ken = $this->getFirst($wrapper->xpath('fme:KEN_NAME'));
					$city = $this->getFirst($wrapper->xpath('fme:GST_NAME'));
					$sub = $this->getFirst($wrapper->xpath('fme:MOJI'));
					$pos = $this->getFirst($wrapper->xpath('gml:surfaceProperty/gml:Surface/gml:patches/gml:PolygonPatch/gml:exterior/gml:LinearRing/gml:posList'));
					$tobi = $this->getFirst($wrapper->xpath('fme:KIGO_D'));

					if ($tobi !== '') {
						continue;
					}

					$friendly = $ken . $city . $sub;
					$latlng = explode(' ', $pos);
					$edge = [];
					for ($i = 0; $i < count($latlng); $i += 2) {
						$edge[] = sprintf('%s %s', $latlng[$i + 1], $latlng[$i]);
					}
					var_dump($friendly);
					AreaQuery::create()
						->filterByFriendlyName($friendly)
						->delete();
					$area = new Area();
					$area
						->setAreaPolygon(GeometryType::getBinaryLocation(sprintf("Polygon((%s))", implode(',', $edge))))
						->setFriendlyName($friendly)
						->save();
				}
			}
		});
	}

	protected function getFirst($element)
	{
		foreach ($element as $child) {
			return trim((string)$child);
		}
		return null;
	}
}
