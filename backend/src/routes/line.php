<?php
declare(strict_types=1);

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SlashApp\JsonRenderer;
use SlashApp\Slim\Middleware\AuthMiddleware;

$app->post('/line', function (ServerRequestInterface $request, ResponseInterface $response, $args) {
    $data = $request->getParsedBody();

    $groupId = $data['events'][0]['source']['groupId'] ?? null;
    if($groupId) {
        file_put_contents("ids", $groupId);
    }

    return JsonRenderer::create()->render($response, []);
})->setArgument(AuthMiddleware::ROUTE_ARG_SKIP, true);

$app->get('/line', function (ServerRequestInterface $request, ResponseInterface $response, $args) {
    return JsonRenderer::create()->render($response, []);
})->setArgument(AuthMiddleware::ROUTE_ARG_SKIP, true);
