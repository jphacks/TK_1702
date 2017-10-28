<?php
declare(strict_types=1);

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SlashApp\JsonRenderer;
use SlashApp\Slim\Middleware\AuthMiddleware;

$app->post('/ping', function (ServerRequestInterface $request, ResponseInterface $response, $args) {
	$body = $request->getParsedBody();
	$received = $body['message'] ?? null;
	if ($received === 'ping') {
		return JsonRenderer::create()->render($response, ['message' => 'pong']);
	} else {
		return JsonRenderer::create()->renderAsError($response, 'message must be ping!', 400);
	}
})->setArgument(AuthMiddleware::ROUTE_ARG_SKIP, true);
