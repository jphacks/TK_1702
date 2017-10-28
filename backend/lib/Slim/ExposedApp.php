<?php
declare(strict_types=1);

namespace Chat\Slim;


use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Interfaces\RouterInterface;

class ExposedApp extends App
{
	public function dispatchRouterAndPrepareRoute(ServerRequestInterface $request, RouterInterface $router)
	{
		return parent::dispatchRouterAndPrepareRoute($request, $router);
	}
}
