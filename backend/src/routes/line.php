<?php
declare(strict_types=1);

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SlashApp\JsonRenderer;
use SlashApp\Slim\Middleware\AuthMiddleware;

$app->post('/line', function (ServerRequestInterface $request, ResponseInterface $response, $args) {
    $data = $request->getParsedBody();

    $groupId = $data['events'][0]['source']['groupId'] ?? null;
    $message = $data['events'][0]['message']['text'} ?? null;
    if($groupId) {
        file_put_contents("ids", $groupId);
    }

    if($message && substr($message, 0, 6) === "dWRpZD") {
        $udid = explode(":", base64_decode($message))[1];
        $user = \ORM\UserQuery::create()
            ->findOneByUdid($udid);
        if($user !== null) {
            $user
                ->setLineId($groupId)
                ->save();
        }
    }

    return JsonRenderer::create()->render($response, []);
})->setArgument(AuthMiddleware::ROUTE_ARG_SKIP, true);

$app->get('/line', function (ServerRequestInterface $request, ResponseInterface $response, $args) {
    return JsonRenderer::create()->render($response, []);
})->setArgument(AuthMiddleware::ROUTE_ARG_SKIP, true);
