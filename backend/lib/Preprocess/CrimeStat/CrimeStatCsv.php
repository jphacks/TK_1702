<?php
declare(strict_types=1);

namespace SlashApp\Preprocess\CrimeStat;


use ORM\AreaQuery;
use ORM\CrimeDangerArea;
use SlashApp\Preprocess\CityNameSafeNormalizer;

class CrimeStatCsv
{
	public function load(string $filename, \DateTime $date)
	{
		$spec = "php://filter/read=convert.iconv.cp932%2Futf-8/resource=$filename";
		$file = new \SplFileObject($spec, 'rb');
		$file->setFlags(
			\SplFileObject::READ_CSV |
			\SplFileObject::SKIP_EMPTY |
			\SplFileObject::READ_AHEAD |
			\SplFileObject::DROP_NEW_LINE
		);
		transaction(function () use ($date, $file) {
			foreach ($file as $i => $row) {
				if ($i === 0) {
					$hittakuri = array_search('非侵入窃盗_ひったくり', $row);
					if ($hittakuri === false) {
						throw new \Exception('非侵入窃盗_ひったくり is not found...？');
					}
					continue;
				}
				$address = '東京都' . CityNameSafeNormalizer::normalize($row[0]);
				/** @noinspection PhpUndefinedVariableInspection */
				$crime_count = $row[$hittakuri];
				$area_id = AreaQuery::create()
					->filterByFriendlyName($address)
					->select('AreaId')
					->findOne();
				if ($area_id === null) {
//					fprintf(STDERR, "unknown area: %s\n", $address);
					continue;
				}
				for ($j = 0; $j < $crime_count; $j++) {
					$crime = new CrimeDangerArea();
					$crime
						->setAreaId($area_id)
						->setDate($date)
						->setReason('ひったくり')
						->save();
				}
			}
		});
	}
}
