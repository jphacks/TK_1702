<?php
declare(strict_types=1);

namespace SlashApp\Propel;


use Propel\Runtime\Propel;

class GeometryType
{
	const SRID = 0;

	/**
	 * @param string|resource|null $v if string, $v is WKT style geolocation string
	 * @return null|resource|\ORM\Base\GEOMETRY
	 * @throws \Exception if $v is invalid wkt style string
	 */
	public static function getBinaryLocation($v)
	{
		// FIXME: ここでsqlを投げるのは無駄
		// mysqlのgeometry型はwkt/wkb形式ではなく独自のものなので、wkt/wkbから独自のものに変換しなくてはならない？(要出典)
		// php側でその変換をやるのは厳しいみたい？geoPHPは対応していないよう。
		// じゃあmysql側で、となると思うが、そうなると、insert/updateのsqlをいじる必要が出てきます。
		// update側はbuildCriteriaをオーバーライドすればST_GeomFromText()付きにするのは簡単そう。
		// insert側は#doInsertをいじればできそうだが、生成されるsqlを編集できるようなフックが存在せず、
		// 本文もPropel\Generator\Builder\Om\ObjectBuilder#addDoInsertBodyRawで作成しているようだが、やはりどこにも
		// behaviorの類で編集できるような場所がない模様。
		// かといって、doInsertをハードコーディングするのはテーブル構造が変わったときに危険だし、
		// behaviorで弄るにしても http://propelorm.org/documentation/06-behaviors.html#replacing-or-removing-existing-methods
		// とちょっとめんどくさそうなので、ここでsqlを投げるようにして、その返り値を設定するようにしてあります。
		//
		// locationをNULL-ableにすれば、postInsertの中でupdateを投げて、もできると思うのですが、
		// spatial indexはnot nullを強要するので、残念です。
		if (is_string($v)) {
			var_dump($v);
			$stmt = Propel::getConnection()->prepare('SELECT ST_GeomFromText(:wkt, :srid) AS a');
			$stmt->bindValue(':wkt', $v, \PDO::PARAM_STR);
			$stmt->bindValue(':srid', self::SRID, \PDO::PARAM_INT);
			if (!$stmt->execute()) {
				throw new \Exception($stmt->errorCode());
			}
			$res = $stmt->fetch(\PDO::FETCH_NUM);
			$v = $res[0];
			if ($v === null) {
				throw new \InvalidArgumentException("invalid wkt type string");
			}
		}
		return $v;
	}

}
