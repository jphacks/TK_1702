<?php
declare(strict_types=1);

use Propel\Generator\Model\Table;
use Propel\Generator\Reverse\MysqlSchemaParser;

class GeoMysqlSchemaParser extends MysqlSchemaParser
{
	function addIndexes(Table $table)
	{
		parent::addIndexes($table);
		foreach ($table->getIndices() as $index) {
			$vi = $index->getVendorInfoForType('mysql');
			$index_type = $vi->getParameter('Index_type');
			if ($index_type === 'SPATIAL') {
				// SPATIALなカラムにはsub-size 32が指定される？が、
				// インデックス作成時には対応していないもよう
				// そのせいでschema.xmlにはsize指定できず、migrationで延々とdrop/create indexするので、
				// SPATIALカラムに対してはsub-sizeを削除することで対応する。
				$index->resetColumnsSize();
			}
		}
	}
}
