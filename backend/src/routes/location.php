<?php
declare(strict_types=1);

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SlashApp\JsonRenderer;

$app->post('/location', function (ServerRequestInterface $request, ResponseInterface $response, $args) {
	$data = $request->getParsedBody();
	/** @var \ORM\User $user */
	$user = $request->getAttribute('user');
	$lat = $data['latitude'] ?? null;
	$long = $data['longitude'] ?? null;

	if (!is_double($lat) || !is_double($long)) {
		return JsonRenderer::create()->renderAsError($response, 'invalid or missing latitude/longitude', 400);
	}

	$location = new \ORM\LocationHistory();
	$location
		->setUserId($user->getUserId())
		->setLocation(\SlashApp\Propel\GeometryType::getBinaryPoint($lat, $long))
		->save();

	$matched_area = \ORM\AreaQuery::create()
		->where(\SlashApp\Propel\GeometryType::mbrContainsWhereClause($lat, $long, 'Area.AreaPolygon'))
		->select('AreaId')
		->find();

	$danger = \ORM\CrimeDangerAreaQuery::create()
		->filterByAreaId($matched_area->toArray())
//		->filterByDate(time() - 12 * 28 * 24 * 60 * 60, \ORM\CrimeDangerAreaQuery::GREATER_EQUAL)
		->joinWithArea()
		->findOne();

	if ($danger !== null) {
		return JsonRenderer::create()->render($response, [
			'status' => 'warn',
			'message' => sprintf('%s で %s', $danger->getArea()->getFriendlyName(), $danger->getReason())
		]);
	} else {
		return JsonRenderer::create()->render($response, ['status' => 'none']);
	}
});
