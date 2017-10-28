<?php
declare(strict_types=1);

namespace SlashApp\Slim\Middleware;


use ORM\User;
use ORM\UserQuery;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SlashApp\JsonRenderer;
use Slim\Interfaces\RouteInterface;

class AuthMiddleware
{
	const ROUTE_ARG_SKIP = 'auth.skip';

	public function __invoke(
		ServerRequestInterface $request,
		ResponseInterface $response,
		callable $next
	): ResponseInterface {
		$udid = $request->getHeaderLine('X-UDID');
		$route = $this->getRoute($request);
		$auth_skipped = !$route || $route->getArgument(self::ROUTE_ARG_SKIP);

		if (!$auth_skipped) {
			if (empty($udid)) {
				return JsonRenderer::create()->renderAsError($response, 'empty udid', 403);
			}
			$user = transaction(function () use ($udid) {
				$user = UserQuery::create()
					->filterByUdid($udid)
					->findOne();
				if ($user === null) {
					$user = new User();
					$user
						->setUdid($udid)
						->save();
				}
				return $user;
			});
			$request = $request->withAttribute('user', $user);
		}

		return $next($request, $response);
	}

	public function getRoute(ServerRequestInterface $request):?RouteInterface
	{
		return $request->getAttribute('route');
	}
}
